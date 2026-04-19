<?php

declare(strict_types=1);

namespace ZMosquita\Core\Support\Facades;

use ZMosquita\Core\Database\TableResolver;
use ZMosquita\Core\Support\Container;

final class Table
{
    private static function service(): TableResolver
    {
        return Container::instance()->get(TableResolver::class);
    }

    public static function iam(string $table): string
    {
        return self::service()->iam($table);
    }

    public static function app(string $table, ?string $appCode = null): string
    {
        return self::service()->app($table, $appCode);
    }
}