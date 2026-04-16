<?php

declare(strict_types=1);

namespace ZMosquita\Core\Repositories;

use ZMosquita\Core\Database\Connection;
use ZMosquita\Core\Database\TableResolver;
use ZMosquita\Core\Database\QueryBuilder;

final class UserPermissionVersionRepository
{
    public function __construct(
        private QueryBuilder $db,
        private TableResolver $tables
    ) {
    }

    public function getVersionForUser(int $userId): int
    {
        $table = $this->tables->iam('user_permission_versions');

        $row = $this->db->fetchOne(
            "SELECT version FROM {$table} WHERE user_id = :user_id LIMIT 1",
            ['user_id' => $userId]
        );

        return $row ? (int)$row['version'] : 1;
    }
}