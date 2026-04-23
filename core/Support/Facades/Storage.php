<?php

declare(strict_types=1);

namespace ZMosquita\Core\Support\Facades;

use ZMosquita\Core\Storage\FileStorageService;
use ZMosquita\Core\Storage\StorageServiceProvider;
use ZMosquita\Core\Auth\ContextManager;
use ZMosquita\Core\Support\Container;

/**
 * Storage facade for convenient file operations
 */
final class Storage
{
    private static ?FileStorageService $instance = null;

    public static function getInstance(): FileStorageService
    {
        if (self::$instance === null) {
            $context = Container::instance()->get(ContextManager::class);
            $basePath = StorageServiceProvider::getBasePath();

            self::$instance = new FileStorageService($context, $basePath);
        }

        return self::$instance;
    }

    public static function __callStatic(string $name, array $arguments)
    {
        return self::getInstance()->{$name}(...$arguments);
    }
}
