<?php

declare(strict_types=1);

namespace ZMosquita\Core\Auth;

use ZMosquita\Core\Repositories\RoleRepository;
use ZMosquita\Core\Repositories\UserPermissionAssignmentRepository;
use ZMosquita\Core\Repositories\UserRoleAssignmentRepository;

final class PermissionResolver
{
    public function __construct(
        private RoleRepository $roles,
        private UserRoleAssignmentRepository $userRoles,
        private UserPermissionAssignmentRepository $userPermissions
    ) {
    }

    /**
     * @return string[]
     */
    public function resolve(int $userId, ?int $tenantId, ?int $appId): array
    {
        $permissions = [];

        // direct global
        foreach ($this->userPermissions->activeGlobal($userId) as $row) {
            $permissions[(string)$row['permission_code']] = true;
        }

        // roles global
        foreach ($this->rolePermissionCodes($this->userRoles->activeGlobal($userId)) as $code) {
            $permissions[$code] = true;
        }

        if ($tenantId !== null) {
            // direct tenant
            foreach ($this->userPermissions->activeTenant($userId, $tenantId) as $row) {
                $permissions[(string)$row['permission_code']] = true;
            }

            // roles tenant
            foreach ($this->rolePermissionCodes($this->userRoles->activeTenant($userId, $tenantId)) as $code) {
                $permissions[$code] = true;
            }
        }

        if ($tenantId !== null && $appId !== null) {
            // direct tenant_app
            foreach ($this->userPermissions->activeTenantApp($userId, $tenantId, $appId) as $row) {
                $permissions[(string)$row['permission_code']] = true;
            }

            // roles tenant_app
            foreach ($this->rolePermissionCodes($this->userRoles->activeTenantApp($userId, $tenantId, $appId)) as $code) {
                $permissions[$code] = true;
            }
        }

        ksort($permissions);

        return array_keys($permissions);
    }

    /**
     * @return string[]
     */
    public function resolveRoles(int $userId, ?int $tenantId, ?int $appId): array
    {
        $roles = [];

        foreach ($this->userRoles->activeGlobal($userId) as $row) {
            $roles[(string)$row['role_code']] = true;
        }

        if ($tenantId !== null) {
            foreach ($this->userRoles->activeTenant($userId, $tenantId) as $row) {
                $roles[(string)$row['role_code']] = true;
            }
        }

        if ($tenantId !== null && $appId !== null) {
            foreach ($this->userRoles->activeTenantApp($userId, $tenantId, $appId) as $row) {
                $roles[(string)$row['role_code']] = true;
            }
        }

        ksort($roles);

        return array_keys($roles);
    }

    public function hasPermission(int $userId, ?int $tenantId, ?int $appId, string $permissionCode): bool
    {
        return in_array($permissionCode, $this->resolve($userId, $tenantId, $appId), true);
    }

    public function hasRole(int $userId, ?int $tenantId, ?int $appId, string $roleCode): bool
    {
        return in_array($roleCode, $this->resolveRoles($userId, $tenantId, $appId), true);
    }

    /**
     * @param array<int, array<string, mixed>> $roleAssignments
     * @return string[]
     */
    private function rolePermissionCodes(array $roleAssignments): array
    {
        if ($roleAssignments === []) {
            return [];
        }

        $roleIds = array_map(
            static fn (array $row): int => (int)$row['role_id'],
            $roleAssignments
        );

        $codes = [];
        foreach ($this->roles->permissionsForRoleIds($roleIds) as $row) {
            $codes[(string)$row['code']] = true;
        }

        return array_keys($codes);
    }
}