# Deployment de Tenant

Este documento describe el proceso completo para crear y desplegar un nuevo tenant en el sistema ZMosquita.

## Arquitectura Database-Per-Tenant

ZMosquita utiliza una arquitectura de aislamiento completo donde cada tenant tiene su propio catálogo (database) MariaDB:

```
zmosquita_iam           (Catálogo central IAM)
├── iam_users
├── iam_tenants
├── iam_applications
└── ...

acme_db                 (Catálogo del tenant ACME)
├── iam_users           (Usuarios específicos del tenant)
├── iam_tenants         (Metadatos del tenant)
├── demo_personas       (Tablas de aplicaciones)
└── ...

beta_db                 (Catálogo del tenant BETA)
├── iam_users
├── iam_tenants
├── demo_personas
└── ...
```

## Prerrequisitos

- Acceso a MariaDB con privilegios CREATE DATABASE
- Archivo `.env` configurado con credenciales de base de datos
- Permisos de ejecución en `bin/zmosquita.php`

## Proceso de Deployment

### Paso 1: Verificar configuración de base de datos

```bash
cat .env | grep DB_
```

Ejemplo de configuración:
```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=zmosquita_iam
DB_USER=zmosquita_user
DB_PASSWORD=secure_password
```

### Paso 2: Crear el tenant

El comando `tenant:make` realiza automáticamente:
1. Crea el catálogo (database) específico del tenant
2. Instala el esquema core en el catálogo del tenant
3. Registra el tenant en la tabla central `iam_tenants`

```bash
php bin/zmosquita tenant:make <code> <nombre> [catalogo]
```

**Parámetros:**
- `code`: Código identificador único del tenant (ej: `acme`)
- `nombre`: Nombre descriptivo del tenant (ej: `ACME Corporation`)
- `catalogo`: (Opcional) Nombre del catálogo MariaDB. Si se omite, usa el `code`

**Ejemplos:**

```bash
# Crear tenant con catálogo explícito
php bin/zmosquita tenant:make acme "ACME Corporation" acme_db

# Crear tenant usando el code como nombre de catálogo
php bin/zmosquita tenant:make beta "Beta Industries"

# Crear tenant con código compuesto
php bin/zmosquita tenant:make cliente-abc "Cliente ABC S.A." cliente_abc_db
```

**Salida esperada:**
```
Creando tenant: acme (ACME Corporation) en catálogo [acme_db]...
✓ Catálogo [acme_db] creado.
Instalando core schema en catálogo [acme_db]...
✓ Core schema instalado en tenant.
✓ Tenant registrado en iam_tenants.

Tenant creado correctamente: acme (ACME Corporation) en [acme_db]
```

### Paso 3: Verificar la creación

```bash
# Listar todos los tenants
php bin/zmosquita tenant:list
```

**Salida esperada:**
```
Tenants registrados:

ID     Código                Nombre                         Catálogo                   Estado    
----------------------------------------------------------------------------------------------------
1      acme                  ACME Corporation               acme_db                    active    
2      beta                  Beta Industries                beta_db                    active    

Total: 2 tenant(s)
```

### Paso 4: Verificar en MariaDB (opcional)

```sql
-- Verificar que el catálogo fue creado
SHOW DATABASES LIKE 'acme_db';

-- Verificar tablas en el catálogo del tenant
USE acme_db;
SHOW TABLES;

-- Verificar registro en IAM
USE zmosquita_iam;
SELECT * FROM iam_tenants WHERE code = 'acme';
```

## Post-Deployment

### Crear usuario administrador del tenant

```sql
USE acme_db;

INSERT INTO iam_users (email, password_hash, name, status, created_at, updated_at)
VALUES (
    'admin@acme.com',
    '$2y$10$...',  -- Hash generado con password_hash()
    'Admin ACME',
    'active',
    NOW(),
    NOW()
);
```

### Asignar membresía del usuario al tenant

```sql
USE zmosquita_iam;

INSERT INTO iam_user_tenant_memberships (user_id, tenant_id, status, created_at, updated_at)
VALUES (
    (SELECT id FROM acme_db.iam_users WHERE email = 'admin@acme.com'),
    (SELECT id FROM iam_tenants WHERE code = 'acme'),
    'active',
    NOW(),
    NOW()
);
```

## Gestión de Tenants

### Eliminar un tenant (peligroso)

```bash
# ADVERTENCIA: Esto elimina todo el catálogo y datos del tenant
php bin/zmosquita tenant:drop acme_db --force
```

**Este comando:**
1. Elimina el catálogo completo `acme_db`
2. Marca el tenant como eliminado (soft delete) en `iam_tenants`

## Troubleshooting

### Error: "database exists"

El catálogo ya existe. Verifica con:
```bash
php bin/zmosquita tenant:list
```

O en MariaDB:
```sql
SHOW DATABASES LIKE 'acme_db';
```

### Error: "Failed to connect to database"

Verifica que las credenciales en `.env` sean correctas y que el servicio MariaDB esté activo:
```bash
sudo systemctl status mariadb
```

### Error durante instalación del core

Revisa los logs en `core/datadef/` para verificar que todos los archivos SQL sean válidos:
```bash
ls -la core/datadef/
```

## Backup y Restore

### Backup de un tenant específico

```bash
mysqldump -u usuario -p acme_db > backup_acme_$(date +%Y%m%d).sql
```

### Restore de un tenant

```bash
mysql -u usuario -p acme_db < backup_acme_20250419.sql
```

## Consideraciones de Seguridad

- Cada tenant opera en su propio catálogo, proporcionando aislamiento lógico completo
- Las conexiones se resuelven dinámicamente según el contexto del tenant
- Los backups pueden realizarse por tenant sin afectar la operatividad de otros
- Para máxima seguridad, considera implementar row-level security adicional a nivel de aplicación
