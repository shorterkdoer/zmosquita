-- =========================================================
-- Seed inicial para:
-- - admin global
-- - tenant de prueba
-- - aplicación demo
-- - roles, permisos y asignaciones
-- =========================================================

START TRANSACTION;

-- ---------------------------------------------------------
-- 1. Usuario admin global
-- password sugerida: admin1234
-- hash bcrypt generado con password_hash(...)
-- ---------------------------------------------------------
INSERT INTO iam_users (
    username,
    email,
    password_hash,
    status,
    created_at,
    updated_at
)
SELECT
    'admin',
    'admin@example.com',
    '$2y$12$1CHpWDHWiYlsYb5mSlJmHOCeOts8/WsOT9250giC8r6uIoTI9qsK6',
    'active',
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM iam_users WHERE username = 'admin' OR email = 'admin@example.com'
);

-- ---------------------------------------------------------
-- 2. Tenant de prueba
-- ---------------------------------------------------------
INSERT INTO iam_tenants (
    code,
    name,
    status,
    created_at,
    updated_at
)
SELECT
    'demo-tenant',
    'Tenant de Prueba',
    'active',
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM iam_tenants WHERE code = 'demo-tenant'
);

-- ---------------------------------------------------------
-- 3. Aplicación demo
-- ---------------------------------------------------------
INSERT INTO iam_applications (
    code,
    name,
    status,
    created_at,
    updated_at
)
SELECT
    'demo',
    'Aplicacion Demo Agenda',
    'active',
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM iam_applications WHERE code = 'demo'
);

-- ---------------------------------------------------------
-- 4. Roles mínimos
-- ---------------------------------------------------------
INSERT INTO iam_roles (
    code,
    name,
    status
)
SELECT 'superadmin', 'Super Administrador Global', 'active'
WHERE NOT EXISTS (
    SELECT 1 FROM iam_roles WHERE code = 'superadmin'
);

INSERT INTO iam_roles (
    code,
    name,
    status
)
SELECT 'demo_admin', 'Administrador Demo', 'active'
WHERE NOT EXISTS (
    SELECT 1 FROM iam_roles WHERE code = 'demo_admin'
);

-- ---------------------------------------------------------
-- 5. Permisos de la app demo
-- ---------------------------------------------------------
INSERT INTO iam_permissions (code, name, status)
SELECT 'personas.view', 'Ver personas', 'active'
WHERE NOT EXISTS (
    SELECT 1 FROM iam_permissions WHERE code = 'personas.view'
);

INSERT INTO iam_permissions (code, name, status)
SELECT 'personas.create', 'Crear personas', 'active'
WHERE NOT EXISTS (
    SELECT 1 FROM iam_permissions WHERE code = 'personas.create'
);

INSERT INTO iam_permissions (code, name, status)
SELECT 'personas.edit', 'Editar personas', 'active'
WHERE NOT EXISTS (
    SELECT 1 FROM iam_permissions WHERE code = 'personas.edit'
);

INSERT INTO iam_permissions (code, name, status)
SELECT 'personas.delete', 'Borrar personas', 'active'
WHERE NOT EXISTS (
    SELECT 1 FROM iam_permissions WHERE code = 'personas.delete'
);

-- ---------------------------------------------------------
-- 6. Asignar permisos al rol demo_admin
-- ---------------------------------------------------------
INSERT INTO iam_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM iam_roles r
JOIN iam_permissions p ON p.code = 'personas.view'
WHERE r.code = 'demo_admin'
  AND NOT EXISTS (
      SELECT 1
      FROM iam_role_permissions rp
      WHERE rp.role_id = r.id
        AND rp.permission_id = p.id
  );

INSERT INTO iam_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM iam_roles r
JOIN iam_permissions p ON p.code = 'personas.create'
WHERE r.code = 'demo_admin'
  AND NOT EXISTS (
      SELECT 1
      FROM iam_role_permissions rp
      WHERE rp.role_id = r.id
        AND rp.permission_id = p.id
  );

INSERT INTO iam_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM iam_roles r
JOIN iam_permissions p ON p.code = 'personas.edit'
WHERE r.code = 'demo_admin'
  AND NOT EXISTS (
      SELECT 1
      FROM iam_role_permissions rp
      WHERE rp.role_id = r.id
        AND rp.permission_id = p.id
  );

INSERT INTO iam_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM iam_roles r
JOIN iam_permissions p ON p.code = 'personas.delete'
WHERE r.code = 'demo_admin'
  AND NOT EXISTS (
      SELECT 1
      FROM iam_role_permissions rp
      WHERE rp.role_id = r.id
        AND rp.permission_id = p.id
  );

-- ---------------------------------------------------------
-- 7. Membresía del admin en el tenant demo
-- ---------------------------------------------------------
INSERT INTO iam_user_tenant_memberships (
    user_id,
    tenant_id,
    status,
    starts_at,
    created_at,
    updated_at
)
SELECT
    u.id,
    t.id,
    'active',
    NOW(),
    NOW(),
    NOW()
FROM iam_users u
JOIN iam_tenants t ON t.code = 'demo-tenant'
WHERE u.username = 'admin'
  AND NOT EXISTS (
      SELECT 1
      FROM iam_user_tenant_memberships m
      WHERE m.user_id = u.id
        AND m.tenant_id = t.id
  );

-- ---------------------------------------------------------
-- 8. Acceso del admin a la app demo dentro del tenant demo
-- ---------------------------------------------------------
INSERT INTO iam_user_app_access (
    user_id,
    tenant_id,
    app_id,
    status,
    starts_at,
    created_at,
    updated_at
)
SELECT
    u.id,
    t.id,
    a.id,
    'active',
    NOW(),
    NOW(),
    NOW()
FROM iam_users u
JOIN iam_tenants t ON t.code = 'demo-tenant'
JOIN iam_applications a ON a.code = 'demo'
WHERE u.username = 'admin'
  AND NOT EXISTS (
      SELECT 1
      FROM iam_user_app_access x
      WHERE x.user_id = u.id
        AND x.tenant_id = t.id
        AND x.app_id = a.id
  );

-- ---------------------------------------------------------
-- 9. Rol global superadmin al admin
-- ---------------------------------------------------------
INSERT INTO iam_user_role_assignments (
    user_id,
    role_id,
    scope_type,
    tenant_id,
    app_id,
    status,
    created_at,
    updated_at
)
SELECT
    u.id,
    r.id,
    'global',
    NULL,
    NULL,
    'active',
    NOW(),
    NOW()
FROM iam_users u
JOIN iam_roles r ON r.code = 'superadmin'
WHERE u.username = 'admin'
  AND NOT EXISTS (
      SELECT 1
      FROM iam_user_role_assignments ura
      WHERE ura.user_id = u.id
        AND ura.role_id = r.id
        AND ura.scope_type = 'global'
  );

-- ---------------------------------------------------------
-- 10. Rol demo_admin del admin en tenant+app demo
-- ---------------------------------------------------------
INSERT INTO iam_user_role_assignments (
    user_id,
    role_id,
    scope_type,
    tenant_id,
    app_id,
    status,
    created_at,
    updated_at
)
SELECT
    u.id,
    r.id,
    'tenant_app',
    t.id,
    a.id,
    'active',
    NOW(),
    NOW()
FROM iam_users u
JOIN iam_roles r ON r.code = 'demo_admin'
JOIN iam_tenants t ON t.code = 'demo-tenant'
JOIN iam_applications a ON a.code = 'demo'
WHERE u.username = 'admin'
  AND NOT EXISTS (
      SELECT 1
      FROM iam_user_role_assignments ura
      WHERE ura.user_id = u.id
        AND ura.role_id = r.id
        AND ura.scope_type = 'tenant_app'
        AND ura.tenant_id = t.id
        AND ura.app_id = a.id
  );

-- ---------------------------------------------------------
-- 11. Contexto preferido
-- ---------------------------------------------------------
INSERT INTO iam_user_context_preferences (
    user_id,
    last_tenant_id,
    last_app_id,
    updated_at
)
SELECT
    u.id,
    t.id,
    a.id,
    NOW()
FROM iam_users u
JOIN iam_tenants t ON t.code = 'demo-tenant'
JOIN iam_applications a ON a.code = 'demo'
WHERE u.username = 'admin'
  AND NOT EXISTS (
      SELECT 1
      FROM iam_user_context_preferences p
      WHERE p.user_id = u.id
  );

-- ---------------------------------------------------------
-- 12. Versionado de permisos
-- ---------------------------------------------------------
INSERT INTO iam_user_permission_versions (
    user_id,
    version,
    updated_at
)
SELECT
    u.id,
    1,
    NOW()
FROM iam_users u
WHERE u.username = 'admin'
  AND NOT EXISTS (
      SELECT 1
      FROM iam_user_permission_versions v
      WHERE v.user_id = u.id
  );

COMMIT;