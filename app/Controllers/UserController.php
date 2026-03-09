<?php
namespace App\Controllers;
use App\Models\User;
use App\Core\Model;
use App\Core\Session;

use App\Core\Request;
use App\Core\Controller;
use App\Core\Helpers\string4query;
use PDO;


class UserController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // Aquí podrías cargar configuraciones específicas del controlador
        $this->pendingquery = '';
        $this->pendingcolumns = '';
    }

    public function show(Request $request, $id)
    {
        // $id es capturado de "/users/{id}"
        echo "Mostrando usuario con ID: $id";
        // Aquí consultaría la BD, mostraría una vista, etc.
    }

    public function store(Request $request)
    {
        // Procesa un formulario POST para crear usuario
        echo "Creando usuario...";
    }

    public function profile(Request $request, $id)
    {
        echo "Perfil del usuario con ID: $id";
    }
    public function showMyFile($id, $file)
    {
        // Aquí puedes mostrar el archivo del usuario
        $local_id = $id ?? null;
        $myfile = $file ?? null;

    // Verificás si el usuario tiene permiso para acceder (opcional pero recomendable)

    // Buscás la ruta del archivo en tu sistema (podría estar en base de datos)
        $ruta = $this->getUserFolder($id); // implementalo vos
        $eff_file = $ruta . $myfile;
// Validás que el archivo exista
        if (!file_exists($eff_file)) {
            http_response_code(404);
            exit('Archivo no encontrado');
        }

// Determinás el tipo de archivo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $tipo = finfo_file($finfo, $ruta);
        finfo_close($finfo);

// Enviás los headers y el archivo
        header('Content-Type: ' . $tipo);
        header('Content-Disposition: inline; filename="' . basename($ruta) . '"');
        readfile($ruta);
        exit;    
    }

    public function pendingbrowse(Request $request, array $params = []): void
    {
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
        }
        if ($user['role'] <> 'admin' ) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
        }



        //$datos = User::findPending();
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/users/users_pending.php';

        $cfg         = $cfgedit['config']    ?? [];
        $id_field = $cfgedit['config']['field_id'];
        $campos      = $cfgedit['campos']    ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos    = $cfgedit['comandos']  ?? [];
        $buttons     = $cfgedit['buttons']   ?? [];
        $tables      = $cfgedit['QrySpec']['tables'] ?? [];
        $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
        $filter      = $cfgedit['QrySpec']['filter'] ?? '';
        $order       = $cfgedit['QrySpec']['order'] ?? [];

        require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';

        $this->pendingquery= str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, 'user_id');
        $zcolumns =  $this->mkcolumns($campos, $actividades);
        $zcolumns =   trim(stripslashes($zcolumns), '"');

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
            'scriptjs_data' => $this->pendingquery,
            'scriptjs_columns' => $this->pendingcolumns,
            'zcolumns' => $zcolumns,
            'url_data' => $_SESSION['base_url']. $cfg['url_data'],
        ]);
    }

public function apiPendingData(Request $request, array $params = []): string
{

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';

    $cfgedit = require $_SESSION['directoriobase'] . '/config/cruds/users/users_pending.php';
    $id_field = $cfgedit['config']['field_id'];

    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
    $order = $cfgedit['QrySpec']['order'] ?? [];

    $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, $id_field);
    //echo $query;
    //die();
    $resultset = User::CustomQry($query);

    $results = [
        "sEcho" => 1,
        "iTotalRecords" => count($resultset),
        "iTotalDisplayRecords" => count($resultset),
        "aaData" => $resultset
    ];

    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}


    }
