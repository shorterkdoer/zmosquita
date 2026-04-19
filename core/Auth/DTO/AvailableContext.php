<?php

declare(strict_types=1);

namespace ZMosquita\Core\Auth\DTO;

final class AvailableContext
{
    public function __construct(
        public int $tenantId,
        public string $tenantCode,
        public string $tenantName,
        public string $tenantCatalog = '',
        public int $appId,
        public string $appCode,
        public string $appName
    ) {
    }

    public function toArray(): array
    {
        return [
            'tenant_id'      => $this->tenantId,
            'tenant_code'    => $this->tenantCode,
            'tenant_name'    => $this->tenantName,
            'tenant_catalog' => $this->tenantCatalog,
            'app_id'         => $this->appId,
            'app_code'       => $this->appCode,
            'app_name'       => $this->appName,
        ];
    }
}