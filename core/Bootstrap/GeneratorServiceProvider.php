<?php

declare(strict_types=1);

namespace ZMosquita\Core\Bootstrap;

use ZMosquita\Core\Database\DataDefMetaResolver;
use ZMosquita\Core\Database\DataDefResolver;
use ZMosquita\Core\Database\QueryBuilder;
use ZMosquita\Core\Database\Schema\SqlSchemaLoader;
use ZMosquita\Core\Generators\Crud\CrudGenerator;
use ZMosquita\Core\Generators\Crud\CrudScaffoldWriter;
use ZMosquita\Core\Generators\Crud\RelationLabelResolver;
use ZMosquita\Core\Generators\Crud\RelationOptionResolver;
use ZMosquita\Core\Generators\DataDefMeta\DataDefMetaGenerator;
use ZMosquita\Core\Generators\MasterDetail\MasterDetailGenerator;
use ZMosquita\Core\Generators\ServiceLayer\ServiceLayerGenerator;
use ZMosquita\Core\Generators\MasterDetail\MasterDetailScaffoldWriter;
use ZMosquita\Core\Generators\MasterDetail\RelationInspector;
use ZMosquita\Core\Generators\Shared\DefinitionNormalizer;
use ZMosquita\Core\Generators\Shared\GeneratorPathResolver;
use ZMosquita\Core\Generators\Shared\StubRenderer;

final class GeneratorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->set(GeneratorPathResolver::class, new GeneratorPathResolver());
        $this->container->set(StubRenderer::class, new StubRenderer());
        $this->container->set(RelationInspector::class, new RelationInspector());

        $this->container->bind(DefinitionNormalizer::class, fn ($c) => new DefinitionNormalizer(
            $c->get(DataDefResolver::class),
            $c->get(DataDefMetaResolver::class),
            $c->get(SqlSchemaLoader::class)
        ));

        $this->container->bind(RelationOptionResolver::class, fn ($c) => new RelationOptionResolver(
            $c->get(QueryBuilder::class)
        ));

        $this->container->bind(RelationLabelResolver::class, fn ($c) => new RelationLabelResolver(
            $c->get(QueryBuilder::class)
        ));

        $this->container->bind(CrudScaffoldWriter::class, fn ($c) => new CrudScaffoldWriter(
            $c->get(GeneratorPathResolver::class),
            $c->get(StubRenderer::class)
        ));

        $this->container->bind(CrudGenerator::class, fn ($c) => new CrudGenerator(
            $c->get(DefinitionNormalizer::class),
            $c->get(CrudScaffoldWriter::class)
        ));

        $this->container->bind(MasterDetailScaffoldWriter::class, fn ($c) => new MasterDetailScaffoldWriter(
            $c->get(GeneratorPathResolver::class),
            $c->get(StubRenderer::class)
        ));

        $this->container->bind(MasterDetailGenerator::class, fn ($c) => new MasterDetailGenerator(
            $c->get(DefinitionNormalizer::class),
            $c->get(RelationInspector::class),
            $c->get(MasterDetailScaffoldWriter::class)
        ));

        $this->container->bind(DataDefMetaGenerator::class, fn ($c) => new DataDefMetaGenerator(
            $c->get(DefinitionNormalizer::class)
        ));

        $this->container->bind(ServiceLayerGenerator::class, fn ($c) => new ServiceLayerGenerator(
            $c->get(DefinitionNormalizer::class),
            $c->get(GeneratorPathResolver::class),
            $c->get(StubRenderer::class)
        ));
    }
}