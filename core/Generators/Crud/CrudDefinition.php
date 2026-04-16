<?php

declare(strict_types=1);

namespace ZMosquita\Core\Generators\Crud;

use ZMosquita\Core\Generators\Shared\ColumnDefinition;
use ZMosquita\Core\Generators\Shared\TableDefinition;

final class CrudDefinition
{
    public function __construct(
        public TableDefinition $table
    ) {
    }

    public function resourceName(): string
    {
        return $this->table->resourceName;
    }

    public function tableName(): string
    {
        return $this->table->tableName;
    }

    public function scope(): string
    {
        return $this->table->scope;
    }

    public function appCode(): ?string
    {
        return $this->table->appCode;
    }

    public function primaryKey(): string
    {
        return $this->table->primaryKey() ?? 'id';
    }

    /**
     * @return ColumnDefinition[]
     */
    public function columns(): array
    {
        return $this->table->columns;
    }

    /**
     * @return ColumnDefinition[]
     */
    public function formColumns(): array
    {
        return $this->table->formColumns();
    }

    /**
     * @return ColumnDefinition[]
     */
    public function tableColumns(): array
    {
        return $this->table->tableColumns();
    }

    public function hasTenantColumn(): bool
    {
        return $this->table->hasColumn('tenant_id');
    }

    public function labelFor(string $column): string
    {
        return $this->table->labelFor($column);
    }

    public function fieldType(string $column): ?string
    {
        return $this->table->fieldType($column);
    }

    /**
     * @return string[]
     */
    public function rulesFor(string $column): array
    {
        return $this->table->rulesFor($column);
    }

    public function isReadonly(string $column): bool
    {
        return $this->table->isReadonly($column);
    }

    public function isHidden(string $column): bool
    {
        return $this->table->isHidden($column);
    }

    public function relationMeta(string $column): array
    {
        return $this->table->relationMeta($column);
    }

    public function hasRelation(string $column): bool
    {
        return $this->relationMeta($column) !== [] || $this->table->foreignKey($column) !== null;
    }

    public function controllerClass(): string
    {
        $custom = $this->generatorMeta()['controller'] ?? null;
        if (is_string($custom) && $custom !== '') {
            return $custom;
        }

        return $this->studly($this->resourceName()) . 'Controller';
    }

    public function modelClass(): string
    {
        $custom = $this->generatorMeta()['model'] ?? null;
        if (is_string($custom) && $custom !== '') {
            return $custom;
        }

        return $this->singularStudly($this->resourceName());
    }

    public function validatorClass(): string
    {
        return $this->singularStudly($this->resourceName()) . 'Validator';
    }

    public function routeBase(): string
    {
        return '/' . strtolower($this->resourceName());
    }

    public function viewFolder(): string
    {
        return strtolower($this->resourceName());
    }

    public function permissionBase(): string
    {
        return strtolower($this->resourceName());
    }

    public function indexPermission(): string
    {
        return $this->permissionBase() . '.view';
    }

    public function createPermission(): string
    {
        return $this->permissionBase() . '.create';
    }

    public function editPermission(): string
    {
        return $this->permissionBase() . '.edit';
    }

    public function deletePermission(): string
    {
        return $this->permissionBase() . '.delete';
    }

    public function namespaceBase(): string
    {
        if ($this->scope() === 'core') {
            return 'ZMosquita\\Core';
        }

        return 'Applications\\' . $this->studly((string)$this->appCode());
    }

    public function controllerNamespace(): string
    {
        return $this->namespaceBase() . '\\Controllers';
    }

    public function modelNamespace(): string
    {
        return $this->namespaceBase() . '\\Models';
    }

    public function validatorNamespace(): string
    {
        return $this->namespaceBase() . '\\Validators';
    }

    /**
     * @return array<string, mixed>
     */
    public function generatorMeta(): array
    {
        return $this->table->meta['generator'] ?? [];
    }

    public function overwrite(): bool
    {
        return (bool)($this->generatorMeta()['overwrite'] ?? false);
    }

    public function lookupMethodName(string $column): string
    {
        return 'lookup' . $this->studly($column);
    }

    private function studly(string $value): string
    {
        $value = str_replace(['-', '_'], ' ', $value);
        $value = ucwords($value);
        return str_replace(' ', '', $value);
    }

    private function singularStudly(string $value): string
    {
        $studly = $this->studly($value);

        if (str_ends_with($studly, 's')) {
            return substr($studly, 0, -1);
        }

        return $studly;
    }
}