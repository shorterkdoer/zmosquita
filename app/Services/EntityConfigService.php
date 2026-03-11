<?php
namespace App\Services;

use Foundation\Core\Session;

/**
 * EntityConfigService - Manages consolidated entity configurations
 *
 * This service replaces the multiple CRUD config files with a unified
 * configuration system that supports inheritance and reuse.
 */
class EntityConfigService
{
    protected string $configPath;
    protected array $baseConfig = [];
    protected array $entityConfigs = [];

    public function __construct()
    {
        $this->configPath = $_SESSION['directoriobase'] . '/config/entities/';
        $this->loadBaseConfig();
    }

    /**
     * Load the base configuration with shared defaults
     */
    protected function loadBaseConfig(): void
    {
        $baseFile = $this->configPath . 'base.php';
        if (file_exists($baseFile)) {
            $this->baseConfig = require $baseFile;
        }
    }

    /**
     * Get entity configuration by entity name
     *
     * @param string $entity Entity name (e.g., 'ciudad', 'provincia', 'user')
     * @param string $view View type: 'index', 'create', 'edit', 'delete', or 'all'
     * @return array Configuration array
     */
    public function get(string $entity, string $view = 'index'): array
    {
        $cacheKey = "{$entity}_{$view}";

        if (isset($this->entityConfigs[$cacheKey])) {
            return $this->entityConfigs[$cacheKey];
        }

        $entityFile = $this->configPath . "{$entity}.php";

        if (!file_exists($entityFile)) {
            // Fall back to old config structure if new config doesn't exist
            return $this->getLegacyConfig($entity, $view);
        }

        $entityConfig = require $entityFile;

        if ($view === 'all') {
            $this->entityConfigs[$cacheKey] = $entityConfig;
        } else {
            $this->entityConfigs[$cacheKey] = $this->mergeWithBase($entityConfig, $view);
        }

        return $this->entityConfigs[$cacheKey];
    }

    /**
     * Get configuration for index view
     */
    public function getIndex(string $entity): array
    {
        return $this->get($entity, 'index');
    }

    /**
     * Get configuration for create view
     */
    public function getCreate(string $entity): array
    {
        return $this->get($entity, 'create');
    }

    /**
     * Get configuration for edit view
     */
    public function getEdit(string $entity): array
    {
        return $this->get($entity, 'edit');
    }

    /**
     * Get configuration for delete view
     */
    public function getDelete(string $entity): array
    {
        return $this->get($entity, 'delete');
    }

    /**
     * Merge entity configuration with base configuration
     */
    protected function mergeWithBase(array $entityConfig, string $view): array
    {
        $config = $entityConfig[$view] ?? [];

        // Merge style from base
        if (isset($this->baseConfig['style'])) {
            $config = array_merge($this->baseConfig['style'], $config);
        }

        // Ensure entity-level metadata is included
        $config['entity'] = $entityConfig['entity'] ?? null;
        $config['table'] = $entityConfig['table'] ?? null;
        $config['route_prefix'] = $entityConfig['route_prefix'] ?? null;
        $config['title'] = $config['title'] ?? $entityConfig['title'] ?? '';
        $config['subtitle'] = $config['subtitle'] ?? $entityConfig['subtitle'] ?? '';
        $config['field_id'] = $config['field_id'] ?? $entityConfig['field_id'] ?? 'id';

        return $config;
    }

    /**
     * Get configuration from legacy config/cruds/ structure
     * This provides backward compatibility during migration
     */
    protected function getLegacyConfig(string $entity, string $view): array
    {
        $legacyPath = $_SESSION['directoriobase'] . '/config/cruds/' . $entity . '/';

        $viewMap = [
            'index' => ['_index.php', 'index.php'],
            'create' => ['_create.php', 'create.php'],
            'edit' => ['_edit.php', 'edit.php'],
            'delete' => ['_delete.php', 'delete.php', '_borrar.php', 'borrar.php'],
            'view' => ['_view.php', 'view.php', '_vista.php', 'vista.php'],
        ];

        $filesToTry = $viewMap[$view] ?? ["_{$view}.php", "{$view}.php"];

        foreach ($filesToTry as $file) {
            $fullPath = $legacyPath . $entity . $file;
            if (file_exists($fullPath)) {
                $config = require $fullPath;
                // Merge with base style if needed
                if (isset($this->baseConfig['style']) && isset($config['config'])) {
                    $config['config'] = array_merge($this->baseConfig['style'], $config['config']);
                }
                return $config;
            }
        }

        // Return minimal default config if nothing found
        return $this->getDefaultConfig($entity, $view);
    }

    /**
     * Get default configuration when no config file exists
     */
    protected function getDefaultConfig(string $entity, string $view): array
    {
        $defaults = [
            'config' => $this->baseConfig['style'] ?? [],
            'campos' => [],
            'comandos' => [],
            'actividades' => [],
            'buttons' => [],
        ];

        // Set appropriate defaults based on view type
        if ($view === 'index') {
            $defaults['config']['tipo'] = 'table';
            $defaults['config']['field_id'] = 'id';
        } elseif ($view === 'create' || $view === 'edit') {
            $defaults['config']['tipo'] = 'form';
            $defaults['config']['method'] = 'POST';
        }

        return $defaults;
    }

    /**
     * Get field configuration for an entity
     */
    public function getFields(string $entity, string $view = 'index'): array
    {
        $config = $this->get($entity, $view);
        return $config['campos'] ?? [];
    }

    /**
     * Get activities configuration for an entity
     */
    public function getActivities(string $entity): array
    {
        $config = $this->get($entity, 'index');
        return $config['actividades'] ?? [];
    }

    /**
     * Get commands configuration for an entity
     */
    public function getCommands(string $entity, string $view = 'index'): array
    {
        $config = $this->get($entity, $view);
        return $config['comandos'] ?? [];
    }

    /**
     * Get buttons configuration for an entity
     */
    public function getButtons(string $entity, string $view = 'index'): array
    {
        $config = $this->get($entity, $view);
        return $config['buttons'] ?? [];
    }

    /**
     * Get all available entities
     */
    public function getAvailableEntities(): array
    {
        $entities = [];

        // Get entities from new config/entities/
        if (is_dir($this->configPath)) {
            $files = glob($this->configPath . '*.php');
            foreach ($files as $file) {
                $basename = basename($file, '.php');
                if ($basename !== 'base') {
                    $entities[] = $basename;
                }
            }
        }

        // Also scan legacy config/cruds/ for entities not yet migrated
        $legacyPath = $_SESSION['directoriobase'] . '/config/cruds/';
        if (is_dir($legacyPath)) {
            $dirs = glob($legacyPath . '*', GLOB_ONLYDIR);
            foreach ($dirs as $dir) {
                $entityName = basename($dir);
                if ($entityName !== 'defaults' && $entityName !== 'config' &&
                    $entityName !== 'document_templates' && !in_array($entityName, $entities)) {
                    $entities[] = $entityName;
                }
            }
        }

        return array_unique($entities);
    }

    /**
     * Check if an entity exists
     */
    public function entityExists(string $entity): bool
    {
        $newConfig = $this->configPath . "{$entity}.php";
        $legacyDir = $_SESSION['directoriobase'] . '/config/cruds/' . $entity . '/';

        return file_exists($newConfig) || is_dir($legacyDir);
    }
}
