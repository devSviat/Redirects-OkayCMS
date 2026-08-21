<?php

namespace Okay\Modules\Sviat\Redirects\Helpers;

use Okay\Core\EntityFactory;
use Okay\Core\Router;
use Okay\Entities\ProductsEntity;
use Okay\Entities\VariantsEntity;
use Okay\Modules\Sviat\Redirects\Entities\RedirectsEntity;

class RedirectsReincarnationHelper
{
    public const SCAN_INTERVAL_SECONDS = 43200;

    private const BATCH_SIZE = 500;
    private const SCHEDULER_POLL_SECONDS = 900;

    private EntityFactory $entityFactory;
    private ?RedirectsEntity $redirectsEntity = null;

    public function __construct(EntityFactory $entityFactory)
    {
        $this->entityFactory = $entityFactory;
    }

    public function runScheduledScan(): void
    {
        $this->scan(true);
    }

    /**
     * Reserve fallback when the system scheduler is unavailable.
     * The database is not modified: scan state is stored in a runtime JSON file.
     */
    public function scheduleIfDue(): void
    {
        if (!$this->claimSchedulerPollWindow() || !$this->isScanDue()) {
            return;
        }

        register_shutdown_function(function (): void {
            if (function_exists('fastcgi_finish_request')) {
                @fastcgi_finish_request();
            }

            try {
                $this->scan(false);
            } catch (\Throwable $e) {
                // A background check must never break a storefront request.
            }
        });
    }

    public function scan(bool $force = true): array
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        if (function_exists('ignore_user_abort')) {
            @ignore_user_abort(true);
        }

        $lock = $this->acquireScanLock();
        if ($lock === null) {
            return $this->buildStatusReport('busy');
        }

        try {
            if (!$force && !$this->isScanDue()) {
                return $this->buildStatusReport('not_due');
            }

            $redirectRows = $this->collectExactRedirects();
            $redirectIds = [];
            $sourceMap = [];

            foreach ($redirectRows as $redirect) {
                $redirectId = (int) ($redirect->id ?? 0);
                $source = $this->normalizeComparablePath((string) ($redirect->from_url ?? ''));
                if ($redirectId <= 0) {
                    continue;
                }

                $redirectIds[] = $redirectId;
                if ($source !== '') {
                    $sourceMap[$source][] = $redirectId;
                }
            }

            $matches = [];
            $productsChecked = 0;
            if (!empty($sourceMap)) {
                [$matches, $productsChecked] = $this->findExistingProductsByRedirectSource($sourceMap);
            }

            $checkedAt = date('Y-m-d H:i:s');
            $stateMatches = [];
            foreach ($matches as $redirectId => $match) {
                $stateMatches[(string) (int) $redirectId] = [
                    'product_id' => (int) ($match['product_id'] ?? 0),
                    'source' => (string) ($match['source'] ?? ''),
                ];
            }

            $this->writeState([
                'checked_at' => $checkedAt,
                'redirects_checked' => count($redirectIds),
                'products_checked' => $productsChecked,
                'matches' => $stateMatches,
            ]);

            return [
                'status' => 'done',
                'found' => count($stateMatches),
                'redirects_checked' => count($redirectIds),
                'products_checked' => $productsChecked,
                'checked_at' => $checkedAt,
            ];
        } finally {
            if (is_resource($lock)) {
                $this->releaseLock($lock);
            }
        }
    }

    /**
     * @return array<int, array{product_id:int, sku:string}> redirect ID => product data
     */
    public function getMatchDetails(): array
    {
        $state = $this->getValidatedState();
        $productIds = [];

        foreach ($state['matches'] as $match) {
            $productId = (int) ($match['product_id'] ?? 0);
            if ($productId > 0) {
                $productIds[] = $productId;
            }
        }

        $productSkus = $this->findProductSkus($productIds);
        $result = [];

        foreach ($state['matches'] as $redirectId => $match) {
            $redirectId = (int) $redirectId;
            $productId = (int) ($match['product_id'] ?? 0);
            if ($redirectId <= 0 || $productId <= 0) {
                continue;
            }

            $result[$redirectId] = [
                'product_id' => $productId,
                'sku' => (string) ($productSkus[$productId] ?? ''),
            ];
        }

        return $result;
    }

    /**
     * Kept for compatibility with code that only needs the matched product ID.
     *
     * @return array<int, int> redirect ID => product ID
     */
    public function getMatches(): array
    {
        $result = [];
        foreach ($this->getMatchDetails() as $redirectId => $match) {
            $result[(int) $redirectId] = (int) $match['product_id'];
        }

        return array_filter($result);
    }

    public function getStatus(): array
    {
        $state = $this->getValidatedState();

        return [
            'found' => count($state['matches']),
            'last_checked_at' => (string) ($state['checked_at'] ?? ''),
            'interval_hours' => (int) (self::SCAN_INTERVAL_SECONDS / 3600),
        ];
    }

    /**
     * Returns the validated number of reincarnated product URLs.
     */
    public function getFoundCount(): int
    {
        $state = $this->getValidatedState();

        return count($state['matches']);
    }

    public function forgetRedirects(array $ids): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            return;
        }

        $state = $this->readState();
        $changed = false;
        foreach ($ids as $id) {
            $key = (string) $id;
            if (isset($state['matches'][$key])) {
                unset($state['matches'][$key]);
                $changed = true;
            }
        }

        if ($changed) {
            $this->writeState($state);
        }
    }

    public function isScanDue(): bool
    {
        if ((int) $this->getRedirectsEntity()->count([
            'type' => RedirectsEntity::TYPE_EXACT,
        ]) === 0) {
            return false;
        }

        $state = $this->readState();
        $checkedAt = strtotime((string) ($state['checked_at'] ?? ''));

        return $checkedAt === false || $checkedAt <= time() - self::SCAN_INTERVAL_SECONDS;
    }

    private function collectExactRedirects(): array
    {
        $redirectsEntity = $this->getRedirectsEntity();
        $total = (int) $redirectsEntity->count([
            'type' => RedirectsEntity::TYPE_EXACT,
        ]);
        if ($total <= 0) {
            return [];
        }

        $rows = [];
        $pages = (int) ceil($total / self::BATCH_SIZE);
        for ($page = 1; $page <= $pages; $page++) {
            $batch = $redirectsEntity
                ->cols(['id', 'from_url'])
                ->find([
                    'type' => RedirectsEntity::TYPE_EXACT,
                    'sort' => 'id',
                    'page' => $page,
                    'limit' => self::BATCH_SIZE,
                ]);

            foreach ($batch as $redirect) {
                if (is_object($redirect)) {
                    $rows[] = $redirect;
                }
            }
        }

        return $rows;
    }

    private function findExistingProductsByRedirectSource(array $sourceMap): array
    {
        /** @var ProductsEntity $productsEntity */
        $productsEntity = $this->entityFactory->get(ProductsEntity::class);
        $total = (int) $productsEntity->count([]);
        if ($total <= 0) {
            return [[], 0];
        }

        $matches = [];
        $productsChecked = 0;
        $targetRedirectCount = $this->countMappedRedirects($sourceMap);
        $pages = (int) ceil($total / self::BATCH_SIZE);

        for ($page = 1; $page <= $pages; $page++) {
            $products = $productsEntity
                ->cols(['id', 'url'])
                ->find([
                    'sort' => 'id',
                    'page' => $page,
                    'limit' => self::BATCH_SIZE,
                ]);

            foreach ($products as $product) {
                if (!is_object($product)) {
                    continue;
                }

                $productsChecked++;
                $productPath = $this->normalizeComparablePath($this->buildProductPath($product));
                if ($productPath === '' || empty($sourceMap[$productPath])) {
                    continue;
                }

                $productId = (int) ($product->id ?? 0);
                foreach ($sourceMap[$productPath] as $redirectId) {
                    $matches[(int) $redirectId] = [
                        'product_id' => $productId,
                        'source' => $productPath,
                    ];
                }

                if (count($matches) >= $targetRedirectCount) {
                    break 2;
                }
            }
        }

        return [$matches, $productsChecked];
    }

    /**
     * Returns the first non-empty SKU by variant position for every product.
     *
     * @return array<int, string> product ID => SKU
     */
    private function findProductSkus(array $productIds): array
    {
        $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));
        if (empty($productIds)) {
            return [];
        }

        /** @var VariantsEntity $variantsEntity */
        $variantsEntity = $this->entityFactory->get(VariantsEntity::class);
        $result = [];

        foreach (array_chunk($productIds, self::BATCH_SIZE) as $chunk) {
            $filter = ['product_id' => $chunk];
            $total = (int) $variantsEntity->count($filter);
            if ($total <= 0) {
                continue;
            }

            $variants = $variantsEntity
                ->cols(['id', 'product_id', 'sku', 'position'])
                ->find([
                    'product_id' => $chunk,
                    'sort' => 'position',
                    'page' => 1,
                    'limit' => $total,
                ]);

            foreach ($variants as $variant) {
                if (!is_object($variant)) {
                    continue;
                }

                $productId = (int) ($variant->product_id ?? 0);
                $sku = trim((string) ($variant->sku ?? ''));
                if ($productId > 0 && $sku !== '' && !isset($result[$productId])) {
                    $result[$productId] = $sku;
                }
            }
        }

        return $result;
    }

    private function getValidatedState(): array
    {
        $state = $this->readState();
        if (empty($state['matches'])) {
            return $state;
        }

        $redirectIds = array_values(array_unique(array_filter(array_map(
            'intval',
            array_keys($state['matches'])
        ))));
        if (empty($redirectIds)) {
            $state['matches'] = [];
            $this->writeState($state);
            return $state;
        }

        $existing = [];
        $redirectsEntity = $this->getRedirectsEntity();
        foreach (array_chunk($redirectIds, self::BATCH_SIZE) as $ids) {
            $rows = $redirectsEntity
                ->cols(['id', 'from_url', 'type'])
                ->find([
                    'id' => $ids,
                    'limit' => count($ids),
                ]);

            foreach ($rows as $redirect) {
                if (!is_object($redirect)) {
                    continue;
                }

                $id = (int) ($redirect->id ?? 0);
                if ($id <= 0 || (string) ($redirect->type ?? '') !== RedirectsEntity::TYPE_EXACT) {
                    continue;
                }

                $existing[$id] = $this->normalizeComparablePath((string) ($redirect->from_url ?? ''));
            }
        }

        $validMatches = [];
        foreach ($state['matches'] as $redirectId => $match) {
            $id = (int) $redirectId;
            $savedSource = (string) ($match['source'] ?? '');
            if ($id <= 0 || !isset($existing[$id]) || $existing[$id] !== $savedSource) {
                continue;
            }
            $validMatches[(string) $id] = $match;
        }

        if ($validMatches !== $state['matches']) {
            $state['matches'] = $validMatches;
            $this->writeState($state);
        }

        return $state;
    }

    private function buildProductPath(object $product): string
    {
        try {
            $generatedUrl = Router::generateUrl(
                'product',
                ['url' => (string) ($product->url ?? '')]
            );
            $path = parse_url((string) $generatedUrl, PHP_URL_PATH);

            return is_string($path) ? $path : '';
        } catch (\Throwable $e) {
            return (string) ($product->url ?? '');
        }
    }

    private function normalizeComparablePath(string $path): string
    {
        $path = trim(html_entity_decode($path, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($path === '') {
            return '';
        }

        if (preg_match('~^https?://~i', $path)) {
            $parsedPath = parse_url($path, PHP_URL_PATH);
            $path = is_string($parsedPath) ? $parsedPath : '';
        } else {
            $path = (string) preg_replace('~[?#].*$~', '', $path);
        }

        $path = str_replace("\0", '', rawurldecode($path));
        $path = (string) preg_replace('~/+~', '/', $path);

        return trim($path, '/');
    }

    private function countMappedRedirects(array $sourceMap): int
    {
        $count = 0;
        foreach ($sourceMap as $redirectIds) {
            $count += count($redirectIds);
        }

        return $count;
    }

    private function buildStatusReport(string $status): array
    {
        $current = $this->getStatus();

        return [
            'status' => $status,
            'found' => (int) ($current['found'] ?? 0),
            'redirects_checked' => 0,
            'products_checked' => 0,
            'checked_at' => (string) ($current['last_checked_at'] ?? ''),
        ];
    }

    private function readState(): array
    {
        $default = [
            'checked_at' => '',
            'redirects_checked' => 0,
            'products_checked' => 0,
            'matches' => [],
        ];

        $path = $this->getStateFilePath();
        if (!is_file($path) || !is_readable($path)) {
            return $default;
        }

        $json = @file_get_contents($path);
        if (!is_string($json) || $json === '') {
            return $default;
        }

        $state = json_decode($json, true);
        if (!is_array($state)) {
            return $default;
        }

        return [
            'checked_at' => (string) ($state['checked_at'] ?? ''),
            'redirects_checked' => (int) ($state['redirects_checked'] ?? 0),
            'products_checked' => (int) ($state['products_checked'] ?? 0),
            'matches' => is_array($state['matches'] ?? null) ? $state['matches'] : [],
        ];
    }

    private function writeState(array $state): void
    {
        $payload = json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($payload)) {
            return;
        }

        $path = $this->getStateFilePath();
        $temporaryPath = $path . '.tmp.' . getmypid();
        if (@file_put_contents($temporaryPath, $payload, LOCK_EX) !== false) {
            if (@rename($temporaryPath, $path)) {
                return;
            }
            @unlink($temporaryPath);
        }

        @file_put_contents($path, $payload, LOCK_EX);
    }

    private function claimSchedulerPollWindow(): bool
    {
        $handle = @fopen($this->getRuntimeFilePath('poll'), 'c+');
        if ($handle === false) {
            return true;
        }

        if (!@flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            return false;
        }

        rewind($handle);
        $nextPollAt = (int) trim((string) stream_get_contents($handle));
        $now = time();
        if ($nextPollAt > $now) {
            @flock($handle, LOCK_UN);
            fclose($handle);
            return false;
        }

        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, (string) ($now + self::SCHEDULER_POLL_SECONDS));
        fflush($handle);
        @flock($handle, LOCK_UN);
        fclose($handle);

        return true;
    }

    /** @return resource|false|null */
    private function acquireScanLock()
    {
        $handle = @fopen($this->getRuntimeFilePath('scan'), 'c+');
        if ($handle === false) {
            // Continue without a lock if the temporary directory is not writable.
            return false;
        }

        if (!@flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            return null;
        }

        return $handle;
    }

    /** @param resource $handle */
    private function releaseLock($handle): void
    {
        @flock($handle, LOCK_UN);
        fclose($handle);
    }

    private function getRuntimeFilePath(string $suffix): string
    {
        $scope = realpath(__DIR__) ?: __DIR__;

        return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . 'okay_sviat_redirects_' . md5($scope) . '_' . $suffix . '.lock';
    }

    private function getStateFilePath(): string
    {
        $scope = realpath(__DIR__) ?: __DIR__;

        return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . 'okay_sviat_redirects_' . md5($scope) . '_reincarnation.json';
    }

    private function getRedirectsEntity(): RedirectsEntity
    {
        if ($this->redirectsEntity === null) {
            $this->redirectsEntity = $this->entityFactory->get(RedirectsEntity::class);
        }

        return $this->redirectsEntity;
    }
}
