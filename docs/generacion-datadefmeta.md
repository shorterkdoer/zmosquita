# Generación de DataDefMeta con CLI

Este documento describe el mecanismo para generar automáticamente los archivos de metadatos (`datadefmeta/*.php`) necesarios para la generación de CRUDs en ZMosquita.

## ¿Qué son los archivos DataDefMeta?

Los archivos `datadefmeta/*.php` contienen metadatos que describen cómo deben generarse los componentes CRUD (Controladores, Modelos, Validadores, Vistas) para una tabla específica.

**Ubicación:**
- Core: `core/datadefmeta/{recurso}.php`
- Aplicaciones: `applications/{app}/datadefmeta/{recurso}.php`

**Propósito:**
Definir etiquetas, tipos de campos, reglas de validación, y configuración del generador para cada recurso.

## Comando CLI

### Sintaxis

```bash
# Para aplicaciones
php bin/zmosquita make:datadefmeta app <appCode> <resource> [opciones]

# Para core
php bin/zmosquita make:datadefmeta core <resource> [opciones]
```

### Opciones

| Opción | Descripción |
|--------|-------------|
| `--force` | Sobrescribe el archivo si ya existe |
| `--dry-run` | Muestra el contenido sin escribir el archivo |

### Ejemplos

```bash
# Generar metadatos para tabla personas de app demo
php bin/zmosquita make:datadefmeta app demo personas

# Forzar sobrescrita de metadatos existentes
php bin/zmosquita make:datadefmeta app demo personas --force

# Vista previa sin escribir archivo
php bin/zmosquita make:datadefmeta app demo personas --dry-run

# Generar metadatos para tabla users de core
php bin/zmosquita make:datadefmeta core users
```

## Cómo Funciona

### 1. Búsqueda del archivo SQL

El comando busca automáticamente el archivo SQL correspondiente en `datadef/`:

```
applications/demo/datadef/001_personas.sql  ← Encontrado con "personas"
applications/demo/datadef/002_ubicaciones.sql  ← Encontrado con "ubicaciones"
core/datadef/01_users.sql  ← Encontrado con "users"
```

**Patrones soportados:**
- `{numero}_{recurso}.sql` - Ej: `001_personas.sql`
- `{prefijo}_{recurso}.sql` - Ej: `app_clientes.sql`
- `{recurso}.sql` - Ej: `personas.sql`

### 2. Análisis del SQL

El comando analiza la sentencia `CREATE TABLE` y extrae:

```sql
CREATE TABLE IF NOT EXISTS demo_personas (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    apellido VARCHAR(100) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(50) NULL,
    email VARCHAR(190) NULL,
    observaciones TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id)
);
```

**Información extraída:**
- Nombre de tabla: `demo_personas`
- Columnas: id, apellido, nombre, telefono, email, observaciones
- Tipos: BIGINT, VARCHAR, TEXT, DATETIME
- Restricciones: NOT NULL, PRIMARY KEY, AUTO_INCREMENT

### 3. Generación de Metadatos

Basándose en el análisis del SQL, el comando genera:

#### 3.1 Labels (Etiquetas)

Genera etiquetas en español automáticamente:

```php
'labels' => [
    'apellido' => 'Apellido',
    'nombre' => 'Nombre',
    'telefono' => 'Telefono',
    'email' => 'Correo electrónico',  // ← Traducción automática
    'observaciones' => 'Observaciones',
],
```

**Traducciones automáticas:**
- `email` → `Correo electrónico`
- `password` → `Contraseña`
- `phone`/`telefono` → `Teléfono`
- `address` → `Dirección`
- `cuit`/`dni` → `CUIT`/`DNI`

#### 3.2 Form Fields

Campos incluidos en el formulario:

```php
'form' => [
    'fields' => [
        'apellido',   // ← NOT NULL → incluido
        'nombre',
        'telefono',   // ← NULL → incluido
        'email',
        'observaciones',
        // id excluido (PK autoincrement)
        // created_at excluido (timestamp)
        // updated_at excluido (timestamp)
    ],
],
```

**Reglas de inclusión:**
- ✅ Campos `NOT NULL`
- ✅ Campos nullable (excepto timestamps)
- ❌ Primary keys con `AUTO_INCREMENT`
- ❌ Timestamps (`created_at`, `updated_at`, `deleted_at`)

#### 3.3 Table Columns

Columnas mostradas en la tabla listado:

```php
'table' => [
    'columns' => [
        'id',           // ← PK incluida para mostrar
        'apellido',
        'nombre',
        'telefono',
        'email',
        'observaciones',
        // created_at excluido
        // updated_at excluido
    ],
],
```

#### 3.4 Field Types

Infiere el tipo de formulario según el tipo SQL:

```php
'fields' => [
    'apellido' => [
        'type' => 'text',           // VARCHAR → text
        'rules' => ['required', 'max:100'],
    ],
    'email' => [
        'type' => 'email',          // nombre contiene "email"
        'rules' => ['nullable', 'max:190'],
    ],
    'telefono' => [
        'type' => 'tel',            // nombre contiene "telefono"
        'rules' => ['nullable', 'max:50'],
    ],
    'observaciones' => [
        'type' => 'textarea',       // TEXT → textarea
        'rules' => ['nullable', 'max:65535'],
    ],
],
```

**Mapeo de tipos:**

| Tipo SQL | Tipo Form | Campo contiene |
|----------|-----------|----------------|
| VARCHAR/CHAR | text | - |
| TEXT/MEDIUMTEXT/LONGTEXT | textarea | - |
| TINYINT/INT/BIGINT | number | - |
| DECIMAL/FLOAT/DOUBLE | number | - |
| DATE | date | - |
| DATETIME/TIMESTAMP | datetime | - |
| TIME | time | - |
| BOOLEAN | checkbox | - |
| ENUM | select | - |
| - | password | password |
| - | email | email |
| - | tel | phone/telefono |
| - | url | url/link |

#### 3.5 Validation Rules

Genera reglas de validación basadas en el tipo SQL:

```php
'fields' => [
    'apellido' => [
        'type' => 'text',
        'rules' => [
            'required',     // ← NOT NULL
            'max:100',      // ← VARCHAR(100)
        ],
    ],
    'email' => [
        'type' => 'email',
        'rules' => [
            'nullable',     // ← NULL
            'max:190',      // ← VARCHAR(190)
        ],
    ],
],
```

**Reglas generadas:**

| Condición SQL | Regla |
|---------------|-------|
| `NOT NULL` | `required` |
| `NULL` | `nullable` |
| `VARCHAR(n)` | `max:n` |
| `CHAR(n)` | `max:n` |
| `TEXT` | `max:65535` |
| `INT/BIGINT` | `integer` |
| `DECIMAL/FLOAT` | `numeric` |
| `DATE/DATETIME` | `date` |
| `BOOLEAN` | `boolean` |

#### 3.6 Generator Config

Configuración del generador CRUD:

```php
'generator' => [
    'controller' => 'PersonasController',  // ← Generado automáticamente
    'model' => 'Persona',                  // ← Singular del recurso
    'overwrite' => false,                  // ← No sobrescribir archivos existentes
],
```

## Archivo Generado Completo

```php
<?php

return [
    'labels' => [
        'id' => 'Id',
        'apellido' => 'Apellido',
        'nombre' => 'Nombre',
        'telefono' => 'Telefono',
        'email' => 'Correo electrónico',
        'observaciones' => 'Observaciones',
    ],

    'form' => [
        'fields' => [
            'apellido',
            'nombre',
            'telefono',
            'email',
            'observaciones',
        ],
    ],

    'table' => [
        'columns' => [
            'id',
            'apellido',
            'nombre',
            'telefono',
            'email',
            'observaciones',
        ],
    ],

    'fields' => [
        'id' => [
            'type' => 'number',
            'rules' => ['required', 'integer'],
        ],
        'apellido' => [
            'type' => 'text',
            'rules' => ['required', 'max:100'],
        ],
        'nombre' => [
            'type' => 'text',
            'rules' => ['required', 'max:100'],
        ],
        'telefono' => [
            'type' => 'tel',
            'rules' => ['nullable', 'max:50'],
        ],
        'email' => [
            'type' => 'email',
            'rules' => ['nullable', 'max:190'],
        ],
        'observaciones' => [
            'type' => 'textarea',
            'rules' => ['nullable', 'max:65535'],
        ],
    ],

    'generator' => [
        'controller' => 'PersonasController',
        'model' => 'Persona',
        'overwrite' => false,
    ],
];
```

## Personalización Post-Generación

Después de generar el archivo, puedes personalizarlo:

### Cambiar etiquetas

```php
'labels' => [
    'apellido' => 'Apellidos',  // ← Personalizado
    'nombre' => 'Nombres',
    // ...
],
```

### Modificar tipos de campo

```php
'fields' => [
    'observaciones' => [
        'type' => 'text',  // ← Cambiado de textarea a text
        'rules' => ['nullable', 'max:500'],
    ],
],
```

### Agregar reglas adicionales

```php
'fields' => [
    'email' => [
        'type' => 'email',
        'rules' => [
            'nullable',
            'max:190',
            'email',          // ← Agregar validación email
            'unique:users',   // ← Agregar unique
        ],
    ],
],
```

### Configurar relaciones

```php
'fields' => [
    'categoria_id' => [
        'type' => 'select',
        'rules' => ['required', 'integer'],
        'relation' => [
            'table' => 'categorias',
            'column' => 'id',
        ],
    ],
],
```

### Excluir campos del formulario

```php
'form' => [
    'fields' => [
        'apellido',
        'nombre',
        // telefono excluido
        // email excluido
    ],
],
```

### Campos ocultos o readonly

```php
'form' => [
    'fields' => ['apellido', 'nombre', ...],
    'hidden' => ['id', 'created_at'],      // ← Ocultos
    'readonly' => ['codigo', 'usuario_id'], // ← Solo lectura
],
```

## Flujo de Trabajo Completo

### 1. Crear tabla SQL

```bash
# Crear archivo datadef
applications/mi-app/datadef/001_clientes.sql
```

```sql
CREATE TABLE IF NOT EXISTS miapp_clientes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(20) NOT NULL,
    razon_social VARCHAR(200) NOT NULL,
    cuit VARCHAR(20) NULL,
    email VARCHAR(190) NULL,
    telefono VARCHAR(50) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_miapp_clientes_codigo (codigo)
);
```

### 2. Generar DataDefMeta

```bash
php bin/zmosquita make:datadefmeta app mi-app clientes
```

### 3. Revisar y ajustar

```bash
nano applications/mi-app/datadefmeta/clientes.php
```

### 4. Generar CRUD

```bash
php bin/zmosquita make:crud app mi-app clientes
```

## Troubleshooting

### Error: "App datadef file not found"

El archivo SQL no existe. Verifica:

```bash
ls -la applications/demo/datadef/
ls -la core/datadef/
```

### Error: "DataDefMeta file already exists"

El archivo ya existe. Usa `--force` para sobrescribir:

```bash
php bin/zmosquita make:datadefmeta app demo personas --force
```

### Error: "Unable to extract table name"

El SQL no tiene el formato correcto. Verifica que:

- La sentencia sea `CREATE TABLE` o `CREATE TABLE IF NOT EXISTS`
- El nombre de la tabla esté entre paréntesis
- No haya errores de sintaxis SQL

### Etiquetas incorrectas

Edita el archivo generado manualmente:

```php
'labels' => [
    'campo' => 'Tu etiqueta personalizada',
],
```

### Tipo de campo incorrecto

Modifica el tipo en el archivo:

```php
'fields' => [
    'mi_campo' => [
        'type' => 'textarea',  // ← Cambiar al tipo deseado
    ],
],
```

## Ventajas del Mecanismo

1. **Automatización**: No escribir metadatos manualmente
2. **Consistencia**: Mismo formato para todos los recursos
3. **Mantenibilidad**: SQL como fuente de verdad
4. **Inteligente**: Inferencia de tipos y reglas
5. **Personalizable**: Editar archivo post-generación
6. **Reproducible**: Regenerar con `--force` cuando cambia el SQL

## Convenciones

### Nomenclatura de archivos

- **SQL**: `{numero}_{recurso}.sql` - Ej: `001_clientes.sql`
- **Meta**: `{recurso}.php` - Ej: `clientes.php`

### Nombres de tabla

- **Core**: `{recurso}` - Ej: `users`
- **App**: `{app}_{recurso}` - Ej: `demo_personas`

### Nombres de campo

- `snake_case` - Ej: `razon_social`, `fecha_nacimiento`
- Evitar palabras reservadas de SQL
- Prefijar con clave de app si hay conflicto

## Referencia

- [Publicación de Aplicaciones](./publicacion-aplicacion.md) - Crear datadef SQL
- [Generación de CRUD](../README.md#generadores) - Usar datadefmeta para generar CRUD
- [CLI Reference](../README.md#referencia-rápida-de-cli) - Todos los comandos disponibles
