<?php

declare(strict_types=1);

namespace ZMosquita\Core\Generators\Crud;

use ZMosquita\Core\Database\QueryBuilder;

final class RelationOptionResolver
{
    public function __construct(
        private QueryBuilder $qb
    ) {
    }

    /**
     * @param array<string, mixed> $relationMeta
     * @return array<int, array{value:mixed,label:string}>
     */
    public function options(array $relationMeta): array
    {
        $table = $relationMeta['table'] ?? null;
        $valueColumn = $relationMeta['value_column'] ?? 'id';
        $displayColumn = $relationMeta['display_column'] ?? 'nombre';
        $orderBy = $relationMeta['order_by'] ?? $displayColumn;
        $where = $relationMeta['where'] ?? [];

        if (!is_string($table) || $table === '') {
            return [];
        }

        $query = $this->qb
            ->table($table)
            ->select([
                "{$valueColumn} AS value",
                "{$displayColumn} AS label",
            ]);

        if (is_array($where) && $where !== []) {
            $query = $query->where($where);
        }

        $rows = $query
            ->orderBy((string)$orderBy)
            ->get();

        return array_map(
            static fn (array $row): array => [
                'value' => $row['value'] ?? null,
                'label' => (string)($row['label'] ?? ''),
            ],
            $rows
        );
    }
}