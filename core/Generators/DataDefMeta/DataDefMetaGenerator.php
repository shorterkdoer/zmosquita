<?php

declare(strict_types=1);

namespace ZMosquita\Core\Generators\DataDefMeta;

use ZMosquita\Core\Generators\Shared\ColumnDefinition;
use ZMosquita\Core\Generators\Shared\DefinitionNormalizer;
use ZMosquita\Core\Generators\Shared\GeneratorContext;
use ZMosquita\Core\Support\Paths;

final class DataDefMetaGenerator
{
    public function __construct(
        private DefinitionNormalizer $normalizer
    ) {
    }

    public function generate(GeneratorContext $context): void
    {
        $table = $context->isCore()
            ? $this->normalizer->fromCore($context->resourceName)
            : $this->normalizer->fromApp((string)$context->appCode, $context->resourceName);

        $path = $this->resolvePath($context);

        if (!$context->force && file_exists($path)) {
            throw new \RuntimeException("DataDefMeta file already exists: {$path}. Use --force to overwrite.");
        }

        $content = $this->generateContent($table, $context);

        if ($context->dryRun) {
            echo "--- Dry-run mode: would write to {$path} ---\n";
            echo $content . "\n";
            echo "--- End of dry-run ---\n";
            return;
        }

        $this->ensureDirectoryExists($path);
        file_put_contents($path, $content);
    }

    private function resolvePath(GeneratorContext $context): string
    {
        $filename = $this->normalize($context->resourceName);

        if ($context->isCore()) {
            return Paths::core('datadefmeta' . DIRECTORY_SEPARATOR . $filename);
        }

        return Paths::application(
            (string)$context->appCode,
            'datadefmeta' . DIRECTORY_SEPARATOR . $filename
        );
    }

    private function normalize(string $name): string
    {
        return str_ends_with($name, '.php') ? $name : $name . '.php';
    }

    private function ensureDirectoryExists(string $path): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    private function generateContent(
        \ZMosquita\Core\Generators\Shared\TableDefinition $table,
        GeneratorContext $context
    ): string {
        $labels = $this->generateLabels($table);
        $formFields = $this->generateFormFields($table);
        $tableColumns = $this->generateTableColumns($table);
        $fields = $this->generateFieldMeta($table);
        $generatorMeta = $this->generateGeneratorMeta($table, $context);

        $content = "<?php\n\nreturn [\n";

        // Labels
        $content .= "    'labels' => [\n";
        foreach ($labels as $key => $label) {
            $content .= "        '{$key}' => '{$label}',\n";
        }
        $content .= "    ],\n\n";

        // Form
        $content .= "    'form' => [\n";
        $content .= "        'fields' => [\n";
        foreach ($formFields as $field) {
            $content .= "            '{$field}',\n";
        }
        $content .= "        ],\n";
        $content .= "    ],\n\n";

        // Table
        $content .= "    'table' => [\n";
        $content .= "        'columns' => [\n";
        foreach ($tableColumns as $column) {
            $content .= "            '{$column}',\n";
        }
        $content .= "        ],\n";
        $content .= "    ],\n\n";

        // Fields
        $content .= "    'fields' => [\n";
        foreach ($fields as $fieldName => $meta) {
            $content .= "        '{$fieldName}' => [\n";
            foreach ($meta as $key => $value) {
                if (is_array($value)) {
                    $content .= "            '{$key}' => [" . implode(', ', array_map(fn($v) => "'{$v}'", $value)) . "],\n";
                } elseif (is_bool($value)) {
                    $content .= "            '{$key}' => " . ($value ? 'true' : 'false') . ",\n";
                } else {
                    $content .= "            '{$key}' => '{$value}',\n";
                }
            }
            $content .= "        ],\n";
        }
        $content .= "    ],\n\n";

        // Generator
        $content .= "    'generator' => [\n";
        foreach ($generatorMeta as $key => $value) {
            if (is_bool($value)) {
                $content .= "        '{$key}' => " . ($value ? 'true' : 'false') . ",\n";
            } else {
                $content .= "        '{$key}' => '{$value}',\n";
            }
        }
        $content .= "    ],\n";

        $content .= "];\n";

        return $content;
    }

    /**
     * @return array<string, string>
     */
    private function generateLabels(\ZMosquita\Core\Generators\Shared\TableDefinition $table): array
    {
        $labels = [];
        foreach ($table->columns as $column) {
            if ($this->shouldIncludeInMeta($column)) {
                $labels[$column->name] = $this->generateLabel($column->name);
            }
        }
        return $labels;
    }

    private function generateLabel(string $columnName): string
    {
        // Convertir snake_case a Title Case
        $words = explode('_', $columnName);
        $label = implode(' ', array_map('ucfirst', $words));

        // Reemplazos comunes para español
        $replacements = [
            'Email' => 'Correo electrónico',
            'Password' => 'Contraseña',
            'Username' => 'Nombre de usuario',
            'Phone' => 'Teléfono',
            'Address' => 'Dirección',
            'Cuit' => 'CUIT',
            'Dni' => 'DNI',
        ];

        foreach ($replacements as $from => $to) {
            if (stristr($label, $from) !== false) {
                return $to;
            }
        }

        return $label;
    }

    /**
     * @return array<string>
     */
    private function generateFormFields(\ZMosquita\Core\Generators\Shared\TableDefinition $table): array
    {
        $fields = [];
        foreach ($table->columns as $column) {
            if ($this->shouldIncludeInForm($column)) {
                $fields[] = $column->name;
            }
        }
        return $fields;
    }

    /**
     * @return array<string>
     */
    private function generateTableColumns(\ZMosquita\Core\Generators\Shared\TableDefinition $table): array
    {
        $columns = [];
        foreach ($table->columns as $column) {
            if ($this->shouldIncludeInTable($column)) {
                $columns[] = $column->name;
            }
        }
        return $columns;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function generateFieldMeta(\ZMosquita\Core\Generators\Shared\TableDefinition $table): array
    {
        $fields = [];
        foreach ($table->columns as $column) {
            if ($this->shouldIncludeInMeta($column)) {
                $fields[$column->name] = [
                    'type' => $this->inferFieldType($column),
                    'rules' => $this->inferRules($column),
                ];

                // Agregar relación si es FK
                if ($column->isForeignKey()) {
                    $ref = $column->meta['references'] ?? [];
                    if (isset($ref['table'], $ref['column'])) {
                        $fields[$column->name]['relation'] = [
                            'table' => $ref['table'],
                            'column' => $ref['column'],
                        ];
                    }
                }
            }
        }
        return $fields;
    }

    private function shouldIncludeInMeta(ColumnDefinition $column): bool
    {
        // Excluir timestamps automáticos
        if ($column->isTimestamp()) {
            return false;
        }

        // Incluir todos los demás campos
        return true;
    }

    private function shouldIncludeInForm(ColumnDefinition $column): bool
    {
        // Excluir PK autoincrement
        if ($column->primaryKey && $column->autoIncrement) {
            return false;
        }

        // Excluir timestamps
        if ($column->isTimestamp()) {
            return false;
        }

        return true;
    }

    private function shouldIncludeInTable(ColumnDefinition $column): bool
    {
        // Excluir timestamps
        if ($column->isTimestamp()) {
            return false;
        }

        // Incluir PK para mostrar ID
        if ($column->primaryKey) {
            return true;
        }

        return true;
    }

    private function inferFieldType(ColumnDefinition $column): string
    {
        $type = strtolower($column->type);
        $name = strtolower($column->name);

        // Por nombre del campo
        if (str_contains($name, 'password')) {
            return 'password';
        }
        if (str_contains($name, 'email')) {
            return 'email';
        }
        if (str_contains($name, 'phone') || str_contains($name, 'telefono')) {
            return 'tel';
        }
        if (str_contains($name, 'url') || str_contains($name, 'link')) {
            return 'url';
        }
        if (str_contains($name, 'date') || str_contains($name, 'fecha')) {
            return 'date';
        }
        if (str_contains($name, 'time') || str_contains($name, 'hora')) {
            return 'time';
        }
        if (str_contains($name, 'datetime')) {
            return 'datetime';
        }
        if (str_contains($name, 'observaciones') || str_contains($name, 'descripcion') || str_contains($name, 'description')) {
            return 'textarea';
        }

        // Por tipo de dato
        if (in_array($type, ['text', 'mediumtext', 'longtext'])) {
            return 'textarea';
        }
        if (in_array($type, ['tinyint', 'int', 'bigint', 'decimal', 'float', 'double'])) {
            return 'number';
        }
        if (in_array($type, ['date', 'datetime', 'timestamp', 'time', 'year'])) {
            return str_replace(['timestamp', 'year'], ['datetime', 'date'], $type);
        }
        if ($type === 'boolean' || $type === 'bool') {
            return 'checkbox';
        }
        if ($type === 'enum') {
            return 'select';
        }

        // Default para VARCHAR y otros
        return 'text';
    }

    /**
     * @return array<string>
     */
    private function inferRules(ColumnDefinition $column): array
    {
        $rules = [];
        $type = strtolower($column->type);

        // Regla nullable
        if (!$column->nullable) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        // Reglas por tipo
        if ($type === 'varchar' || $type === 'char') {
            if ($column->length) {
                $rules[] = 'max:' . $column->length;
            } else {
                $rules[] = 'max:255';
            }
        }

        if ($type === 'text') {
            $rules[] = 'max:65535';
        }

        if (in_array($type, ['int', 'tinyint', 'bigint', 'integer'])) {
            $rules[] = 'integer';
        }

        if (in_array($type, ['decimal', 'float', 'double'])) {
            $rules[] = 'numeric';
        }

        if (in_array($type, ['date', 'datetime', 'timestamp'])) {
            $rules[] = 'date';
        }

        if ($type === 'boolean' || $type === 'bool') {
            $rules[] = 'boolean';
        }

        // Reglas por nombre del campo
        $name = strtolower($column->name);
        if (str_contains($name, 'email')) {
            if (!in_array('nullable', $rules)) {
                $rules[] = 'email';
            }
        }

        if (str_contains($name, 'url')) {
            if (!in_array('nullable', $rules)) {
                $rules[] = 'url';
            }
        }

        if (str_contains($name, 'cuit') || str_contains($name, 'dni')) {
            $rules[] = 'max:20';
        }

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    private function generateGeneratorMeta(
        \ZMosquita\Core\Generators\Shared\TableDefinition $table,
        GeneratorContext $context
    ): array {
        $resourceName = $table->resourceName;

        $controllerClass = $this->studly($resourceName) . 'Controller';
        $modelClass = $this->singularStudly($resourceName);

        return [
            'controller' => $controllerClass,
            'model' => $modelClass,
            'overwrite' => false,
        ];
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
