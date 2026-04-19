# Multi-Application Framework - Implementation Summary

## Overview

The framework has been modified to support multiple applications running on the same codebase. Each application can be accessed via its own subdomain while sharing configuration and resources.

## Changes Made

### 1. New Framework Components

#### `AppManager.php` - Application Manager
**Location**: `framework/src/Foundation/Core/AppManager.php`

A new service class that manages multi-application functionality:

- Detects the current application based on subdomain
- Provides application-specific paths (controllers, models, views, config)
- Manages application namespaces
- Handles configuration merging between global and app-specific settings

**Key Methods**:
- `detectApplication()` - Determines which app to use based on HTTP_HOST
- `getCurrentApp()` - Returns the current app name
- `getAppNamespace()` - Returns the namespace for the current app
- `getAppBasePath()` - Returns the base path for the current app
- `getViewsPath()` - Returns the views path for the current app
- `getConfigPath()` - Returns the config path for the current app
- `getRoutesFile()` - Returns the routes file for the current app

### 2. Configuration Files

#### `config/applications.php`
New configuration file for registering applications:

```php
return [
    'app_name' => [
        'subdomain' => 'subdomain',
        'name' => 'Human Readable Name',
        'description' => 'Description',
        'config' => [
            // Optional config overrides
        ],
    ],
];
```

### 3. Modified Files

#### `index.php`
**Changes**:
- Added `AppManager` import and initialization
- Application detection before route loading
- Dynamic path resolution based on current app
- Configuration merging for app-specific overrides
- Uses `AppManager::getRoutesFile()` to load appropriate routes

#### `framework/src/Foundation/Core/Router.php`
**Changes in `dispatch()` method**:
- Resolves controller namespaces dynamically
- Tries current app namespace first, falls back to `App\` namespace
- Same fallback logic for middleware
- Better error messages showing full namespace paths

#### `framework/src/Foundation/Crud/Controller.php`
**Changes**:
- Constructor now uses `$_SESSION['base_pathviews']` for views
- `view()` method updated to use dynamic views path
- Maintains backward compatibility

#### `composer.json`
**Changes**:
- Added `Applications\\` namespace pointing to `applications/` directory

### 4. New Directory Structure

```
applications/
├── README.md                    # Documentation
└── example/                     # Example application
    ├── Controllers/
    │   └── HomeController.php
    ├── Models/                  # (empty, for future use)
    ├── Services/                # (empty, for future use)
    ├── Middlewares/             # (empty, for future use)
    ├── views/
    │   └── example/
    │       ├── home.php
    │       └── test.php
    └── config/
        └── routes.php
```

## How It Works

### Request Flow

1. Request arrives at `index.php`
2. `AppManager::detectApplication()` checks `HTTP_HOST` for subdomain
3. Application-specific paths are set in `$_SESSION`
4. Configuration is loaded (global + app-specific overrides)
5. Routes are loaded from app-specific or global routes file
6. `Router::dispatch()` resolves controllers with current app namespace
7. Controller renders views from app-specific views directory

### Subdomain Detection

- `example.domain.com` → Example Application
- `blog.domain.com` → Blog Application
- `domain.com` (no subdomain) → Default Application (`app/`)

### Namespace Resolution

**Controllers**:
1. Try `Applications\{CurrentApp}\Controllers\{Controller}`
2. Fall back to `App\Controllers\{Controller}`

**Middleware**:
1. Try `Applications\{CurrentApp}\Middlewares\{Middleware}`
2. Fall back to `App\Middlewares\{Middleware}`

## Creating a New Application

### Step-by-Step

1. **Create directory structure**:
   ```bash
   mkdir -p applications/myapp/{Controllers,Models,Services,Middlewares,views,config}
   ```

2. **Register in `config/applications.php`**:
   ```php
   'myapp' => [
       'subdomain' => 'myapp',
       'name' => 'My Application',
       'description' => 'Description',
   ],
   ```

3. **Create controllers** with namespace `Applications\Myapp\Controllers`

4. **Create views** in `applications/myapp/views/`

5. **(Optional) Create app-specific routes** in `applications/myapp/config/routes.php`

6. **Configure DNS** for the subdomain

## Session Variables Available

| Variable | Description |
|----------|-------------|
| `$_SESSION['current_app']` | Current application name (null for default) |
| `$_SESSION['current_app_namespace']` | Current application namespace |
| `$_SESSION['current_app_base_path']` | Current application base path |
| `$_SESSION['base_pathviews']` | Current application views path |

## Backward Compatibility

All changes are **backward compatible**:

- Default application (`app/`) continues to work as before
- Existing routes and controllers unchanged
- When no subdomain matches, framework uses default app
- Shared resources in `app/` accessible to all applications

## Example Application

An example application is provided at `applications/example/` demonstrating:

- Controller creation with proper namespace
- View rendering with app-specific templates
- Route definitions with parameters
- Directory structure

To enable it, uncomment the `example` entry in `config/applications.php` and configure DNS.

## Future Enhancements

Possible improvements for the future:

1. **Application Isolation**: Separate database connections per app
2. **Asset Management**: App-specific public asset directories
3. **Middleware Groups**: Per-app middleware stacks
4. **Service Providers**: Application-specific service providers
5. **Console Commands**: Artisan-like commands for app management
6. **App Templates**: Scaffold new apps from templates

## Testing

To test the multi-app functionality locally:

1. Add entries to `/etc/hosts`:
   ```
   127.0.0.1 example.local
   127.0.0.1 blog.local
   ```

2. Configure web server to accept these domains

3. Uncomment example app in `config/applications.php`

4. Access at `http://example.local/`

## Documentation

See `applications/README.md` for detailed usage instructions.
