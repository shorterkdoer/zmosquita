<?php

declare(strict_types=1);

namespace ZMosquita\Core\Support\Facades;

use ZMosquita\Core\Auth\AuthorizationManager;
use ZMosquita\Core\Support\Container;

final class Authz
{
    private static function service(): AuthorizationManager
    {
        return Container::instance()->get(AuthorizationManager::class);
    }

    public static function can(string $permissionCode): bool
    {
        return self::service()->can($permissionCode);
    }

    public static function cannot(string $permissionCode): bool
    {
        return self::service()->cannot($permissionCode);
    }

    public static function hasRole(string $roleCode): bool
    {
        return self::service()->hasRole($roleCode);
    }

    public static function permissions(): array
    {
        return self::service()->permissions();
    }

    public static function roles(): array
    {
        return self::service()->roles();
    }

    public static function refresh(): void
    {
        self::service()->refresh();
    }
}