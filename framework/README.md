# Foundation Framework

Framework PHP ligero con funcionalidad CRUD genérica y herramientas para desarrollo rápido de aplicaciones.

## Características

- **Enrutamiento simple** - Sistema de rutas con parámetros y middleware
- **ORM ligero** - Modelo base con operaciones CRUD completas
- **Controladores CRUD** - Funcionalidad lista para usar con DataTables
- **Master-Detail** - Relaciones padre-hijo (Facturas con ítems, Órdenes con productos, etc.)
- **Generador de CRUDs** - Herramienta CLI para generar código desde SQL
- **Seguridad** - CSRF, validación, y sanitización integradas
- **Vistas con Plates** - Motor de plantillas nativo de PHP

## Instalación

### Como dependencia en tu proyecto:

```bash
composer require zmosquita/foundation
```

### Para una nueva aplicación:

```bash
# Crear nuevo proyecto
composer create-project zmosquita/foundation myapp

# Configurar
cd myapp
cp .env.example .env
# Editar .env con tu configuración

# Instalar dependencias
composer install

# Configurar servidor web para apuntar al directorio del proyecto
```

## Estructura del Directorio

```
project/
├── app/
│   ├── Controllers/    # Controladores de la aplicación
│   ├── Models/         # Modelos de la aplicación
│   ├── Services/       # Lógica de negocio
│   ├── Repositories/   # Acceso a datos
│   ├── Middlewares/    # Middleware personalizado
│   ├── Core/           # Clases base que extienden el framework
│   │   ├── Controller.php
│   │   └── Model.php
│   └── Helpers/        # Helpers de la aplicación
├── config/
│   ├── routes.php      # Definición de rutas
│   ├── db.php          # Configuración de base de datos
│   ├── settings.php    # Configuración general
│   └── cruds/          # Configuraciones de CRUDs
│       └── defaults/   # Plantillas CRUD por defecto
├── views/              # Plantillas de vistas (Plates)
│   ├── layouts/        # Layouts principales
│   └── cruds/          # Vistas CRUD genéricas
├── public/             # Archivos públicos
│   ├── index.php       # Punto de entrada
│   ├── css/            # Hojas de estilo
│   ├── js/             # JavaScript
│   └── img/            # Imágenes
├── storage/            # Almacenamiento de archivos
│   ├── uploads/        # Archivos subidos por usuarios
│   └── users/          # Archivos específicos por usuario
├── tmp/                # Archivos temporales
├── vendor/             # Dependencias de Composer
├── .env                # Configuración de entorno
├── .env.example        # Plantilla de configuración
├── composer.json       # Dependencias del proyecto
└── makecrud            # Generador de CRUDs
```

## Guía de Inicio Rápido

### 1. Crear un Modelo

```php
<?php
// app/Models/User.php
namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected static string $table = 'users';
}
```

### 2. Crear un Controlador

```php
<?php
// app/Controllers/UserController.php
namespace App\Controllers;

use App\Core\Controller;
use Foundation\Core\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request): void
    {
        $users = User::all();
        $this->view('users/index', ['users' => $users]);
    }

    public function create(Request $request): void
    {
        $this->view('users/create');
    }

    public function store(Request $request): void
    {
        User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email')
        ]);
        $this->redirect('/users');
    }
}
```

### 3. Definir Rutas

```php
<?php
// config/routes.php
use Foundation\Core\Router;
use App\Controllers\UserController;

Router::get('/users', [UserController::class, 'index']);
Router::get('/users/create', [UserController::class, 'create']);
Router::post('/users/store', [UserController::class, 'store']);
```

### 4. Crear una Vista

```php
<!-- views/users/index.php -->
<h1>Users</h1>
<ul>
<?php foreach ($users as $user): ?>
    <li><?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)</li>
<?php endforeach; ?>
</ul>
<a href="/users/create">New User</a>
```

## Generador de CRUDs

El framework incluye una herramienta CLI para generar CRUDs completos desde archivos SQL:

```bash
# Listar archivos SQL disponibles
php makecrud --list

# Generar CRUD completo
php makecrud productos
```

Esto genera:
- **Modelo** (`app/Models/Producto.php`)
- **Repository** (`app/Repositories/ProductoRepository.php`)
- **Service** (`app/Services/ProductoService.php`)
- **Controller** (`app/Controllers/ProductoController.php`)
- **Configuraciones CRUD** (`config/cruds/producto/`)
- **Rutas** en `config/routes.php`

### Estructura del archivo SQL

```sql
-- config/datadef/productos.sql
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    categoria_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Componentes del Framework

### Foundation\Database\Model

Modelo base con operaciones CRUD:

```php
// Buscar
$user = User::find(1);
$all = User::all();

// Crear
User::create(['name' => 'John', 'email' => 'john@example.com']);

// Actualizar
User::update(1, ['name' => 'Jane']);

// Eliminar
User::delete(1);

// Consulta personalizada
$results = User::customQuery("SELECT * FROM users WHERE active = 1");

// Dropdown para selects
$options = User::HtmlDropDown(['mostrarcampo' => ['name']]);
```

### Foundation\Crud\Controller

Controlador base con funcionalidad CRUD:

```php
// Renderizar vista
$this->view('users/index', ['users' => $users]);

// Redireccionar
$this->redirect('/users');

// Validar CSRF
$this->validateCSRF();

// Endpoint API para DataTables (genérico)
public function apiData(Request $request): void
{
    parent::apiData($request);
}
```

### Foundation\Core\Router

Sistema de enrutamiento:

```php
// Ruta simple
Router::get('/about', [HomeController::class, 'about']);

// Ruta con parámetros
Router::get('/users/{id}', [UserController::class, 'show']);

// Ruta POST
Router::post('/users', [UserController::class, 'store']);

// Ruta con middleware
Router::get('/admin', [AdminController::class, 'index'], [AuthMiddleware::class]);
```

### Foundation\Core\Request

Manejo de solicitudes HTTP:

```php
public function store(Request $request): void
{
    // Obtener input
    $name = $request->input('name');

    // Obtener archivo
    $file = $request->file('avatar');

    // Verificar método
    if ($request->is('POST')) {
        // ...
    }
}
```

## Configuración

### Base de Datos

```php
<?php
// config/db.php
return [
    'dsn' => 'mysql:host=localhost;dbname=myapp;charset=utf8mb4',
    'username' => 'root',
    'password' => '',
];
```

### Configuración General

```php
<?php
// config/settings.php
return [
    'name' => 'My Application',
    'debug' => true,
    'basellave' => 'your-secret-key',
    'db' => require __DIR__ . '/db.php',
];
```

## Vistas CRUD Genéricas

El framework incluye vistas CRUD preconfiguradas en `views/cruds/`:

- `index.php` - Vista principal con tabla
- `formsection.php` - Sección de formulario
- `inputhtml.inc.php` - Input genérico
- `textareahtml.inc.php` - Textarea genérico
- `datehtml.inc.php` - Selector de fecha
- `filehtml.inc.php` - Upload de archivos

## Master-Detail (Relaciones Padre-Hijo)

El framework incluye soporte completo para relaciones master-detail, útiles para:

- **Facturas** con líneas de factura
- **Órdenes** con ítems de orden
- **Presupuestos** con detalles
- **Remitos** con artículos
- Cualquier relación padre-hijo

### Configuración Básica

Extiende `Foundation\Crud\MasterDetailController`:

```php
<?php
namespace App\Controllers;

use Foundation\Crud\MasterDetailController;

class InvoiceController extends MasterDetailController
{
    protected array $masterConfig = [
        'table' => 'invoices',
        'primaryKey' => 'id',
        'route' => 'invoices',
        'title' => 'Facturas',
        'singular' => 'Factura',
        'displayField' => 'number',

        'fields' => [
            [
                'name' => 'number',
                'type' => 'text',
                'label' => 'Número',
                'required' => true,
                'hidden' => false,
                'width' => 4,
            ],
            // ... más campos
        ],
    ];

    protected array $detailConfig = [
        'table' => 'invoice_items',
        'primaryKey' => 'id',
        'title' => 'Ítems',

        'fields' => [
            [
                'name' => 'product_id',
                'type' => 'select',
                'label' => 'Producto',
                'required' => true,
                'options' => [], // Se cargan dinámicamente
            ],
            [
                'name' => 'quantity',
                'type' => 'number',
                'label' => 'Cantidad',
                'required' => true,
            ],
            // ... más campos
        ],
    ];

    protected string $foreignKey = 'invoice_id';
}
```

### Rutas Master-Detail

```php
use App\Controllers\InvoiceController;

Router::get('/invoices', [InvoiceController::class, 'index']);
Router::get('/invoices/create', [InvoiceController::class, 'create']);
Router::post('/invoices/store', [InvoiceController::class, 'store']);
Router::get('/invoices/edit/{id}', [InvoiceController::class, 'edit']);
Router::post('/invoices/update/{id}', [InvoiceController::class, 'update']);
Router::post('/invoices/delete/{id}', [InvoiceController::class, 'delete']);
Router::get('/api/invoices/data', [InvoiceController::class, 'apiData']);
```

### Esquema SQL de Ejemplo

```sql
-- Tabla maestra
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    number VARCHAR(50) NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    invoice_date DATE NOT NULL,
    status ENUM('draft', 'sent', 'paid') DEFAULT 'draft',
    subtotal DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla detalle
CREATE TABLE invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    product_id INT,
    description VARCHAR(255),
    quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
    unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    tax_rate DECIMAL(5,2) NOT NULL DEFAULT 21,
    line_total DECIMAL(12,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Funcionalidades Incluidas

- **Listado con contador** - Muestra cuántos detalles tiene cada registro maestro
- **CRUD completo** - Crear, editar, eliminar maestros con sus detalles
- **Gestión inline** - Agregar/editar/eliminar detalles desde el formulario del maestro
- **DataTables** - Integración completa con DataTables para el listado
- **Validaciones** - Validación de campos obligatorios
- **Cálculos** - Soporte para cálculos de totales en el cliente

### Vistas Master-Detail

Las vistas genéricas se encuentran en `views/cruds/master-detail/`:

- `index.php` - Listado de registros maestros con contador de detalles
- `create.php` - Formulario para crear nuevo registro maestro
- `edit.php` - Formulario maestro con gestión inline de detalles

## Seguridad

### CSRF Protection

Los formularios incluyen protección CSRF automática:

```php
// En el controlador
$this->validateCSRF();

// En la vista
<input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
```

### Middleware

Crea middleware personalizado:

```php
<?php
// app/Middlewares/AuthMiddleware.php
namespace App\Middlewares;

use Foundation\Middleware\BaseMiddleware;

class AuthMiddleware extends BaseMiddleware
{
    public function handle(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
    }
}
```

## Licencia

MIT License - ver archivo LICENSE para más detalles.

## Soporte

Para reportar bugs o solicitar características, abre un issue en el repositorio.
