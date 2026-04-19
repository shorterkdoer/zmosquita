# Documentación ZMosquita

Guías de deployment y gestión del sistema multi-tenant database-per-tenant.

## Arquitectura

ZMosquita utiliza una arquitectura de **database-per-tenant** donde cada tenant (organización) tiene su propio catálogo MariaDB, proporcionando aislamiento lógico completo entre clientes.

```
zmosquita_iam              (Catálogo central IAM)
├── iam_users              (Usuarios del sistema)
├── iam_tenants            (Registro de tenants)
├── iam_applications       (Catálogo de aplicaciones)
└── ...

acme_db                    (Catálogo del tenant ACME)
├── iam_users              (Usuarios específicos del tenant)
├── iam_tenants            (Metadatos del tenant)
├── demo_personas          (Tablas de aplicaciones)
└── ...

beta_db                    (Catálogo del tenant BETA)
├── iam_users
├── iam_tenants
└── ...
```

## Guías Disponibles

### [Deployment en Servidor](./deployment-servidor.md)

Guía completa para sysadmins para instalar ZMosquita en un servidor Ubuntu en producción.

**Contenido:**
- Instalación de stack LAMP (Linux, Apache, MariaDB, PHP)
- Clonado desde GitHub
- Configuración de base de datos
- Configuración de Apache VirtualHost
- SSL/TLS con Let's Encrypt
- Backup automatizado
- Monitoreo y troubleshooting

**Ideal para:** Primer deployment en servidor nuevo.

### [Deployment de Tenant](./deployment-tenant.md)

Guía completa para crear y desplegar un nuevo tenant en el sistema.

**Contenido:**
- Creación de catálogo específico por tenant
- Instalación de esquema core
- Registro en tabla central `iam_tenants`
- Verificación y troubleshooting
- Backup y restore por tenant

**Comandos clave:**
```bash
php bin/zmosquita tenant:make <code> <nombre> [catalogo]
php bin/zmosquita tenant:list
php bin/zmosquita tenant:drop <catalogo> --force
```

### [Publicación de Aplicación](./publicacion-aplicacion.md)

Guía para crear y publicar una nueva aplicación en el repositorio core.

**Contenido:**
- Estructura de directorios de aplicaciones
- Reglas de diseño database-per-tenant
- Convenciones de nomenclatura
- Creación de scripts SQL (datadef)
- Datos iniciales (initialseeds)
- Instalación en catálogo IAM
- Generación de CRUD

**Comandos clave:**
```bash
php bin/zmosquita install:app <appCode>
php bin/zmosquita make:crud app <appCode> <recurso>
```

### [Generación de DataDefMeta](./generacion-datadefmeta.md)

Guía para generar automáticamente archivos de metadatos desde SQL.

**Contenido:**
- Comando `make:datadefmeta`
- Análisis automático de SQL
- Inferencia de tipos y reglas
- Personalización post-generación
- Flujo de trabajo completo

**Comandos clave:**
```bash
php bin/zmosquita make:datadefmeta app <appCode> <recurso>
php bin/zmosquita make:datadefmeta core <recurso>
```

**Contenido:**
- Estructura de directorios de aplicaciones
- Reglas de diseño database-per-tenant
- Convenciones de nomenclatura
- Creación de scripts SQL (datadef)
- Datos iniciales (initialseeds)
- Instalación en catálogo IAM
- Generación de CRUD

**Comandos clave:**
```bash
php bin/zmosquita install:app <appCode>
php bin/zmosquita make:crud app <appCode> <recurso>
```

### [Deployment de Aplicación para Tenant](./deployment-aplicacion-tenant.md)

Guía para desplegar una aplicación específica en un tenant existente.

**Contenido:**
- Instalación de app en catálogo del tenant
- Configuración de permisos y roles
- Verificación post-deployment
- Actualización de aplicaciones
- Deployment masivo en múltiples tenants
- Backup y restore por aplicación

**Comandos clave:**
```bash
php bin/zmosquita tenant:app:install <tenantCode> <appCode>
```

## Flujo de Trabajo Completo

### 0. Instalar en servidor (primera vez)

```bash
# Ver guía: deployment-servidor.md
# - Instalar stack LAMP
# - Clonar desde GitHub
# - Configurar base de datos
# - Configurar Apache
# - Ejecutar install:core
```

### 1. Crear una nueva aplicación

```bash
# Ver guía: publicacion-aplicacion.md
mkdir -p applications/mi-app/datadef
mkdir -p applications/mi-app/initialseeds
# Crear scripts SQL...
php bin/zmosquita install:app mi-app

# Generar metadatos para cada tabla
php bin/zmosquita make:datadefmeta app mi-app clientes

# Generar CRUD
php bin/zmosquita make:crud app mi-app clientes
```

### 2. Crear un nuevo tenant

```bash
# Ver guía: deployment-tenant.md
php bin/zmosquita tenant:make cliente-abc "Cliente ABC S.A." cliente_abc_db
```

### 3. Instalar aplicación en el tenant

```bash
# Ver guía: deployment-aplicacion-tenant.md
php bin/zmosquita tenant:app:install cliente-abc mi-app
```

## Referencia Rápida de CLI

### Gestión de Tenants

```bash
# Crear tenant
php bin/zmosquita tenant:make <code> <nombre> [catalogo]

# Listar tenants
php bin/zmosquita tenant:list

# Eliminar tenant (peligroso)
php bin/zmosquita tenant:drop <catalogo> --force
```

### Gestión de Aplicaciones

```bash
# Instalar app en IAM
php bin/zmosquita install:app <appCode>

# Instalar app en tenant
php bin/zmosquita tenant:app:install <tenantCode> <appCode>
```

### Generadores

```bash
# Generar metadatos desde SQL (paso previo a CRUD)
php bin/zmosquita make:datadefmeta app <appCode> <recurso>
php bin/zmosquita make:datadefmeta core <recurso>

# Generar CRUD completo
php bin/zmosquita make:crud app <appCode> <recurso>

# Generar solo componente específico
php bin/zmosquita make:crud app <appCode> <recurso> --only=model
php bin/zmosquita make:crud app <appCode> <recurso> --only=controller
php bin/zmosquita make:crud app <appCode> <recurso> --only=views
php bin/zmosquita make:crud app <appCode> <recurso> --only=routes

# Vista previa sin escribir archivos
php bin/zmosquita make:crud app <appCode> <recurso> --dry-run

# Forzar sobrescrita
php bin/zmosquita make:crud app <appCode> <recurso> --force
```

### Gestión Core

```bash
# Instalar esquema core en IAM
php bin/zmosquita install:core
```

## Reglas Importantes

### Database-Per-Tenant

En arquitectura database-per-tenant, cada tenant tiene su propio catálogo:

**❌ NO hacer en aplicaciones:**
- No incluir columnas `tenant_id` en tablas
- No crear FKs hacia `iam_tenants` (distintos catálogos)
- No crear FKs hacia tablas de otros catálogos

**✅ SÍ hacer:**
- Todas las tablas pertenecen al tenant por defecto
- Usar tipos de datos apropiados
- Incluir índices para performance
- Incluir `created_at` y `updated_at` para auditoría

### Convenciones

**Tablas:**
- Prefijo con código de aplicación: `{codigo}_{recurso}`
- Singular, snake_case
- Ejemplo: `contabilidad_cuentas`, `demo_personas`

**Archivos SQL:**
- Orden numérico: `001_`, `002_`, etc.
- Nombre descriptivo en snake_case
- Ejemplo: `001_clientes.sql`, `002_facturas.sql`

## Troubleshooting Común

### Error: "database exists"
El catálogo ya existe. Verificar con:
```bash
php bin/zmosquita tenant:list
```

### Error: "App datadef file not found"
Verificar que los archivos existan:
```bash
ls -la applications/mi-app/datadef/
```

### Error: "Table already exists"
Las tablas ya existen. Eliminar o crear script de migración.

### Error de conexión
Verificar `.env` y servicio MariaDB:
```bash
cat .env | grep DB_
sudo systemctl status mariadb
```

## Estructura de Directorios

```
zmosquita2/
├── applications/              # Aplicaciones del sistema
│   ├── demo/                 # App de demostración
│   │   ├── datadef/          # Scripts SQL de definición
│   │   ├── initialseeds/     # Datos iniciales
│   │   └── datadefmeta/      # Metadatos para CRUD
│   └── mi-app/               # Tu aplicación
├── bin/
│   └── zmosquita.php         # CLI principal
├── core/                     # Framework core
│   ├── datadef/              # Scripts SQL core
│   ├── Database/             # Componentes de base de datos
│   └── ...
├── docs/                     # Esta documentación
└── .env                      # Configuración de entorno
```

## Soporte

Para más información o problemas, consultar:
- Verificar estado: `php bin/zmosquita.php --help`
- Revisar logs de MariaDB: `/var/log/mysql/error.log`
- Revisar configuración: `cat .env`
