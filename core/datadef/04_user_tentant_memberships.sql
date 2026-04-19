CREATE TABLE user_tenant_memberships (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    tenant_id BIGINT UNSIGNED NOT NULL,
    status ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    starts_at DATETIME NULL,
    ends_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    deleted_at DATETIME NULL,
    UNIQUE KEY uq_user_tenant_membership (user_id, tenant_id),
    KEY idx_utm_tenant (tenant_id),
    KEY idx_utm_status (status),
    CONSTRAINT fk_utm_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_utm_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;