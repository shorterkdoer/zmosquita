<?php

declare(strict_types=1);

namespace ZMosquita\Core\Repositories;

use ZMosquita\Core\Database\Connection;
use ZMosquita\Core\Database\TableResolver;
use ZMosquita\Core\Database\QueryBuilder;

final class UserPermissionAssignmentRepository
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
        $assignments = $this->tables->iam('user_permission_assignments');
        $permissions = $this->tables->iam('permissions');

        $sql = "SELECT upa.*, p.code AS permission_code
                FROM {$assignments} upa
                INNER JOIN {$permissions} p ON p.id = upa.permission_id
                WHERE upa.user_id = :user_id
                  AND upa.scope_type = :scope_type
                  AND upa.status = 'active'
                  AND upa.effect = 'allow'
                  AND p.status = 'active'
                  AND (upa.expires_at IS NULL OR upa.expires_at >= NOW())";

        $params = [
            'user_id' => $userId,
            'scope_type' => $scopeType,
        ];

        if ($tenantId !== null) {
            $sql .= " AND upa.tenant_id = :tenant_id";
            $params['tenant_id'] = $tenantId;
        }

        if ($appId !== null) {
            $sql .= " AND upa.app_id = :app_id";
            $params['app_id'] = $appId;
        }

        return $this->db->fetchAll($sql, $params);
    }
}