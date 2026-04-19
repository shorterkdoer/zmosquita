<?php

declare(strict_types=1);

namespace ZMosquita\Core\Database;

use RuntimeException;
use ZMosquita\Core\Support\Paths;

final class InitialSeedsResolver
{
    public function core(string $name): string
    {
        $path = Paths::core('initialseeds', $this->normalize($name));

        if (!is_file($path)) {
            throw new RuntimeException("Core initial seed file not found: $path");
        }

        return $path;
    }

    public function app(string $appCode, string $name): string
    {
        $path = Paths::application($appCode, 'initialseeds', $this->normalize($name));

        if (!is_file($path)) {
            throw new RuntimeException("App initial seed file not found: $path");
        }

        return $path;
    }

    public function allCore(): array
    {
        $files = glob(Paths::core('initialseeds', '*.sql')) ?: [];
        $files = array_values(array_filter($files, 'is_file'));
        sort($files, SORT_NATURAL);

        return $files;
    }

    public function allApp(string $appCode): array
    {
        $files = glob(Paths::application($appCode, 'initialseeds', '*.sql')) ?: [];
        $files = array_values(array_filter($files, 'is_file'));
        sort($files, SORT_NATURAL);

        return $files;
    }

    public function existsCore(string $name): bool
    {
        return is_file(Paths::core('initialseeds', $this->normalize($name)));
    }

    public function existsApp(string $appCode, string $name): bool
    {
        return is_file(Paths::application($appCode, 'initialseeds', $this->normalize($name)));
    }

    public function hasCoreSeeds(): bool
    {
        return is_dir(Paths::core('initialseeds'));
    }

    public function hasAppSeeds(string $appCode): bool
    {
        return is_dir(Paths::application($appCode, 'initialseeds'));
    }

    private function normalize(string $name): string
    {
        return str_ends_with($name, '.sql') ? $name : $name . '.sql';
    }
}
