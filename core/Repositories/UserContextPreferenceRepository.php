<?php

declare(strict_types=1);

namespace ZMosquita\Core\Repositories;

use ZMosquita\Core\Database\Connection;
use ZMosquita\Core\Database\TableResolver;
use ZMosquita\Core\Database\QueryBuilder;

final class UserContextPreferenceRepository
{
    public function __construct(
        private QueryBuilder $db,
        private TableResolver $tables
    ) {
    }

    public function findByUserId(int $userId): ?array
    {
        $table = $this->tables->iam('user_context_preferences');

        return $this->db->fetchOne(
            "SELECT * FROM {$table} WHERE user_id = :user_id LIMIT 1",
            ['user_id' => $userId]
        );
    }

    public function saveLastContext(int $userId, int $tenantId, int $appId): void
    {
        $table = $this->tables->iam('user_context_preferences');

        $exists = $this->findByUserId($userId);

        if ($exists) {
            $this->db->execute(
                "UPDATE {$table}
                 SET last_tenant_id = :tenant_id,
                     last_app_id = :app_id,
                     updated_at = NOW()
                 WHERE user_id = :user_id",
                [
                    'user_id'   => $userId,
                    'tenant_id' => $tenantId,
                    'app_id'    => $appId,
                ]
            );
            return;
        }

        $this->db->execute(
            "INSERT INTO {$table} (user_id, last_tenant_id, last_app_id, updated_at)
             VALUES (:user_id, :tenant_id, :app_id, NOW())",
            [
                'user_id'   => $userId,
                'tenant_id' => $tenantId,
                'app_id'    => $appId,
            ]
        );
    }
}