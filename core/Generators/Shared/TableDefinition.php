<?php

declare(strict_types=1);

namespace ZMosquita\Core\Generators\Shared;

final class TableDefinition
{
    /**
     * @param ColumnDefinition[] $columns
     * @param ForeignKeyDefinition[] $foreignKeys
     */
    public function __construct(
        public string $resourceName,
        public string $tableName,
        public string $scope,
        public ?string $appCode,
        public array $columns = [],
        public array $foreignKeys = [],
        public array $meta = [],
    ) {
    }

    public function primaryKey(): ?string
    {
        foreach ($this->columns as $column) {
            if ($column->primaryKey) {
                return $column->name;
            }
        }

        return null;
    }

    public function hasColumn(string $name): bool
    {
        foreach ($this->columns as $column) {
            if ($column->name === $name) {
                return true;
            }
        }

        return false;
    }

    public function column(string $name): ?ColumnDefinition
    {
        foreach ($this->columns as $column) {
            if ($column->name === $name) {
                return $column;
            }
        }

        return null;
    }

    /**
     * @return ColumnDefinition[]
     */
    public function formColumns(): array
    {
        $configured = $this->meta['form']['fields'] ?? null;
        $excluded = $this->meta['form']['excluded'] ?? [];
        $hidden = $this->meta['form']['hidden'] ?? [];

        if (is_array($configured)) {
            $result = [];
            foreach ($configured as $fieldName) {
                $fieldName = (string)$fieldName;

                if (in_array($fieldName, $excluded, true)) {
                    continue;
                }

                $column = $this->column($fieldName);
                if ($column) {
                    $result[] = $column;
                }
            }
            return $result;
        }

        return array_values(array_filter(
            $this->columns,
            static fn (ColumnDefinition $column): bool =>
                !$column->primaryKey
                && !$column->autoIncrement
                && !$column->isTimestamp()
                && !in_array($column->name, $excluded, true)
                && !in_array($column->name, $hidden, true)
        ));
    }

    /**
     * @return ColumnDefinition[]
     */
    public function tableColumns(): array
    {
        $configured = $this->meta['table']['columns'] ?? null;
        $excluded = $this->meta['table']['excluded'] ?? [];
        $hidden = $this->meta['table']['hidden'] ?? [];

        if (is_array($configured)) {
            $result = [];
            foreach ($configured as $fieldName) {
                $fieldName = (string)$fieldName;

                if (in_array($fieldName, $excluded, true)) {
                    continue;
                }

                $column = $this->column($fieldName);
                if ($column) {
                    $result[] = $column;
                }
            }
            return $result;
        }

        return array_values(array_filter(
            $this->columns,
            static fn (ColumnDefinition $column): bool =>
                !$column->isTimestamp()
                && !in_array($column->name, $excluded, true)
                && !in_array($column->name, $hidden, true)
        ));
    }

    public function labelFor(string $column): string
    {
        $labels = $this->meta['labels'] ?? [];

        if (isset($labels[$column]) && is_string($labels[$column])) {
            return $labels[$column];
        }

        return ucwords(str_replace('_', ' ', $column));
    }

    public function fieldMeta(string $column): array
    {
        $fields = $this->meta['fields'] ?? [];
        return isset($fields[$column]) && is_array($fields[$column]) ? $fields[$column] : [];
    }

    public function fieldType(string $column): ?string
    {
        $meta = $this->fieldMeta($column);
        return isset($meta['type']) && is_string($meta['type']) ? $meta['type'] : null;
    }

    /**
     * @return string[]
     */
    public function rulesFor(string $column): array
    {
        $meta = $this->fieldMeta($column);
        $rules = $meta['rules'] ?? [];

        return is_array($rules) ? array_values(array_map('strval', $rules)) : [];
    }

    public function isReadonly(string $column): bool
    {
        $readonly = $this->meta['form']['readonly'] ?? [];
        return in_array($column, $readonly, true);
    }

    public function isHidden(string $column): bool
    {
        $hidden = $this->meta['form']['hidden'] ?? [];
        return in_array($column, $hidden, true);
    }

    public function relationMeta(string $column): array
    {
        $fieldMeta = $this->fieldMeta($column);
        if (isset($fieldMeta['relation']) && is_array($fieldMeta['relation'])) {
            return $fieldMeta['relation'];
        }

        $relations = $this->meta['relations'] ?? [];
        return isset($relations[$column]) && is_array($relations[$column]) ? $relations[$column] : [];
    }

    public function foreignKey(string $column): ?ForeignKeyDefinition
    {
        foreach ($this->foreignKeys as $fk) {
            if ($fk->column === $column) {
                return $fk;
            }
        }

        return null;
    }
}