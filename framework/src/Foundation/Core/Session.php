<?php

namespace Foundation\Core;

class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (!self::$started && session_status() === PHP_SESSION_NONE) {
            session_start();
            self::$started = true;
        }
    }

    /**
     * Regenerate session ID to prevent session fixation attacks
     *
     * @param bool $deleteOldSession Whether to delete the old session file
     * @return bool
     */
    public static function regenerate(bool $deleteOldSession = true): bool
    {
        self::start();
        return session_regenerate_id($deleteOldSession);
    }

    /**
     * Check if current session is valid (IP and UserAgent validation)
     *
     * @return bool
     */
    public static function isValid(): bool
    {
        self::start();

        if (!isset($_SESSION['_security'])) {
            self::initializeSecurity();
            return true;
        }

        $security = $_SESSION['_security'];

        // Validate IP address (allow for IPv6 variations)
        $currentIp = self::getClientIp();
        if (!hash_equals($security['ip'] ?? '', $currentIp)) {
            self::clear();
            return false;
        }

        // Validate User Agent
        $currentUa = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (!hash_equals($security['ua'] ?? '', $currentUa)) {
            self::clear();
            return false;
        }

        // Check session age (max 24 hours)
        if (isset($security['created']) && (time() - $security['created'] > 86400)) {
            self::clear();
            return false;
        }

        // Update last activity timestamp
        $security['last_activity'] = time();
        $_SESSION['_security'] = $security;

        return true;
    }

    /**
     * Initialize security checks for the session
     */
    private static function initializeSecurity(): void
    {
        $_SESSION['_security'] = [
            'ip' => self::getClientIp(),
            'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created' => time(),
            'last_activity' => time(),
        ];
    }

    /**
     * Get client IP address (handles proxies)
     *
     * @return string
     */
    private static function getClientIp(): string
    {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);

                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Check if session has expired based on last activity
     *
     * @param int $timeout Timeout in seconds (default: 30 minutes)
     * @return bool
     */
    public static function isExpired(int $timeout = 1800): bool
    {
        self::start();

        if (!isset($_SESSION['_security']['last_activity'])) {
            return false;
        }

        return (time() - $_SESSION['_security']['last_activity']) > $timeout;
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

    /**
     * Clear all session data and destroy session
     */
    public static function clear(): void
    {
        self::start();
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_unset();
        session_destroy();
        self::$started = false;
    }

    /**
     * Get authenticated user from session
     *
     * @return array|null
     */
    public static function user(): ?array
    {
        return self::get('user');
    }

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    public static function isAuthenticated(): bool
    {
        return self::has('user') && self::isValid();
    }

    /**
     * Get user role from session
     *
     * @return string|null
     */
    public static function getRole(): ?string
    {
        return self::get('user.role');
    }

    /**
     * Check if user has specific role
     *
     * @param string $role
     * @return bool
     */
    public static function hasRole(string $role): bool
    {
        return self::getRole() === $role;
    }
}
