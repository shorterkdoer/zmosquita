<?php

declare(strict_types=1);

namespace ZMosquita\Core\Generators\MasterDetail;

use ZMosquita\Core\Generators\Crud\CrudDefinition;

final class RelationInspector
{
    public function detect(CrudDefinition $master, CrudDefinition $detail): ?string
    {
        // 1) metadata explícita
        $mdMeta = $detail->table->meta['master_detail'] ?? null;
        if (is_array($mdMeta)) {
            $masterResource = $mdMeta['master_resource'] ?? null;
            $foreignKey = $mdMeta['foreign_key'] ?? null;

            if (
                is_string($masterResource)
                && is_string($foreignKey)
                && $masterResource === $master->resourceName()
                && $detail->table->hasColumn($foreignKey)
            ) {
                return $foreignKey;
            }
        }

        // 2) foreign key real
        foreach ($detail->table->foreignKeys as $fk) {
            if ($fk->referencedTable === $master->tableName()) {
                return $fk->column;
            }
        }

        // 3) convención por nombre
        $candidates = [
            $this->singular($master->resourceName()) . '_id',
            $master->resourceName() . '_id',
        ];

        foreach ($candidates as $candidate) {
            if ($detail->table->hasColumn($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    public function validate(CrudDefinition $master, CrudDefinition $detail, string $foreignKey): bool
    {
        return $detail->table->hasColumn($foreignKey)
            && $master->primaryKey() !== '';
    }

    private function singular(string $value): string
    {
        return str_ends_with($value, 's')
            ? substr($value, 0, -1)
            : $value;
    }
}