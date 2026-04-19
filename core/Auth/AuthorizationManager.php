<?php

declare(strict_types=1);

namespace ZMosquita\Core\Auth;

final class AuthorizationManager
{
    public function __construct(
        private AuthManager $auth,
        private ContextManager $context,
        private PermissionResolver $resolver,
        private SessionGuard $session
    ) {
    }

    public function can(string $permissionCode): bool
    {
        if (!$this->auth->check()) {
            return false;
        }

        $context = $this->context->context();

        if (!$context) {
            return false;
        }

        return in_array($permissionCode, $this->permissions(), true);
    }

    public function cannot(string $permissionCode): bool
    {
        return !$this->can($permissionCode);
    }

    public function hasRole(string $roleCode): bool
    {
        if (!$this->auth->check()) {
            return false;
        }

        $context = $this->context->context();

        if (!$context) {
            return false;
        }

        return in_array($roleCode, $this->roles(), true);
    }

    /**
     * @return string[]
     */
    public function permissions(): array
    {
        $context = $this->context->context();

        if (!$context) {
            return [];
        }

        $cacheKey = $this->cacheKey();
        $cached = $this->session->get('permissions');

        if (
            is_array($cached)
            && ($cached['cache_key'] ?? null) === $cacheKey
            && is_array($cached['codes'] ?? null)
        ) {
            return $cached['codes'];
        }

        $codes = $this->resolver->resolve(
            $context->userId,
            $context->tenantId,
            $context->appId
        );

        $this->session->set('permissions', [
            'cache_key' => $cacheKey,
            'codes' => $codes,
        ]);

        return $codes;
    }

    /**
     * @return string[]
     */
    public function roles(): array
    {
        $context = $this->context->context();

        if (!$context) {
            return [];
        }

        $cacheKey = $this->cacheKey();
        $cached = $this->session->get('roles');

        if (
            is_array($cached)
            && ($cached['cache_key'] ?? null) === $cacheKey
            && is_array($cached['codes'] ?? null)
        ) {
            return $cached['codes'];
        }

        $codes = $this->resolver->resolveRoles(
            $context->userId,
            $context->tenantId,
            $context->appId
        );

        $this->session->set('roles', [
            'cache_key' => $cacheKey,
            'codes' => $codes,
        ]);

        return $codes;
    }

    public function refresh(): void
    {
        $this->clearCache();
        $this->context->refresh();
    }

    public function clearCache(): void
    {
        $this->session->remove('permissions');
        $this->session->remove('roles');
    }

    public function cacheKey(): ?string
    {
        $context = $this->context->context();

        if (!$context) {
            return null;
        }

        return sprintf(
            'u%d:t%d:a%d:v%d',
            $context->userId,
            $context->tenantId,
            $context->appId,
            $context->permissionVersion
        );
    }

    public function inContext(int $tenantId, int $appId, string $permissionCode): bool
    {
        $userId = $this->auth->id();

        if ($userId === null) {
            return false;
        }

        if (!$this->context->availableContexts($userId)) {
            return false;
        }

        return $this->resolver->hasPermission($userId, $tenantId, $appId, $permissionCode);
    }
}