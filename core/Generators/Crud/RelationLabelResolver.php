<?php

declare(strict_types=1);

namespace ZMosquita\Core\Generators\Crud;

use ZMosquita\Core\Database\QueryBuilder;

final class RelationLabelResolver
{
    public function __construct(
        private QueryBuilder $qb
    ) {
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, mixed> $relationMeta
     */
    public function labelFor(array $row, string $column, array $relationMeta): string
    {
        $value = $row[$column] ?? null;

        if ($value === null || $value === '') {
            return '';
        }

        $table = $relationMeta['table'] ?? null;
        $valueColumn = $relationMeta['value_column'] ?? 'id';
        $displayColumn = $relationMeta['display_column'] ?? 'nombre';

        if (!is_string($table) || $table === '') {
            return (string)$value;
        }

        $result = $this->qb
            ->table($table)
            ->select(["{$displayColumn} AS label"])
            ->where((string)$valueColumn, $value)
            ->first();

        return (string)($result['label'] ?? $value);
    }
}