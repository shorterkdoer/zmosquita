<?php

namespace Foundation\Crud;

use League\Plates\Engine;
use PDO;
use Foundation\Core\Request;
use Foundation\Core\CSRF;
use Foundation\Core\Session;

/**
 * Base Controller class with CRUD functionality
 *
 * Provides common controller methods for CRUD operations,
 * including view rendering, redirection, and DataTables integration.
 */
abstract class Controller
{
    protected ?Engine $viewEngine = null;
    protected string $pendingquery = '';
    protected string $pendingcolumns = '';

    public function __construct()
    {
        if (isset($_SESSION['directoriobase'])) {
            $this->viewEngine = new Engine($_SESSION['directoriobase'] . '/views');
        }
    }

    /**
     * Render a view template.
     *
     * @param string $template Template name (without .php extension)
     * @param array $data Data to pass to the template
     * @return void
     */
    protected function view(string $template, array $data = []): void
    {
        if ($this->viewEngine === null) {
            $this->viewEngine = new Engine($_SESSION['directoriobase'] . '/views');
        }
        echo $this->viewEngine->render($template, $data);
        exit;
    }

    /**
     * Redirect to a URL.
     *
     * @param string $url The URL to redirect to
     * @return void
     */
    protected function redirect(string $url): void
    {
        header("Location: " . $url);
        exit;
    }

    /**
     * Get the user's upload folder path.
     *
     * @param int $userId User ID
     * @return string Full path to the user's upload folder
     */
    protected function getUserUploadFolder(int $userId): string
    {
        $baseFolder = $_SESSION['directoriobase'] . '/storage/uploads/';
        $gconfig = require $_SESSION['directoriobase'] . '/config/settings.php';
        $secretword = $gconfig['basellave'];

        $folderName = md5($userId . $secretword);
        $fullPath = $baseFolder . $folderName . DIRECTORY_SEPARATOR;

        if (!file_exists($fullPath)) {
            $oldUmask = umask(0);
            mkdir($fullPath, 0777, true);
            umask($oldUmask);
        }

        return $fullPath;
    }

    /**
     * Get the user's folder relative path.
     *
     * @param int $userId User ID
     * @return string Relative path to the user's folder
     */
    protected static function getUserFolder(int $userId): string
    {
        $baseFolder = '/storage/uploads/';
        $gconfig = require $_SESSION['directoriobase'] . '/config/settings.php';
        $secretword = $gconfig['basellave'];

        $folderName = md5($userId . $secretword);
        return $baseFolder . $folderName . DIRECTORY_SEPARATOR;
    }

    /**
     * Generate JavaScript columns definition for DataTables.
     *
     * @param array $campos Fields configuration
     * @param array $acciones Actions configuration
     * @return string JavaScript columns array
     */
    public static function mkColumns(array $campos, array $acciones): string
    {
        $columns = [];

        foreach ($campos as $key => $campo) {
            if (!$campo['hidden']) {
                $label = addslashes($campo['label']);
                $columns[] = "{ data: '$key', title: '$label' }";
            }
        }

        foreach ($acciones as $key => $accion) {
            $label = addslashes($accion['text']);
            $columns[] = "{ data: '$key', title: '$label', orderable: false, searchable: false }";
        }

        return implode(",\n    ", $columns);
    }

    /**
     * Generate DataTables JavaScript from configuration.
     *
     * @param array $campos Fields configuration
     * @param array $acciones Actions configuration
     * @param string $ajaxurl AJAX data source URL
     * @param string $tableid Table HTML element ID
     * @return string JavaScript code
     */
    public static function buildDataTablesScript(
        array $campos,
        array $acciones,
        string $ajaxurl,
        string $tableid
    ): string {
        $coljs = self::mkColumns($campos, $acciones);

        return <<<JS
$(document).ready(function () {
    $('#$tableid').DataTable({
        ajax: {
            url: '$ajaxurl',
            dataSrc: 'aaData'
        },
        processing: true,
        serverSide: false,
        responsive: true,
        paging: true,
        searching: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        columns: [
            $coljs
        ]
    });
});
JS;
    }

    /**
     * Calculate JavaScript columns with options.
     *
     * @param array $fields Fields configuration
     * @param array $acciones Actions configuration
     * @return array Array of JavaScript column definitions
     */
    public static function calcJSColumns(array $fields, array $acciones): array
    {
        $cols = [];
        foreach ($fields as $key => $field) {
            if (empty($field['hidden'])) {
                $cols[] = "{ data: '$key', name: '$key', searchable: " .
                    (!empty($field['searchable']) ? 'true' : 'false') . ", orderable: " .
                    (!empty($field['orderable']) ? 'true' : 'false') . " }";
            }
        }

        // Add actions column
        foreach ($acciones as $key => $accion) {
            $cols[] = "{ data: '$key', name: '$key' }";
        }

        return $cols;
    }

    /**
     * Get the field name from a dotted notation string.
     *
     * @param string $zstring Dotted notation (e.g., 'table.field')
     * @return string The field name
     */
    public function getCampo(string $zstring): string
    {
        $pos = strpos($zstring, '.');
        if ($pos === false) {
            return $zstring;
        }
        return substr($zstring, $pos + 1);
    }

    /**
     * Execute a custom SQL query.
     *
     * @param string $customquery SQL query
     * @return array Results as associative arrays
     */
    public static function customQuery(string $customquery): array
    {
        $stmt = self::getDB()->query($customquery);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get the last database error message.
     *
     * @return string Error message
     */
    public static function customError(): string
    {
        $localerror = self::getDB()->errorInfo();
        return $localerror[2] ?? 'Unknown database error';
    }

    /**
     * Get a PDO database connection instance.
     *
     * @return PDO
     */
    protected static function getDB(): PDO
    {
        $config = require $_SESSION['directoriobase'] . '/config/settings.php';
        $dsn = $config['db']['dsn'];
        $username = $config['db']['username'];
        $password = $config['db']['password'];
        $options = $config['db']['options'] ?? [];

        return new PDO($dsn, $username, $password, $options);
    }

    /**
     * Render a form from a configuration file.
     *
     * @param string $configfile Path to the configuration file
     * @param int $id Optional ID for edit forms
     * @param array $datos Optional data to populate the form
     * @return void
     */
    protected function makeform(string $configfile, int $id = 0, array $datos = []): void
    {
        if (!file_exists($configfile)) {
            die("Configuration file not found: $configfile");
        }

        $cfgedit = require $configfile;
        $cfg = $cfgedit['config'] ?? [];

        // Populate select options from related tables
        foreach ($cfg['campos'] as $campo => &$atributos) {
            if (($atributos['type'] ?? '') === 'select' && empty($atributos['options'])) {
                if (isset($atributos['tabla_rel']) && !empty($atributos['tabla_rel'])) {
                    $tabla = $atributos['tabla_rel'];
                    $sql = "SELECT id, nombre FROM `$tabla` ORDER BY nombre";

                    $db = self::getDB();
                    $stmt = $db->query($sql);
                    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $atributos['options'] = $options;
                }
            }
        }

        $cfgedit = require $configfile;
        $cfg = $cfgedit['config'] ?? [];
        $cfg['url_action'] .= '/' . $id;
        $campos = $cfgedit['campos'] ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos = $cfgedit['comandos'] ?? [];
        $buttons = $cfgedit['buttons'] ?? [];

        $this->view('cruds/index', [
            'cfg' => $cfg,
            'fields' => $campos,
            'values' => $datos,
            'actions' => $actividades,
            'comandos' => $comandos,
            'buttons' => $buttons,
            'id' => $id,
        ]);
    }

    /**
     * Get the referer path from the HTTP request.
     *
     * @return string|null The referer path or null if not available
     */
    public function getRefererPath(): ?string
    {
        if (empty($_SERVER['HTTP_REFERER'])) {
            return null;
        }

        $url_parts = parse_url($_SERVER['HTTP_REFERER']);
        $path = $url_parts['path'] ?? '/';
        $query = isset($url_parts['query']) ? '?' . $url_parts['query'] : '';

        return $path . $query;
    }

    /**
     * Validate CSRF token for POST requests.
     *
     * Use this method at the beginning of any controller action that handles POST data.
     *
     * @param string $lockTo Optional lock to a specific value (e.g., username)
     * @return void
     * @throws \RuntimeException If token is invalid
     */
    protected function validateCSRF(string $lockTo = ''): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validateOrFail($lockTo);
        }
    }

    /**
     * Check if request is POST and CSRF is valid.
     *
     * @return bool True if POST request with valid CSRF token
     */
    protected function isPostWithValidCSRF(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }
        return CSRF::validate();
    }

    /**
     * Regenerate CSRF token.
     *
     * Call this after login to prevent session fixation.
     *
     * @return void
     */
    protected function regenerateCSRF(): void
    {
        CSRF::regenerate();
    }

    /**
     * Generic API data endpoint for DataTables.
     *
     * Automatically detects entity name from controller class name
     * and loads configuration from config/cruds/{entity}/.
     *
     * @param Request $request The HTTP request
     * @param array $params Route parameters
     * @return void Sends JSON response
     */
    public function apiData(Request $request, array $params = []): void
    {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $nombreClase = get_class($this);
        $entidad = strtolower(str_replace('Controller', '', basename(str_replace('\\', '/', $nombreClase))));

        $configPath = $_SESSION['directoriobase'] . "/config/cruds/$entidad/{$entidad}_index.php";
        if (!file_exists($configPath)) {
            echo json_encode(['error' => 'Configuration file not found']);
            return;
        }

        $cfgedit = require $configPath;
        $cfg = $cfgedit['config'] ?? [];
        $campos = $cfgedit['campos'] ?? [];
        $actions = $cfgedit['actividades'] ?? [];
        $idField = $cfg['field_id'] ?? 'id';

        $modelClass = '\\App\\Models\\' . ucfirst($entidad);
        if (!class_exists($modelClass)) {
            echo json_encode(['error' => 'Model not found']);
            return;
        }

        // DataTables parameters
        $draw = intval($_GET['draw'] ?? 0);
        $start = intval($_GET['start'] ?? 0);
        $length = intval($_GET['length'] ?? 10);
        $searchValue = $_GET['search']['value'] ?? '';

        // Initial query
        $query = $modelClass::query();

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($campos, $searchValue) {
                foreach ($campos as $key => $field) {
                    if (!empty($field['searchable'])) {
                        $q->orWhere($key, 'LIKE', "%$searchValue%");
                    }
                }
            });
        }

        // Filtered total
        $recordsFiltered = $query->count();

        // Pagination
        $rows = $query->offset($start)->limit($length)->get();

        // Unfiltered total
        $total = $modelClass::count();

        // Build output array
        $data = array_map(function ($row) use ($campos, $actions, $idField) {
            $item = [];
            foreach ($campos as $key => $field) {
                if (empty($field['hidden'])) {
                    $item[$key] = $row[$key];
                }
            }

            // Action buttons
            $item['acciones'] = '';
            foreach ($actions as $label => $action) {
                $url = rtrim($action['url'], '/') . '/' . $row[$idField];
                $item['acciones'] .= "<a href='$url' class='{$action['class']} btn-sm me-1'>
                    <i class='{$action['icon']}'></i> {$action['text']}
                </a>";
            }

            return $item;
        }, $rows);

        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }

    /**
     * Generate JavaScript columns string.
     *
     * @param array $campos Fields configuration
     * @return string JavaScript columns definition
     */
    protected function jscolumns(array $campos): string
    {
        $jscolumns = "columns: [";
        foreach ($campos as $campo) {
            $jscolumns .= "{ data: '" . trim($campo['nombre']) . "', title: '" . trim($campo['label']) . "' },";
        }
        $jscolumns = rtrim($jscolumns, ',') . "],";

        return $jscolumns;
    }
}
