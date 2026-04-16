<?php

declare(strict_types=1);

namespace ZMosquita\Core\Generators\Crud;

use RuntimeException;
use ZMosquita\Core\Generators\Shared\GeneratorContext;
use ZMosquita\Core\Generators\Shared\GeneratorPathResolver;
use ZMosquita\Core\Generators\Shared\StubRenderer;
use ZMosquita\Core\Support\Paths;

final class CrudScaffoldWriter
{
    public function __construct(
        private GeneratorPathResolver $paths,
        private StubRenderer $renderer
    ) {
    }

    public function writeController(GeneratorContext $context, CrudDefinition $definition): void
    {
        $this->paths->ensureDirectories($context);

        $target = $this->paths->controllerPath($context);
        if (!$context->shouldForce() && !$definition->overwrite() && is_file($target)) {
            return;
        }

        $template = Paths::core('Generators/Crud/Templates/controller.stub.php');

        $lookupMethods = [];
        $createLookups = [];
        $editLookups = [];

        foreach ($definition->formColumns() as $column) {
            if (!$definition->hasRelation($column->name)) {
                continue;
            }

            $relation = $definition->relationMeta($column->name);
            if (!isset($relation['table'])) {
                continue;
            }

            $method = $definition->lookupMethodName($column->name);
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
            'resource' => $definition->resourceName(),
            'route_base' => $definition->routeBase(),
            'view_folder' => $definition->viewFolder(),
            'primary_key' => $definition->primaryKey(),
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

    public function writeModel(GeneratorContext $context, CrudDefinition $definition): void
    {
        $this->paths->ensureDirectories($context);

        $target = $this->paths->modelPath($context);
        if (!$context->shouldForce() && !$definition->overwrite() && is_file($target)) {
            return;
        }

        $template = Paths::core('Generators/Crud/Templates/model.stub.php');

        $fillable = array_map(
            static fn ($column) => "'" . $column->name . "'",
            array_filter(
                $definition->formColumns(),
                static fn ($column) => $column->name !== 'tenant_id'
            )
        );

        $content = $this->renderer->render($template, [
            'namespace' => $definition->modelNamespace(),
            'model_class' => $definition->modelClass(),
            'table_name' => $definition->tableName(),
            'primary_key' => $definition->primaryKey(),
            'fillable' => implode(', ', $fillable),
            'has_tenant_column' => $definition->hasTenantColumn() ? 'true' : 'false',
        ]);

        $this->writeFile($target, $content, $context);
    }

    public function writeValidator(GeneratorContext $context, CrudDefinition $definition): void
    {
        $this->paths->ensureDirectories($context);

        $target = $this->paths->validatorPath($context);
        if (!$context->shouldForce() && !$definition->overwrite() && is_file($target)) {
            return;
        }

        $template = Paths::core('Generators/Crud/Templates/validator.stub.php');

        $rules = [];
        $labels = [];

        foreach ($definition->formColumns() as $column) {
            if ($column->name === 'tenant_id') {
                continue;
            }

            $fieldRules = $definition->rulesFor($column->name);
            $rules[] = "                '{$column->name}' => [" . implode(', ', array_map(
                static fn (string $rule): string => "'" . $rule . "'",
                $fieldRules
            )) . "],";

            $labels[] = "                '{$column->name}' => '" . addslashes($definition->labelFor($column->name)) . "',";
        }

        $content = $this->renderer->render($template, [
            'namespace' => $definition->validatorNamespace(),
            'validator_class' => $definition->validatorClass(),
            'rules' => implode("\n", $rules),
            'labels' => implode("\n", $labels),
        ]);

        $this->writeFile($target, $content, $context);
    }

    public function writeIndexView(GeneratorContext $context, CrudDefinition $definition): void
    {
        $this->paths->ensureDirectories($context);

        $target = $this->paths->viewsPath($context) . DIRECTORY_SEPARATOR . 'index.php';
        if (!$context->shouldForce() && !$definition->overwrite() && is_file($target)) {
            return;
        }

        $template = Paths::core('Generators/Crud/Templates/index.stub.php');

        $headers = [];
        $cells = [];
        $relationLookups = [];

        foreach ($definition->tableColumns() as $column) {
            $headers[] = '<th>' . htmlspecialchars($definition->labelFor($column->name), ENT_QUOTES, 'UTF-8') . '</th>';

            if ($definition->hasRelation($column->name) && isset($definition->relationMeta($column->name)['table'])) {
                $relationVar = '$' . $column->name . 'Label';
                $relationMeta = var_export($definition->relationMeta($column->name), true);

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
            'resource_title' => $this->headline($definition->resourceName()),
            'route_base' => $definition->routeBase(),
            'thead' => implode("\n        ", $headers),
            'relation_lookups' => implode("\n            ", $relationLookups),
            'tbody_cells' => implode("\n            ", $cells),
            'create_permission' => $definition->createPermission(),
            'edit_permission' => $definition->editPermission(),
            'delete_permission' => $definition->deletePermission(),
            'primary_key' => $definition->primaryKey(),
        ]);

        $this->writeFile($target, $content, $context);
    }

    public function writeFormView(GeneratorContext $context, CrudDefinition $definition): void
    {
        $this->paths->ensureDirectories($context);

        $target = $this->paths->viewsPath($context) . DIRECTORY_SEPARATOR . 'form.php';
        if (!$context->shouldForce() && !$definition->overwrite() && is_file($target)) {
            return;
        }

        $template = Paths::core('Generators/Crud/Templates/form.stub.php');

        $fields = [];

        foreach ($definition->formColumns() as $column) {
            if ($column->name === 'tenant_id') {
                continue;
            }

            if ($definition->isHidden($column->name)) {
                $fields[] = $this->hiddenField($column->name);
                continue;
            }

            if ($definition->hasRelation($column->name) && isset($definition->relationMeta($column->name)['table'])) {
                $fields[] = $this->selectField($definition, $column->name);
                continue;
            }

            $fieldType = $definition->fieldType($column->name) ?? $this->htmlInputType($column->type);
            $readonly = $definition->isReadonly($column->name);

            $fields[] = match ($fieldType) {
                'textarea' => $this->textareaField($definition, $column->name, $readonly),
                'checkbox' => $this->checkboxField($definition, $column->name, $readonly),
                default => $this->inputField($definition, $column->name, $fieldType, $readonly),
            };
        }

        $content = $this->renderer->render($template, [
            'resource_title' => $this->headline($definition->resourceName()),
            'fields' => implode("\n\n", $fields),
        ]);

        $this->writeFile($target, $content, $context);
    }

    public function appendRoutes(GeneratorContext $context, CrudDefinition $definition): void
    {
        $target = $this->paths->routesPath($context);

        if (!is_file($target)) {
            $this->writeFile($target, "<?php\n\n", $context);
        }

        $routes = $this->routesBlock($definition);
        $existing = file_get_contents($target);

        if ($existing === false) {
            throw new RuntimeException("Unable to read routes file: {$target}");
        }

        if (str_contains($existing, $routes)) {
            return;
        }

        if ($context->shouldDryRun()) {
            echo "[DRY-RUN] append routes to {$target}\n";
            return;
        }

        file_put_contents($target, rtrim($existing) . "\n\n" . $routes . "\n");
    }

    private function routesBlock(CrudDefinition $definition): string
    {
        $controllerFqcn = '\\' . $definition->controllerNamespace() . '\\' . $definition->controllerClass();
        $routeBase = $definition->routeBase();
        $resource = $definition->resourceName();

        return <<<PHP
// CRUD {$resource}
\$router->get('{$routeBase}', [{$controllerFqcn}::class, 'index']);
\$router->get('{$routeBase}/create', [{$controllerFqcn}::class, 'create']);
\$router->post('{$routeBase}', [{$controllerFqcn}::class, 'store']);
\$router->get('{$routeBase}/{id}/edit', [{$controllerFqcn}::class, 'edit']);
\$router->post('{$routeBase}/{id}', [{$controllerFqcn}::class, 'update']);
\$router->post('{$routeBase}/{id}/delete', [{$controllerFqcn}::class, 'delete']);
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

    private function inputField(CrudDefinition $definition, string $column, string $type, bool $readonly): string
    {
        $label = htmlspecialchars($definition->labelFor($column), ENT_QUOTES, 'UTF-8');
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

    private function textareaField(CrudDefinition $definition, string $column, bool $readonly): string
    {
        $label = htmlspecialchars($definition->labelFor($column), ENT_QUOTES, 'UTF-8');
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

    private function checkboxField(CrudDefinition $definition, string $column, bool $readonly): string
    {
        $label = htmlspecialchars($definition->labelFor($column), ENT_QUOTES, 'UTF-8');
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

    private function selectField(CrudDefinition $definition, string $column): string
    {
        $label = htmlspecialchars($definition->labelFor($column), ENT_QUOTES, 'UTF-8');

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