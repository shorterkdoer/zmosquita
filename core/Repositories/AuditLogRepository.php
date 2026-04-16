<?php

declare(strict_types=1);

namespace ZMosquita\Core\Repositories;

use ZMosquita\Core\Database\Connection;
use ZMosquita\Core\Database\TableResolver;
use ZMosquita\Core\Database\QueryBuilder;

final class AuditLogRepository
{
    public function __construct(
        private QueryBuilder $db,
        private TableResolver $tables
    ) {
    }

    public function insert(array $data): void
    {
        $table = $this->tables->iam('audit_log');

        $defaults = [
            'actor_user_id'   => null,
            'subject_user_id' => null,
            'tenant_id'       => null,
            'app_id'          => null,
            'event_type'      => null,
            'entity_type'     => null,
            'entity_id'       => null,
            'payload_json'    => null,
            'ip_address'      => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent'      => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ];

        $data = array_merge($defaults, $data);

        $this->db->execute(
            "INSERT INTO {$table}
            (
              actor_user_id, subject_user_id, tenant_id, app_id,
              event_type, entity_type, entity_id, payload_json,
              ip_address, user_agent, created_at
            )
            VALUES
            (
              :actor_user_id, :subject_user_id, :tenant_id, :app_id,
              :event_type, :entity_type, :entity_id, :payload_json,
              :ip_address, :user_agent, NOW()
            )",
            [
                'actor_user_id'   => $data['actor_user_id'],
                'subject_user_id' => $data['subject_user_id'],
                'tenant_id'       => $data['tenant_id'],
                'app_id'          => $data['app_id'],
                'event_type'      => $data['event_type'],
                'entity_type'     => $data['entity_type'],
                'entity_id'       => $data['entity_id'],
                'payload_json'    => $data['payload_json'] ? json_encode($data['payload_json'], JSON_UNESCAPED_UNICODE) : null,
                'ip_address'      => $data['ip_address'],
                'user_agent'      => $data['user_agent'],
            ]
        );
    }
}