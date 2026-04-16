<?php

declare(strict_types=1);

namespace ZMosquita\Core\Repositories;

use ZMosquita\Core\Database\Connection;
use ZMosquita\Core\Database\TableResolver;
use ZMosquita\Core\Database\QueryBuilder;

final class MembershipRepository
{
    public function __construct(
        private QueryBuilder $db,
        private TableResolver $tables
    ) {
    }

    public function activeForUser(int $userId): array
    {
        $table = $this->tables->iam('user_tenant_memberships');

        return $this->db->fetchAll(
            "SELECT * FROM {$table}
             WHERE user_id = :user_id
               AND status = 'active'
               AND deleted_at IS NULL
               AND (starts_at IS NULL OR starts_at <= NOW())
               AND (ends_at IS NULL OR ends_at >= NOW())",
            ['user_id' => $userId]
        );
    }

    public function existsActive(int $userId, int $tenantId): bool
    {
        $table = $this->tables->iam('user_tenant_memberships');

        $row = $this->db->fetchOne(
            "SELECT id FROM {$table}
             WHERE user_id = :user_id
               AND tenant_id = :tenant_id
               AND status = 'active'
               AND deleted_at IS NULL
               AND (starts_at IS NULL OR starts_at <= NOW())
               AND (ends_at IS NULL OR ends_at >= NOW())
             LIMIT 1",
            ['user_id' => $userId, 'tenant_id' => $tenantId]
        );

        return $row !== null;
    }
}