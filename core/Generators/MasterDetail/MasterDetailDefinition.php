<?php

declare(strict_types=1);

namespace ZMosquita\Core\Generators\MasterDetail;

use ZMosquita\Core\Generators\Crud\CrudDefinition;

final class MasterDetailDefinition
{
    public function __construct(
        public CrudDefinition $master,
        public CrudDefinition $detail,
        public string $foreignKey,
        public array $meta = [],
    ) {
    }

    public function masterResource(): string
    {
        return $this->master->resourceName();
    }

    public function detailResource(): string
    {
        return $this->detail->resourceName();
    }

    public function masterTable(): string
    {
        return $this->master->tableName();
    }

    public function detailTable(): string
    {
        return $this->detail->tableName();
    }

    public function masterPrimaryKey(): string
    {
        return $this->master->primaryKey();
    }

    public function detailPrimaryKey(): string
    {
        return $this->detail->primaryKey();
    }

    public function foreignKey(): string
    {
        return $this->foreignKey;
    }

    public function controllerClass(): string
    {
        return $this->detail->controllerClass() . 'Nested';
    }

    public function controllerNamespace(): string
    {
        return $this->detail->controllerNamespace();
    }

    public function modelClass(): string
    {
        return $this->detail->modelClass();
    }

    public function modelNamespace(): string
    {
        return $this->detail->modelNamespace();
    }

    public function validatorClass(): string
    {
        return $this->detail->validatorClass();
    }

    public function validatorNamespace(): string
    {
        return $this->detail->validatorNamespace();
    }

    public function masterRouteBase(): string
    {
        return $this->master->routeBase();
    }

    public function detailRouteSegment(): string
    {
        return strtolower($this->detail->resourceName());
    }

    public function viewFolder(): string
    {
        return strtolower($this->detail->resourceName()) . '_nested';
    }

    public function indexPermission(): string
    {
        return $this->detail->indexPermission();
    }

    public function createPermission(): string
    {
        return $this->detail->createPermission();
    }

    public function editPermission(): string
    {
        return $this->detail->editPermission();
    }

    public function deletePermission(): string
    {
        return $this->detail->deletePermission();
    }

    public function masterLabelColumn(): string
    {
        return (string)($this->meta['master_label_column'] ?? $this->master->primaryKey());
    }
}