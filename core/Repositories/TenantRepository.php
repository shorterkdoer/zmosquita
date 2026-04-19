<?php

declare(strict_types=1);

namespace ZMosquita\Core\Repositories;

use ZMosquita\Core\Database\Connection;
use ZMosquita\Core\Database\TableResolver;
use ZMosquita\Core\Database\QueryBuilder;

final class TenantRepository
{
    public function __construct(
        private QueryBuilder $db,
        private TableResolver $tables
    ) {
    }

    public function findById(int $id): ?array
    {
        $table = $this->tables->iam('tenants');

        return $this->db->fetchOne(
            "SELECT id, code, name, catalog, description, status, created_at, updated_at
             FROM {$table}
             WHERE id = :id
               AND deleted_at IS NULL
               AND status = 'active'
             LIMIT 1",
            ['id' => $id]
        );
    }

    public function findByCode(string $code): ?array
    {
        $table = $this->tables->iam('tenants');

        return $this->db->fetchOne(
            "SELECT id, code, name, catalog, description, status, created_at, updated_at
             FROM {$table}
             WHERE code = :code
               AND deleted_at IS NULL
               AND status = 'active'
             LIMIT 1",
            ['code' => $code]
        );
    }

    public function findByCatalog(string $catalog): ?array
    {
        $table = $this->tables->iam('tenants');

        return $this->db->fetchOne(
            "SELECT id, code, name, catalog, description, status, created_at, updated_at
             FROM {$table}
             WHERE catalog = :catalog
               AND deleted_at IS NULL
             LIMIT 1",
            ['catalog' => $catalog]
        );
    }

    public function getAllActive(): array
    {
        $table = $this->tables->iam('tenants');

        return $this->db->fetchAll(
            "SELECT id, code, name, catalog, description, status, created_at, updated_at
             FROM {$table}
             WHERE deleted_at IS NULL
               AND status = 'active'
             ORDER BY name ASC"
        );
    }
}