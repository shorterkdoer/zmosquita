<?php

namespace App\Core;

use ParagonIE\AntiCSRF\AntiCSRF;

/**
 * CSRF Protection Helper
 *
 * Provides methods for generating and validating CSRF tokens
 * using ParagonIE's AntiCSRF library
 */
class CSRF
{
    private static ?AntiCSRF $instance = null;

    /**
     * Get the AntiCSRF instance (singleton)
     */
    private static function getInstance(): AntiCSRF
    {
        if (self::$instance === null) {
            self::$instance = new AntiCSRF([
                'form_expiration' => 3600, // 1 hour
                'session_name' => 'csrf_token'
            ]);
        }
        return self::$instance;
    }

    /**
     * Insert a hidden CSRF token field into a form
     *
     * @param string $lockTo Optional lock to a specific value (e.g., username)
     * @return string HTML for hidden input field
     */
    public static function tokenField(string $lockTo = ''): string
    {
        return self::getInstance()->insertToken($lockTo, false);
    }

    /**
     * Get the current CSRF token value
     *
     * @param string $lockTo Optional lock to a specific value
     * @return string The token value
     */
    public static function getToken(string $lockTo = ''): string
    {
        return self::getInstance()->getToken($lockTo);
    }

    /**
     * Validate a CSRF token from POST request
     *
     * @param string $lockTo Optional lock to a specific value
     * @return bool True if valid, false otherwise
     */
    public static function validate(string $lockTo = ''): bool
    {
        try {
            return self::getInstance()->validateRequest($lockTo);
        } catch (\Exception $e) {
            error_log('CSRF validation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate token and throw exception if invalid
     *
     * @param string $lockTo Optional lock to a specific value
     * @throws \RuntimeException if token is invalid
     */
    public static function validateOrFail(string $lockTo = ''): void
    {
        if (!self::validate($lockTo)) {
            throw new \RuntimeException('CSRF token validation failed. Possible session timeout or malicious request.');
        }
    }

    /**
     * Regenerate the CSRF token
     *
     * Call this after login to prevent session fixation attacks
     */
    public static function regenerate(): void
    {
        self::getInstance()->renewToken();
    }
}
