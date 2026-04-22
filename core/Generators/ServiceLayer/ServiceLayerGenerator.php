<?php

declare(strict_types=1);

namespace ZMosquita\Core\Generators\ServiceLayer;

use ZMosquita\Core\Generators\Shared\DefinitionNormalizer;
use ZMosquita\Core\Generators\Shared\GeneratorContext;
use ZMosquita\Core\Generators\Shared\GeneratorPathResolver;
use ZMosquita\Core\Generators\Shared\StubRenderer;
use ZMosquita\Core\Support\Paths;

final class ServiceLayerGenerator
{
    public function __construct(
        private DefinitionNormalizer $normalizer,
        private GeneratorPathResolver $paths,
        private StubRenderer $renderer
    ) {
    }

    public function generateService(GeneratorContext $context): void
    {
        $definition = $this->getDefinition($context);

        $this->paths->ensureDirectories($context);
        $target = $this->paths->servicePath($context);

        if (!$context->shouldForce() && is_file($target)) {
            echo "! Servicio ya existe: {$target} (usa --force para sobrescribir)\n";
            return;
        }

        $template = Paths::core('Generators/ServiceLayer/Templates/service.stub.php');

        $content = $this->renderer->render($template, [
            'namespace' => $this->serviceNamespace($context),
            'service_class' => $this->serviceClassName($context),
            'repository_class' => $this->repositoryClassName($context),
            'repository_namespace' => $this->repositoryNamespace($context),
            'repository_property' => lcfirst($this->repositoryClassName($context)),
            'resource' => $context->resourceName,
            'resource_singular' => $this->singularStudly($context->resourceName),
        ]);

        $this->writeFile($target, $content, $context);
    }

    public function generateRepository(GeneratorContext $context): void
    {
        $definition = $this->getDefinition($context);

        $this->paths->ensureDirectories($context);
        $target = $this->paths->repositoryPath($context);

        if (!$context->shouldForce() && is_file($target)) {
            echo "! Repositorio ya existe: {$target} (usa --force para sobrescribir)\n";
            return;
        }

        $template = Paths::core('Generators/ServiceLayer/Templates/repository.stub.php');

        $content = $this->renderer->render($template, [
            'namespace' => $this->repositoryNamespace($context),
            'repository_class' => $this->repositoryClassName($context),
            'table_name' => $definition->tableName,
            'primary_key' => $definition->primaryKey() ?? 'id',
            'resource' => $context->resourceName,
        ]);

        $this->writeFile($target, $content, $context);
    }

    private function getDefinition(GeneratorContext $context)
    {
        return $context->isCore()
            ? $this->normalizer->fromCore($context->resourceName)
            : $this->normalizer->fromApp((string)$context->appCode, $context->resourceName);
    }

    private function serviceNamespace(GeneratorContext $context): string
    {
        if ($context->isCore()) {
            return 'ZMosquita\\Core\\Services';
        }

        return 'Applications\\' . $this->studly((string)$context->appCode) . '\\Services';
    }

    private function serviceClassName(GeneratorContext $context): string
    {
        return $this->studly($context->resourceName) . 'Service';
    }

    private function repositoryNamespace(GeneratorContext $context): string
    {
        if ($context->isCore()) {
            return 'ZMosquita\\Core\\Repositories';
        }

        return 'Applications\\' . $this->studly((string)$context->appCode) . '\\Repositories';
    }

    private function repositoryClassName(GeneratorContext $context): string
    {
        return $this->studly($context->resourceName) . 'Repository';
    }

    private function writeFile(string $target, string $content, GeneratorContext $context): void
    {
        if ($context->shouldDryRun()) {
            echo "--- Dry-run mode: would write to {$target} ---\n";
            echo $content . "\n";
            echo "--- End of dry-run ---\n";
            return;
        }

        file_put_contents($target, $content);
        echo "✓ Created: {$target}\n";
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
