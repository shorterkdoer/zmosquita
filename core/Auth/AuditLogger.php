<?php

declare(strict_types=1);

namespace ZMosquita\Core\Auth;

use ZMosquita\Core\Repositories\AuditLogRepository;

final class AuditLogger
{
    public function __construct(
        private AuditLogRepository $auditLogs
    ) {
    }

    public function log(array $data): void
    {
        $this->auditLogs->insert($data);
    }

    public function authLoginSuccess(int $userId, array $extra = []): void
    {
        $this->log(array_merge([
            'actor_user_id'   => $userId,
            'subject_user_id' => $userId,
            'event_type'      => 'auth.login.success',
        ], $extra));
    }

    public function authLoginFailure(?string $identity = null, array $extra = []): void
    {
        $payload = array_merge(['identity' => $identity], $extra['payload_json'] ?? []);

        $this->log(array_merge([
            'event_type'   => 'auth.login.failure',
            'payload_json' => $payload,
        ], $extra));
    }

    public function authLogout(?int $userId = null, array $extra = []): void
    {
        $this->log(array_merge([
            'actor_user_id'   => $userId,
            'subject_user_id' => $userId,
            'event_type'      => 'auth.logout',
        ], $extra));
    }

    public function contextSwitch(int $userId, int $tenantId, int $appId, array $extra = []): void
    {
        $this->log(array_merge([
            'actor_user_id'   => $userId,
            'subject_user_id' => $userId,
            'tenant_id'       => $tenantId,
            'app_id'          => $appId,
            'event_type'      => 'context.switch',
        ], $extra));
    }
}