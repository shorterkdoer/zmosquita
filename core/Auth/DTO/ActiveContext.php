<?php

declare(strict_types=1);

namespace ZMosquita\Core\Auth\DTO;

final class ActiveContext
{
    public function __construct(
        public int $userId,
        public int $tenantId,
        public string $tenantCode,
        public string $tenantCatalog = '',
        public int $appId,
        public string $appCode,
        public int $permissionVersion = 1
    ) {
    }

    public function toArray(): array
    {
        return [
            'user_id'            => $this->userId,
            'tenant_id'          => $this->tenantId,
            'tenant_code'        => $this->tenantCode,
            'tenant_catalog'     => $this->tenantCatalog,
            'app_id'             => $this->appId,
            'app_code'           => $this->appCode,
            'permission_version' => $this->permissionVersion,
        ];
    }
}