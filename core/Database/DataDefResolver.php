<?php

declare(strict_types=1);

namespace ZMosquita\Core\Database;

use RuntimeException;
use ZMosquita\Core\Support\Paths;

final class DataDefResolver
{
    public function core(string $name): string
    {
        $path = Paths::coreDataDef($this->normalize($name));

        if (is_file($path)) {
            return $path;
        }

        // Try to find a file matching pattern *_{name}.sql
        // Remove .sql for glob pattern, add it back later
        $nameWithoutExt = str_ends_with($name, '.sql') ? substr($name, 0, -4) : $name;
        $pattern = rtrim(Paths::coreDataDef(''), '/') . '/';
        $globPattern = $pattern . '*' . $nameWithoutExt . '.sql';
        $files = glob($globPattern) ?: [];

        foreach ($files as $file) {
            if (is_file($file)) {
                return $file;
            }
        }

        throw new RuntimeException("Core datadef file not found: $path");
    }

    public function app(string $appCode, string $name): string
    {
        $path = Paths::appDataDef($appCode, $this->normalize($name));

        if (is_file($path)) {
            return $path;
        }

        // Try to find a file matching pattern *_{name}.sql
        // Remove .sql for glob pattern, add it back later
        $nameWithoutExt = str_ends_with($name, '.sql') ? substr($name, 0, -4) : $name;
        $pattern = rtrim(Paths::appDataDef($appCode, ''), '/') . '/';
        $globPattern = $pattern . '*' . $nameWithoutExt . '.sql';
        $files = glob($globPattern) ?: [];

        foreach ($files as $file) {
            if (is_file($file)) {
                return $file;
            }
        }

        throw new RuntimeException("App datadef file not found: $path");
    }

    public function allCore(): array
{
    $files = glob(Paths::coreDataDef('*.sql')) ?: [];
    $files = array_values(array_filter($files, 'is_file'));
    sort($files, SORT_NATURAL);
    return $files;
}
    public function allApp(string $appCode): array
{
    $files = glob(Paths::appDataDef($appCode, '*.sql')) ?: [];
    $files = array_values(array_filter($files, 'is_file'));
    sort($files, SORT_NATURAL);
    return $files;
}
    public function existsCore(string $name): bool
    {
        return is_file(Paths::coreDataDef($this->normalize($name)));
    }

    public function existsApp(string $appCode, string $name): bool
    {
        return is_file(Paths::appDataDef($appCode, $this->normalize($name)));
    }

    private function normalize(string $name): string
    {
        return str_ends_with($name, '.sql') ? $name : $name . '.sql';
    }
}