<?php

namespace Okay\Modules\Sviat\Redirects\Export;

/**
 * Запис CSV без виконуваних формул (CWE-1236).
 *
 * Своя копія, а не Okay\Core\Export\CsvWriter: у стоковому OkayCMS 4.5.3 цього
 * класу немає, а модуль працює і там. Правило префіксів те саме.
 */
class FormulaSafeCsv
{
    private const array FORMULA_PREFIXES = ['=', '+', '-', '@', "\t", "\r"];

    /**
     * @param resource $handle
     * @param array<int|string, mixed> $values
     */
    public static function putRow($handle, array $values, string $delimiter = ';'): void
    {
        fputcsv($handle, array_map(self::escapeValue(...), $values), $delimiter, '"', '');
    }

    /** int і float лишаються числами, будь-який рядок вважається текстом. */
    public static function escapeValue(mixed $value): string|int|float
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        $value = (string) $value;
        if ($value !== '' && in_array($value[0], self::FORMULA_PREFIXES, true)) {
            return "'" . $value;
        }

        return $value;
    }

    /** Зворотна операція до escapeValue: імпорт читає власний експорт без змін. */
    public static function unescapeValue(string $value): string
    {
        if (strlen($value) > 1 && $value[0] === "'" && in_array($value[1], self::FORMULA_PREFIXES, true)) {
            return substr($value, 1);
        }

        return $value;
    }
}
