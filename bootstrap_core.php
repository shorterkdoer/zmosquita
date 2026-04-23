<?php

declare(strict_types=1);

use ZMosquita\Core\Bootstrap\ApplicationBootstrap;
use ZMosquita\Core\Bootstrap\AuthServiceProvider;
use ZMosquita\Core\Bootstrap\DatabaseServiceProvider;
use ZMosquita\Core\Bootstrap\GeneratorServiceProvider;
use ZMosquita\Core\Bootstrap\HttpServiceProvider;
use ZMosquita\Core\Bootstrap\StorageServiceProvider;
use ZMosquita\Core\Support\Container;

require_once __DIR__ . '/vendor/autoload.php';

$config = [
    'database' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'database' => 'zmosquita',
        'username' => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
    ],
];

$container = Container::instance();

$bootstrap = new ApplicationBootstrap($container);
$bootstrap->bootstrap(__DIR__, $config, [
    DatabaseServiceProvider::class,
    AuthServiceProvider::class,
    GeneratorServiceProvider::class,
    HttpServiceProvider::class,
    StorageServiceProvider::class,
]);