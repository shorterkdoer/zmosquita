#!/usr/bin/env php
<?php

declare(strict_types=1);

use ZMosquita\Core\Database\Schema\AppSchemaInstaller;
use ZMosquita\Core\Database\Schema\CoreSchemaInstaller;
use ZMosquita\Core\Database\Schema\TenantSchemaInstaller;
use ZMosquita\Core\Generators\Crud\CrudGenerator;
use ZMosquita\Core\Generators\DataDefMeta\DataDefMetaGenerator;
use ZMosquita\Core\Generators\MasterDetail\MasterDetailGenerator;
use ZMosquita\Core\Generators\ServiceLayer\ServiceLayerGenerator;
use ZMosquita\Core\Generators\Shared\GeneratorContext;
use ZMosquita\Core\Support\Container;

require dirname(__DIR__) . '/bootstrap_core.php';

$args = $argv;
array_shift($args);

$command = $args[0] ?? null;

if ($command === null) {
    help();
    exit(1);
}

$options = parseOptions($args);
$positionals = parsePositionals($args);

try {
    switch ($command) {
        case 'install:core':
            installCore();
            break;

        case 'install:app':
            installApp($positionals);
            break;

        case 'tenant:make':
            makeTenant($positionals, $options);
            break;

        case 'tenant:app:install':
            installTenantApp($positionals, $options);
            break;

        case 'tenant:drop':
            dropTenant($positionals, $options);
            break;

        case 'tenant:list':
            listTenants();
            break;

        case 'make:crud':
            makeCrud($positionals, $options);
            break;

        case 'make:master-detail':
            makeMasterDetail($positionals, $options);
            break;

        case 'make:datadefmeta':
            makeDataDefMeta($positionals, $options);
            break;

        case 'make:service':
            makeService($positionals, $options);
            break;

        case 'help':
        case '--help':
        case '-h':
            help();
            break;

        default:
            echo "Comando desconocido: {$command}\n\n";
            help();
            exit(1);
    }
} catch (Throwable $e) {
    fwrite(STDERR, "[ERROR] " . $e->getMessage() . PHP_EOL);
    exit(1);
}

function installCore(): void
{
    $installer = Container::instance()->get(CoreSchemaInstaller::class);
    $result = $installer->install();

    if (!$result->ok) {
        echo "Falló la instalación del core.\n";
        print_r($result->errors);
        exit(1);
    }

    echo "Core instalado correctamente.\n";
    foreach ($result->files as $file) {
        echo " - {$file}\n";
    }
}

function installApp(array $positionals): void
{
    $appCode = $positionals[1] ?? null;

    if (!$appCode) {
        throw new InvalidArgumentException('Uso: install:app <appCode>');
    }

    $installer = Container::instance()->get(AppSchemaInstaller::class);
    $result = $installer->install($appCode);

    if (!$result->ok) {
        echo "Falló la instalación de la app {$appCode}.\n";
        print_r($result->errors);
        exit(1);
    }

    echo "App {$appCode} instalada correctamente.\n";
    foreach ($result->files as $file) {
        echo " - {$file}\n";
    }
}

/**
 * @param array<string, mixed> $options
 */
function makeCrud(array $positionals, array $options): void
{
    $scope = $positionals[1] ?? null;
    $force = (bool)($options['force'] ?? false);
    $dryRun = (bool)($options['dry-run'] ?? false);
    $only = $options['only'] ?? null;

    if ($scope === 'core') {
        $resource = $positionals[2] ?? null;
        if (!$resource) {
            throw new InvalidArgumentException('Uso: make:crud core <resource> [--force] [--dry-run] [--only=...]');
        }

        $context = new GeneratorContext('core', null, $resource, $force, $dryRun);
    } elseif ($scope === 'app') {
        $appCode = $positionals[2] ?? null;
        $resource = $positionals[3] ?? null;

        if (!$appCode || !$resource) {
            throw new InvalidArgumentException('Uso: make:crud app <appCode> <resource> [--force] [--dry-run] [--only=...]');
        }

        $context = new GeneratorContext('app', $appCode, $resource, $force, $dryRun);
    } else {
        throw new InvalidArgumentException('Uso: make:crud core <resource> | make:crud app <appCode> <resource>');
    }

    $generator = Container::instance()->get(CrudGenerator::class);
    $definition = $generator->definition($context);

    switch ($only) {
        case null:
            $generator->generate($context);
            break;
        case 'controller':
            $generator->controller($context, $definition);
            break;
        case 'model':
            $generator->model($context, $definition);
            break;
        case 'validator':
            $generator->validator($context, $definition);
            break;
        case 'views':
            $generator->views($context, $definition);
            break;
        case 'routes':
            $generator->routes($context, $definition);
            break;
        default:
            throw new InvalidArgumentException("Valor inválido para --only en make:crud: {$only}");
    }

    echo "CRUD procesado correctamente para {$context->qualifiedName()}.\n";
}

/**
 * @param array<string, mixed> $options
 */
function makeMasterDetail(array $positionals, array $options): void
{
    $scope = $positionals[1] ?? null;
    $force = (bool)($options['force'] ?? false);
    $dryRun = (bool)($options['dry-run'] ?? false);
    $only = $options['only'] ?? null;

    if ($scope === 'core') {
        $master = $positionals[2] ?? null;
        $detail = $positionals[3] ?? null;

        if (!$master || !$detail) {
            throw new InvalidArgumentException('Uso: make:master-detail core <master> <detail> [--force] [--dry-run] [--only=...]');
        }

        $context = new GeneratorContext('core', null, $detail, $force, $dryRun);
    } elseif ($scope === 'app') {
        $appCode = $positionals[2] ?? null;
        $master = $positionals[3] ?? null;
        $detail = $positionals[4] ?? null;

        if (!$appCode || !$master || !$detail) {
            throw new InvalidArgumentException('Uso: make:master-detail app <appCode> <master> <detail> [--force] [--dry-run] [--only=...]');
        }

        $context = new GeneratorContext('app', $appCode, $detail, $force, $dryRun);
    } else {
        throw new InvalidArgumentException('Uso: make:master-detail core <master> <detail> | make:master-detail app <appCode> <master> <detail>');
    }

    $generator = Container::instance()->get(MasterDetailGenerator::class);
    $definition = $generator->definition($context, $master, $detail);

    switch ($only) {
        case null:
            $generator->generate($context, $master, $detail);
            break;
        case 'controller':
            $generator->controller($context, $definition);
            break;
        case 'views':
            $generator->views($context, $definition);
            break;
        case 'routes':
            $generator->routes($context, $definition);
            break;
        default:
            throw new InvalidArgumentException("Valor inválido para --only en make:master-detail: {$only}");
    }

    echo "Master-Detail procesado correctamente.\n";
}

/**
 * @param array<string, mixed> $options
 */
function makeDataDefMeta(array $positionals, array $options): void
{
    $scope = $positionals[1] ?? null;
    $force = (bool)($options['force'] ?? false);
    $dryRun = (bool)($options['dry-run'] ?? false);

    if ($scope === 'core') {
        $resource = $positionals[2] ?? null;
        if (!$resource) {
            throw new InvalidArgumentException('Uso: make:datadefmeta core <resource> [--force] [--dry-run]');
        }

        $context = new GeneratorContext('core', null, $resource, $force, $dryRun);
    } elseif ($scope === 'app') {
        $appCode = $positionals[2] ?? null;
        $resource = $positionals[3] ?? null;

        if (!$appCode || !$resource) {
            throw new InvalidArgumentException('Uso: make:datadefmeta app <appCode> <resource> [--force] [--dry-run]');
        }

        $context = new GeneratorContext('app', $appCode, $resource, $force, $dryRun);
    } else {
        throw new InvalidArgumentException('Uso: make:datadefmeta core <resource> | make:datadefmeta app <appCode> <resource>');
    }

    $generator = Container::instance()->get(DataDefMetaGenerator::class);

    echo "Generando DataDefMeta para {$context->qualifiedName()}...\n";
    $generator->generate($context);
    echo "✓ DataDefMeta generado correctamente en ";
    echo $context->isCore()
        ? "core/datadefmeta/{$resource}.php\n"
        : "applications/{$context->appCode}/datadefmeta/{$resource}.php\n";
}

/**
 * @param array<string, mixed> $options
 */
function makeService(array $positionals, array $options): void
{
    $scope = $positionals[1] ?? null;
    $force = (bool)($options['force'] ?? false);
    $dryRun = (bool)($options['dry-run'] ?? false);

    if ($scope === 'core') {
        $resource = $positionals[2] ?? null;
        if (!$resource) {
            throw new InvalidArgumentException('Uso: make:service core <resource> [--force] [--dry-run]');
        }
        $context = new GeneratorContext('core', null, $resource, $force, $dryRun);
    } elseif ($scope === 'app') {
        $appCode = $positionals[2] ?? null;
        $resource = $positionals[3] ?? null;

        if (!$appCode || !$resource) {
            throw new InvalidArgumentException('Uso: make:service app <appCode> <resource> [--force] [--dry-run]');
        }
        $context = new GeneratorContext('app', $appCode, $resource, $force, $dryRun);
    } else {
        throw new InvalidArgumentException('Uso: make:service core <resource> | make:service app <appCode> <resource>');
    }

    $generator = Container::instance()->get(ServiceLayerGenerator::class);

    echo "Generando Service Layer para {$context->qualifiedName()}...\n";

    $generator->generateService($context);
    $generator->generateRepository($context);

    echo "✓ Service Layer generado correctamente:\n";
    echo "  - Service: ";
    echo $context->isCore()
        ? "core/Services/{$context->resourceName}Service.php\n"
        : "applications/{$context->appCode}/Services/{$context->resourceName}Service.php\n";
    echo "  - Repository: ";
    echo $context->isCore()
        ? "core/Repositories/{$context->resourceName}Repository.php\n"
        : "applications/{$context->appCode}/Repositories/{$context->resourceName}Repository.php\n";
}

function makeTenant(array $positionals, array $options): void
{
    $code = $positionals[1] ?? null;
    $name = $positionals[2] ?? null;
    $catalog = $positionals[3] ?? $code;

    if (!$code || !$name) {
        throw new InvalidArgumentException('Uso: tenant:make <code> <name> [catalog]');
    }

    $installer = Container::instance()->get(TenantSchemaInstaller::class);

    echo "Creando tenant: {$code} ({$name}) en catálogo [{$catalog}]...\n";

    if ($installer->tenantExists($catalog)) {
        throw new RuntimeException("El catálogo [{$catalog}] ya existe.");
    }

    $installer->createTenantCatalog($catalog);
    echo "✓ Catálogo [{$catalog}] creado.\n";

    echo "Instalando core schema en catálogo [{$catalog}]...\n";
    $result = $installer->installCoreToTenant($catalog, withSeeds: true);

    if (!$result->ok) {
        echo "✗ Falló la instalación del core en el tenant.\n";
        print_r($result->errors);
        exit(1);
    }

    echo "✓ Core schema instalado en tenant.\n";

    $iam = Container::instance()->get(\ZMosquita\Core\Database\Connection::class);
    $iam = $iam->iam();

    $now = date('Y-m-d H:i:s');
    $iam->execute(
        "INSERT INTO iam_tenants (code, name, catalog, status, created_at, updated_at)
         VALUES (:code, :name, :catalog, 'active', :now, :now)",
        ['code' => $code, 'name' => $name, 'catalog' => $catalog, 'now' => $now]
    );

    echo "✓ Tenant registrado en iam_tenants.\n";
    echo "\nTenant creado correctamente: {$code} ({$name}) en [{$catalog}]\n";
}

function installTenantApp(array $positionals, array $options): void
{
    $tenantCode = $positionals[1] ?? null;
    $appCode = $positionals[2] ?? null;

    if (!$tenantCode || !$appCode) {
        throw new InvalidArgumentException('Uso: tenant:app:install <tenantCode> <appCode>');
    }

    $iam = Container::instance()->get(\ZMosquita\Core\Database\Connection::class);
    $iam = $iam->iam();

    $tenant = $iam->fetchOne(
        "SELECT id, code, name, catalog FROM iam_tenants WHERE code = :code AND deleted_at IS NULL",
        ['code' => $tenantCode]
    );

    if (!$tenant) {
        throw new RuntimeException("Tenant [{$tenantCode}] no encontrado.");
    }

    $installer = Container::instance()->get(TenantSchemaInstaller::class);

    echo "Instalando app [{$appCode}] en tenant [{$tenantCode}] (catálogo: {$tenant['catalog']})...\n";

    $result = $installer->installAppToTenant($tenant['catalog'], $appCode, withSeeds: true);

    if (!$result->ok) {
        echo "✗ Falló la instalación de la app.\n";
        print_r($result->errors);
        exit(1);
    }

    echo "✓ App [{$appCode}] instalada correctamente.\n";
}

function dropTenant(array $positionals, array $options): void
{
    $catalog = $positionals[1] ?? null;
    $force = (bool)($options['force'] ?? false);

    if (!$catalog) {
        throw new InvalidArgumentException('Uso: tenant:drop <catalog> [--force]');
    }

    if (!$force) {
        echo "ADVERTENCIA: Esto eliminará todo el catálogo [{$catalog}].\n";
        echo "Para confirmar, usa --force\n";
        exit(1);
    }

    $installer = Container::instance()->get(TenantSchemaInstaller::class);

    echo "Eliminando catálogo [{$catalog}]...\n";

    if ($installer->dropTenantCatalog($catalog, force: true)) {
        echo "✓ Catálogo [{$catalog}] eliminado.\n";
    } else {
        echo "✗ No se pudo eliminar el catálogo.\n";
        exit(1);
    }

    $iam = Container::instance()->get(\ZMosquita\Core\Database\Connection::class);
    $iam = $iam->iam();

    $now = date('Y-m-d H:i:s');
    $iam->execute(
        "UPDATE iam_tenants SET deleted_at = :now, updated_at = :now WHERE catalog = :catalog",
        ['catalog' => $catalog, 'now' => $now]
    );

    echo "✓ Tenant marcado como eliminado.\n";
}

function listTenants(): void
{
    $iam = Container::instance()->get(\ZMosquita\Core\Database\Connection::class);
    $iam = $iam->iam();

    $tenants = $iam->fetchAll(
        "SELECT id, code, name, catalog, status, created_at FROM iam_tenants WHERE deleted_at IS NULL ORDER BY name ASC"
    );

    if (empty($tenants)) {
        echo "No hay tenants registrados.\n";
        return;
    }

    echo "\nTenants registrados:\n\n";
    echo str_pad('ID', 6) . ' ';
    echo str_pad('Código', 20) . ' ';
    echo str_pad('Nombre', 30) . ' ';
    echo str_pad('Catálogo', 25) . ' ';
    echo str_pad('Estado', 10) . "\n";
    echo str_repeat('-', 110) . "\n";

    foreach ($tenants as $tenant) {
        echo str_pad((string)$tenant['id'], 6) . ' ';
        echo str_pad($tenant['code'], 20) . ' ';
        echo str_pad(substr($tenant['name'], 0, 30), 30) . ' ';
        echo str_pad($tenant['catalog'], 25) . ' ';
        echo str_pad($tenant['status'], 10) . "\n";
    }

    echo "\nTotal: " . count($tenants) . " tenant(s)\n";
}

/**
 * @return array<string, mixed>
 */
function parseOptions(array $args): array
{
    $options = [];

    foreach ($args as $arg) {
        if (!str_starts_with($arg, '--')) {
            continue;
        }

        $option = substr($arg, 2);

        if ($option === 'force') {
            $options['force'] = true;
            continue;
        }

        if ($option === 'dry-run') {
            $options['dry-run'] = true;
            continue;
        }

        if (str_starts_with($option, 'only=')) {
            $options['only'] = substr($option, 5);
            continue;
        }
    }

    return $options;
}

function parsePositionals(array $args): array
{
    return array_values(array_filter(
        $args,
        static fn (string $arg): bool => !str_starts_with($arg, '--')
    ));
}

function help(): void
{
    echo <<<TXT
ZMosquita CLI

Comandos:
  install:core
      Instala el esquema core desde core/datadef/*.sql

  install:app <appCode>
      Instala el esquema de una app desde applications/<appCode>/datadef/*.sql

  tenant:make <code> <name> <catalog>
      Crea un nuevo tenant con catálogo separado

  tenant:make <code> <name>
      Crea un nuevo tenant usando el code como nombre de catálogo

  tenant:app:install <tenantCode> <appCode>
      Instala una aplicación en el catálogo del tenant

  tenant:drop <catalog> [--force]
      Elimina el catálogo de un tenant (peligroso)

  tenant:list
      Lista todos los tenants registrados

  make:crud core <resource> [--force] [--dry-run] [--only=controller|model|validator|views|routes]
      Genera CRUD para un recurso core

  make:crud app <appCode> <resource> [--force] [--dry-run] [--only=controller|model|validator|views|routes]
      Genera CRUD para un recurso de app

  make:master-detail core <master> <detail> [--force] [--dry-run] [--only=controller|views|routes]
      Genera Master-Detail para recursos core

  make:master-detail app <appCode> <master> <detail> [--force] [--dry-run] [--only=controller|views|routes]
      Genera Master-Detail para recursos de app

  make:datadefmeta core <resource> [--force] [--dry-run]
      Genera archivo de metadatos para un recurso core

  make:datadefmeta app <appCode> <resource> [--force] [--dry-run]
      Genera archivo de metadatos para un recurso de app

  make:service core <resource> [--force] [--dry-run]
      Genera Service y Repository para un recurso core

  make:service app <appCode> <resource> [--force] [--dry-run]
      Genera Service y Repository para un recurso de app

Ejemplos:
  php bin/zmosquita install:core
  php bin/zmosquita install:app clinica
  php bin/zmosquita tenant:make acme "ACME Corp" acme_db
  php bin/zmosquita tenant:make acme "ACME Corp"
  php bin/zmosquita tenant:app:install acme contabilidad
  php bin/zmosquita tenant:list
  php bin/zmosquita tenant:drop acme_db --force
  php bin/zmosquita make:crud app clinica pacientes
  php bin/zmosquita make:crud app clinica pacientes --only=model
  php bin/zmosquita make:crud app clinica pacientes --force
  php bin/zmosquita make:crud app clinica pacientes --dry-run
  php bin/zmosquita make:master-detail app facturacion facturas factura_items
  php bin/zmosquita make:master-detail app facturacion facturas factura_items --only=routes
  php bin/zmosquita make:datadefmeta app demo personas
  php bin/zmosquita make:datadefmeta app demo personas --force
  php bin/zmosquita make:datadefmeta app demo personas --dry-run
  php bin/zmosquita make:service app demo personas
  php bin/zmosquita make:service app demo personas --force

TXT;
}