<?php

declare(strict_types=1);

namespace ZMosquita\Core\Repositories;

use ZMosquita\Core\Database\Connection;
use ZMosquita\Core\Database\TableResolver;
use ZMosquita\Core\Database\QueryBuilder;

final class PermissionRepository
{
    public function __construct(
        private QueryBuilder $db,
        private TableResolver $tables
    ) {
    }

    public function findByCode(string $code): ?array
    {
        $table = $this->tables->iam('permissions');

        return $this->db->fetchOne(
            "SELECT * FROM {$table}
             WHERE code = :code
               AND status = 'active'
             LIMIT 1",
            ['code' => $code]
        );
    }
}