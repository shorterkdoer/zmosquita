<?php

namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value = null): mixed
    {
        self::start();

        // Set flash
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }

        // Get and delete flash
        $flash = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);

        return $flash;
    }

    public static function clear(): void
    {
        self::start();
        session_unset();
        session_destroy();
    }
}
