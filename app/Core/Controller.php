<?php

namespace App\Core;

use League\Plates\Engine;
use PDO;
use Foundation\Core\Request;
use Foundation\Core\CSRF;
use Foundation\Core\Response;
use App\Core\Helpers\Sanitizer;

class Controller
{
    protected Engine $viewEngine;
    protected string $pendingquery = '';
    protected string $pendingcolumns = '';


    public function __construct()
    {
        $this->viewEngine = new Engine($_SESSION['directoriobase']. '/views');
        //$this->viewEngine = new Engine($_SESSION['directoriobase'].'/templates');
    }

    protected function view(string $template, array $data = []): void
    {
        echo $this->viewEngine->render($template, $data);
        exit;
    }

    protected function redirect(string $url): void
    {
        //echo "Location: " . $_SESSION['directoriobase'] . $url;
        //$config = require $_SESSION['base_url'].'/config/settings.php';
        //header("Location: " . $_SESSION['base_url'] . $url);
        header("Location: " . $url);
        exit;
    }

   protected function getUserUploadFolder(int $userId): string
    {
        // Define la carpeta base para uploads. Se recomienda que "storage" esté fuera del webroot.
        $baseFolder = $_SESSION['directoriobase'].'/storage/uploads/';
        // Genera un nombre de carpeta utilizando un hash (con una "sal" secreta).

        $gconfig = require $_SESSION['directoriobase'].'/config/settings.php';
        $secretword = $gconfig['basellave'];

        $folderName = md5($userId . $secretword);
        $fullPath = $baseFolder . $folderName . DIRECTORY_SEPARATOR;
        //echo $fullPath;
        
        if (!file_exists($fullPath)) {
            //echo "Creando carpeta: $fullPath\n";
            $oldUmask = umask(0); // Desactiva la umask momentáneamente
            mkdir($fullPath, 0777, true); // true permite crear directorios recursivos
            umask($oldUmask); // Restablece la umask original
        }else{
        }
        return $fullPath;
    }
    protected static function getUserFolder(int $userId): string
    {
        // Define la carpeta base para uploads. Se recomienda que "storage" esté fuera del webroot.
        $baseFolder = '/storage/uploads/';
        // Genera un nombre de carpeta utilizando un hash (con una "sal" secreta).

        $gconfig = require $_SESSION['directoriobase'].'/config/settings.php';
        $secretword = $gconfig['basellave'];

        $folderName = md5($userId . $secretword);
        $fullPath = $baseFolder . $folderName . DIRECTORY_SEPARATOR;
        //echo $fullPath;
        
        return $fullPath;
    }
    protected function jscolumns(array $campos): string
    {
    
    /*
        columns: [
      { data: 'id', title: 'ID' },
      { data: 'user_id', title: 'Usuario' },
      { data: 'comprobante', title: 'Comprobante' },
      { data: 'fecha', title: 'Fecha' },
      { data: 'observaciones', title: 'Observaciones' },
      { data: 'tramite_id', title: 'Trámite' }
    ]
    */
    
        $jscolumns = "columns: [";
        foreach ($campos as $campo) {
            $jscolumns .= "{ data: '" . trim($campo['nombre']) . "', title: '" . trim($campo['label']) . "' },";
        }            
        $jscolumns = rtrim($jscolumns, ',') . "],";

        return $jscolumns;
    }

public function apiData(Request $request, array $params = []): void
{
    header('Content-Type: application/json');
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $nombreClase = get_class($this);  // e.g. App\Controllers\DatosPersonalesController
    $entidad = strtolower(str_replace('Controller', '', basename(str_replace('\\', '/', $nombreClase)))); // datospersonales

    $configPath = $_SESSION['directoriobase'] . "/config/cruds/$entidad/{$entidad}_index.php";
    if (!file_exists($configPath)) {
        echo json_encode(['error' => 'Archivo de configuración no encontrado']);
        return;
    }

    $cfgedit     = require $configPath;
    $cfg         = $cfgedit['config'] ?? [];
    $campos      = $cfgedit['campos'] ?? [];
    $actions     = $cfgedit['actividades'] ?? [];
    $idField     = $cfg['field_id'] ?? 'id';

    $modelClass = '\\App\\Models\\' . ucfirst($entidad); // e.g. \App\Models\DatosPersonales
    if (!class_exists($modelClass)) {
        echo json_encode(['error' => 'Modelo no encontrado']);
        return;
    }

    // Parámetros de DataTables
    $draw = intval($_GET['draw'] ?? 0);
    $start = intval($_GET['start'] ?? 0);
    $length = intval($_GET['length'] ?? 10);
    $searchValue = $_GET['search']['value'] ?? '';

    // Query inicial
    $query = $modelClass::query();

    // Filtro por búsqueda
    if (!empty($searchValue)) {
        $query->where(function ($q) use ($campos, $searchValue) {
            foreach ($campos as $key => $field) {
                if (!empty($field['searchable'])) {
                    $q->orWhere($key, 'LIKE', "%$searchValue%");
                }
            }
        });
    }

    // Total filtrado
    $recordsFiltered = $query->count();

    // Paginado
    $rows = $query->offset($start)->limit($length)->get();

    // Total sin filtrar
    $total = $modelClass::count();

    // Generar array de salida
    $data = array_map(function ($row) use ($campos, $actions, $idField) {
        $item = [];
        foreach ($campos as $key => $field) {
            if (empty($field['hidden'])) {
                $item[$key] = $row[$key];
            }
        }

        // Botones de acción
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

    // *** Aquí NO usamos json_encode, devolvemos el JS tal cual ***
    return implode(",\n    ", $columns);
}

public function getCampo(string $zstring) {
    $pos = strpos($zstring, '.');
    if ($pos === false) {
        return $zstring; // No tiene punto, devuelve el string completo
    }
    return substr($zstring, $pos + 1);
}
/*{
    $columns = [];
    foreach ($campos as $key => $campo) {
        if (empty($campo['hidden'])) {
            $label = addslashes($campo['label']);
            $columns[] = '{ data: "'. $key .'"' . ', title:'. '"' .$label . '"' .' }';
        }
    }
    
    // Acciones como botones (si hay)
    foreach ($acciones as $key => $accion) {
        $label = addslashes($accion['text']);
        $columns[] = '{ data: '.'"'. $key .'"'.', title: '.'"'. $label . '"'.' }';
    }
        
    //return json_encode(implode(",\n", $columns));
    return json_encode(implode(", ", $columns));
}*/

public static function buildDataTablesScript(array $campos, array $acciones, string $ajaxurl, string $tableid): string
{
    $columns = [];

    // Columnas visibles
    foreach ($campos as $key => $campo) {
        if (empty($campo['hidden'])) {
            $label = addslashes($campo['label']);
            $columns[] = "{ data: '$key', title: '$label' }";
        }
    }

    // Acciones como botones (si hay)
    foreach ($acciones as $key => $accion) {
        $columns[] = "{ data: '$key', title: '$key', orderable: false, searchable: false }";
    }

    $coljs = self::mkColumns($campos, $acciones);
    //implode(",\n", $columns);

    return <<<JS
$(document).ready(function () {
    $('#$tableid').DataTable({
        ajax: {
            url: '$ajaxurl',
            dataSrc: 'aaData'    // <-- aquí
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
    // Agregar columna de acciones
    foreach ($acciones as $key => $accion) {
        {
            $cols[] = "{ data: '$key', name: '$key' }";
        }
    }

    //$cols[] = "{ data: 'acciones', orderable: false, searchable: false }";
    return $cols;
}
     public static function CustomQry(string $customquery): array

    {
        $stmt = self::getDB()->query($customquery);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

        public static function CustomError(): string

    {
        $localerror = self::getDB()->errorInfo();
        return $localerror[2] ?? 'Error desconocido en la consulta SQL';

    }

    /**
     * Get a PDO database connection instance.
     */
    protected static function getDB(): PDO
    {
        // Adjust the path to your settings file as needed
        $config = require $_SESSION['directoriobase'].'/config/settings.php';
        $dsn = $config['db']['dsn'];
        $username = $config['db']['username'];
        $password = $config['db']['password'];
        $options = $config['db']['options'] ?? [];

        return new PDO($dsn, $username, $password, $options);
    }
    protected static function makeform(string $configfile, int $id = 0, array $datos = []) 
    {

    if (!file_exists($configfile)) {
            die("Archivo de configuración no encontrado: $configfile");
    }
    $cfgedit     = require $configfile; 
    $cfg         = $cfgedit['config']    ?? [];

    foreach ($cfg['campos'] as $campo => &$atributos) {
        if (($atributos['type'] ?? '') === 'select' &&
            empty($atributos['options']))
            if (isset($atributos['tabla_rel'])) {
                if (!empty($atributos['tabla_rel'])) 
                    {
                    $tabla = $atributos['tabla_rel'];
                    // Se asume que la columna de visualización es 'nombre', pero se puede mejorar
                    $sql = "SELECT id, nombre FROM `$tabla` ORDER BY nombre";

                    $db = self::getDB(); // Asegurate de tener este método en el controller base
                    $stmt = $db->query($sql);
                    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $atributos['options'] = $options;
                    }
                }   
            } 
        $cfgedit     = require $configfile; 
        $cfg         = $cfgedit['config']    ?? [];
        $cfg['url_action'] .= '/' . $id; // <— se agrega el id a la url
        $campos      = $cfgedit['campos']    ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos    = $cfgedit['comandos']  ?? [];
        $buttons     = $cfgedit['buttons']   ?? [];
        // recorrer los campos y para los que tengan 'type' => 'select', armar el query y agregar las opciones

//        $campos['provincia_id']['options'] = $provincias;
//        $campos['ciudad_id']['options']    = $ciudades;

        self::view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $campos,     // <— coherente con index/create
            'values'   => $datos,       // array simple con claves=>valores
            'actions'  => $actividades,
            'comandos' => $comandos,
            'buttons'  => $buttons,
            'id'      => $id,
        ]);

    }
    public function getRefererPath()
    {
        if (empty($_SERVER['HTTP_REFERER'])) {
            return null;
        }

        $url_parts = parse_url($_SERVER['HTTP_REFERER']);

    // Si no tiene path, asumimos raíz
        $path = $url_parts['path'] ?? '/';
        $query = isset($url_parts['query']) ? '?' . $url_parts['query'] : '';

        return $path . $query;
    }

    /**
     * Validate CSRF token for POST requests
     *
     * Use this method at the beginning of any controller action that handles POST data
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
     * Check if request is POST and CSRF is valid
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
     * Regenerate CSRF token
     *
     * Call this after login to prevent session fixation
     */
    protected function regenerateCSRF(): void
    {
        CSRF::regenerate();
    }

}
