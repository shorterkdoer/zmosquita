# Publicación de Aplicación en Repositorio Core

Este documento describe el proceso para crear y publicar una nueva aplicación en el repositorio core de ZMosquita.

## Estructura de Aplicaciones

Cada aplicación reside en `applications/{codigo}/` con la siguiente estructura:

```
applications/
└── mi-app/
    ├── datadef/              # Scripts SQL de definición de tablas
    │   ├── 001_entidad_a.sql
    │   ├── 002_entidad_b.sql
    │   └── ...
    ├── initialseeds/         # Datos iniciales de ejemplo
    │   ├── 01_datos_ejemplo.sql
    │   └── ...
    └── datadefmeta/          # Metadatos para generadores CRUD (opcional)
        └── ...
```

## Prerrequisitos

- Conocimiento de SQL y DDL MariaDB
- Comprensión del modelo de datos de la aplicación
- Familiaridad con la arquitectura database-per-tenant

## Reglas de Diseño

### Database-Per-Tenant

En ZMosquita, cada tenant tiene su propio catálogo MariaDB. Por lo tanto:

**❌ NO hacer:**
- No incluir columnas `tenant_id` en tablas de aplicación
- No crear FKs hacia `iam_tenants` (están en catálogos distintos)
- No crear FKs hacia tablas de otros catálogos

**✅ SÍ hacer:**
- Todas las tablas pertenecen al tenant por defecto
- Usar tipos de datos apropiados (BIGINT, VARCHAR, TEXT, etc.)
- Incluir índices para columnas frecuentemente consultadas
- Incluir `created_at` y `updated_at` para auditoría

### Convenciones de Nomenclatura

**Tablas:**
- Prefijo con código de aplicación: `{codigo}_{recurso}`
- Singular, snake_case
- Ejemplo: `contabilidad_cuentas`, `contabilidad_asientos`

**Columnas:**
- snake_case
- Claves primarias: `id BIGINT UNSIGNED AUTO_INCREMENT`
- Timestamps: `created_at DATETIME`, `updated_at DATETIME`

**Archivos:**
- Orden numérico para ejecución secuencial: `001_`, `002_`, etc.
- Nombre descriptivo en snake_case

## Proceso de Creación

### Paso 1: Crear directorio de la aplicación

```bash
mkdir -p applications/mi-app/datadef
mkdir -p applications/mi-app/initialseeds
```

### Paso 2: Definir esquema de tablas

Crea archivos SQL en `applications/mi-app/datadef/`:

**Ejemplo: `applications/mi-app/datadef/001_clientes.sql`**

```sql
CREATE TABLE IF NOT EXISTS miapp_clientes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(50) NOT NULL,
    razon_social VARCHAR(200) NOT NULL,
    cuit VARCHAR(20) NULL,
    email VARCHAR(190) NULL,
    telefono VARCHAR(50) NULL,
    direccion TEXT NULL,
    ciudad VARCHAR(100) NULL,
    observaciones TEXT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miapp_clientes_codigo (codigo),
    KEY idx_miapp_clientes_razon_social (razon_social),
    KEY idx_miapp_clientes_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Ejemplo con relación: `applications/mi-app/datadef/002_facturas.sql`**

```sql
CREATE TABLE IF NOT EXISTS miapp_facturas (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    cliente_id BIGINT UNSIGNED NOT NULL,
    numero VARCHAR(50) NOT NULL,
    fecha DATE NOT NULL,
    total DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    estado ENUM('pendiente', 'pagada', 'cancelada') NOT NULL DEFAULT 'pendiente',
    observaciones TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miapp_facturas_numero (numero),
    KEY idx_miapp_facturas_cliente (cliente_id),
    KEY idx_miapp_facturas_fecha (fecha),
    KEY idx_miapp_facturas_estado (estado),
    CONSTRAINT fk_miapp_facturas_cliente
        FOREIGN KEY (cliente_id) REFERENCES miapp_clientes(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Paso 3: (Opcional) Crear datos iniciales

Crea `applications/mi-app/initialseeds/01_datos_ejemplo.sql`:

```sql
-- Datos de ejemplo para testing/demo
INSERT INTO miapp_clientes (
    codigo, razon_social, cuit, email, telefono,
    direccion, ciudad, activo, created_at, updated_at
) VALUES
('CLI001', 'Cliente Ejemplo S.A.', '20-12345678-9', 'info@ejemplo.com', '+54 11 1234-5678', 'Av. Corrientes 1234', 'CABA', 1, NOW(), NOW()),
('CLI002', 'Otra Empresa Ltda.', '27-87654321-0', 'contacto@otra.com', '+54 11 9876-5432', 'Belgrano 567', 'Córdoba', 1, NOW(), NOW());
```

### Paso 4: Instalar en catálogo IAM

Para que la aplicación esté disponible para ser instalada en tenants:

```bash
php bin/zmosquita install:app mi-app
```

**Este comando:**
- Ejecuta todos los scripts `datadef/*.sql` en el catálogo IAM
- Registra la aplicación en `iam_applications`
- Ejecuta `initialseeds/*.sql` si existen

**Salida esperada:**
```
App mi-app instalada correctamente.
 - /path/to/applications/mi-app/datadef/001_clientes.sql
 - /path/to/applications/mi-app/datadef/002_facturas.sql
```

### Paso 5: Verificar instalación

```sql
USE zmosquita_iam;

-- Verificar que la aplicación esté registrada
SELECT * FROM iam_applications WHERE code = 'mi-app';

-- Verificar tablas creadas
SHOW TABLES LIKE 'miapp_%';
```

### Paso 6: Generar CRUD (opcional)

Si deseas generar automáticamente controladores, modelos, vistas y rutas:

```bash
# Generar CRUD completo
php bin/zmosquita make:crud app mi-app clientes

# Generar solo el modelo
php bin/zmosquita make:crud app mi-app clientes --only=model

# Vista previa sin escribir archivos
php bin/zmosquita make:crud app mi-app clientes --dry-run
```

## Verificación Final

### Checklist de Publicación

- [ ] Directorios `datadef/` e `initialseeds/` creados
- [ ] Scripts SQL numerados y ordenados (001_, 002_, ...)
- [ ] Tablas sin `tenant_id` ni FKs a `iam_tenants`
- [ ] PKs como `id BIGINT UNSIGNED AUTO_INCREMENT`
- [ ] Índices en columnas frecuentemente consultadas
- [ ] Timestamps `created_at` y `updated_at`
- [ ] Charset `utf8mb4` y collation `utf8mb4_unicode_ci`
- [ ] Aplicación instalada en IAM: `install:app mi-app`
- [ ] Registro en `iam_applications` verificado
- [ ] CRUD generado (si aplica)

## Metadatos para Generadores (Opcional)

Para que los generadores CRUD funcionen correctamente, puedes crear archivos de metadatos en `datadefmeta/`:

**Ejemplo: `applications/mi-app/datadefmeta/clientes.json`**

```json
{
    "entity": "clientes",
    "label": "Cliente",
    "plural": "Clientes",
    "fields": [
        {
            "name": "codigo",
            "label": "Código",
            "type": "text",
            "required": true,
            "unique": true
        },
        {
            "name": "razon_social",
            "label": "Razón Social",
            "type": "text",
            "required": true
        },
        {
            "name": "email",
            "label": "Email",
            "type": "email"
        }
    ]
}
```

## Troubleshooting

### Error: "App datadef file not found"

Verifica que los archivos existan en la ruta correcta:
```bash
ls -la applications/mi-app/datadef/
```

### Error: "Table already exists"

Si reinstalas, elimina las tablas primero:
```sql
DROP TABLE IF EXISTS miapp_facturas;
DROP TABLE IF EXISTS miapp_clientes;
```

### Error en FKs

Asegúrate de que las tablas referenciadas se creen antes que las que las referencian. Usa el orden numérico de los archivos:
- `001_` para tablas independientes
- `002_` para tablas que dependen de `001_`
- etc.

## Ejemplo Completo

Ver la aplicación `demo` como referencia:
```bash
cat applications/demo/datadef/001_personas.sql
cat applications/demo/initialseeds/01_personas_ejemplo.sql
```
