<?php

declare(strict_types=1);

namespace ZMosquita\Core\Support\Facades;

use ZMosquita\Core\Database\DataDefResolver;
use ZMosquita\Core\Support\Container;

final class DataDef
{
    private static function service(): DataDefResolver
    {
        return Container::instance()->get(DataDefResolver::class);
    }

    public static function core(string $name): string
    {
        return self::service()->core($name);
    }

    public static function app(string $appCode, string $name): string
    {
        return self::service()->app($appCode, $name);
    }

    public static function allCore(): array
    {
        return self::service()->allCore();
    }

    public static function allApp(string $appCode): array
    {
        return self::service()->allApp($appCode);
    }
}