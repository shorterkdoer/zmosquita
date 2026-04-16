<?php

declare(strict_types=1);

namespace ZMosquita\Core\Models;

use ZMosquita\Core\Database\QueryBuilder;
use ZMosquita\Core\Support\Container;
use ZMosquita\Core\Support\Facades\Context;

abstract class BaseModel
{
    protected QueryBuilder $qb;

    protected string $table = '';

    protected string $primaryKey = 'id';

    /** @var string[] */
    protected array $fillable = [];

    protected bool $hasTenantColumn = false;

    public function __construct()
    {
        $this->qb = Container::instance()->get(QueryBuilder::class);
    }

    public function all(): array
    {
        return $this->newQuery()
            ->orderBy($this->primaryKey, 'desc')
            ->get();
    }

    public function allByMaster(int $masterId, string $foreignKey): array
    {
        return $this->newQuery()
            ->where($foreignKey, $masterId)
            ->orderBy($this->primaryKey, 'desc')
            ->get();
    }

    public function find(int $id): ?array
    {
        return $this->newQuery()
            ->where($this->primaryKey, $id)
            ->first();
    }

    public function findByMaster(int $id, int $masterId, string $foreignKey): ?array
    {
        return $this->newQuery()
            ->where($this->primaryKey, $id)
            ->where($foreignKey, $masterId)
            ->first();
    }

    public function create(array $data): bool
    {
        $data = $this->prepareWriteData($data);

        if ($data === []) {
            return false;
        }

        return $this->newQuery()->insert($data);
    }

    public function createGetId(array $data): int|string|false
    {
        $data = $this->prepareWriteData($data);

        if ($data === []) {
            return false;
        }

        return $this->newQuery()->insertGetId($data);
    }

    public function update(int $id, array $data): bool
    {
        $data = $this->filterFillable($data);

        if ($data === []) {
            return false;
        }

        return $this->newQuery()
            ->where($this->primaryKey, $id)
            ->update($data);
    }

    public function updateByMaster(int $id, int $masterId, string $foreignKey, array $data): bool
    {
        $data = $this->filterFillable($data);

        if ($data === []) {
            return false;
        }

        return $this->newQuery()
            ->where($this->primaryKey, $id)
            ->where($foreignKey, $masterId)
            ->update($data);
    }

    public function delete(int $id): bool
    {
        return $this->newQuery()
            ->where($this->primaryKey, $id)
            ->delete();
    }

    public function deleteByMaster(int $id, int $masterId, string $foreignKey): bool
    {
        return $this->newQuery()
            ->where($this->primaryKey, $id)
            ->where($foreignKey, $masterId)
            ->delete();
    }

    public function newQuery(): QueryBuilder
    {
        $query = $this->qb->table($this->table);

        if ($this->hasTenantColumn) {
            $query = $query->where('tenant_id', $this->tenantId());
        }

        return $query;
    }

    protected function prepareWriteData(array $data): array
    {
        $data = $this->filterFillable($data);

        if ($this->hasTenantColumn) {
            $data['tenant_id'] = $this->tenantId();
        }

        return $data;
    }

    protected function filterFillable(array $data): array
    {
        return array_intersect_key($data, array_flip($this->fillable));
    }

    protected function tenantId(): int
    {
        return (int)(Context::tenant()['id'] ?? 0);
    }
}