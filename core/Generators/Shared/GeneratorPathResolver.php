<?php

declare(strict_types=1);

namespace ZMosquita\Core\Generators\Shared;

use RuntimeException;
use ZMosquita\Core\Support\Paths;

final class GeneratorPathResolver
{
    public function controllerPath(GeneratorContext $context): string
    {
        $class = $this->studly($context->resourceName) . 'Controller.php';

        if ($context->isCore()) {
            return Paths::core('Http/Controllers/' . $class);
        }

        return Paths::application($context->appCode, 'Controllers/' . $class);
    }

    public function modelPath(GeneratorContext $context): string
    {
        $class = $this->singularStudly($context->resourceName) . '.php';

        if ($context->isCore()) {
            return Paths::core('Models/' . $class);
        }

        return Paths::application($context->appCode, 'Models/' . $class);
    }

    public function validatorPath(GeneratorContext $context): string
    {
        $class = $this->singularStudly($context->resourceName) . 'Validator.php';

        if ($context->isCore()) {
            return Paths::core('Validators/' . $class);
        }

        return Paths::application($context->appCode, 'Validators/' . $class);
    }

    public function viewsPath(GeneratorContext $context): string
    {
        if ($context->isCore()) {
            return Paths::core('Views/' . $context->resourceName);
        }

        return Paths::application($context->appCode, 'Views/' . $context->resourceName);
    }

    public function routesPath(GeneratorContext $context): string
    {
        if ($context->isCore()) {
            return Paths::core('routes.php');
        }

        return Paths::application($context->appCode, 'routes.php');
    }

    public function servicePath(GeneratorContext $context): string
    {
        $class = $this->studly($context->resourceName) . 'Service.php';

        if ($context->isCore()) {
            return Paths::core('Services/' . $class);
        }

        return Paths::application($context->appCode, 'Services/' . $class);
    }

    public function repositoryPath(GeneratorContext $context): string
    {
        $class = $this->studly($context->resourceName) . 'Repository.php';

        if ($context->isCore()) {
            return Paths::core('Repositories/' . $class);
        }

        return Paths::application($context->appCode, 'Repositories/' . $class);
    }

    public function appBasePath(string $appCode): string
    {
        return Paths::application($appCode);
    }

    public function ensureDirectories(GeneratorContext $context): void
    {
        $paths = [
            dirname($this->controllerPath($context)),
            dirname($this->modelPath($context)),
            dirname($this->validatorPath($context)),
            dirname($this->servicePath($context)),
            dirname($this->repositoryPath($context)),
            $this->viewsPath($context),
        ];

        foreach ($paths as $path) {
            if (!is_dir($path) && !mkdir($path, 0775, true) && !is_dir($path)) {
                throw new RuntimeException("Unable to create directory: {$path}");
            }
        }
    }

    private function studly(string $value): string
    {
        $value = str_replace(['-', '_'], ' ', $value);
        $value = ucwords($value);
        return str_replace(' ', '', $value);
    }

    private function singularStudly(string $value): string
    {
        $studly = $this->studly($value);

        if (str_ends_with($studly, 's')) {
            return substr($studly, 0, -1);
        }

        return $studly;
    }
}