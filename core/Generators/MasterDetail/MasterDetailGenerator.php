<?php

declare(strict_types=1);

namespace ZMosquita\Core\Generators\MasterDetail;

use RuntimeException;
use ZMosquita\Core\Generators\Crud\CrudDefinition;
use ZMosquita\Core\Generators\Shared\DefinitionNormalizer;
use ZMosquita\Core\Generators\Shared\GeneratorContext;

final class MasterDetailGenerator
{
    public function __construct(
        private DefinitionNormalizer $normalizer,
        private RelationInspector $inspector,
        private MasterDetailScaffoldWriter $writer
    ) {
    }

    public function generate(GeneratorContext $context, string $masterResource, string $detailResource): void
    {
        $definition = $this->definition($context, $masterResource, $detailResource);

        $this->controller($context, $definition);
        $this->views($context, $definition);
        $this->routes($context, $definition);
    }

    public function definition(GeneratorContext $context, string $masterResource, string $detailResource): MasterDetailDefinition
    {
        $masterTable = $context->isCore()
            ? $this->normalizer->fromCore($masterResource)
            : $this->normalizer->fromApp((string)$context->appCode, $masterResource);

        $detailTable = $context->isCore()
            ? $this->normalizer->fromCore($detailResource)
            : $this->normalizer->fromApp((string)$context->appCode, $detailResource);

        $master = new CrudDefinition($masterTable);
        $detail = new CrudDefinition($detailTable);

        $foreignKey = $this->inspector->detect($master, $detail);

        if (!$foreignKey || !$this->inspector->validate($master, $detail, $foreignKey)) {
            throw new RuntimeException(
                "Unable to determine master-detail relation between [{$masterResource}] and [{$detailResource}]"
            );
        }

        $meta = $detail->table->meta['master_detail'] ?? [];

        return new MasterDetailDefinition(
            master: $master,
            detail: $detail,
            foreignKey: $foreignKey,
            meta: is_array($meta) ? $meta : [],
        );
    }

    public function controller(GeneratorContext $context, MasterDetailDefinition $definition): void
    {
        $this->writer->writeController($context, $definition);
    }

    public function views(GeneratorContext $context, MasterDetailDefinition $definition): void
    {
        $this->writer->writeIndexView($context, $definition);
        $this->writer->writeFormView($context, $definition);
    }

    public function routes(GeneratorContext $context, MasterDetailDefinition $definition): void
    {
        $this->writer->appendRoutes($context, $definition);
    }
}