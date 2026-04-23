<?php

declare(strict_types=1);

namespace ZMosquita\Core\Storage;

use ZMosquita\Core\Support\Paths;

final class SecureStorage
{
    private static ?FileStorageService $instance = null;
    private static array $config = [];

    public static function configure(array $config): void
    {
        self::$config = array_replace([
            'base_path' => null,
            'max_file_size' => 10 * 1024 * 1024, // 10MB
            'allowed_extensions' => ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'doc', 'docx', 'xls', 'xlsx'],
            'allowed_mimes' => [
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/gif',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
        ], $config);

        $basePath = self::$config['base_path'] ?? Paths::base('storage');
        self::$instance = new FileStorageService(
            // Will be resolved via container in real usage
            null,
            $basePath
        );
    }

    public static function getInstance(): FileStorageService
    {
        if (self::$instance === null) {
            self::configure([]);
        }

        return self::$instance;
    }

    public static function forCurrentTenant(): FileStorageService
    {
        return self::getInstance();
    }

    public static function getMaxFileSize(): int
    {
        return self::$config['max_file_size'] ?? 10 * 1024 * 1024;
    }

    public static function getAllowedExtensions(): array
    {
        return self::$config['allowed_extensions'] ?? ['pdf', 'png', 'jpg', 'jpeg'];
    }

    public static function getAllowedMimes(): array
    {
        return self::$config['allowed_mimes'] ?? [
            'application/pdf',
            'image/jpeg',
            'image/png',
        ];
    }
}
