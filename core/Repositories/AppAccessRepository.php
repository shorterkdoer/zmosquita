<?php

declare(strict_types=1);

namespace ZMosquita\Core\Repositories;

use ZMosquita\Core\Database\Connection;
use ZMosquita\Core\Database\TableResolver;
use ZMosquita\Core\Database\QueryBuilder;

final class AppAccessRepository
{
    public function __construct(
        private QueryBuilder $db,
        private TableResolver $tables
    ) {
    }

    public function activeForUserAndTenant(int $userId, int $tenantId): array
    {
        $table = $this->tables->iam('user_app_access');

        return $this->db->fetchAll(
            "SELECT * FROM {$table}
             WHERE user_id = :user_id
               AND tenant_id = :tenant_id
               AND status = 'active'
               AND deleted_at IS NULL
               AND (starts_at IS NULL OR starts_at <= NOW())
               AND (ends_at IS NULL OR ends_at >= NOW())",
            ['user_id' => $userId, 'tenant_id' => $tenantId]
        );
    }

    public function existsActive(int $userId, int $tenantId, int $appId): bool
    {
        $table = $this->tables->iam('user_app_access');

        $row = $this->db->fetchOne(
            "SELECT id FROM {$table}
             WHERE user_id = :user_id
               AND tenant_id = :tenant_id
               AND app_id = :app_id
               AND status = 'active'
               AND deleted_at IS NULL
               AND (starts_at IS NULL OR starts_at <= NOW())
               AND (ends_at IS NULL OR ends_at >= NOW())
             LIMIT 1",
            ['user_id' => $userId, 'tenant_id' => $tenantId, 'app_id' => $appId]
        );

        return $row !== null;
    }
}