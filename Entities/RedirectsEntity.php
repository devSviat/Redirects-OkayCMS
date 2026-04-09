<?php


namespace Okay\Modules\Sviat\Redirects\Entities;

use Okay\Core\Entity\Entity;

class RedirectsEntity extends Entity
{
    public const TABLE = 'sviat__redirects';
    public const ALIAS = 'sr';
    public const STATUS_301 = '301';
    public const STATUS_302 = '302';
    public const TYPE_EXACT = 'exact';
    public const TYPE_PATTERN = 'pattern';
    public const STATUSES = [
        self::STATUS_301,
        self::STATUS_302,
    ];
    public const TYPES = [
        self::TYPE_EXACT,
        self::TYPE_PATTERN,
    ];

    protected static $fields = [
        'id',
        'name',
        'from_url',
        'to_url',
        'type',
        'is_lang',
        'status',
        'enabled',
        'hits',
        'created_at',
        'updated_at',
        'last_hit_at',
    ];

    protected static $defaultOrderFields = [
        'updated_at DESC',
        'id DESC',
    ];

    protected static $searchFields = [
        'name',
        'from_url',
        'to_url',
    ];

    protected static $table = self::TABLE;

    protected static $tableAlias = self::ALIAS;

    protected function filter__enabled($enabled)
    {
        if ($enabled === null || $enabled === '') {
            return;
        }

        $this->select
            ->where(self::ALIAS . '.enabled = :enabled_state')
            ->bindValue('enabled_state', (int) $enabled);
    }

    protected function filter__status($status)
    {
        if (!in_array((string) $status, self::STATUSES, true)) {
            return;
        }

        $this->select
            ->where(self::ALIAS . '.status = :redirect_status')
            ->bindValue('redirect_status', (string) $status);
    }

    public function filter__disabled()
    {
        $this->select->where('(' . self::ALIAS . '.enabled = 0 OR ' . self::ALIAS . '.enabled IS NULL)');
    }

    protected function filter__type($type)
    {
        if (!in_array((string) $type, self::TYPES, true)) {
            return;
        }

        $this->select
            ->where(self::ALIAS . '.type = :redirect_type')
            ->bindValue('redirect_type', (string) $type);
    }

    protected function filter__from_url($fromUrl)
    {
        $values = [];
        foreach ((array) $fromUrl as $value) {
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }

            $trimmed = ltrim($value, '/');
            if ($trimmed === '') {
                continue;
            }

            // Match redirects stored with and without leading slash.
            $values[] = $trimmed;
            $values[] = '/' . $trimmed;
        }

        $values = array_values(array_unique($values));
        if (empty($values)) {
            return;
        }

        if (count($values) === 1) {
            $this->select
                ->where(self::ALIAS . '.from_url = :exact_from_url')
                ->bindValue('exact_from_url', $values[0]);

            return;
        }

        $this->select->where(self::ALIAS . '.from_url IN (:from_url_values)')
            ->bindValue('from_url_values', $values);
    }

    protected function filter__best_pattern_for($requestPath)
    {
        $requestPath = ltrim(trim((string) $requestPath), '/');
        if ($requestPath === '') {
            return;
        }

        $this->select
            ->where(self::ALIAS . '.type = :pattern_type')
            ->bindValue('pattern_type', self::TYPE_PATTERN)
            ->where(':request_path LIKE CONCAT(' . self::ALIAS . '.from_url, "%")')
            ->bindValue('request_path', $requestPath);
    }

    protected function filter__sort($value)
    {
        if ($value !== null && $value !== '') {
            $this->order($value);
        }
    }
}
