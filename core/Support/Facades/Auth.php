<?php

declare(strict_types=1);

namespace ZMosquita\Core\Support\Facades;

use ZMosquita\Core\Auth\AuthManager;
use ZMosquita\Core\Auth\DTO\LoginResult;
use ZMosquita\Core\Support\Container;

final class Auth
{
    private static function service(): AuthManager
    {
        return Container::instance()->get(AuthManager::class);
    }

    public static function login(string $identity, string $password): LoginResult
    {
        return self::service()->login($identity, $password);
    }

    public static function logout(): void
    {
        self::service()->logout();
    }

    public static function check(): bool
    {
        return self::service()->check();
    }

    public static function id(): ?int
    {
        return self::service()->id();
    }

    public static function user(): ?array
    {
        return self::service()->user();
    }
}