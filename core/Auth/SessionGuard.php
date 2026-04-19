<?php

declare(strict_types=1);

namespace ZMosquita\Core\Auth;

final class SessionGuard
{
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = $_SESSION;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    public function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $target =& $_SESSION;

        foreach ($segments as $segment) {
            if (!isset($target[$segment]) || !is_array($target[$segment])) {
                $target[$segment] = [];
            }
            $target =& $target[$segment];
        }

        $target = $value;
    }

    public function has(string $key): bool
    {
        return $this->get($key, '__missing__') !== '__missing__';
    }

    public function remove(string $key): void
    {
        $segments = explode('.', $key);
        $target =& $_SESSION;
        $last = array_pop($segments);

        foreach ($segments as $segment) {
            if (!isset($target[$segment]) || !is_array($target[$segment])) {
                return;
            }
            $target =& $target[$segment];
        }

        unset($target[$last]);
    }

    public function forget(string $key): void
    {
        $this->remove($key);
    }

    public function clear(): void
    {
        $_SESSION = [];
    }

    public function all(): array
    {
        return $_SESSION;
    }

    public function regenerate(): void
    {
        session_regenerate_id(true);
    }
}