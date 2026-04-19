# Plan: Multi-Tenant con Aislamiento por Catálogo

## Objetivo
Implementar aislamiento de datos por tenant usando catálogos (bases de datos) separados en MariaDB, permitiendo que cada tenant tenga su propia base de datos y las aplicaciones se desplieguen en ellas.

## Arquitectura Actual
- `Connection`: Maneja una sola conexión PDO
- `ContextManager`: Gestiona contexto tenant/aplicación en sesión
- `TableResolver`: Prefija tablas (`iam_` para core, `{app}_` para aplicaciones)
- `DataDefResolver`: Resuelve archivos `datadef/*.sql`
- `TenantRepository`: Consulta tabla `tenants` (ya tiene campo `catalog`)

## Problema a Resolver
Actualmente todos los tenants comparten la misma base de datos. Necesitamos:
1. Crear un catálogo separado por tenant
2. Las tablas de cada aplicación vivan en el catálogo del tenant
3. Soporte para datos iniciales (initialseeds)
4. Cambio dinámico de conexión según el tenant activo

## Solución Propuesta

### 1. Connection Pool y Tenant-Aware Connection

#### Componentes Nuevos

**`core/Database/TenantConnectionResolver.php`**
```php
final class TenantConnectionResolver
{
    private array $connections = [];
    private ?Connection $iamConnection = null;

    public function resolve(?int $tenantId = null): Connection;
    public function resolveForCatalog(string $catalog): Connection;
    public function iam(): Connection;
    public function hasActiveTenant(): bool;
    public function disconnectTenant(int $tenantId): void;
    public function disconnectAll(): void;
}
```

**`core/Database/TenantAwareConnection.php`**
```php
final class TenantAwareConnection
{
    public function __construct(
        private TenantConnectionResolver $resolver,
        private ?ContextManager $contextManager = null
    );

    public function current(): Connection;
    public function forTenant(int $tenantId): Connection;
    public function forCatalog(string $catalog): Connection;
    public function iam(): Connection;
}
```

### 2. Actualizar DatabaseServiceProvider

```php
// core/Bootstrap/DatabaseServiceProvider.php

$this->container->bind(TenantConnectionResolver::class, fn ($c) => {
    $config = $c->get(Config::class);
    return new TenantConnectionResolver($config->getArray('database'));
});

$this->container->bind(Connection::class, fn ($c) => {
    $tenantResolver = $c->get(TenantConnectionResolver::class);
    $contextManager = $c->has(ContextManager::class)
        ? $c->get(ContextManager::class)
        : null;

    return new TenantAwareConnection($tenantResolver, $contextManager);
});
```

### 3. Schema Installer para Tenants

**`core/Database/Schema/TenantSchemaInstaller.php`**
```php
final class TenantSchemaInstaller
{
    public function __construct(
        private TenantConnectionResolver $connections,
        private CoreSchemaInstaller $coreInstaller,
        private AppSchemaInstaller $appInstaller
    );

    public function createTenantCatalog(string $catalog, array $options = []): bool;
    public function dropTenantCatalog(string $catalog): bool;
    public function installCoreToTenant(string $catalog): SchemaInstallResult;
    public function installAppToTenant(string $catalog, string $appCode): SchemaInstallResult;
    public function tenantExists(string $catalog): bool;
}
```

### 4. Soporte para InitialSeeds

**`core/Database/Schema/InitialSeedsInstaller.php`**
```php
final class InitialSeedsInstaller
{
    public function __construct(
        private Connection $connection,
        private InitialSeedsResolver $resolver,
        private SqlSchemaLoader $loader
    );

    public function install(string $appCode): SchemaInstallResult;
    public function installFile(string $appCode, string $path): SchemaInstallResult;
}
```

**`core/Database/InitialSeedsResolver.php`**
```php
final class InitialSeedsResolver
{
    public function app(string $appCode, string $name): string;
    public function allApp(string $appCode): array;
    public function existsApp(string $appCode, string $name): bool;
}
```

**Actualizar `core/Support/Paths.php`**
```php
public static function appInitialSeeds(string $appCode, string $file = ''): string
{
    return self::application($appCode, 'initialseeds' . ($file ? DIRECTORY_SEPARATOR . ltrim($file, DIRECTORY_SEPARATOR) : ''));
}
```

### 5. Actualizar TenantRepository

**`core/Repositories/TenantRepository.php`**
```php
public function findById(int $id): ?array
{
    $table = $this->tables->iam('tenants');

    return $this->db->fetchOne(
        "SELECT id, code, name, catalog, description, status, created_at, updated_at
         FROM {$table}
         WHERE id = :id
           AND deleted_at IS NULL
           AND status = 'active'
         LIMIT 1",
        ['id' => $id]
    );
}

public function findByCode(string $code): ?array;
public function findByCatalog(string $catalog): ?array;
public function getAllActive(): array;
```

### 6. Comandos CLI

**`core/Console/Commands/TenantMakeCommand.php`**
```php
final class TenantMakeCommand
{
    protected string $name = 'tenant:make';
    protected string $description = 'Create a new tenant catalog and install core schema';

    public function handle(): int
    {
        // 1. Prompt for tenant code, name, catalog
        // 2. Create tenant catalog
        // 3. Install core schema to tenant catalog
        // 4. Insert tenant record in iam.tenants
    }
}
```

**`core/Console/Commands/TenantAppInstallCommand.php`**
```php
final class TenantAppInstallCommand
{
    protected string $name = 'tenant:app:install';
    protected string $description = 'Install an application to a tenant catalog';

    public function handle(): int
    {
        // 1. Select tenant
        // 2. Select application
        // 3. Install app schema to tenant catalog
        // 4. Install initial seeds
        // 5. Insert application access record
    }
}
```

**`core/Console/Commands/TenantDropCommand.php`**
```php
final class TenantDropCommand
{
    protected string $name = 'tenant:drop';
    protected string $description = 'Drop a tenant catalog (DANGEROUS!)';

    public function handle(): int
    {
        // 1. Confirm operation
        // 2. Drop tenant catalog
        // 3. Soft delete tenant record
    }
}
```

**`core/Console/Commands/TenantListCommand.php`**
```php
final class TenantListCommand
{
    protected string $name = 'tenant:list';
    protected string $description = 'List all tenants with their catalogs';

    public function handle(): int
    {
        // Display tenants from iam.tenants
    }
}
```

## Flujo de Instalación

### Crear un Nuevo Tenant

1. **Ejecutar comando**: `php cli tenant:make`
   - Ingresar código: `empresa_acme`
   - Ingresar nombre: `ACME Corporation`
   - Ingresar catálogo: `acme_db` (o generar automáticamente)

2. **El comando ejecuta**:
   ```sql
   CREATE DATABASE `acme_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Instalar core schema**:
   - Ejecuta todos los scripts de `core/datadef/*.sql` en `acme_db`
   - Crea tablas: `iam_users`, `iam_tenants`, `iam_applications`, etc.

4. **Insertar registro**:
   ```sql
   INSERT INTO iam_tenants (code, name, catalog, status, created_at, updated_at)
   VALUES ('empresa_acme', 'ACME Corporation', 'acme_db', 'active', NOW(), NOW());
   ```

### Instalar una Aplicación en un Tenant

1. **Ejecutar comando**: `php cli tenant:app:install`
   - Seleccionar tenant: `ACME Corporation`
   - Seleccionar aplicación: `contabilidad`

2. **El comando ejecuta**:
   - Se conecta a `acme_db`
   - Ejecuta scripts de `applications/contabilidad/datadef/*.sql`
   - Crea tablas: `contabilidad_*`
   - Ejecuta scripts de `applications/contabilidad/initialseeds/*.sql`
   - Inserta datos iniciales

### Flujo de Autenticación

1. **Login**:
   - Usuario se autentica contra catálogo `iam` (base de datos central)
   - `AuthManager` valida credenciales

2. **Resolución de Contexto**:
   - `ContextResolver` obtiene tenants y aplicaciones disponibles
   - Usuario selecciona contexto (o se usa el default)

3. **Cambio de Contexto**:
   ```php
   $contextManager->setContext($tenantId, $appId);
   ```
   - Guarda en sesión: tenant_id, app_id, **catalog**
   - `TenantConnectionResolver` crea conexión para el catálogo del tenant
   - Todas las consultas posteriores usan esa conexión

4. **Consultas**:
   - Tablas core: `SELECT * FROM iam_users WHERE ...` (conexión iam)
   - Tablas app: `SELECT * FROM contabilidad_facturas WHERE ...` (conexión tenant)

## Estructura de Directorios

```
zmosquita2/
├── core/
│   ├── datadef/                 # Schema core (iam)
│   │   ├── 01_users.sql
│   │   ├── 02_tenants.sql
│   │   └── ...
│   ├── Database/
│   │   ├── TenantConnectionResolver.php    # NUEVO
│   │   ├── TenantAwareConnection.php       # NUEVO
│   │   └── Schema/
│   │       ├── TenantSchemaInstaller.php   # NUEVO
│   │       └── InitialSeedsInstaller.php   # NUEVO
│   └── Console/
│       └── Commands/
│           ├── TenantMakeCommand.php       # NUEVO
│           ├── TenantAppInstallCommand.php # NUEVO
│           ├── TenantDropCommand.php       # NUEVO
│           └── TenantListCommand.php       # NUEVO
└── applications/
    ├── contabilidad/
    │   ├── datadef/                # Schema aplicación
    │   │   ├── 01_facturas.sql
    │   │   ├── 02_clientes.sql
    │   │   └── ...
    │   └── initialseeds/           # Datos iniciales
    │       ├── 01_configuracion.sql
    │       ├── 02_roles.sql
    │       └── ...
    └── sueldos/
        ├── datadef/
        └── initialseeds/
```

## Consideraciones Importantes

### Configuración de Base de Datos

`.env`:
```ini
DB_HOST=localhost
DB_PORT=3306
DB_NAME=iam          # Base de datos central (core/iam)
DB_USER=root
DB_PASS=secret
```

### Seguridad

1. **Permisos de MySQL**: Crear usuario con permisos específicos
   ```sql
   CREATE USER 'zmosquita'@'localhost' IDENTIFIED BY 'secret';
   GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX
       ON `iam`.* TO 'zmosquita'@'localhost';
   GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX
       ON `tenant_%`.* TO 'zmosquita'@'localhost';
   ```

2. **Validación de Catálogo**: Evitar SQL injection en nombres de catálogo

### Backup y Recovery

1. **Backup por Tenant**:
   ```bash
   mysqldump -u root -p acme_db > backup_acme_$(date +%Y%m%d).sql
   ```

2. **Restore**:
   ```bash
   mysql -u root -p acme_db < backup_acme_20250419.sql
   ```

3. **Operación sin afectar otros tenants**: Cada tenant es una base de datos independiente

## Impacto en Código Existente

### Mínimo Cambio Requerido

1. **Connection**: Reemplazado por `TenantAwareConnection`
   - Mantiene interfaz compatible
   - Métodos existentes siguen funcionando

2. **QueryBuilder**: Sin cambios
   - Recibe Connection via inyección de dependencias
   - No sabe si es tenant-specific o iam

3. **Repositories**: Sin cambios significativos
   - `TableResolver` sigue prefijando tablas
   - Solo cambia la conexión subyacente

### Cambios Requeridos

1. **ContextManager**: Agregar campo `catalog` al contexto
2. **TenantRepository**: Incluir campo `catalog` en queries
3. **CLI**: Nuevos comandos para gestión

## Implementación por Etapas

### Etapa 1: Infraestructura de Conexiones
1. Crear `TenantConnectionResolver`
2. Crear `TenantAwareConnection`
3. Actualizar `DatabaseServiceProvider`
4. Actualizar `ContextManager` para incluir catalog

### Etapa 2: Schema Installers
1. Crear `TenantSchemaInstaller`
2. Crear `InitialSeedsInstaller`
3. Actualizar `Paths` para initialseeds
4. Actualizar `TenantRepository` para catalog

### Etapa 3: Comandos CLI
1. Crear `TenantMakeCommand`
2. Crear `TenantAppInstallCommand`
3. Crear `TenantDropCommand`
4. Crear `TenantListCommand`

### Etapa 4: Testing y Documentación
1. Tests de integración
2. Documentación de uso
3. Guía de backup/recovery

## Ventajas de esta Solución

1. **Aislamiento Total**: Cada tenant tiene su propia base de datos
2. **Backup/Restore Simple**: Operaciones a nivel de catálogo
3. **Escalabilidad**: Fácil mover tenants entre servidores
4. **Código Limpio**: Mínimo impacto en código existente
5. **Compatibilidad**: Mantiene prefijos de tablas existentes
6. **Seguridad**: Permisos granulares por catálogo

## Alternativas Consideradas

### 1. Schema-based (mismo catálogo, schemas separados)
- **Descartado**: MariaDB no tiene verdaderos schemas aislados
- **Prefijos de tabla**: Es lo que tenemos ahora

### 2. Filtro por tenant_id en todas las queries
- **Descartado**: Riesgo de filtración de datos, complejo de mantener
- **Row Level Security**: No soportado bien en MariaDB

### 3. Base de datos por aplicación (no por tenant)
- **Descartado**: No proporciona aislamiento por tenant
- **Backup afectaría todos los tenants**

## Conclusión

La solución propuesta utiliza **catálogos separados por tenant** con:
- Una base de datos `iam` central para autenticación
- Una base de datos por tenant con sus tablas de aplicaciones
- Soporte para datos iniciales via `initialseeds/`
- Comandos CLI para gestión simplificada
- Mínimo impacto en código existente
