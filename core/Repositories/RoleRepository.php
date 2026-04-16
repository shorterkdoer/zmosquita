<?php

declare(strict_types=1);

namespace ZMosquita\Core\Repositories;

use ZMosquita\Core\Database\Connection;
use ZMosquita\Core\Database\TableResolver;
use ZMosquita\Core\Database\QueryBuilder;

final class RoleRepository
{
    public function __construct(
        private QueryBuilder $db,
        private TableResolver $tables
    ) {
    }

    public function findByCode(string $code): ?array
    {
        $table = $this->tables->iam('roles');

        return $this->db->fetchOne(
            "SELECT * FROM {$table}
             WHERE code = :code
               AND status = 'active'
             LIMIT 1",
            ['code' => $code]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function permissionsForRoleIds(array $roleIds): array
    {
        if ($roleIds === []) {
            return [];
        }

        $rolesPlaceholders = implode(',', array_fill(0, count($roleIds), '?'));
        $rolePermissions = $this->tables->iam('role_permissions');
        $permissions = $this->tables->iam('permissions');

        return $this->db->fetchAll(
            "SELECT rp.role_id, p.code
             FROM {$rolePermissions} rp
             INNER JOIN {$permissions} p ON p.id = rp.permission_id
             WHERE rp.role_id IN ({$rolesPlaceholders})
               AND p.status = 'active'",
            $roleIds
        );
    }
}