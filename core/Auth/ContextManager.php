<?php

declare(strict_types=1);

namespace ZMosquita\Core\Auth;

use ZMosquita\Core\Auth\DTO\ActiveContext;
use ZMosquita\Core\Auth\DTO\AvailableContext;
use ZMosquita\Core\Repositories\UserContextPreferenceRepository;
use ZMosquita\Core\Repositories\UserPermissionVersionRepository;

final class ContextManager
{
    public function __construct(
        private AuthManager $auth,
        private ContextResolver $resolver,
        private SessionGuard $session,
        private UserContextPreferenceRepository $preferences,
        private UserPermissionVersionRepository $permissionVersions,
        private AuditLogger $audit
    ) {
    }

    /**
     * @return AvailableContext[]
     */
    public function availableContexts(?int $userId = null): array
    {
        $userId ??= $this->auth->id();

        if (!$userId) {
            return [];
        }

        return $this->resolver->resolveAvailableContexts($userId);
    }

    public function switch(int $tenantId, int $appId): bool
    {
        return $this->setContext($tenantId, $appId);
    }

    public function setContext(int $tenantId, int $appId): bool
    {
        $userId = $this->auth->id();

        if (!$userId) {
            return false;
        }

        $context = $this->resolver->findContext($userId, $tenantId, $appId);

        if (!$context) {
            return false;
        }

        $version = $this->permissionVersions->getVersionForUser($userId);

        $catalog = $this->resolver->findTenant($context->tenantId)['catalog'] ?? null;

        $this->session->set('context', [
            'user_id'            => $userId,
            'tenant_id'          => $context->tenantId,
            'tenant_code'        => $context->tenantCode,
            'tenant_name'        => $context->tenantName,
            'tenant_catalog'     => $catalog,
            'app_id'             => $context->appId,
            'app_code'           => $context->appCode,
            'app_name'           => $context->appName,
            'permission_version' => $version,
            'resolved_at'        => date('Y-m-d H:i:s'),
        ]);

        $this->session->remove('permissions');

        $this->rememberLastContext($tenantId, $appId);
        $this->audit->contextSwitch($userId, $tenantId, $appId);

        return true;
    }

    public function clear(): void
    {
        $this->session->remove('context');
        $this->session->remove('permissions');
    }

    public function isValid(): bool
    {
        return $this->validateCurrentContext();
    }

    public function context(): ?ActiveContext
    {
        $ctx = $this->session->get('context');

        if (!is_array($ctx) || empty($ctx['user_id']) || empty($ctx['tenant_id']) || empty($ctx['app_id'])) {
            return null;
        }

        return new ActiveContext(
            userId: (int)$ctx['user_id'],
            tenantId: (int)$ctx['tenant_id'],
            tenantCode: (string)$ctx['tenant_code'],
            tenantCatalog: (string)($ctx['tenant_catalog'] ?? ''),
            appId: (int)$ctx['app_id'],
            appCode: (string)$ctx['app_code'],
            permissionVersion: (int)($ctx['permission_version'] ?? 1)
        );
    }

    public function tenant(): ?array
    {
        $ctx = $this->session->get('context');

        return is_array($ctx) && isset($ctx['tenant_id'])
            ? [
                'id'      => (int)$ctx['tenant_id'],
                'code'    => (string)$ctx['tenant_code'],
                'name'    => (string)($ctx['tenant_name'] ?? ''),
                'catalog' => (string)($ctx['tenant_catalog'] ?? ''),
            ]
            : null;
    }

    public function app(): ?array
    {
        $ctx = $this->session->get('context');

        return is_array($ctx) && isset($ctx['app_id'])
            ? [
                'id'   => (int)$ctx['app_id'],
                'code' => (string)$ctx['app_code'],
                'name' => (string)($ctx['app_name'] ?? ''),
            ]
            : null;
    }

    public function tenantId(): ?int
    {
        return $this->tenant()['id'] ?? null;
    }

    public function appId(): ?int
    {
        return $this->app()['id'] ?? null;
    }

    public function hasContext(): bool
    {
        return $this->context() !== null;
    }

    public function rememberLastContext(int $tenantId, int $appId): void
    {
        $userId = $this->auth->id();

        if ($userId) {
            $this->preferences->saveLastContext($userId, $tenantId, $appId);
        }
    }

    public function restorePreferredContext(): bool
    {
        $userId = $this->auth->id();

        if (!$userId) {
            return false;
        }

        $pref = $this->preferences->findByUserId($userId);

        if (!$pref || empty($pref['last_tenant_id']) || empty($pref['last_app_id'])) {
            return false;
        }

        return $this->setContext((int)$pref['last_tenant_id'], (int)$pref['last_app_id']);
    }

    public function resolveSingleContext(): bool
    {
        $contexts = $this->availableContexts();

        if (count($contexts) !== 1) {
            return false;
        }

        $context = $contexts[0];

        return $this->setContext($context->tenantId, $context->appId);
    }

    public function validateCurrentContext(): bool
    {
        $ctx = $this->context();

        if (!$ctx) {
            return false;
        }

        if (!$this->auth->check()) {
            return false;
        }

        return $this->resolver->contextExists($ctx->userId, $ctx->tenantId, $ctx->appId);
    }

    public function refresh(): ?ActiveContext
    {
        if (!$this->validateCurrentContext()) {
            $this->clear();
            return null;
        }

        $ctx = $this->context();

        if (!$ctx) {
            return null;
        }

        $version = $this->permissionVersions->getVersionForUser($ctx->userId);

        $this->session->set('context.permission_version', $version);

        return new ActiveContext(
            userId: $ctx->userId,
            tenantId: $ctx->tenantId,
            tenantCode: $ctx->tenantCode,
            tenantCatalog: $ctx->tenantCatalog,
            appId: $ctx->appId,
            appCode: $ctx->appCode,
            permissionVersion: $version
        );
    }
}