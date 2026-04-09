<?php


namespace Okay\Modules\Sviat\Redirects\Extensions;


use Okay\Core\EntityFactory;
use Okay\Core\Modules\Extender\ExtensionInterface;
use Okay\Core\Request;
use Okay\Core\Router;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\ProductsEntity;
use Okay\Modules\Sviat\Redirects\Entities\RedirectsEntity;

class RedirectsExtension implements ExtensionInterface
{
    private const STATUS_PERMANENT = '301';
    private const SAFE_HOST_PATTERN = '/^[a-z0-9.-]+$/';
    private const LANGUAGE_PREFIX_PATTERN = '/^[a-z]{2}(?:-[a-z]{2})?$/';
    private const MAX_REDIRECT_HOPS = 5;

    private EntityFactory $entityFactory;
    private Request $request;
    private ?RedirectsEntity $redirectsEntity = null;
    private ?string $primaryHost = null;
    private ?string $scheme = null;

    public function __construct(EntityFactory $entityFactory, Request $request)
    {
        $this->entityFactory = $entityFactory;
        $this->request = $request;
    }

    public function redirect(): void
    {
        $requestUri = (string) $this->request->getRequestUri();
        $normalizedUri = $this->normalizeUri($requestUri, false);
        $lookupUri = $this->normalizeUri($requestUri, true);

        if ($normalizedUri === '') {
            return;
        }

        $resolved = $this->resolveRedirectChain($lookupUri);
        if ($resolved !== null) {
            $this->sendRedirect($this->buildAbsoluteUrl($resolved['target_path']), (int) $resolved['status']);
        }

        $requestHost = $this->normalizeHost($_SERVER['HTTP_HOST'] ?? '');
        $hostMismatch = false;
        if (!$this->isLocalHost($requestHost)) {
            $expectedHost = $this->getPrimaryHost();
            $hostMismatch = !$this->isLocalHost($expectedHost) && $requestHost !== $expectedHost;
        }

        if ($hostMismatch) {
            $this->sendRedirect($this->buildAbsoluteUrl($normalizedUri), (int) self::STATUS_PERMANENT);
        }
    }

    public function createProductDeleteRedirects($result, $ids): void
    {
        $productIds = $this->normalizeIds($ids);
        if (empty($productIds)) {
            return;
        }

        $productsEntity = $this->entityFactory->get(ProductsEntity::class);
        $productsLimit = count($productIds);
        $products = $productsEntity->find([
            'id' => $productIds,
            'limit' => $productsLimit,
        ]);
        if (empty($products)) {
            return;
        }

        $categoriesEntity = $this->entityFactory->get(CategoriesEntity::class);
        $redirectsEntity = $this->getRedirectsEntity();
        $fallbackCategories = $this->collectFallbackCategoryIds($categoriesEntity, $productIds);
        $categoryPathCache = [];

        $redirectCandidates = [];
        foreach ($products as $product) {
            $sourcePath = $this->normalizePath($this->buildProductPath($product));
            if ($sourcePath === '') {
                continue;
            }

            $categoryId = !empty($product->main_category_id)
                ? (int) $product->main_category_id
                : ($fallbackCategories[(int) $product->id] ?? null);
            if (empty($categoryId)) {
                continue;
            }

            if (!array_key_exists($categoryId, $categoryPathCache)) {
                $category = $categoriesEntity->get($categoryId);
                $categoryPathCache[$categoryId] = empty($category)
                    ? ''
                    : $this->normalizePath($this->buildCategoryPath($category));
            }

            $targetPath = $categoryPathCache[$categoryId];
            if ($targetPath === '') {
                continue;
            }

            $redirectCandidates[(int) $product->id] = [
                'from_url' => $sourcePath,
                'to_url' => $targetPath,
            ];
        }

        if (empty($redirectCandidates)) {
            return;
        }

        $existingSources = $this->collectExistingSources($redirectsEntity, $redirectCandidates);
        $this->persistMissingRedirects($redirectsEntity, $redirectCandidates, $existingSources);
    }

    private function persistMissingRedirects(
        RedirectsEntity $redirectsEntity,
        array $redirectCandidates,
        array $existingSources
    ): void {
        $now = date('Y-m-d H:i:s');

        foreach ($redirectCandidates as $productId => $redirectData) {
            if (isset($existingSources[$redirectData['from_url']])) {
                continue;
            }

            $redirectsEntity->add((object) [
                'name' => 'Redirect for removed product #' . $productId,
                'from_url' => $redirectData['from_url'],
                'to_url' => $redirectData['to_url'],
                'enabled' => 1,
                'is_lang' => 1,
                'status' => self::STATUS_PERMANENT,
                'type' => RedirectsEntity::TYPE_EXACT,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function collectExistingSources(RedirectsEntity $redirectsEntity, array $redirectCandidates): array
    {
        $sourceValues = array_values(array_unique(array_column($redirectCandidates, 'from_url')));
        if (empty($sourceValues)) {
            return [];
        }

        $redirects = $redirectsEntity->find([
            'from_url' => $sourceValues,
            'limit' => count($sourceValues),
        ]);

        $map = [];
        foreach ($redirects as $redirect) {
            $map[$this->normalizePath($redirect->from_url)] = true;
        }

        return $map;
    }

    private function collectFallbackCategoryIds(CategoriesEntity $categoriesEntity, array $productIds): array
    {
        $fallback = [];
        foreach ($categoriesEntity->getProductCategories($productIds) as $link) {
            $productId = (int) ($link->product_id ?? 0);
            if ($productId <= 0 || isset($fallback[$productId])) {
                continue;
            }
            $fallback[$productId] = (int) $link->category_id;
        }

        return $fallback;
    }

    private function buildProductPath($product): string
    {
        try {
            return (string) parse_url(
                Router::generateUrl('product', ['url' => (string) $product->url]),
                PHP_URL_PATH
            );
        } catch (\Throwable $e) {
            return (string) (!empty($product->slug_url) ? $product->slug_url : $product->url);
        }
    }

    private function buildCategoryPath($category): string
    {
        try {
            return (string) parse_url(
                Router::generateUrl('category', ['url' => (string) $category->url]),
                PHP_URL_PATH
            );
        } catch (\Throwable $e) {
            return (string) (!empty($category->slug_url) ? $category->slug_url : $category->path_url);
        }
    }

    private function resolveRedirectChain(string $startPath): ?array
    {
        $visitedPaths = [];
        $currentPath = $startPath;
        $resolved = null;

        for ($hop = 0; $hop < self::MAX_REDIRECT_HOPS; $hop++) {
            if (isset($visitedPaths[$currentPath])) {
                break;
            }
            $visitedPaths[$currentPath] = true;

            $matchedRedirect = $this->matchRedirectBySource($currentPath);
            if ($matchedRedirect === null) {
                break;
            }

            $redirect = $matchedRedirect['redirect'];
            $nextPath = $matchedRedirect['target_path'];
            if ($nextPath === '' || $nextPath === $currentPath) {
                break;
            }

            $this->touchRedirectStats($redirect);
            $resolved = [
                'target_path' => $nextPath,
                'status' => (int) ($redirect->status ?? self::STATUS_PERMANENT),
            ];
            $currentPath = $nextPath;
        }

        return $resolved;
    }

    private function matchRedirectBySource(string $normalizedSource): ?array
    {
        $languageContext = $this->extractLanguageContext($normalizedSource);

        // Exact rules always have priority over pattern rules.
        $exact = $this->findFirstEnabledRedirect([
            'from_url' => $normalizedSource,
            'type' => RedirectsEntity::TYPE_EXACT,
        ]);
        if ($exact !== null) {
            $target = $this->normalizePath((string) ($exact->to_url ?? ''));
            if ($target === '') {
                return null;
            }

            return [
                'redirect' => $exact,
                'target_path' => $target,
            ];
        }

        if ($languageContext['base_path'] !== null) {
            $exactMultilingual = $this->findFirstEnabledRedirect([
                'from_url' => $languageContext['base_path'],
                'type' => RedirectsEntity::TYPE_EXACT,
                'is_lang' => 1,
            ]);
            if ($exactMultilingual !== null) {
                $target = $this->normalizePath((string) ($exactMultilingual->to_url ?? ''));
                if ($target !== '') {
                    return [
                        'redirect' => $exactMultilingual,
                        'target_path' => $this->prependLanguagePrefix($target, $languageContext['prefix']),
                    ];
                }
            }
        }

        $patternCandidates = $this->getRedirectsEntity()->find([
            'enabled' => 1,
            'limit' => 500,
        ]);

        $bestMatch = null;
        $bestScore = -1;
        foreach ($patternCandidates as $pattern) {
            if (!is_object($pattern)) {
                continue;
            }
            if ((string) ($pattern->type ?? '') !== RedirectsEntity::TYPE_PATTERN) {
                continue;
            }

            $resolvedTarget = $this->resolvePatternTargetPath($normalizedSource, $pattern);
            if ($resolvedTarget !== null) {
                $score = $this->getPatternMatchScore((string) ($pattern->from_url ?? ''));
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = [
                        'redirect' => $pattern,
                        'target_path' => $resolvedTarget,
                    ];
                }
            }

            if (empty($pattern->is_lang) || $languageContext['base_path'] === null) {
                continue;
            }

            $resolvedTarget = $this->resolvePatternTargetPath($languageContext['base_path'], $pattern);
            if ($resolvedTarget !== null) {
                $resolvedTarget = $this->prependLanguagePrefix($resolvedTarget, $languageContext['prefix']);
                $score = $this->getPatternMatchScore((string) ($pattern->from_url ?? ''));
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = [
                        'redirect' => $pattern,
                        'target_path' => $resolvedTarget,
                    ];
                }
            }
        }

        return $bestMatch;
    }

    private function extractLanguageContext(string $path): array
    {
        $path = $this->normalizePath($path);
        if ($path === '' || strpos($path, '/') === false) {
            return [
                'prefix' => null,
                'base_path' => null,
            ];
        }

        [$firstSegment, $rest] = explode('/', $path, 2);
        if ($rest === '' || !preg_match(self::LANGUAGE_PREFIX_PATTERN, $firstSegment)) {
            return [
                'prefix' => null,
                'base_path' => null,
            ];
        }

        return [
            'prefix' => $firstSegment,
            'base_path' => $rest,
        ];
    }

    private function prependLanguagePrefix(string $path, ?string $prefix): string
    {
        $path = $this->normalizePath($path);
        $prefix = $this->normalizePath((string) $prefix);
        if ($path === '' || $prefix === '') {
            return $path;
        }

        return $prefix . '/' . $path;
    }

    private function findFirstEnabledRedirect(array $filter): ?object
    {
        $redirect = $this->getRedirectsEntity()->findOne($filter + [
            'enabled' => 1,
            'limit' => 1,
        ]);

        if (is_object($redirect) && !empty($redirect->to_url)) {
            return $redirect;
        }

        return null;
    }

    private function resolvePatternTargetPath(string $requestPath, object $redirect): ?string
    {
        $targetTemplate = $this->normalizePath((string) ($redirect->to_url ?? ''));
        $fromTemplate = $this->normalizePath((string) ($redirect->from_url ?? ''));
        if ($targetTemplate === '' || $fromTemplate === '') {
            return null;
        }

        if (strpos($fromTemplate, '$slug') !== false) {
            $slug = $this->extractSlugByTemplate($requestPath, $fromTemplate);
            if ($slug === null) {
                return null;
            }

            if (strpos($targetTemplate, '$slug') !== false) {
                return $this->normalizePath(str_replace('$slug', $slug, $targetTemplate));
            }

            return $targetTemplate;
        }

        if (strpos($requestPath, $fromTemplate) !== 0) {
            return null;
        }

        $suffix = ltrim(substr($requestPath, strlen($fromTemplate)), '/');
        if (strpos($targetTemplate, '$slug') !== false) {
            if ($suffix === '') {
                return null;
            }

            return $this->normalizePath(str_replace('$slug', $suffix, $targetTemplate));
        }

        if ($suffix === '') {
            return $targetTemplate;
        }

        return rtrim($targetTemplate, '/') . '/' . $suffix;
    }

    private function extractSlugByTemplate(string $requestPath, string $fromTemplate): ?string
    {
        $parts = explode('$slug', $fromTemplate, 2);
        $left = $parts[0];
        $right = $parts[1];

        if ($left !== '' && strpos($requestPath, $left) !== 0) {
            return null;
        }

        if ($right !== '' && substr($requestPath, -strlen($right)) !== $right) {
            return null;
        }

        $start = strlen($left);
        $end = $right === '' ? strlen($requestPath) : strlen($requestPath) - strlen($right);
        if ($end <= $start) {
            return null;
        }

        $slug = trim(substr($requestPath, $start, $end - $start), '/');
        if ($slug === '') {
            return null;
        }

        return $slug;
    }

    private function getPatternMatchScore(string $fromTemplate): int
    {
        $normalized = $this->normalizePath($fromTemplate);
        $withoutSlug = str_replace('$slug', '', $normalized);
        return strlen($withoutSlug);
    }

    private function touchRedirectStats($redirect): void
    {
        if (empty($redirect->id)) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $this->getRedirectsEntity()->update((int) $redirect->id, (object) [
            'hits' => (int) ($redirect->hits ?? 0) + 1,
            'last_hit_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function sendRedirect(string $location, int $statusCode): void
    {
        if (preg_match('/[\r\n]/', $location)) {
            return;
        }
        header('Location: ' . $location, true, $statusCode);
        exit();
    }

    private function buildAbsoluteUrl(string $path): string
    {
        $host = $this->resolveRedirectHost();
        $path = $this->normalizePath($path);
        if ($host === '' || $path === '') {
            return $this->resolveScheme() . $host . '/';
        }

        return $this->resolveScheme() . $host . '/' . $path;
    }

    private function resolveRedirectHost(): string
    {
        $requestHost = $this->normalizeHost($_SERVER['HTTP_HOST'] ?? '');
        if ($this->isLocalHost($requestHost)) {
            return $requestHost;
        }

        return $this->getPrimaryHost();
    }

    private function normalizeUri(string $uri, bool $lowercase = true): string
    {
        $path = strtok($uri, '?');
        $path = $path === false ? $uri : $path;
        $value = trim((string) $path);

        return $this->normalizePath($lowercase ? mb_strtolower($value) : $value);
    }

    private function normalizePath($path): string
    {
        $path = trim((string) $path);
        $path = str_replace("\0", '', $path);
        $path = preg_replace('/[\r\n]+/', '', $path);
        $path = ltrim($path, '/');

        return $path;
    }

    private function normalizeIds($ids): array
    {
        $normalized = [];
        foreach ((array) $ids as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $normalized[$id] = $id;
            }
        }

        return array_values($normalized);
    }

    private function resolveScheme(): string
    {
        if ($this->scheme !== null) {
            return $this->scheme;
        }

        $httpsEnabled = !empty($_SERVER['HTTPS']) && in_array(strtolower((string) $_SERVER['HTTPS']), ['on', '1'], true);
        $forwardedHttps = !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https';
        $sslHeader = !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_SSL']) === 'on';
        $port443 = (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';

        $this->scheme = ($httpsEnabled || $forwardedHttps || $sslHeader || $port443) ? 'https://' : 'http://';
        return $this->scheme;
    }

    private function getPrimaryHost(): string
    {
        if ($this->primaryHost !== null) {
            return $this->primaryHost;
        }

        // Prefer SERVER_NAME to avoid trusting user-controlled Host header.
        $serverName = $this->normalizeHost($_SERVER['SERVER_NAME'] ?? '');
        if ($serverName !== '') {
            $this->primaryHost = $serverName;
            return $this->primaryHost;
        }

        $httpHost = $this->normalizeHost($_SERVER['HTTP_HOST'] ?? '');
        $this->primaryHost = ($httpHost === '' ? 'localhost' : $httpHost);
        return $this->primaryHost;
    }

    private function normalizeHost(string $host): string
    {
        $host = $this->extractHostWithoutPort($host);
        $host = preg_replace('/^www\./', '', $host);
        if ($host === '') {
            return '';
        }

        if (!preg_match(self::SAFE_HOST_PATTERN, $host)) {
            return '';
        }

        return $host;
    }

    private function isLocalHost(string $host): bool
    {
        $host = $this->extractHostWithoutPort($host);
        return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }

    private function extractHostWithoutPort(string $host): string
    {
        $host = strtolower(trim($host));
        if ($host === '') {
            return '';
        }

        // Handle bracketed IPv6 host: [::1]:8080 -> ::1
        if (strpos($host, '[') === 0) {
            $endBracketPos = strpos($host, ']');
            if ($endBracketPos !== false) {
                return substr($host, 1, $endBracketPos - 1);
            }
            return trim($host, '[]');
        }

        // Strip port if present: localhost:8080 -> localhost
        if (strpos($host, ':') !== false) {
            $parts = explode(':', $host, 2);
            return $parts[0];
        }

        return $host;
    }

    private function getRedirectsEntity(): RedirectsEntity
    {
        if ($this->redirectsEntity === null) {
            $this->redirectsEntity = $this->entityFactory->get(RedirectsEntity::class);
        }

        return $this->redirectsEntity;
    }
}
