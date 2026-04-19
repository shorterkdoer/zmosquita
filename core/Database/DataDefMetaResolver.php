<?php

declare(strict_types=1);

namespace ZMosquita\Core\Database;

use RuntimeException;
use ZMosquita\Core\Support\Paths;

final class DataDefMetaResolver
{
    public function core(string $name): ?string
    {
        $path = Paths::core('datadefmeta/' . $this->normalize($name, 'php'));

        return is_file($path) ? $path : null;
    }

    public function app(string $appCode, string $name): ?string
    {
        $path = Paths::application($appCode, 'datadefmeta/' . $this->normalize($name, 'php'));

        return is_file($path) ? $path : null;
    }

    public function loadCore(string $name): array
    {
        $path = $this->core($name);

        return $path ? $this->loadMetaFile($path) : [];
    }

    public function loadApp(string $appCode, string $name): array
    {
        $path = $this->app($appCode, $name);

        return $path ? $this->loadMetaFile($path) : [];
    }

    private function loadMetaFile(string $path): array
    {
        $data = require $path;

        if (!is_array($data)) {
            throw new RuntimeException("Metadata file must return an array: {$path}");
        }

        return $data;
    }

    private function normalize(string $name, string $extension): string
    {
        return str_ends_with($name, '.' . $extension) ? $name : $name . '.' . $extension;
    }
}