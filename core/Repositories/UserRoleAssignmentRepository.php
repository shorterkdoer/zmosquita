<?php

declare(strict_types=1);

namespace ZMosquita\Core\Repositories;

use ZMosquita\Core\Database\Connection;
use ZMosquita\Core\Database\TableResolver;
use ZMosquita\Core\Database\QueryBuilder;

final class UserRoleAssignmentRepository
{
    public function __construct(
        private QueryBuilder $db,
        private TableResolver $tables
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function activeGlobal(int $userId): array
    {
        return $this->fetchByScope($userId, 'global');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function activeTenant(int $userId, int $tenantId): array
    {
        return $this->fetchByScope($userId, 'tenant', $tenantId);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function activeTenantApp(int $userId, int $tenantId, int $appId): array
    {
        return $this->fetchByScope($userId, 'tenant_app', $tenantId, $appId);
    }

    private function fetchByScope(
        int $userId,
        string $scopeType,
        ?int $tenantId = null,
        ?int $appId = null
    ): array {
        $assignments = $this->tables->iam('user_role_assignments');
        $roles = $this->tables->iam('roles');

        $sql = "SELECT ura.*, r.code AS role_code, r.name AS role_name
                FROM {$assignments} ura
                INNER JOIN {$roles} r ON r.id = ura.role_id
                WHERE ura.user_id = :user_id
                  AND ura.scope_type = :scope_type
                  AND ura.status = 'active'
                  AND r.status = 'active'
                  AND (ura.expires_at IS NULL OR ura.expires_at >= NOW())";

        $params = [
            'user_id' => $userId,
            'scope_type' => $scopeType,
        ];

        if ($tenantId !== null) {
            $sql .= " AND ura.tenant_id = :tenant_id";
            $params['tenant_id'] = $tenantId;
        }

        if ($appId !== null) {
            $sql .= " AND ura.app_id = :app_id";
            $params['app_id'] = $appId;
        }

        return $this->db->fetchAll($sql, $params);
    }
}