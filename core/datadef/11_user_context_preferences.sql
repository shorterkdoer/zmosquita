CREATE TABLE user_context_preferences (
    user_id BIGINT UNSIGNED PRIMARY KEY,
    last_tenant_id BIGINT UNSIGNED NULL,
    last_app_id BIGINT UNSIGNED NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_ucp_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_ucp_tenant FOREIGN KEY (last_tenant_id) REFERENCES tenants(id),
    CONSTRAINT fk_ucp_app FOREIGN KEY (last_app_id) REFERENCES applications(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
