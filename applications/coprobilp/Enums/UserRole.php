<?php

namespace App\Enums;

/**
 * User Role Enumeration
 *
 * Defines all valid user roles in the system with their hierarchy:
 * - superuser: Full system access, can manage admins and system configuration
 * - admin: Administrative access, can manage users, matriculas, cobranzas
 * - user: Regular user access, can manage their own data
 * - guest: Unauthenticated visitor, limited public access
 */
enum UserRole: string
{
    case SUPERUSER = 'superuser';
    case ADMIN = 'admin';
    case USER = 'user';
    case GUEST = 'guest';

    /**
     * Check if a given role string is valid
     *
     * @param string $role The role to validate
     * @return bool True if the role exists, false otherwise
     */
    public static function isValid(string $role): bool
    {
        return in_array($role, array_column(self::cases(), 'value'), true);
    }

    /**
     * Get the default role for new users
     *
     * @return self
     */
    public static function getDefault(): self
    {
        return self::USER;
    }

    /**
     * Get role for unauthenticated users
     *
     * @return self
     */
    public static function getGuest(): self
    {
        return self::GUEST;
    }

    /**
     * Check if this role can access administrative features
     * Admin and superuser can access admin features
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this === self::ADMIN || $this === self::SUPERUSER;
    }

    /**
     * Check if this role has superuser privileges
     *
     * @return bool
     */
    public function isSuperuser(): bool
    {
        return $this === self::SUPERUSER;
    }

    /**
     * Check if this role is a regular authenticated user
     *
     * @return bool
     */
    public function isUser(): bool
    {
        return $this === self::USER;
    }

    /**
     * Check if this role represents an unauthenticated guest
     *
     * @return bool
     */
    public function isGuest(): bool
    {
        return $this === self::GUEST;
    }

    /**
     * Get the display label for this role
     *
     * @return string
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::SUPERUSER => 'Superusuario',
            self::ADMIN => 'Administrador',
            self::USER => 'Usuario',
            self::GUEST => 'Visitante',
        };
    }

    /**
     * Get role hierarchy level (higher = more privileges)
     *
     * @return int
     */
    public function getLevel(): int
    {
        return match ($this) {
            self::GUEST => 0,
            self::USER => 1,
            self::ADMIN => 2,
            self::SUPERUSER => 3,
        };
    }

    /**
     * Check if this role has equal or higher privileges than another role
     *
     * @param self $other The role to compare against
     * @return bool
     */
    public function hasEqualOrHigherPrivilegesThan(self $other): bool
    {
        return $this->getLevel() >= $other->getLevel();
    }

    /**
     * Create enum from string, defaulting to guest if invalid
     *
     * @param string $role The role string
     * @return self
     */
    public static function fromString(string $role): self
    {
        try {
            return self::from($role);
        } catch (\ValueError $e) {
            return self::GUEST;
        }
    }
}
