CREATE TABLE user_permission_versions (
    user_id BIGINT UNSIGNED PRIMARY KEY,
    version BIGINT UNSIGNED NOT NULL DEFAULT 1,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_upv_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;