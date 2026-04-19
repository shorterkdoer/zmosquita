# Deployment de Aplicación para Tenant

Este documento describe el proceso para desplegar una aplicación específica en un tenant existente.

## Arquitectura

```
zmosquita_iam                    (Catálogo central)
├── iam_applications             (Catálogo de apps disponibles)
└── iam_tenants                  (Tenants del sistema)

acme_db                          (Catálogo del tenant ACME)
├── iam_*                        (Tablas core del tenant)
└── demo_personas                (Tablas de apps instaladas)

beta_db                          (Catálogo del tenant BETA)
├── iam_*                        (Tablas core del tenant)
└── facturacion_*                (Otras apps instaladas)
```

## Prerrequisitos

- Tenant creado y verificado: `php bin/zmosquita tenant:list`
- Aplicación publicada en core: `docs/publicacion-aplicacion.md`
- Permisos para escribir en la base de datos del tenant

## Proceso de Deployment

### Paso 1: Verificar tenant existente

```bash
php bin/zmosquita tenant:list
```

**Salida esperada:**
```
Tenants registrados:

ID     Código                Nombre                         Catálogo                   Estado    
----------------------------------------------------------------------------------------------------
1      acme                  ACME Corporation               acme_db                    active    
```

### Paso 2: Verificar aplicación disponible

```sql
USE zmosquita_iam;
SELECT id, code, name, description, status
FROM iam_applications
WHERE status = 'active';
```

### Paso 3: Instalar aplicación en el tenant

El comando `tenant:app:install` realiza automáticamente:
1. Lee todos los scripts `datadef/*.sql` de la aplicación
2. Ejecuta los scripts en el catálogo del tenant
3. Ejecuta `initialseeds/*.sql` si existen
4. Registra el acceso en `iam_user_app_access` (si corresponde)

```bash
php bin/zmosquita tenant:app:install <tenantCode> <appCode>
```

**Parámetros:**
- `tenantCode`: Código del tenant (ej: `acme`)
- `appCode`: Código de la aplicación (ej: `demo`, `contabilidad`)

**Ejemplos:**

```bash
# Instalar app demo en tenant acme
php bin/zmosquita tenant:app:install acme demo

# Instalar app contabilidad en cliente-abc
php bin/zmosquita tenant:app:install cliente-abc contabilidad
```

**Salida esperada:**
```
Instalando app [demo] en tenant [acme] (catálogo: acme_db)...
✓ App [demo] instalada correctamente.
```

### Paso 4: Verificar instalación en el tenant

```sql
-- Conectar al catálogo del tenant
USE acme_db;

-- Verificar tablas de la aplicación
SHOW TABLES LIKE 'demo_%';

-- Verificar estructura de tabla
DESCRIBE demo_personas;

-- Verificar datos iniciales (si existen)
SELECT COUNT(*) FROM demo_personas;
```

**Salida esperada:**
```
+---------------------------+
| Tables_in_acme_db (demo_) |
+---------------------------+
| demo_personas             |
+---------------------------+
```

### Paso 5: Configurar permisos de acceso

La aplicación está instalada pero debes configurar quién puede accederla.

#### 5.1 Registrar acceso a nivel tenant

```sql
USE zmosquita_iam;

INSERT INTO iam_user_app_access (
    user_id, tenant_id, app_id, status, is_default, created_at, updated_at
)
SELECT
    u.id,
    t.id,
    (SELECT id FROM iam_applications WHERE code = 'demo'),
    'active',
    1,
    NOW(),
    NOW()
FROM iam_users u
CROSS JOIN iam_tenants t
WHERE u.email = 'admin@acme.com'
  AND t.code = 'acme';
```

#### 5.2 Asignar roles específicos de la aplicación

```sql
USE zmosquita_iam;

-- Asignar rol de administrador de la app
INSERT INTO iam_user_role_assignments (
    user_id, tenant_id, role_id, created_at, updated_at
)
SELECT
    u.id,
    t.id,
    r.id,
    NOW(),
    NOW()
FROM iam_users u
CROSS JOIN iam_tenants t
CROSS JOIN iam_roles r
WHERE u.email = 'admin@acme.com'
  AND t.code = 'acme'
  AND r.code = 'demo_admin';
```

## Deployment en Varios Tenants

### Instalar misma app en múltiples tenants

```bash
# Script para deployment masivo
for tenant in acme beta cliente-abc; do
    echo "Instalando demo en $tenant..."
    php bin/zmosquita tenant:app:install $tenant demo
done
```

### Instalar múltiples apps en un tenant

```bash
php bin/zmosquita tenant:app:install acme demo
php bin/zmosquita tenant:app:install acme contabilidad
php bin/zmosquita tenant:app:install acme facturacion
```

## Actualización de Aplicación

Cuando una aplicación tiene cambios en su esquema:

### Opción 1: Scripts de migración

Crea scripts de migración específicos:

```bash
# applications/demo/migrations/002_add_telefono_column.sql
ALTER TABLE demo_personas ADD COLUMN telefono VARCHAR(50) NULL AFTER email;
```

### Opción 2: Reinstalación completa

⚠️ **Advertencia:** Esto elimina datos existentes.

```bash
# 1. Eliminar tablas de la app en el tenant
mysql -u usuario -p acme_db -e "DROP TABLE IF EXISTS demo_personas;"

# 2. Reinstalar
php bin/zmosquita tenant:app:install acme demo
```

### Opción 3: Script de migración controlada

```bash
#!/bin/bash
TENANT_DB="acme_db"
APP="demo"

echo "Migrando $APP en $TENANT_DB..."

# Ejecutar migraciones en orden
for migration in applications/$APP/migrations/*.sql; do
    echo "Ejecutando $migration..."
    mysql -u usuario -p $TENANT_DB < $migration
done

echo "Migración completada."
```

## Verificación Post-Deployment

### Checklist

- [ ] Aplicación instalada: `php bin/zmosquita tenant:app:install` ejecutado sin errores
- [ ] Tablas creadas en catálogo del tenant: `SHOW TABLES LIKE 'app_%'`
- [ ] Datos iniciales insertados: `SELECT COUNT(*) FROM app_tabla`
- [ ] Permisos configurados: `iam_user_app_access` actualizado
- [ ] Roles asignados: `iam_user_role_assignments` actualizado
- [ ] Acceso web verificado: Login con usuario y probar acceso a la app

### Script de verificación

```sql
-- Verificar estado completo de instalación
USE zmosquita_iam;

SELECT
    t.code AS tenant,
    t.catalog AS catalogo,
    a.code AS aplicacion,
    a.name AS nombre_app,
    uaa.status AS acceso_status
FROM iam_tenants t
CROSS JOIN iam_applications a
LEFT JOIN iam_user_app_access uaa ON uaa.tenant_id = t.id AND uaa.app_id = a.id
WHERE t.code = 'acme' AND a.code = 'demo';
```

## Troubleshooting

### Error: "Tenant not found"

Verifica que el tenant exista:
```bash
php bin/zmosquita tenant:list
```

### Error: "App datadef file not found"

La aplicación no tiene archivos datadef. Verifica:
```bash
ls -la applications/demo/datadef/
```

### Error: "Table already exists"

Las tablas ya existen en el catálogo del tenant. Opciones:
- Eliminar tablas manualmente si son datos de prueba
- Crear script de migración para producción
- Usar `CREATE TABLE IF NOT EXISTS` en scripts

### Error: "Foreign key constraint fails"

Verifica que las tablas referenciadas existan y se creen en el orden correcto (usando numeración de archivos).

### Error de conexión

Verifica que el catálogo del tenant existe:
```sql
SHOW DATABASES LIKE 'acme_db';
```

## Backup y Restore por Aplicación

### Backup de tablas específicas de una app

```bash
# Backup solo de tablas de la app demo en tenant acme
mysqldump -u usuario -p acme_db demo_personas > backup_demo_acme_$(date +%Y%m%d).sql
```

### Restore de aplicación específica

```bash
# Restore de tablas de demo
mysql -u usuario -p acme_db < backup_demo_acme_20250419.sql
```

## Monitoreo

### Consultar aplicaciones instaladas por tenant

```sql
-- Para un tenant específico
SELECT 
    t.code AS tenant,
    t.name AS tenant_nombre,
    a.code AS app,
    a.name AS app_nombre
FROM iam_tenants t
CROSS JOIN information_schema.tables x
JOIN iam_applications a ON x.table_name LIKE CONCAT(a.code, '\\_%')
WHERE x.table_schema = t.catalog
  AND t.code = 'acme'
GROUP BY a.code;
```

### Consultar tenants con una app específica

```sql
SELECT 
    t.code AS tenant,
    t.name AS tenant_nombre,
    t.catalog AS catalogo
FROM iam_tenants t
WHERE EXISTS (
    SELECT 1
    FROM information_schema.tables x
    JOIN iam_applications a ON x.table_name LIKE CONCAT(a.code, '\\_%')
    WHERE x.table_schema = t.catalog
      AND a.code = 'demo'
);
```

## Consideraciones de Producción

1. **Testing previo:** Probar deployment en ambiente de staging
2. **Backups:** Realizar backup del catálogo antes de instalar
3. **Ventana de mantenimiento:** Informar a usuarios del mantenimiento
4. **Rollback plan:** Tener script de rollback preparado
5. **Validación:** Verificar funcionalidad crítica post-deployment
6. **Documentación:** Registrar versión instalada y cambios realizados

## Ejemplo Completo de Deployment

```bash
# 1. Verificar tenant
php bin/zmosquita tenant:list | grep acme

# 2. Backup del catálogo
mysqldump -u usuario -p acme_db > backup_acme_pre_demo_$(date +%Y%m%d_%H%M).sql

# 3. Instalar aplicación
php bin/zmosquita tenant:app:install acme demo

# 4. Verificar tablas creadas
mysql -u usuario -p acme_db -e "SHOW TABLES LIKE 'demo_%';"

# 5. Configurar permisos
mysql -u usuario -p zmosquita_iam < config_permisos_acme_demo.sql

# 6. Validar datos iniciales
mysql -u usuario -p acme_db -e "SELECT COUNT(*) FROM demo_personas;"

echo "Deployment completado exitosamente."
```
