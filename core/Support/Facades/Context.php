<?php

declare(strict_types=1);

namespace ZMosquita\Core\Support\Facades;

use ZMosquita\Core\Auth\ContextManager;
use ZMosquita\Core\Support\Container;

final class Context
{
    public static function service(): ContextManager
    {
        return Container::instance()->get(ContextManager::class);
    }

    public static function availableContexts(?int $userId = null): array
    {
        return self::service()->availableContexts($userId);
    }

    public static function switch(int $tenantId, int $appId): bool
    {
        return self::service()->switch($tenantId, $appId);
    }

    public static function context(): ?object
    {
        return self::service()->context();
    }

    public static function tenant(): ?array
    {
        return self::service()->tenant();
    }

    public static function app(): ?array
    {
        return self::service()->app();
    }

    public static function isValid(): bool
    {
        return self::service()->isValid();
    }

    public static function clear(): void
    {
        self::service()->clear();
    }

    public static function restorePreferredContext(): bool
    {
        return self::service()->restorePreferredContext();
    }

    public static function resolveSingleContext(): bool
    {
        return self::service()->resolveSingleContext();
    }
}