<?php

declare(strict_types=1);

namespace ZMosquita\Core\Generators\Crud;

use ZMosquita\Core\Generators\Shared\DefinitionNormalizer;
use ZMosquita\Core\Generators\Shared\GeneratorContext;

final class CrudGenerator
{
    public function __construct(
        private DefinitionNormalizer $normalizer,
        private CrudScaffoldWriter $writer
    ) {
    }

    public function generate(GeneratorContext $context): void
    {
        $definition = $this->definition($context);

        $this->controller($context, $definition);
        $this->model($context, $definition);
        $this->validator($context, $definition);
        $this->views($context, $definition);
        $this->routes($context, $definition);
    }

    public function definition(GeneratorContext $context): CrudDefinition
    {
        $table = $context->isCore()
            ? $this->normalizer->fromCore($context->resourceName)
            : $this->normalizer->fromApp((string)$context->appCode, $context->resourceName);

        return new CrudDefinition($table);
    }

    public function controller(GeneratorContext $context, ?CrudDefinition $definition = null): void
    {
        $definition ??= $this->definition($context);
        $this->writer->writeController($context, $definition);
    }

    public function model(GeneratorContext $context, ?CrudDefinition $definition = null): void
    {
        $definition ??= $this->definition($context);
        $this->writer->writeModel($context, $definition);
    }

    public function validator(GeneratorContext $context, ?CrudDefinition $definition = null): void
    {
        $definition ??= $this->definition($context);
        $this->writer->writeValidator($context, $definition);
    }

    public function views(GeneratorContext $context, ?CrudDefinition $definition = null): void
    {
        $definition ??= $this->definition($context);
        $this->writer->writeIndexView($context, $definition);
        $this->writer->writeFormView($context, $definition);
    }

    public function routes(GeneratorContext $context, ?CrudDefinition $definition = null): void
    {
        $definition ??= $this->definition($context);
        $this->writer->appendRoutes($context, $definition);
    }
}