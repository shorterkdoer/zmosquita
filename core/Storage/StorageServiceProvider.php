<?php

declare(strict_types=1);

namespace ZMosquita\Core\Storage;

use ZMosquita\Core\Auth\ContextManager;
use ZMosquita\Core\Support\Config;

final class StorageServiceProvider
{
    private static ?FileStorageService $fileStorage = null;

    public static function register(Config $config): void
    {
        // Configure base storage path from config or default
        $basePath = $config->get('storage.base_path')
            ?? $config->getArray('database')['base_path'] . '/storage'
            ?? dirname(__DIR__, 3) . '/storage';

        // Ensure storage directory exists
        if (!is_dir($basePath)) {
            mkdir($basePath, 0775, true);
        }

        // Protect storage directory
        self::protectStorageDirectory($basePath);
    }

    public static function fileStorage(ContextManager $context): FileStorageService
    {
        if (self::$fileStorage === null) {
            $basePath = self::getBasePath();
            self::$fileStorage = new FileStorageService($context, $basePath);
        }

        return self::$fileStorage;
    }

    public static function getBasePath(): string
    {
        return $_ENV['STORAGE_PATH'] ?? dirname(__DIR__, 3) . '/storage';
    }

    private static function protectStorageDirectory(string $basePath): void
    {
        $htaccess = $basePath . '/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Deny from all\n");
        }

        $webHtaccess = $basePath . '/web/.htaccess';
        $webDir = $basePath . '/web';
        if (!is_dir($webDir)) {
            mkdir($webDir, 0775, true);
        }
        if (!file_exists($webHtaccess)) {
            file_put_contents($webHtaccess, "Deny from all\n");
        }
    }
}
