# Multi-Application Framework

This framework now supports multiple applications running on the same codebase, with each application accessible via its own subdomain.

## How it Works

The framework detects which application to use based on the subdomain in the HTTP request:

- `example.yourdomain.com` → Example Application
- `blog.yourdomain.com` → Blog Application
- `yourdomain.com` (no subdomain) → Default Application (`app/`)

## Creating a New Application

### 1. Create the Application Structure

```bash
mkdir -p applications/myapp/{Controllers,Models,Services,Middlewares,views,config}
```

### 2. Register the Application

Add your application to `config/applications.php`:

```php
return [
    'myapp' => [
        'subdomain' => 'myapp',
        'name' => 'My Application',
        'description' => 'Description of my application',
    ],
];
```

### 3. Create Controllers

Create controllers in `applications/myapp/Controllers/`:

```php
<?php

namespace Applications\Myapp\Controllers;

use Foundation\Crud\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home', [
            'title' => 'My Application'
        ]);
    }
}
```

### 4. Create Views

Create views in `applications/myapp/views/`:

```php
<!-- applications/myapp/views/home.php -->
<!DOCTYPE html>
<html>
<head>
    <title><?= $this->e($title) ?></title>
</head>
<body>
    <h1>Welcome to My Application</h1>
</body>
</html>
```

### 5. Define Routes

Optionally create application-specific routes in `applications/myapp/config/routes.php`:

```php
<?php

use Foundation\Core\Router;

Router::get('/', ['HomeController', 'index']);
Router::get('/about', ['HomeController', 'about']);
```

### 6. Configure DNS and Web Server

Add a DNS record for your subdomain pointing to your server, and configure your web server to accept the subdomain.

## Application Configuration

### Global Configuration

All applications share the global configuration in `config/settings.php`:
- Database connections
- Mail settings
- Security keys
- etc.

### Application-Specific Configuration

You can override global settings per application:

```php
// config/applications.php
return [
    'myapp' => [
        'subdomain' => 'myapp',
        'name' => 'My Application',
        'config' => [
            'title' => 'My App Title',  // Overrides global title
            'subtitle' => 'My App Subtitle',
        ],
    ],
];
```

## Namespace Conventions

- **Default Application**: `App\`
- **Additional Applications**: `Applications\{AppName}\`

## Controller Resolution

When defining routes, you can use short controller names:

```php
// Short name - will be resolved based on current app
Router::get('/', ['HomeController', 'index']);

// Full namespace - always uses this specific controller
Router::get('/', ['\Applications\Myapp\Controllers\HomeController', 'index']);
```

The router first tries to find the controller in the current application, then falls back to the default `App\` namespace.

## Middleware Resolution

Middleware works similarly to controllers:

```php
Router::get('/admin', ['AdminController', 'index'], ['AuthMiddleware']);
```

The router will first look for `Applications\{CurrentApp}\Middlewares\AuthMiddleware`, then fall back to `App\Middlewares\AuthMiddleware`.

## Shared Resources

You can share resources between applications by placing them in the default `app/` directory:

- Shared models in `app/Models/`
- Shared services in `app/Services/`
- Shared controllers in `app/Controllers/`
- Shared middleware in `app/Middlewares/`

These will be accessible from all applications as fallbacks.

## Example Application

An example application is provided in `applications/example/` to demonstrate the structure. To enable it:

1. Uncomment the `example` entry in `config/applications.php`
2. Add `example` subdomain to your DNS/server configuration
3. Access at `http://example.yourdomain.com`

## Session Data

The following session variables are available:

- `$_SESSION['current_app']` - Current application name (null for default)
- `$_SESSION['current_app_namespace']` - Current application namespace
- `$_SESSION['current_app_base_path']` - Current application base path
- `$_SESSION['base_pathviews']` - Current application views path
