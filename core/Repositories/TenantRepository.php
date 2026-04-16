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
            "SELECT * FROM {$table}
             WHERE id = :id
               AND deleted_at IS NULL
               AND status = 'active'
             LIMIT 1",
            ['id' => $id]
        );
    }
}