<?php

declare(strict_types=1);

namespace ZMosquita\Core\Auth;

use ZMosquita\Core\Auth\DTO\AvailableContext;
use ZMosquita\Core\Repositories\AppAccessRepository;
use ZMosquita\Core\Repositories\ApplicationRepository;
use ZMosquita\Core\Repositories\MembershipRepository;
use ZMosquita\Core\Repositories\TenantRepository;

final class ContextResolver
{
    public function __construct(
        private MembershipRepository $memberships,
        private AppAccessRepository $appAccess,
        private TenantRepository $tenants,
        private ApplicationRepository $applications
    ) {
    }

    /**
     * @return AvailableContext[]
     */
    public function resolveAvailableContexts(int $userId): array
    {
        $result = [];

        foreach ($this->memberships->activeForUser($userId) as $membership) {
            $tenantId = (int)$membership['tenant_id'];
            $tenant = $this->tenants->findById($tenantId);

            if (!$tenant) {
                continue;
            }

            foreach ($this->appAccess->activeForUserAndTenant($userId, $tenantId) as $access) {
                $appId = (int)$access['app_id'];
                $app = $this->applications->findById($appId);

                if (!$app) {
                    continue;
                }

                $result[] = new AvailableContext(
                    tenantId: $tenantId,
                    tenantCode: (string)$tenant['code'],
                    tenantName: (string)$tenant['name'],
                    tenantCatalog: (string)($tenant['catalog'] ?? ''),
                    appId: $appId,
                    appCode: (string)$app['code'],
                    appName: (string)$app['name']
                );
            }
        }

        usort($result, fn (AvailableContext $a, AvailableContext $b) =>
            [$a->tenantName, $a->appName] <=> [$b->tenantName, $b->appName]
        );

        return $result;
    }

    public function contextExists(int $userId, int $tenantId, int $appId): bool
    {
        return $this->memberships->existsActive($userId, $tenantId)
            && $this->appAccess->existsActive($userId, $tenantId, $appId)
            && $this->tenants->findById($tenantId) !== null
            && $this->applications->findById($appId) !== null;
    }

    public function findTenant(int $tenantId): ?array
    {
        return $this->tenants->findById($tenantId);
    }

    public function findApp(int $appId): ?array
    {
        return $this->applications->findById($appId);
    }

    public function findContext(int $userId, int $tenantId, int $appId): ?AvailableContext
    {
        foreach ($this->resolveAvailableContexts($userId) as $context) {
            if ($context->tenantId === $tenantId && $context->appId === $appId) {
                return $context;
            }
        }

        return null;
    }
}