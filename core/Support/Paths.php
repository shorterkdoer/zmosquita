<?php

declare(strict_types=1);

namespace ZMosquita\Core\Support;

final class Paths
{
    private static ?string $basePath = null;

    public static function setBasePath(string $basePath): void
    {
        self::$basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
    }

    public static function base(string $path = ''): string
    {
        $base = self::$basePath ?? dirname(__DIR__, 2);

        return $path === ''
            ? $base
            : $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }

    public static function core(string $path = ''): string
    {
        return self::base('core' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }

    public static function applications(string $path = ''): string
    {
        return self::base('applications' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }

    public static function application(string $appCode, string $path = ''): string
    {
        $prefix = 'applications' . DIRECTORY_SEPARATOR . $appCode;
        return self::base($prefix . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }

    public static function coreDataDef(string $file = ''): string
    {
        return self::core('datadef' . ($file ? DIRECTORY_SEPARATOR . ltrim($file, DIRECTORY_SEPARATOR) : ''));
    }

    public static function appDataDef(string $appCode, string $file = ''): string
    {
        return self::application($appCode, 'datadef' . ($file ? DIRECTORY_SEPARATOR . ltrim($file, DIRECTORY_SEPARATOR) : ''));
    }
}