<?php

namespace App\Controllers;


use App\Core\Controller;
use Foundation\Core\Request;
use Foundation\Core\Session;
use App\Models\Mailslog;
use App\Support\Sanitizer;
use PDO;

class MailsLogController extends Controller
{
    public function mainview(): void
    {
        /* revisar los paths */
        $this->makegrid('cruds/mailslog/index.php');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];
    $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/mailslog/index.php';
    $cfg         = $cfgedit['config']    ?? [];
    $id_field = $cfgedit['config']['field_id'];
    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    //$filter = $cfgedit['QrySpec']['filter'] ?? '';
    $filter = "(user_id = " . $_SESSION['user']['id'] . ")";
    $order = $cfgedit['QrySpec']['order'] ?? [];
    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';
    $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, $id_field);
    $this->pendingquery = $query; // Guardamos la consulta pendiente para usarla en el script JS
    $this->pendingcolumns = json_encode($campos); // Guardamos los campos pendientes para usarlos en el script JS
    // $this->pendingcolumns = $campos;
    $comandos = $cfgedit['comandos'] ?? [];
    $buttons = $cfgedit['buttons'] ?? [];
        // Ejecuta la consulta y obtiene los datos
    $datos = Mailslog::CustomQry($query);
    $zcolumns =  Self::mkcolumns($campos, $actividades);
    $zcolumns =   trim(stripslashes($zcolumns), '"');
    $this->pendingcolumns = $zcolumns; // Guardamos las columnas pendientes para usarlas en el script JS
    $this->view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $campos,     // <— coherente con index/create
            'style'    => $style,
            'values'   => $datos,       // array simple con claves=>valores
            'actions'  => $actividades,
            'comandos' => $comandos,
            'buttons'  => $buttons,
            'divname' => $cfg['divname'],
            'id'      => 'id',
            'link_id' => 'user_id',
            'scriptjs_data' => $this->pendingquery,
            'scriptjs_columns' => $this->pendingcolumns,
            'zcolumns' => $zcolumns,
            'url_data' => $_SESSION['base_url']. $cfg['url_data'],
            'user_id' => $_SESSION['user']['id'],
        ]);



    }

    public function create(): void
    {
        $id = $params[0] ?? null; // Get the ID from the URL parameters
        if (!$id) {
            Session::flash('error', 'ID de comprobante no especificado.');
            $this->redirect('/mailslog'); // Redirect to the main view if no ID is provided
            return;
        }
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];
        $cfgcreate = require $_SESSION['directoriobase'] . '/config/cruds/mailslog/create.php';
        $cfg       = $cfgcreate['config']      ?? [];

        $cfg['url_action'] .= '/' . $id; // <— se agrega el id a la url

        $campos    = $cfgcreate['campos']      ?? [];
        $actividades = $cfgcreate['actividades'] ?? [];
        $comandos    = $cfgcreate['comandos']  ?? [];
        $buttons     = $cfgcreate['buttons']   ?? [];

        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'style'    => $style,
            'fields'   => $campos,
            'values'   => [],
            'actions'  => $actividades,
            'comandos' => $comandos,
            'buttons'  => $buttons,
            'id'       => $id,  // Add the ID here
            'user_id' => $_SESSION['user']['id'], // Add user ID for file upload
        ]);

    }

    public function store($request): void
    {

       $id = $params[0] ?? null; // Get the ID from the URL parameters
            if (!$id) {
                Session::flash('error', 'Algo salió mal!');
                $this->redirect('/miscomprobantes');
                return;
            }

            //eliminar espacios en blanco del nombre del archivo
            if (isset($_FILES['comprobante']['name'])) {
                $_FILES['comprobante']['name'] = preg_replace('/\s+/', '_', $_FILES['comprobante']['name']);
            }
            $zzdata = [
                'user_id' => $id,
                'comprobante' => $_FILES['comprobante']['name'] ?? null, // Get the file name from the uploaded fil e
                'fecha' => $_POST['fecha'],
                'observaciones' => Sanitizer::text($_POST['observaciones'])
            ];

            //$data = $_POST;
            Mailslog::create($zzdata);


            // Handle file upload
            if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['comprobante'];
                $fileName = $file['name']; // Unique filename

                $uploadDir = $this->getUserUploadFolder($id);
                // Get upload directory
                //$uploadDir = $_SESSION['directoriobase'] . '/storage/uploads/' . md5($_SESSION['user_id'] . require($_SESSION['directoriobase'].'/config/settings.php')['basellave']) . '/';

                // Create directory if it doesn't exist
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
                    $data['comprobante'] = $fileName;
                } else {
                    throw new \Exception('Error al subir el archivo');
                }
            } else {
                throw new \Exception('No se ha seleccionado ningún archivo');
            }

            // Create record in database

            Session::flash('success', 'Comprobante guardado exitosamente.');
            $this->redirect('/mailslog');

    }

    public function edit($request, array $params): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
        }

        $id = (int)($params[0] ?? 0);

        if ($user['role'] == 'user' && $id != $user['id']) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/dashboard');
        }

        // Pasar todas las variables a la vista

        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/mailslog/edit.php';
        $cfg         = $cfgedit['config']    ?? [];
        $cfg['url_action'] .= '/' . $id; // <— se agrega el id a la url
        $campos      = $cfgedit['campos']    ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos    = $cfgedit['comandos']  ?? [];
        $buttons     = $cfgedit['buttons']   ?? [];

        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $campos,     // <— coherente con index/create
            'values'   => [],       // array simple con claves=>valores
            'actions'  => $actividades,
            'comandos' => $comandos,
            'buttons'  => $buttons,
            'id'      => $id,
            'style'    => $style,
            'user_id' => $id
        ]);
    }

    public function update($request, array $params): void
    {
            $id = $params[0] ?? null;
        if (!$id) {
            Session::flash('error', 'ID de comprobante no especificado.');
            $this->redirect('/miscomprobantes');
            return;
        }

        $nombre = trim($request->input('nombre'));
        if (empty($nombre)) {
            Session::flash('error', 'El nombre de la ciudad es obligatorio.');
            $this->redirect('/comprobantespago/edit/' . $id);
            return;
        }

        if (!Mailslog::find($id)) {
            Session::flash('error', 'Comprobante no encontrado.');
            $this->redirect('/miscomprobantes');
            return;
        }

        Mailslog::update($id, ['nombre' => $nombre]);
        Session::flash('success', 'Comprobante actualizada.');
        $this->redirect('/miscomprobantes');

    }


    public function ver($request, array $params): void
    {
    }

    public function borrar($request, array $params): void
    {
            $id = $params[0] ?? null;
        if (!$id) {
            Session::flash('error', 'ID de comprobante no especificado.');
            $this->redirect('/miscomprobantes');
            return;
        }

         $comprob = Mailslog::find($id);
        if (!$comprob) {
            Session::flash('error', 'Comprobante no encontrado.');
            $this->redirect('/miscomprobantes');
            return;
        }

        Mailslog::delete($id);
        Session::flash('success', 'Comprobante eliminado.');
        $this->redirect('/miscomprobantes');

    }
    // para destinos futuros
    public function grid(): void
    {
    }

    // Helper methods moved inside class
    public static function mkColumns(array $campos, array $acciones): string
    {
        $columns = [];

        foreach ($campos as $key => $campo) {
            if (empty($campo['hidden'])) {
                $label = addslashes($campo['label']);
                $columns[] = "{ data: '$key', title: '$label' }";
            }
        }

        foreach ($acciones as $key => $accion) {
            $label = addslashes($accion['text']);
            $columns[] = "{ data: '$key', title: '$label', orderable: false, searchable: false }";
        }

        return implode(",
        ", $columns);
    }

    public static function buildDataTablesScript(array $campos, array $acciones, string $ajaxurl, string $tableid): string
    {
        $columns = [];

        foreach ($campos as $key => $campo) {
            if (empty($campo['hidden'])) {
                $label = addslashes($campo['label']);
                $columns[] = "{ data: '$key', title: '$label' }";
            }
        }

        foreach ($acciones as $key => $accion) {
            $columns[] = "{ data: '$key', title: '$key', orderable: false, searchable: false }";
        }

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

        foreach ($acciones as $key => $accion) {
            $cols[] = "{ data: '$key', name: '$key' }";
        }

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

    protected static function getDB(): PDO
    {
        $config = require $_SESSION['directoriobase'].'/config/settings.php';
        $dsn = $config['db']['dsn'];
        $username = $config['db']['username'];
        $password = $config['db']['password'];
        $options = $config['db']['options'] ?? [];

        return new PDO($dsn, $username, $password, $options);
    }
}
