# Organización de Código Específico por Aplicación

Este documento define las convenciones y mejores prácticas para organizar el código específico de cada aplicación en ZMosquita.

## Estructura Base de una Aplicación

```
applications/
├── {appCode}/
│   ├── Controllers/          # Controladores HTTP
│   ├── Models/               # Modelos de dominio
│   ├── Services/             # Lógica de negocio
│   ├── Repositories/         # Acceso a datos
│   ├── Validators/           # Validación de entrada
│   ├── Middlewares/          # Middleware HTTP
│   ├── Enums/                # Enumeraciones
│   ├── Support/              # Clases de soporte
│   ├── Core/                 # Base classes (legado, evitar)
│   ├── Views/                # Vistas de presentación
│   ├── datadef/              # Definición SQL de tablas
│   ├── datadefmeta/          # Metadatos para generadores
│   └── initialseeds/         # Datos iniciales
```

## Namespaces

**Convención PSR-4:**

```php
// Controllers
namespace Applications\{AppCode}\Controllers;
use Applications\{AppCode}\Models\{Model};

// Models
namespace Applications\{AppCode}\Models;

// Services
namespace Applications\{AppCode}\Services;

// Repositories
namespace Applications\{AppCode}\Repositories;

// Validators
namespace Applications\{AppCode}\Validators;

// Middlewares
namespace Applications\{AppCode}\Middlewares;

// Enums
namespace Applications\{AppCode}\Enums;

// Support
namespace Applications\{AppCode}\Support;
```

**Ejemplo concreto:**

```php
// applications/demo/Controllers/PersonasController.php
namespace Applications\Demo\Controllers;

use Applications\Demo\Services\PersonaService;
use Applications\Demo\Validators\PersonaValidator;

class PersonasController
{
    public function __construct(
        private PersonaService $service
    ) {}
}
```

## 1. Controllers (`Controllers/`)

**Propósito:** Manejar requests HTTP, orquestar servicios, retornar respuestas.

**Ubicación:** `applications/{app}/Controllers/`

**Responsabilidades:**
- ✅ Recibir request y extraer parámetros
- ✅ Llamar a services para lógica de negocio
- ✅ Retornar respuestas HTTP (JSON, vistas, redirects)
- ❌ NO contener lógica de negocio
- ❌ NO acceder directamente a la base de datos
- ❌ NO contener validación compleja

**Estructura recomendada:**

```php
<?php
declare(strict_types=1);

namespace Applications\Demo\Controllers;

use Applications\Demo\Services\PersonaService;
use Applications\Demo\Validators\PersonaValidator;
use ZMosquita\Core\Http\Controllers\BaseController;

class PersonasController extends BaseController
{
    public function __construct(
        private PersonaService $service,
        private PersonaValidator $validator
    ) {}

    public function index(): string
    {
        $personas = $this->service->getAll();
        return $this->view('personas/index', ['personas' => $personas]);
    }

    public function store(): void
    {
        $data = $this->request->post();

        $this->validator->validate($data);
        $persona = $this->service->create($data);

        $this->redirect('/personas/' . $persona['id']);
    }
}
```

## 2. Services (`Services/`)

**Propósito:** Contener lógica de negocio específica de la aplicación.

**Ubicación:** `applications/{app}/Services/`

**Responsabilidades:**
- ✅ Reglas de negocio
- ✅ Orquestación de múltiples repositorios
- ✅ Cálculos y transformaciones
- ✅ Integración con APIs externas
- ✅ Envío de emails/notificaciones
- ❌ NO acceder directamente a request/response

**Estructura recomendada:**

```php
<?php
declare(strict_types=1);

namespace Applications\Demo\Services;

use Applications\Demo\Repositories\PersonaRepository;
use Applications\Demo\Repositories\UbicacionRepository;
use Applications\Demo\Services\EmailService;

class PersonaService
{
    public function __construct(
        private PersonaRepository $personaRepo,
        private UbicacionRepository $ubicacionRepo,
        private EmailService $emailService
    ) {}

    public function create(array $data): array
    {
        // Validaciones de negocio
        if ($this->personaRepo->existsByEmail($data['email'])) {
            throw new \RuntimeException('Email ya registrado');
        }

        // Transformaciones
        $data['codigo'] = $this->generarCodigo($data['nombre']);

        // Crear entidad
        $persona = $this->personaRepo->create($data);

        // Efectos secundarios
        $this->emailService->sendBienvenida($persona);

        return $persona;
    }

    private function generarCodigo(string $nombre): string
    {
        return strtoupper(substr($nombre, 0, 3)) . rand(1000, 9999);
    }
}
```

**Servicios por dominio:**
- `PersonaService` - Gestión de personas
- `MatriculaService` - Gestión de matrículas
- `PagoService` - Procesamiento de pagos
- `ReporteService` - Generación de reportes
- `NotificacionService` - Envío de notificaciones

## 3. Repositories (`Repositories/`)

**Propósito:** Abstracción del acceso a datos (Base de Datos).

**Ubicación:** `applications/{app}/Repositories/`

**Responsabilidades:**
- ✅ Queries SQL
- ✅ CRUD básico
- ✅ Queries complejas específicas del dominio
- ❌ NO contener lógica de negocio
- ❌ NO conocer sobre HTTP

**Estructura recomendada:**

```php
<?php
declare(strict_types=1);

namespace Applications\Demo\Repositories;

use ZMosquita\Core\Database\TableResolver;
use ZMosquita\Core\Database\QueryBuilder;

class PersonaRepository
{
    public function __construct(
        private QueryBuilder $db,
        private TableResolver $tables
    ) {}

    private function table(): string
    {
        return $this->tables->app('demo', 'personas');
    }

    public function find(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table()} WHERE id = ?", [$id]
        );
    }

    public function create(array $data): array
    {
        $this->db->insert($this->table(), $data);
        return $this->find((int) $this->db->lastInsertId());
    }

    public function existsByEmail(string $email): bool
    {
        return $this->db->fetchOne(
            "SELECT 1 FROM {$this->table()} WHERE email = ?",
            [$email]
        ) !== null;
    }

    public function searchByTerm(string $term): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table()}
             WHERE nombre LIKE ? OR apellido LIKE ?
             ORDER BY nombre ASC",
            ["%{$term}%", "%{$term}%"]
        );
    }
}
```

**Extender de BaseRepository (opcional):**

```php
use ZMosquita\Core\Repositories\BaseRepository;

class PersonaRepository extends BaseRepository
{
    protected string $table = 'demo_personas';

    public function searchActive(): array
    {
        return $this->where('activo', 1);
    }
}
```

## 4. Models (`Models/`)

**Propósito:** Modelos de dominio, DTOs, entidades.

**Ubicación:** `applications/{app}/Models/`

**Uso:** Alternativa ligera a Repositories para apps simples.

```php
<?php
declare(strict_types=1);

namespace Applications\Demo\Models;

use ZMosquita\Core\Models\BaseModel;

class Persona extends BaseModel
{
    protected string $table = 'demo_personas';

    protected array $fillable = [
        'apellido', 'nombre', 'email', 'telefono'
    ];

    public function getByEmail(string $email): ?array
    {
        return $this->whereFirst('email', $email);
    }

    public function getFullName(): string
    {
        return $this->nombre . ' ' . $this->apellido;
    }
}
```

## 5. Validators (`Validators/`)

**Propósito:** Validación de datos de entrada.

**Ubicación:** `applications/{app}/Validators/`

```php
<?php
declare(strict_types=1);

namespace Applications\Demo\Validators;

use ZMosquita\Core\Support\ValidationException;

class PersonaValidator
{
    public function validate(array $data, bool $isUpdate = false): void
    {
        $errors = [];

        if (!$isUpdate && empty($data['apellido'])) {
            $errors['apellido'] = 'El apellido es requerido';
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email inválido';
        }

        if (isset($data['telefono']) && strlen($data['telefono']) < 8) {
            $errors['telefono'] = 'Teléfono debe tener al menos 8 caracteres';
        }

        // Validaciones de negocio específicas
        if (!empty($data['cuit']) && !$this->validarCuit($data['cuit'])) {
            $errors['cuit'] = 'CUIT inválido';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }
    }

    private function validarCuit(string $cuit): bool
    {
        // Lógica de validación de CUIT
        return strlen($cuit) === 11;
    }
}
```

## 6. Middlewares (`Middlewares/`)

**Propósito:** Filtros HTTP antes/después del controller.

**Ubicación:** `applications/{app}/Middlewares/`

```php
<?php
declare(strict_types=1);

namespace Applications\Demo\Middlewares;

use ZMosquita\Core\Http\Request;

class SoloAdminMiddleware
{
    public function handle(Request $request, callable $next)
    {
        if (!$this->esAdmin($request)) {
            http_response_code(403);
            echo 'Acceso denegado';
            exit;
        }

        return $next($request);
    }

    private function esAdmin(Request $request): bool
    {
        return $_SESSION['role'] === 'admin';
    }
}
```

## 7. Enums (`Enums/`)

**Propósito:** Constantes tipadas del dominio.

**Ubicación:** `applications/{app}/Enums/`

```php
<?php
declare(strict_types=1);

namespace Applications\Demo\Enums;

enum EstadoPersona: string
{
    case ACTIVO = 'activo';
    case INACTIVO = 'inactivo';
    case PENDIENTE = 'pendiente';

    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVO => 'Activo',
            self::INACTIVO => 'Inactivo',
            self::PENDIENTE => 'Pendiente',
        };
    }

    public function puedeEditar(): bool
    {
        return $this !== self::INACTIVO;
    }
}
```

## 8. Support (`Support/`)

**Propósito:** Clases utilitarias específicas de la app.

**Ubicación:** `applications/{app}/Support/`

```php
<?php
declare(strict_types=1);

namespace Applications\Demo\Support;

class CuitValidator
{
    public static function validar(string $cuit): bool
    {
        // Algoritmo de validación de CUIT
        $cuit = preg_replace('/[^0-9]/', '', $cuit);

        if (strlen($cuit) !== 11) {
            return false;
        }

        // Cálculo del dígito verificador
        // ...

        return true;
    }

    public static function formatear(string $cuit): string
    {
        return preg_replace('/(\d{2})(\d{8})(\d{1})/', '$1-$2-$3', $cuit);
    }
}
```

## Convenciones de Nombres

### Clases

- **Controllers:** `{Recurso}Controller` → `PersonasController`
- **Services:** `{Recurso}Service` → `PersonaService`
- **Repositories:** `{Recurso}Repository` → `PersonaRepository`
- **Models:** `{Recurso}` → `Persona`
- **Validators:** `{Recurso}Validator` → `PersonaValidator`
- **Middlewares:** `{Función}Middleware` → `SoloAdminMiddleware`
- **Enums:** `{Dominio}` → `EstadoPersona`

### Métodos

```php
// CRUD
find(), findAll(), create(), update(), delete()

// Búsqueda
searchBy{Criterio}(), findBy{Campo}()

// Negocio
calcular{X}(), validar{X}(), procesar{X}()

// Booleanos
es{X}(), tiene{X}(), puede{X}()
```

## Inyección de Dependencias

**Usar constructor injection:**

```php
class PersonaService
{
    public function __construct(
        private PersonaRepository $personaRepo,
        private EmailService $emailService,
        private ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();
    }
}
```

## Casos Especiales

### Servicios Compartidos Entre Apps

Si un servicio es usado por múltiples aplicaciones:

```
core/Services/
└── EmailService.php        # Servicio genérico

applications/demo/Services/
└── PersonaService.php       # Usa EmailService genérico

applications/otra/Services/
└── ClienteService.php       # Usa EmailService genérico
```

### Helpers Globales vs Específicos

```
core/Support/                # Funciones genéricas del framework
└── DateHelper.php
└── NumberHelper.php

applications/demo/Support/   # Funciones específicas de demo
└── CuitHelper.php           # Solo usa demo
```

### Configuración por App

```
applications/demo/
├── config/
│   ├── app.php              # Config específica de demo
│   └── menus.php            # Menús específicos
```

## Patron Recomendado: Service Layer

```
Request
  ↓
Middleware
  ↓
Controller (Orquesta)
  ↓
Service (Lógica de Negocio) ←→ Validator
  ↓                          ↓
Repository                  External API
  ↓
Database
```

## Ejemplo Completo: Crear una Persona

**Request:** `POST /personas`

**Controller:**
```php
public function store(): void
{
    $data = $this->request->post();
    $this->service->crearPersona($data);
    $this->redirect('/personas');
}
```

**Service:**
```php
public function crearPersona(array $data): array
{
    // 1. Validaciones de negocio
    if ($this->repo->existeEmail($data['email'])) {
        throw new BusinessException('Email ya existe');
    }

    // 2. Preparar datos
    $data['codigo'] = $this->generarCodigo();
    $data['estado'] = EstadoPersona::PENDIENTE;

    // 3. Persistir
    $persona = $this->repo->create($data);

    // 4. Efectos secundarios
    $this->emailService->enviarBienvenida($persona);
    $this->auditoriaService->registrar('persona_creada', $persona);

    return $persona;
}
```

## Generación Automática con CLI

El comando `make:service` genera automáticamente la capa de Service y Repository:

```bash
# Para aplicaciones
php bin/zmosquita make:service app <appCode> <recurso> [--force] [--dry-run]

# Para core
php bin/zmosquita make:service core <recurso> [--force] [--dry-run]
```

**Qué genera:**

1. **Service** (`Services/{Recurso}Service.php`)
   - Constructor injection del Repository
   - Métodos CRUD: `getAll()`, `findById()`, `create()`, `update()`, `delete()`
   - Namespace: `Applications\{AppCode}\Services`

2. **Repository** (`Repositories/{Recurso}Repository.php`)
   - Uso de `QueryBuilder` y `TableResolver`
   - Métodos CRUD con SQL preparado
   - Namespace: `Applications\{AppCode}\Repositories`

**Ejemplo de uso:**

```bash
# Generar Service y Repository para tabla personas
php bin/zmosquita make:service app demo personas

# Genera:
# - applications/demo/Services/PersonasService.php
# - applications/demo/Repositories/PersonasRepository.php
```

**Después de generar:**

1. Agregar lógica de negocio al Service
2. Agregar queries específicas al Repository
3. Inyectar el Service en el Controller

```php
// En el Controller
public function __construct(
    private PersonaService $service
) {}

public function index(): void
{
    $personas = $this->service->getAll();
    // ...
}
```

## Archivos a NO Crear en `applications/{app}/`

- ❌ **Vendor libraries** → Usar `composer require`
- ❌ **Framework base classes** → Usar `core/`
- ❌ **Base classes** (`BaseController`, `BaseModel`) → Usar las del `core/`
- ❌ **Database connection** → Usar inyección de dependencias del `core/`
- ❌ **Auth/Session handlers** → Usar los del `core/`

## Migración desde Código Legado

Si tienes código en formato antiguo:

```php
// ANTIGUO: App\Models\Persona
// NUEVO: Applications\{AppCode}\Models\Persona

// ANTIGUO: App\Services\MatriculaService
// NUEVO: Applications\{AppCode}\Services\MatriculaService
```

## Checklist para Nuevo Código

- [ ] Namespace correcto: `Applications\{AppCode}\{Directorio}`
- [ ] `declare(strict_types=1)` al inicio
- [ ] Constructor injection para dependencias
- [ ] Tipos de retorno en métodos
- [ ] Excepciones específicas del dominio
- [ ] Sin acceso directo a `$_GET`, `$_POST` en Service/Repository
- [ ] Sin `echo`/`print` en Service/Repository
- [ ] Tests en `tests/` o `applications/{app}/Tests/`

## Recursos Adicionales

- [PSR-4: Autoloading Standard](https://www.php-fig.org/psr/psr-4/)
- [PSR-12: Extended Coding Style](https://www.php-fig.org/psr/psr-12/)
- [Dependency Injection en ZMosquita Core](../core/Support/Container.php)
