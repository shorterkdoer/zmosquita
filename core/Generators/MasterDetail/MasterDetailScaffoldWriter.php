<?php

declare(strict_types=1);

namespace ZMosquita\Core\Generators\MasterDetail;

use RuntimeException;
use ZMosquita\Core\Generators\Shared\GeneratorContext;
use ZMosquita\Core\Generators\Shared\GeneratorPathResolver;
use ZMosquita\Core\Generators\Shared\StubRenderer;
use ZMosquita\Core\Support\Paths;

final class MasterDetailScaffoldWriter
{
    public function __construct(
        private GeneratorPathResolver $paths,
        private StubRenderer $renderer
    ) {
    }

    public function writeController(GeneratorContext $context, MasterDetailDefinition $definition): void
    {
        $this->paths->ensureDirectories($context);

        $target = $this->nestedControllerPath($context, $definition);
        if (!$context->shouldForce() && !$definition->detail->overwrite() && is_file($target)) {
            return;
        }

        $template = Paths::core('Generators/MasterDetail/Templates/controller.stub.php');

        $lookupMethods = [];
        $createLookups = [];
        $editLookups = [];

        foreach ($definition->detail->formColumns() as $column) {
            if ($column->name === $definition->foreignKey()) {
                continue;
            }

            if (!$definition->detail->hasRelation($column->name)) {
                continue;
            }

            $relation = $definition->detail->relationMeta($column->name);
            if (!isset($relation['table'])) {
                continue;
            }

            $method = $definition->detail->lookupMethodName($column->name);
            $var = '$' . $column->name . 'Options';

            $lookupMethods[] = $this->buildLookupMethod($method, $relation);
            $createLookups[] = "{$var} = \$this->{$method}();";
            $editLookups[] = "{$var} = \$this->{$method}();";
        }

        $content = $this->renderer->render($template, [
            'namespace' => $definition->controllerNamespace(),
            'controller_class' => $definition->controllerClass(),
            'model_namespace' => $definition->modelNamespace(),
            'model_class' => $definition->modelClass(),
            'validator_namespace' => $definition->validatorNamespace(),
            'validator_class' => $definition->validatorClass(),
            'master_resource' => $definition->masterResource(),
            'detail_resource' => $definition->detailResource(),
            'view_folder' => $definition->viewFolder(),
            'foreign_key' => $definition->foreignKey(),
            'master_primary_key' => $definition->masterPrimaryKey(),
            'detail_primary_key' => $definition->detailPrimaryKey(),
            'master_route_base' => $definition->masterRouteBase(),
            'detail_route_segment' => $definition->detailRouteSegment(),
            'index_permission' => $definition->indexPermission(),
            'create_permission' => $definition->createPermission(),
            'edit_permission' => $definition->editPermission(),
            'delete_permission' => $definition->deletePermission(),
            'create_lookups' => implode("\n        ", $createLookups),
            'edit_lookups' => implode("\n        ", $editLookups),
            'lookup_methods' => implode("\n\n", $lookupMethods),
        ]);

        $this->writeFile($target, $content, $context);
    }

    public function writeIndexView(GeneratorContext $context, MasterDetailDefinition $definition): void
    {
        $dir = $this->nestedViewsPath($context, $definition);
        $target = $dir . DIRECTORY_SEPARATOR . 'index.php';

        if (!$context->shouldForce() && !$definition->detail->overwrite() && is_file($target)) {
            return;
        }

        $template = Paths::core('Generators/MasterDetail/Templates/index.stub.php');

        $headers = [];
        $cells = [];
        $relationLookups = [];

        foreach ($definition->detail->tableColumns() as $column) {
            if ($column->name === $definition->foreignKey()) {
                continue;
            }

            $headers[] = '<th>' . htmlspecialchars($definition->detail->labelFor($column->name), ENT_QUOTES, 'UTF-8') . '</th>';

            if ($definition->detail->hasRelation($column->name) && isset($definition->detail->relationMeta($column->name)['table'])) {
                $relationVar = '$' . $column->name . 'Label';
                $relationMeta = var_export($definition->detail->relationMeta($column->name), true);

                $relationLookups[] = <<<PHP
\$resolver = \\ZMosquita\\Core\\Support\\Container::instance()->get(\\ZMosquita\\Core\\Generators\\Crud\\RelationLabelResolver::class);
{$relationVar} = \$resolver->labelFor(\$row, '{$column->name}', {$relationMeta});
PHP;

                $cells[] = "<td><?= htmlspecialchars((string){$relationVar}, ENT_QUOTES, 'UTF-8') ?></td>";
            } else {
                $cells[] = "<td><?= htmlspecialchars((string)(\$row['{$column->name}'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>";
            }
        }

        $content = $this->renderer->render($template, [
            'master_resource_title' => $this->headline($definition->masterResource()),
            'detail_resource_title' => $this->headline($definition->detailResource()),
            'master_route_base' => $definition->masterRouteBase(),
            'detail_route_segment' => $definition->detailRouteSegment(),
            'detail_primary_key' => $definition->detailPrimaryKey(),
            'thead' => implode("\n        ", $headers),
            'relation_lookups' => implode("\n            ", $relationLookups),
            'tbody_cells' => implode("\n            ", $cells),
            'create_permission' => $definition->createPermission(),
            'edit_permission' => $definition->editPermission(),
            'delete_permission' => $definition->deletePermission(),
        ]);

        $this->writeFile($target, $content, $context);
    }

    public function writeFormView(GeneratorContext $context, MasterDetailDefinition $definition): void
    {
        $dir = $this->nestedViewsPath($context, $definition);
        $target = $dir . DIRECTORY_SEPARATOR . 'form.php';

        if (!$context->shouldForce() && !$definition->detail->overwrite() && is_file($target)) {
            return;
        }

        $template = Paths::core('Generators/MasterDetail/Templates/form.stub.php');

        $fields = [];

        foreach ($definition->detail->formColumns() as $column) {
            if ($column->name === $definition->foreignKey()) {
                continue;
            }

            if ($definition->detail->isHidden($column->name)) {
                $fields[] = $this->hiddenField($column->name);
                continue;
            }

            if ($definition->detail->hasRelation($column->name) && isset($definition->detail->relationMeta($column->name)['table'])) {
                $fields[] = $this->selectField($definition, $column->name);
                continue;
            }

            $fieldType = $definition->detail->fieldType($column->name) ?? $this->htmlInputType($column->type);
            $readonly = $definition->detail->isReadonly($column->name);

            $fields[] = match ($fieldType) {
                'textarea' => $this->textareaField($definition, $column->name, $readonly),
                'checkbox' => $this->checkboxField($definition, $column->name, $readonly),
                default => $this->inputField($definition, $column->name, $fieldType, $readonly),
            };
        }

        $content = $this->renderer->render($template, [
            'detail_resource_title' => $this->headline($definition->detailResource()),
            'foreign_key' => $definition->foreignKey(),
            'fields' => implode("\n\n", $fields),
        ]);

        $this->writeFile($target, $content, $context);
    }

    public function appendRoutes(GeneratorContext $context, MasterDetailDefinition $definition): void
    {
        $target = $this->paths->routesPath($context);

        if (!is_file($target)) {
            $this->writeFile($target, "<?php\n\n", $context);
        }

        $block = $this->routesBlock($definition);
        $existing = file_get_contents($target);

        if ($existing === false) {
            throw new RuntimeException("Unable to read routes file: {$target}");
        }

        if (str_contains($existing, $block)) {
            return;
        }

        if ($context->shouldDryRun()) {
            echo "[DRY-RUN] append routes to {$target}\n";
            return;
        }

        file_put_contents($target, rtrim($existing) . "\n\n" . $block . "\n");
    }

    private function routesBlock(MasterDetailDefinition $definition): string
    {
        $controllerFqcn = '\\' . $definition->controllerNamespace() . '\\' . $definition->controllerClass();
        $masterBase = $definition->masterRouteBase();
        $detailSeg = $definition->detailRouteSegment();

        return <<<PHP
// Master-Detail {$definition->masterResource()} -> {$definition->detailResource()}
\$router->get('{$masterBase}/{masterId}/{$detailSeg}', [{$controllerFqcn}::class, 'index']);
\$router->get('{$masterBase}/{masterId}/{$detailSeg}/create', [{$controllerFqcn}::class, 'create']);
\$router->post('{$masterBase}/{masterId}/{$detailSeg}', [{$controllerFqcn}::class, 'store']);
\$router->get('{$masterBase}/{masterId}/{$detailSeg}/{id}/edit', [{$controllerFqcn}::class, 'edit']);
\$router->post('{$masterBase}/{masterId}/{$detailSeg}/{id}', [{$controllerFqcn}::class, 'update']);
\$router->post('{$masterBase}/{masterId}/{$detailSeg}/{id}/delete', [{$controllerFqcn}::class, 'delete']);
PHP;
    }

    private function buildLookupMethod(string $method, array $relation): string
    {
        $table = (string)$relation['table'];
        $valueColumn = (string)($relation['value_column'] ?? 'id');
        $displayColumn = (string)($relation['display_column'] ?? 'nombre');
        $orderBy = (string)($relation['order_by'] ?? $displayColumn);

        return <<<PHP
private function {$method}(): array
{
    \$db = \\ZMosquita\\Core\\Support\\Container::instance()->get(\\ZMosquita\\Core\\Database\\Connection::class);

    return \$db->fetchAll(
        "SELECT {$valueColumn} AS value, {$displayColumn} AS label
         FROM {$table}
         ORDER BY {$orderBy}"
    );
}
PHP;
    }

    private function nestedControllerPath(GeneratorContext $context, MasterDetailDefinition $definition): string
    {
        if ($context->isCore()) {
            return Paths::core('Http/Controllers/' . $definition->controllerClass() . '.php');
        }

        return Paths::application((string)$context->appCode, 'Controllers/' . $definition->controllerClass() . '.php');
    }

    private function nestedViewsPath(GeneratorContext $context, MasterDetailDefinition $definition): string
    {
        if ($context->isCore()) {
            return Paths::core('Views/' . $definition->viewFolder());
        }

        return Paths::application((string)$context->appCode, 'Views/' . $definition->viewFolder());
    }

    private function writeFile(string $path, string $content, GeneratorContext $context): void
    {
        $dir = dirname($path);

        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException("Unable to create directory: {$dir}");
        }

        if ($context->shouldDryRun()) {
            echo "[DRY-RUN] write {$path}\n";
            return;
        }

        $result = file_put_contents($path, $content);

        if ($result === false) {
            throw new RuntimeException("Unable to write file: {$path}");
        }
    }

    private function inputField(MasterDetailDefinition $definition, string $column, string $type, bool $readonly): string
    {
        $label = htmlspecialchars($definition->detail->labelFor($column), ENT_QUOTES, 'UTF-8');
        $readonlyAttr = $readonly ? ' readonly' : '';

        return <<<PHP
<div class="form-group">
    <label for="{$column}">{$label}</label>
    <input
        type="{$type}"
        name="{$column}"
        id="{$column}"
        class="form-control"
        value="<?= htmlspecialchars((string)(\$item['{$column}'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"{$readonlyAttr}
    >
    <?php foreach ((\$errors['{$column}'] ?? []) as \$error): ?>
        <div class="text-danger"><?= htmlspecialchars((string)\$error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endforeach; ?>
</div>
PHP;
    }

    private function textareaField(MasterDetailDefinition $definition, string $column, bool $readonly): string
    {
        $label = htmlspecialchars($definition->detail->labelFor($column), ENT_QUOTES, 'UTF-8');
        $readonlyAttr = $readonly ? ' readonly' : '';

        return <<<PHP
<div class="form-group">
    <label for="{$column}">{$label}</label>
    <textarea name="{$column}" id="{$column}" class="form-control"{$readonlyAttr}><?= htmlspecialchars((string)(\$item['{$column}'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
    <?php foreach ((\$errors['{$column}'] ?? []) as \$error): ?>
        <div class="text-danger"><?= htmlspecialchars((string)\$error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endforeach; ?>
</div>
PHP;
    }

    private function checkboxField(MasterDetailDefinition $definition, string $column, bool $readonly): string
    {
        $label = htmlspecialchars($definition->detail->labelFor($column), ENT_QUOTES, 'UTF-8');
        $disabledAttr = $readonly ? ' disabled' : '';

        return <<<PHP
<div class="form-group">
    <label>
        <input type="checkbox" name="{$column}" value="1" <?= !empty(\$item['{$column}']) ? 'checked' : '' ?>{$disabledAttr}>
        {$label}
    </label>
    <?php foreach ((\$errors['{$column}'] ?? []) as \$error): ?>
        <div class="text-danger"><?= htmlspecialchars((string)\$error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endforeach; ?>
</div>
PHP;
    }

    private function hiddenField(string $column): string
    {
        return <<<PHP
<input type="hidden" name="{$column}" value="<?= htmlspecialchars((string)(\$item['{$column}'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
PHP;
    }

    private function selectField(MasterDetailDefinition $definition, string $column): string
    {
        $label = htmlspecialchars($definition->detail->labelFor($column), ENT_QUOTES, 'UTF-8');

        return <<<PHP
<div class="form-group">
    <label for="{$column}">{$label}</label>
    <select name="{$column}" id="{$column}" class="form-control">
        <option value="">-- seleccionar --</option>
        <?php foreach ((${$column}Options ?? []) as \$option): ?>
            <option
                value="<?= htmlspecialchars((string)(\$option['value'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                <?= (string)(\$item['{$column}'] ?? '') === (string)(\$option['value'] ?? '') ? 'selected' : '' ?>
            >
                <?= htmlspecialchars((string)(\$option['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php foreach ((\$errors['{$column}'] ?? []) as \$error): ?>
        <div class="text-danger"><?= htmlspecialchars((string)\$error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endforeach; ?>
</div>
PHP;
    }

    private function htmlInputType(string $sqlType): string
    {
        return match (strtolower($sqlType)) {
            'int', 'bigint', 'smallint', 'tinyint', 'decimal', 'float', 'double' => 'number',
            'date' => 'date',
            'datetime', 'timestamp' => 'datetime-local',
            'text', 'mediumtext', 'longtext' => 'textarea',
            default => 'text',
        };
    }

    private function headline(string $value): string
    {
        return ucwords(str_replace('_', ' ', $value));
    }
}