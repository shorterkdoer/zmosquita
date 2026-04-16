#!/usr/bin/env php
<?php

declare(strict_types=1);

use ZMosquita\Core\Database\Schema\AppSchemaInstaller;
use ZMosquita\Core\Database\Schema\CoreSchemaInstaller;
use ZMosquita\Core\Generators\Crud\CrudGenerator;
use ZMosquita\Core\Generators\MasterDetail\MasterDetailGenerator;
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

        case 'make:crud':
            makeCrud($positionals, $options);
            break;

        case 'make:master-detail':
            makeMasterDetail($positionals, $options);
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

  make:crud core <resource> [--force] [--dry-run] [--only=controller|model|validator|views|routes]
      Genera CRUD para un recurso core

  make:crud app <appCode> <resource> [--force] [--dry-run] [--only=controller|model|validator|views|routes]
      Genera CRUD para un recurso de app

  make:master-detail core <master> <detail> [--force] [--dry-run] [--only=controller|views|routes]
      Genera Master-Detail para recursos core

  make:master-detail app <appCode> <master> <detail> [--force] [--dry-run] [--only=controller|views|routes]
      Genera Master-Detail para recursos de app

Ejemplos:
  php bin/zmosquita install:core
  php bin/zmosquita install:app clinica
  php bin/zmosquita make:crud app clinica pacientes
  php bin/zmosquita make:crud app clinica pacientes --only=model
  php bin/zmosquita make:crud app clinica pacientes --force
  php bin/zmosquita make:crud app clinica pacientes --dry-run
  php bin/zmosquita make:master-detail app facturacion facturas factura_items
  php bin/zmosquita make:master-detail app facturacion facturas factura_items --only=routes

TXT;
}