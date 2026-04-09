<?php

namespace Okay\Modules\Sviat\Redirects\Helpers;

use Okay\Core\EntityFactory;
use Okay\Modules\Sviat\Redirects\Entities\RedirectsEntity;

class RedirectsImportHelper
{
    private const BATCH_SIZE = 500;

    private EntityFactory $entityFactory;

    public function __construct(EntityFactory $entityFactory)
    {
        $this->entityFactory = $entityFactory;
    }

    public function importFromCsv(string $csvPath, bool $updateExisting = false): array
    {
        $result = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'duplicates' => 0,
            'invalid' => 0,
        ];

        if (!is_file($csvPath) || !is_readable($csvPath)) {
            return $result;
        }

        $handle = fopen($csvPath, 'rb');
        if ($handle === false) {
            return $result;
        }

        $delimiter = $this->detectDelimiter($handle);
        $header = fgetcsv($handle, 0, $delimiter);

        $headerMap = $this->buildHeaderMap($header);
        $hasHeader = isset($headerMap['from_url'], $headerMap['to_url']);
        if (!$hasHeader) {
            rewind($handle);
            $headerMap = [];
        }

        $seenSources = [];
        $batch = [];
        $redirectsEntity = $this->entityFactory->get(RedirectsEntity::class);

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $result['total']++;
            $candidate = $this->buildCandidateFromRow($row, $headerMap);
            if ($candidate === null) {
                $result['invalid']++;
                continue;
            }

            $sourceKey = $this->normalizePath($candidate['from_url']);
            if ($sourceKey === '') {
                $result['invalid']++;
                continue;
            }

            if (isset($seenSources[$sourceKey])) {
                $result['duplicates']++;
                continue;
            }

            $seenSources[$sourceKey] = true;
            $batch[$sourceKey] = $candidate;

            if (count($batch) >= self::BATCH_SIZE) {
                $this->processBatch($redirectsEntity, $batch, $updateExisting, $result);
                $batch = [];
            }
        }

        fclose($handle);

        if (!empty($batch)) {
            $this->processBatch($redirectsEntity, $batch, $updateExisting, $result);
        }

        return $result;
    }

    private function processBatch(
        RedirectsEntity $redirectsEntity,
        array $candidates,
        bool $updateExisting,
        array &$result
    ): void {
        if (empty($candidates)) {
            return;
        }

        $sourceKeys = array_keys($candidates);
        $existing = $redirectsEntity->find([
            'from_url' => $sourceKeys,
            'limit' => count($sourceKeys) * 2,
        ]);

        $existingMap = [];
        foreach ($existing as $redirect) {
            $sourceKey = $this->normalizePath((string) $redirect->from_url);
            if ($sourceKey === '') {
                continue;
            }
            if (!isset($existingMap[$sourceKey])) {
                $existingMap[$sourceKey] = $redirect;
            }
        }

        $now = date('Y-m-d H:i:s');
        foreach ($candidates as $sourceKey => $candidate) {
            if (isset($existingMap[$sourceKey])) {
                if (!$updateExisting) {
                    $result['duplicates']++;
                    continue;
                }

                $redirectId = (int) ($existingMap[$sourceKey]->id ?? 0);
                if ($redirectId > 0) {
                    $redirectsEntity->update($redirectId, (object) [
                        'name' => $candidate['name'] !== '' ? $candidate['name'] : ('CSV redirect: ' . $sourceKey),
                        'to_url' => $candidate['to_url'],
                        'status' => $candidate['status'],
                        'enabled' => $candidate['enabled'],
                        'is_lang' => $candidate['is_lang'],
                        'type' => $candidate['type'],
                        'updated_at' => $now,
                    ]);
                    $result['updated']++;
                    continue;
                }
            }

            $redirectsEntity->add((object) [
                'name' => $candidate['name'] !== '' ? $candidate['name'] : ('CSV redirect: ' . $sourceKey),
                'from_url' => $sourceKey,
                'to_url' => $candidate['to_url'],
                'status' => $candidate['status'],
                'enabled' => $candidate['enabled'],
                'is_lang' => $candidate['is_lang'],
                'type' => $candidate['type'],
                'hits' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $result['created']++;
        }
    }

    private function detectDelimiter($handle): string
    {
        $line = fgets($handle);
        rewind($handle);
        if (!is_string($line)) {
            return ',';
        }

        $commaCount = substr_count($line, ',');
        $semicolonCount = substr_count($line, ';');
        return $semicolonCount > $commaCount ? ';' : ',';
    }

    private function buildHeaderMap($header): array
    {
        if (!is_array($header)) {
            return [];
        }

        $map = [];
        foreach ($header as $index => $column) {
            $key = $this->normalizeHeaderKey((string) $column);
            if ($key !== '') {
                $map[$key] = $index;
            }
        }

        return $map;
    }

    private function normalizeHeaderKey(string $value): string
    {
        // Remove UTF-8 BOM from the first column name if present.
        $value = str_replace("\xEF\xBB\xBF", '', $value);
        $value = trim(strtolower($value));
        if ($value === 'url_from') {
            return 'from_url';
        }
        if ($value === 'url_to') {
            return 'to_url';
        }
        if (in_array($value, ['is_lang', 'is_multilingual', 'multi_language', 'multilang'], true)) {
            return 'is_lang';
        }

        return $value;
    }

    private function buildCandidateFromRow(array $row, array $headerMap): ?array
    {
        if (!empty($headerMap)) {
            $urlFrom = $this->getRowValue($row, $headerMap, 'from_url');
            $urlTo = $this->getRowValue($row, $headerMap, 'to_url');
            $name = $this->getRowValue($row, $headerMap, 'name');
            $status = $this->normalizeStatus($this->getRowValue($row, $headerMap, 'status'));
            $enabled = $this->normalizeEnabled($this->getRowValue($row, $headerMap, 'enabled'));
            $multilingual = $this->normalizeEnabled($this->getRowValue($row, $headerMap, 'is_lang'));
            $type = $this->normalizeType($this->getRowValue($row, $headerMap, 'type'), $urlFrom);
        } else {
            $urlFrom = trim((string) ($row[0] ?? ''));
            $urlTo = trim((string) ($row[1] ?? ''));
            $name = trim((string) ($row[2] ?? ''));
            $status = $this->normalizeStatus((string) ($row[3] ?? RedirectsEntity::STATUS_301));
            $enabled = $this->normalizeEnabled((string) ($row[4] ?? '1'));
            $multilingual = $this->normalizeEnabled((string) ($row[5] ?? '0'));
            $type = $this->normalizeType((string) ($row[6] ?? ''), $urlFrom);
        }

        $urlFrom = $this->normalizePath($urlFrom);
        $urlTo = $this->normalizePath($urlTo);
        if ($urlFrom === '' || $urlTo === '') {
            return null;
        }

        return [
            'name' => $name,
            'from_url' => $urlFrom,
            'to_url' => $urlTo,
            'status' => $status,
            'enabled' => $enabled,
            'is_lang' => $multilingual,
            'type' => $type,
        ];
    }

    private function getRowValue(array $row, array $headerMap, string $field): string
    {
        if (!isset($headerMap[$field])) {
            return '';
        }

        return trim((string) ($row[$headerMap[$field]] ?? ''));
    }

    private function normalizePath(string $path): string
    {
        return ltrim(trim(strtolower($path)), '/');
    }

    private function normalizeStatus(string $status): string
    {
        return in_array($status, RedirectsEntity::STATUSES, true)
            ? $status
            : RedirectsEntity::STATUS_301;
    }

    private function normalizeEnabled(string $enabled): int
    {
        $value = strtolower(trim($enabled));
        if (in_array($value, ['0', 'false', 'no', 'off', ''], true)) {
            return 0;
        }

        return 1;
    }

    private function normalizeType(string $type, string $fromUrl): string
    {
        $type = strtolower(trim($type));
        if (in_array($type, RedirectsEntity::TYPES, true)) {
            return $type;
        }

        if (strpos($fromUrl, '$slug') !== false) {
            return RedirectsEntity::TYPE_PATTERN;
        }

        return RedirectsEntity::TYPE_EXACT;
    }
}
