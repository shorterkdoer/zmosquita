<?php

namespace App\Controllers;

use App\Core\Controller;
use Foundation\Core\Request;
use Foundation\Core\Session;
use App\Models\DatosPersonales;
use App\Models\Provincia;
use App\Models\Ciudad;
use App\Models\Comision;
use App\Models\Matricula;
use App\Models\User;
use App\Support\Sanitizer;
use App\Services\UserService;
use App\Services\DocumentService;
use App\Services\MatriculaService;
use setasign\Fpdi\Tcpdf\Fpdi;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Imagick;

/**
 * DatosPersonalesController - Handles personal data and credential management
 *
 * Refactored to use Service Layer for business logic
 */
class DatosPersonalesController extends Controller
{
    protected UserService $userService;
    protected DocumentService $documentService;
    protected MatriculaService $matriculaService;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Initialize services
        $this->userService = new UserService();
        $this->documentService = new DocumentService();
        $this->matriculaService = new MatriculaService(
            new \App\Services\TramiteService(new \App\Services\EmailService()),
            new \App\Services\EmailService()
        );
    }
    public function edit(Request $request, array $params = []): void
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
        
        if ( $id !== $user['id']) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
        }
        //$user = User::findByEmail($email);

        // Se busca el registro de datospersonales por el user_id.

        //echo "ID--yyyy---: $id <br>";
        

        $datos = DatosPersonales::findByUserId($id);
//        $datos = $this->CustomQry("SELECT id FROM datospersonales WHERE user_id = ". $id);
        // Si no existe el registro, podrías crearlo de forma automática.
        if (!($datos) || count($datos) == 0) {

            $datos = DatosPersonales::create( ['user_id' => $id]);
            //$datos = findByUserId($id);
        }
        $datos = DatosPersonales::findByUserId($id);
        
        // Pasar todas las variables a la vista
/*
        $this->view('datospersonales/edit', [
            'datospersonales' => $datos,
            'provincias'      => $provincias,
            'ciudades'        => $ciudades
        ]);
*/
        //$localfile = $_SESSION['directoriobase'] . '/config/cruds/datospersonales/datospersonales_edit.php';
        //self::makeform( $localfile, $id, $datos);

        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/datospersonales/datospersonales_edit.php';
        $cfg         = $cfgedit['config']    ?? [];
        $cfg['url_action'] .= '/' . $id; // <— se agrega el id a la url
        $campos      = $cfgedit['campos']    ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos    = $cfgedit['comandos']  ?? [];
        $buttons     = $cfgedit['buttons']   ?? [];

        $provincias = Provincia::HtmlDropDown($campos['provincia_id']['options'] ?? []);
        $ciudades = Ciudad::HtmlDropDown($campos['ciudad_id']['options'] ?? []);
    
        $campos['provincia_id']['listavalores'] = $provincias;
        $campos['ciudad_id']['listavalores']    = $ciudades;

        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $campos,     // <— coherente con index/create
            'values'   => $datos,       // array simple con claves=>valores
            'actions'  => $actividades,
            'comandos' => $comandos,
            'buttons'  => $buttons,
            'id'      => $id,
            'style'    => $style,
            'user_id' => $id
        ]);
  
        //$tablaHTML = renderTablaHTML($config, $datos, $provincias, $ciudades);

    }

    public function vistaadmin(Request $request, array $params = []): void
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
            $this->redirect('/user-dashboard');
        }
        //$user = User::findByEmail($email);

        // Se busca el registro de datospersonales por el user_id.
        $datos = DatosPersonales::findByUserId($id);
        if($datos ==null) {
            Session::flash('error', 'No se encontraron datos personales para este usuario.');
            $this->redirect('/dashboard');
        }
        // Si no existe el registro, podrías crearlo de forma automática.
        // manejar el error si no existe
        //$provincias = Provincia::all();
        //$ciudades = Ciudad::all();
        
        // Pasar todas las variables a la vista
/*
        $this->view('datospersonales/edit', [
            'datospersonales' => $datos,
            'provincias'      => $provincias,
            'ciudades'        => $ciudades
        ]);
*/
 
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/datospersonales/vistadatos.php';
        $cfg         = $cfgedit['config']    ?? [];
        //$cfg['url_action'] .= '/' . $id; // <— se agrega el id a la url
        $campos      = $cfgedit['campos']    ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos    = $cfgedit['comandos']  ?? [];
        $buttons     = $cfgedit['buttons']   ?? [];
        $provincias = Provincia::HtmlDropDown($campos['provincia_id']['options'] ?? []);
        $ciudades = Ciudad::HtmlDropDown($campos['ciudad_id']['options'] ?? []);
    
        $campos['provincia_id']['listavalores'] = $provincias;
        $campos['ciudad_id']['listavalores']    = $ciudades;

        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $campos,     // <— coherente con index/create
            'values'   => $datos,       // array simple con claves=>valores
            'actions'  => $actividades,
            'comandos' => $comandos,
            'buttons'  => $buttons,
            'id'      => $id,
            'style'    => $style,
            'user_id' => $id
        ]);
   
        //$tablaHTML = renderTablaHTML($config, $datos, $provincias, $ciudades);

    }

public function vistalegajo(Request $request, array $params = []): void
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
            $this->redirect('/user-dashboard');
        }
        //$user = User::findByEmail($email);

        // Se busca el registro de datospersonales por el user_id.
        $datos = $this->matriculaService->findByUserId($id);
        if ($datos == null) {
            Session::flash('error', 'No se encontraron datos personales para este usuario.');
            $this->redirect('/dashboard');
        }
 
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/matricula/vistalegajo.php';
        $cfg         = $cfgedit['config']    ?? [];
        //$cfg['url_action'] .= '/' . $id; // <— se agrega el id a la url
        $campos      = $cfgedit['campos']    ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos    = $cfgedit['comandos']  ?? [];
        $buttons     = $cfgedit['buttons']   ?? [];
        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $campos,     // <— coherente con index/create
            'values'   => $datos,       // array simple con claves=>valores
            'actions'  => $actividades,
            'comandos' => $comandos,
            'buttons'  => $buttons,
            'id'      => $id,
            'style'    => $style,
            'user_id' => $id
        ]);
   
        //$tablaHTML = renderTablaHTML($config, $datos, $provincias, $ciudades);

    }


    public function update(Request $request, array $params = []): void
    {
        // Se asume que el usuario está logueado.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
            return;
        }

        $id = (int)($params[0] ?? 0);

        if ($user['role'] == 'user' && $id != $user['id']) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
            return;
        }

        $userId = $user['id'];

        // Recoger y limpiar los datos enviados.
        $data = [
            'nombre'          => Sanitizer::text(trim($_POST['nombre'] ?? ''), 100),
            'apellido'        => Sanitizer::text(trim($_POST['apellido'] ?? ''), 100),
            'dni'             => Sanitizer::text(trim($_POST['dni'] ?? ''), 15),
            'direccion_calle' => Sanitizer::text(trim($_POST['direccion_calle'] ?? ''), 80),
            'direccion_numero'=> Sanitizer::text(trim($_POST['direccion_numero'] ?? ''), 10),
            'direccion_piso'  => Sanitizer::text(trim($_POST['direccion_piso'] ?? ''), 10),
            'direccion_depto' => Sanitizer::text(trim($_POST['direccion_depto'] ?? ''), 10),
            'direccion_cp'    => Sanitizer::text(trim($_POST['direccion_cp'] ?? ''), 10),
            'telefono'        => Sanitizer::text(trim($_POST['telefono'] ?? ''), 15),
            'celular'         => Sanitizer::text(trim($_POST['celular'] ?? ''), 15),
            'mailparticular'  => filter_var(trim($_POST['mailparticular'] ?? ''), FILTER_SANITIZE_EMAIL),
            'maillaboral'     => filter_var(trim($_POST['maillaboral'] ?? ''), FILTER_SANITIZE_EMAIL),
        ];

        if (isset($_POST['provincia_id'])) {
            $data['provincia_id'] = trim($_POST['provincia_id']);
        }

        if (isset($_POST['ciudad_id'])) {
            $data['ciudad_id'] = trim($_POST['ciudad_id']);
        }

        // Use UserService to update personal data
        $result = $this->userService->updatePersonalData($userId, $data);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            $this->redirect('/user-dashboard');
            return;
        }

        Session::flash('success', 'Datos Personales actualizados correctamente.');
        $this->redirect('/user-dashboard');
    }
   
    public function adminbrowse(Request $request, array $params = []): void
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

        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        // si es el presi o el vice, el array puede otorgar matricula
        if (Comision::espresidente($user['id']) || Comision::esvicepresidente($user['id'])) {
            $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/datospersonales/datospersonales_index.php';
        } else {
            $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/datospersonales/datospersonales_index2.php';
        }

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

        $this->pendingquery= str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, 'm.user_id');
        $zcolumns =  Self::mkColumns($campos, $actividades);
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
            'link_id' => 'user_id',
            'scriptjs_data' => $this->pendingquery,
            'scriptjs_columns' => $this->pendingcolumns,
            'zcolumns' => $zcolumns,
            'url_data' => $_SESSION['base_url']. $cfg['url_data'],


            ]);
    }
    
public function padronview(Request $request): void
{
    // 1) Informa errores

    // 2) Inicia sesión si es necesario
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];

    $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/datospersonales/datospersonales_index.php';
    $id_field = $cfgedit['config']['field_id'];

    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
    $order = $cfgedit['QrySpec']['order'] ?? [];
    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';
    $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, 'm.user_id');
    $resultset = $this->documentService->customQuery($query);

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

    public function activosbrowse(Request $request, array $params = []): void
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

        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        // si es el presi o el vice, el array puede otorgar matricula
        $cfgedit     = require $_SESSION['directoriobase'] . '/views/matriculas/activos_index.php';

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

        $this->pendingquery= str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, 'm.user_id');
//        echo ".......................::";
//        die();

//        $jscampos = [];
//        $jscampos = json_encode(quitaaliascampos($campos));

        $zcolumns =  Self::mkcolumns($campos, $actividades);
        $zcolumns =   trim(stripslashes($zcolumns), '"');

        $datos = DatosPersonales::CustomQry($this->pendingquery);

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


            ]);
    }


    public function estadosolicitudes(Request $request, array $params = []): void
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

        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        // si es el presi o el vice, el array puede otorgar matricula
        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/datospersonales/inscripcionesabiertas.php';

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

        $this->pendingquery= str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, 'm.user_id');
        $zcolumns =  Self::mkcolumns($campos, $actividades);
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
            'link_id' => 'user_id',
            'scriptjs_data' => $this->pendingquery,
            'scriptjs_columns' => $this->pendingcolumns,
            'zcolumns' => $zcolumns,
            'url_data' => $_SESSION['base_url']. $cfg['url_data'],


            ]);
    }


    public function adminmatriculados(Request $request, array $params = []): void
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

        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/datasources/matriculados.php';

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

        $this->pendingquery= str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, 'm.user_id');
        $zcolumns =  Self::mkcolumns($campos, $actividades);
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
            'link_id' => 'user_id',
            'scriptjs_data' => $this->pendingquery,
            'scriptjs_columns' => $this->pendingcolumns,
            'zcolumns' => $zcolumns,
            'url_data' => $_SESSION['base_url']. $cfg['url_data'],


            ]);
    }




public function activosview(Request $request): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];
        
    $cfgedit     = require $_SESSION['directoriobase'] . '/views/matriculas/activos_index.php';

    //$cfgedit     = require $_SESSION['directoriobase'] . '/config/datasources/matriculados.php';
    $id_field = $cfgedit['config']['field_id'];


    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
    $order = $cfgedit['QrySpec']['order'] ?? [];
    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';
    $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, 'm.user_id');
    
    
    
    $resultset = $this->documentService->customQuery($query);

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


    public function matriculadosview(Request $request): void
{
    // 1) Informa errores

    // 2) Inicia sesión si es necesario
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];
        
    $cfgedit     = require $_SESSION['directoriobase'] . '/config/datasources/matriculados.php';

    //$cfgedit     = require $_SESSION['directoriobase'] . '/config/datasources/matriculados.php';
    $id_field = $cfgedit['config']['field_id'];


    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
    $order = $cfgedit['QrySpec']['order'] ?? [];
    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';
    $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, 'm.user_id');
    
    
    
    $resultset = $this->documentService->customQuery($query);

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


    public function adminvista(Request $request, array $params = []): void
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
        $id = (int)($params[0] ?? 0);
        $datos = DatosPersonales::findByUserId($id);
        // Si no existe el registro, podrías crearlo de forma automática.
        if (!$datos) {
            DatosPersonales::create(['user_id' => $user['id']]);
            $datos = DatosPersonales::findByUserId($user['id']);
        }
        
        $provincias = Provincia::findById($datos['provincia_id']);
        $ciudades = Ciudad::findById($datos['ciudad_id']);
        $datos['provincia'] = $provincias['nombre'];
        $datos['ciudad']    = $ciudades['nombre'];

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/datospersonales/datospersonales_adminview.php';
        $cfg         = $cfgedit['config']    ?? [];
        $campos      = $cfgedit['campos']    ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos    = $cfgedit['comandos']  ?? [];
        $buttons     = $cfgedit['buttons']   ?? [];

        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $campos,     // <— coherente con index/create
            'values'   => $datos,       // array simple con claves=>valores
            'actions'  => $actividades,
            'comandos' => $comandos,
            'buttons'  => $buttons,
            'id'      => $id,
            'user_id' => $id,
        ]);
   
        //$tablaHTML = renderTablaHTML($config, $datos, $provincias, $ciudades);

    }


    public function rolevista(Request $request, array $params = []): void
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
        $id = (int)($params[0] ?? 0);
        $datos = DatosPersonales::findByUserIdWithRole($id);
        // Si no existe el registro, podrías crearlo de forma automática.
        if (!$datos) {
            DatosPersonales::create(['user_id' => $user['id']]);
            $datos = DatosPersonales::findByUserId($user['id']);
        }
        
        $provincias = Provincia::findById($datos['provincia_id']);
        $ciudades = Ciudad::findById($datos['ciudad_id']);
        $datos['provincia'] = $provincias['nombre'];
        $datos['ciudad']    = $ciudades['nombre'];

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/datospersonales/datospersonales_roleview.php';
        $cfg         = $cfgedit['config']    ?? [];
        $cfg['url_action'] .= '/' . $id; // <— se agrega el id a la url

        $campos      = $cfgedit['campos']    ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos    = $cfgedit['comandos']  ?? [];
        $buttons     = $cfgedit['buttons']   ?? [];

        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $campos,     // <— coherente con index/create
            'values'   => $datos,       // array simple con claves=>valores
            'actions'  => $actividades,
            'comandos' => $comandos,
            'buttons'  => $buttons,
            'id'      => $id,
            'user_id' => $id,
        ]);
   
        //$tablaHTML = renderTablaHTML($config, $datos, $provincias, $ciudades);

    }

    public function roleupdate(Request $request, array $params = []): void
    {
        // Se asume que el usuario está logueado.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
            return;
        }

        $id = (int)($params[0] ?? 0);

        if ($user['role'] == 'user' && $id != $user['id']) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
            return;
        }

        // Use UserService to toggle role
        $result = $this->userService->toggleRole($id);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            $this->redirect('/dashboard');
            return;
        }

        Session::flash('success', 'Rol actualizado a ' . $result['new_role'] . '.');
        $this->redirect('/dashboard');
    }



public function generarPDF(Request $request, array $params): void
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
            $this->redirect('/user-dashboard');
        }
        //$user = User::findByEmail($email);

        // Se busca el registro de datospersonales por el user_id.
        $datos = $this->matriculaService->findByUserId($id);
        if ($datos == null) {
            Session::flash('error', 'No se encontraron datos personales para este usuario.');
            $this->redirect('/dashboard');
        }


    $user_id = $id;

    if (!$user_id) {
        Session::flash('error', 'ID de usuario no especificado');
        $this->redirect('/datospersonales');
        return;
    }

    $persona = DatosPersonales::findByUserId($user_id);
    $matricula = $this->matriculaService->findByUserId($user_id);

    if (!$persona || !$matricula) {
        Session::flash('error', 'Datos no encontrados.');
        $this->redirect('/datospersonales');
        return;
    }
    $rutaaladjunto = $this->documentService->getUserFolder($id);
    if (!$rutaaladjunto) {
        Session::flash('error', 'No se pudo determinar la ruta de los archivos adjuntos.');
        $this->redirect('/datospersonales');
        return;
    }

    }

// [[]] {{}}  revisar el procedimiento de generación de credenciales

        public function emitircredencial()
        {
// {{}} revisar si es esta rutina la que debe usarse, parece que no
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $locuser = $_SESSION['user']['id'] ?? null;
        $matricula = $this->matriculaService->findByUserId($locuser);
        if($matricula == null) {
            Session::flash('error', 'No se encontraron datos de matrícula para este usuario.');
            $this->redirect('/dashboard');
        }elseif(($matricula['comisionotorgante'] == null) || ($matricula['funcionario'] == null)) {
            Session::flash('error', 'No se ha asignado matrícula aún.');
            $this->redirect('/dashboard');
        }
        //require_once $_SESSION['directoriobase'] . '/lib/fpdf/fpdf.php'; // Ajustar según tu estructura
        //require_once $_SESSION['directoriobase'] . '/libs/phpqrcode/qrlib.php'; // Para generar QR
        if ($matricula == null) {
            die('Matrícula no encontrada.');
        }

        $locuser = $matricula['user_id'];
        $asignada = $matricula['matriculaasignada'];
        $this->redirect('/credencial/'.$asignada);
        }




    public function showcredencial(request $request, array $params): void
        {
        $id = ($params[0] ?? '');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }   
        $matricula = $this->matriculaService->findByAsignada($id);

        //require_once $_SESSION['directoriobase'] . '/lib/fpdf/fpdf.php'; // Ajustar según tu estructura
        //require_once $_SESSION['directoriobase'] . '/libs/phpqrcode/qrlib.php'; // Para generar QR
        if ($matricula == null) {
            die('Matrícula no encontrada.');
        }
        if($matricula['carnet']==null){
            Session::flash('error', 'No se ha asignado matrícula aún.');
            $this->redirect('/dashboard');

        }
         // echo "Matrícula encontrada";
         $locuser = $matricula['user_id'];
         $rutaaladjunto = $this->documentService->getUserFolder($locuser);
         $this->redirect($rutaaladjunto . $matricula['carnet']);

    }


        public function showcarnet(request $request, array $params): void
        {

        $id = ($params[0] ?? '');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }   

        $matricula = $this->matriculaService->findByAsignada($id);
        $aprobado = $matricula['aprobado'] ?? ''; 
        $aprobado = date('d-m-Y', strtotime($aprobado));

        $estadomatricula = "";
        if ($matricula['baja'] == null) {
            $estadomatricula = "Se encuentra matriculado desde: $aprobado\n";
        }else{
            $estadomatricula = "Estado actual: De baja desde el " . date('d-m-Y', strtotime($matricula['baja'])) . ". \n ";
        }

        //require_once $_SESSION['directoriobase'] . '/lib/fpdf/fpdf.php'; // Ajustar según tu estructura
        //require_once $_SESSION['directoriobase'] . '/libs/phpqrcode/qrlib.php'; // Para generar QR
        if ($matricula == null) {
            Session::flash('error', 'No se encuentra matrícula');
            $this->redirect($_SERVER['HTTP_REFERER']);

        }

        $locuser = $matricula['user_id'];
        $fotocarnet = $matricula['fotocarnet'];
        
        $datos = DatosPersonales::findByUserId( $locuser);
        if ($datos == null) {
            Session::flash('error', 'No se datos personales');
            $this->redirect($_SERVER['HTTP_REFERER']);
        }


        $persona = DatosPersonales::QrySingleRec( 'Select * from datospersonales where user_id = ' . $locuser);



        $apellido = $persona['apellido'] ?? '';
        $nombre = $persona['nombre'] ?? '';


        $nombreCompleto = trim($apellido . ' ' . $nombre);
        $dni = $persona['dni'] ?? '';
        
        // --- AGREGAR IMAGEN DE FONDO ---
        //$imagen_base = '/public/img/credencial_carnet_fondo.jpeg';
        $comisionactuante = $matricula['comisionotorgante'] ?? '';
        $funcionario = $matricula['funcionario'] ?? '';
        
        //buscar la comision
        $comision = Comision::findById($comisionactuante);
        //si el funcionario es el presi, tomar el carnet_presi de la comision

        $imagen_firma = '';
        if ($funcionario === $comision['user_presi']) {
            $otorganteCargo = "Presidente";
            $imagen_firma = $comision['firmapresi'] ;
        } elseif ($funcionario === $comision['user_vice']) {
            $otorganteCargo = "Vicepresidente";
            //$otorganteNombre = $comision['vicepresidente'] ;
            $imagen_firma = $comision['firmavice'] ;
        } 
        $otorganteNombre = '';
        $datafuncionario = DatosPersonales::findByUserId($funcionario);
        if ($datafuncionario) {
            $otorganteNombre = $datafuncionario['apellido'] . ', ' . $datafuncionario['nombre'];
        }
        if ($imagen_firma == '') {
            Session::flash('error', 'Error de configuración de comisión o funcionario.');
            $this->redirect($_SERVER['HTTP_REFERER']);

        }
    
        //tomar el directorio del usuario sea vice o presi según corresponda con getuserfolder
        //y agregar la imagen de fondo
        //si no existe la imagen, usar la de base
        $funcionariofolder = $this->documentService->getUserFolder($funcionario);
        $firmaPath = $_SESSION['directoriobase'] . $funcionariofolder . $imagen_firma;
        //$imagen_fondo = $_SESSION['directoriobase'] . $imagen_base;
        //$logoPath = $_SESSION['directoriobase'] . $funcionariofolder. '/public/img/Logocertificadomatriculacion.png';
        $logoPath = $_SESSION['directoriobase'] .'/public/img/Logocertificadomatriculacion.png';


        $pdf = new Fpdi('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(false); // para posicionamiento absoluto cómodo

        $left = 20;                     // margen izquierdo
        $right = 20;                    // margen derecho
        $pageW = $pdf->GetPageWidth();
        $contentW = $pageW - $left - $right;

        // ---------- LOGO ----------
        $logoY = 15;
        $logoW = 60;                    // ancho en mm
        $pdf->Image($logoPath, $left, $logoY, $logoW); // x, y, w


        // Estimá la altura ocupada por el logo para ubicar el título debajo
        // (si tu logo cambia, ajustá este valor fino)
        $logoH = 22; // aprox. para el PNG adjunto
        $gapAfterLogo = 10;

        $titleY = $logoY + $logoH + $gapAfterLogo;

        // ---------- TÍTULO CENTRADO ----------
        $pdf->SetY($titleY);
        $pdf->SetFont('helvetica', 'B', 22);
        $pdf->Cell(0, 10, 'CONSTANCIA DE MATRICULACIÓN', 0, 1, 'C');

        // Pequeño subtítulo (opcional)
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 6, 'Consejo Profesional Bioquímico de La Pampa', 0, 1, 'C');

        $pdf->Ln(10);

        // ---------- CUERPO ----------
        $pdf->SetFont('helvetica', '', 13);
        $pdf->SetX($left);
        $pdf->MultiCell($contentW, 8,
        "El Consejo Profesional Bioquímico de La Pampa deja constancia que:\n" . "$nombreCompleto\n" .
        "DNI: $dni\n\n" . $estadomatricula, 0, 'C');

        // ---------- FIRMA (centrada) ----------
        $firmY = 180;                  // ajustá esta Y para subir/bajar el bloque de firma
        $firmW = 70;                   // ancho de la imagen de firma
        $firmX = ($pageW - $firmW) / 2;


        $pdf->Image($firmaPath, $firmX, $firmY, $firmW);

        // Línea bajo la firma
        $lineY = $firmY + 35;          // ajustá según tu imagen
        $linePadding = 40;             // margen interno de la línea respecto a los bordes
        $pdf->SetDrawColor(0);         // negro
        $pdf->SetLineWidth(0.3);
        $pdf->Line($left + $linePadding, $lineY, $pageW - $right - $linePadding, $lineY);

        // Nombre y cargo
        $pdf->SetY($lineY + 4);
        $pdf->SetFont('helvetica', '', 12);
//        $pdf->Cell(0, 6, ($otorganteNombre), 0, 1, 'C');
        $pdf->SetFont('helvetica', 'I', 10);
//        $pdf->Cell(0, 5, ($otorganteCargo), 0, 1, 'C');

        // ---------- ESTADO ACTUAL ----------
        $pdf->SetY(250); // cerca del pie
        $pdf->SetFont('helvetica', 'B', 12);
        $estado = $matricula['estado'] ?? 'Desconocido';
        $estado = 'Habilitado';
        //$pdf->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT',"Estado Actual de la Matrícula: $estado"), 0, 1, 'C');

        //$pdf->Output('I', 'constancia.pdf');

        $pdf->SetFont('helvetica', 'B', 12);

        // Normalizo el texto
        $estado = ucfirst(strtolower(trim($estado)));

        // Aplico color según el estado
        switch ($estado) {
            case 'Habilitado':
                $pdf->SetTextColor(0, 128, 0);       // verde
                break;
            case 'Suspendido':
                $pdf->SetTextColor(255, 215, 0);     // amarillo
                break;
            case 'Inhabilitado':
                $pdf->SetTextColor(220, 20, 60);     // rojo carmesí
                break;
            case 'Baja':
                $pdf->SetTextColor(0, 0, 0);         // negro
                break;
            default:
                $pdf->SetTextColor(0, 0, 0);         // por las dudas
                break;
        }

        // Texto principal del estado
        $pdf->Cell(0, 8, "Siendo su estado actual: $estado", 0, 1, 'C');
        //$pdf->Cell( 0, 10, mb_convert_encoding("Siendo su estado actual: $estado", 'ISO-8859-1', 'UTF-8'), 0, 1, 'C' );    

        // Vuelvo al color por defecto (negro)
        $pdf->SetTextColor(0, 0, 0);







        $pdf->Output('I', 'constancia_matriculacion_'. $id .'.pdf');
    }


    public function regenerarcarnet(request $request, array $params): void
        {
        $id = ($params[0] ?? '');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }   
        $this->generarcredenciales($request, $params);
        Session::flash('Success', 'Credenciales emitidas!');

            
    
    }
    
    public function generarcredenciales(request $request, array $params): void
        {
            $id = ($params[0] ?? '');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }   
        $matricula = $this->matriculaService->findByAsignada($id);

        //require_once $_SESSION['directoriobase'] . '/lib/fpdf/fpdf.php'; // Ajustar según tu estructura
        //require_once $_SESSION['directoriobase'] . '/libs/phpqrcode/qrlib.php'; // Para generar QR
        if ($matricula == null) {
            error_log('generarcredenciales - Matrícula no encontrada al inicio.');
        }

        $locuser = $matricula['user_id'];
        //$datos = Matricula::freezedata($locuser);
        $fotocarnet = $matricula['fotocarnet'];
        
        $datos = DatosPersonales::findByUserId( $locuser);
        if ($datos == null) {
            error_log('generarcredenciales - Registro de usuario no encontrados.');
        }


        $pdf = new Fpdi('P', 'mm', 'A5');
        $pdf->AddPage();
        
        // --- CONFIGURACIÓN BÁSICA ---
        $x0 = 36; // margen izquierdo del modelo
        $y0 = 20; // margen superior del modelo


        // --- AGREGAR IMAGEN DE FONDO ---
        // --- AGREGAR IMAGEN DE FONDO ---
        //$imagen_base = '/public/img/credencial_carnet_fondo.jpeg';
        $comisionactuante = $matricula['comisionotorgante'] ?? '';
        $funcionario = $matricula['funcionario'] ?? '';



        //buscar la comision
        $comision = Comision::findById($comisionactuante);
        //si el funcionario es el presi, tomar el carnet_presi de la comision

        $imagen_base = '';
        if ($funcionario === $comision['user_presi']) {
            $imagen_base = $comision['carnet_presi'] ;
        } elseif ($funcionario === $comision['user_vice']) {
            $imagen_base = $comision['carnet_vice'] ;
        } 


        if ($imagen_base == '') {
            Session::flash('error', 'Error de configuración de comisión o funcionario.');
            error_log('generarcredenciales - Error de configuración de comisión o funcionario.');            
            header("Location: " . '/estadomatricula/' . $id);

        }
    
        //tomar el directorio del usuario sea vice o presi según corresponda con getuserfolder
        //y agregar la imagen de fondo
        //si no existe la imagen, usar la de base
        $funcionariofolder = $this->documentService->getUserFolder($funcionario);

        $imagen_fondo = $_SESSION['directoriobase'] . $funcionariofolder . $imagen_base;

        //$imagen_fondo = $_SESSION['directoriobase'] . '/public/img/credencial_fondo.jpeg';
        $pdf->Image($imagen_fondo, $x0, $y0, 75, 50);


        // --- FOTOCARNET ---

        $uploadFolder = $this->documentService->getUserFolder($locuser);
        if (!empty($matricula['fotocarnet'])) {
            $fotocarnet =  $_SESSION['directoriobase'] . '/' .$uploadFolder .$matricula['fotocarnet'];
            if (file_exists($fotocarnet)) {
                $pdf->Image($fotocarnet, $x0 + 53, $y0 + 5, 19, 19);
            }
        }


        // --- NOMBRE Y APELLIDO ---
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetXY($x0 + 4, $y0 + 18);
        $pdf->MultiCell(46, 4, mb_convert_case($datos['apellido'] . ', ' . $datos['nombre'], MB_CASE_TITLE, 'UTF-8'), 0, 'L');

        // --- MATRÍCULA ASIGNADA ---
        $pdf->SetXY($x0 + 4, $y0 + 28);
        $pdf->Cell(25, 5, $matricula['matriculaasignada'], 0, 0, 'L');

        // --- APROBADO ---
        $pdf->SetXY($x0 + 32, $y0 + 28);
        $fecha_aprobado = date('d/m/Y', strtotime($matricula['aprobado'])) ;
        $pdf->Cell(18, 5, $fecha_aprobado, 0, 0, 'L');


        $prot = $_SERVER['HTTPS'] ?? 'off' ? 'https://' : 'http://';
        //$prot = '';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $url = $prot . $host . '/carnet/' . urlencode($matricula['matriculaasignada']);
        $localfile = $_SESSION['directoriobase'] .'/tmp/qr_' . $matricula['matriculaasignada'] . '.png';
        

        // {{}}
        error_log('generarcredenciales - Generando QR para URL: ' . $url);
        DatosPersonalesController::generarQR( $url , $localfile, 2400    );
        //{{}}
        
        
        $image = imagecreatefrompng($localfile);

        $converted = $_SESSION['directoriobase'] . '/tmp/qr_final.png';

        $bg = imagecreatetruecolor(300, 300);
        $white = imagecolorallocate($bg, 255, 255, 255);
        imagefilledrectangle($bg, 0, 0, 300, 300, $white);
        imagecopyresampled($bg, $image, 0, 0, 0, 0, 300, 300, imagesx($image), imagesy($image));

        imagepng($bg, $converted);

        imagedestroy($image);
        imagedestroy($bg);


        $pdf->Image($converted, $x0 + 53, $y0 + 28, 19, 19);
        $carnetfilename = 'credencial_' . $matricula['matriculaasignada'];
        $pdfFile = $_SESSION['directoriobase'] . '/' .$uploadFolder . $carnetfilename . '.pdf';
        //$pngFile = $uploadFolder . $carnetfilename . '.png';
        $pdf->Output('F', $pdfFile);

        $pngFile = $_SESSION['directoriobase'] . '/' .$uploadFolder .$carnetfilename . '.png';
        //carnet_png($pdfFile, $pngFile);

        
        //$pdf->Output('I', 'credencial_' . $matricula['matriculaasignada'] . '.pdf');
        
        $dpi = 300;
        $imagick = new Imagick();
        $imagick->setResolution($dpi, $dpi);
        $imagick->readImage($pdfFile.'[0]'); // primera página
        $imagick->setImageFormat('png');

        // Conversión mm->px
        $pxPerMm = $dpi / 25.4;
        $x = (int) round(36 * $pxPerMm);
        $y = (int) round(20 * $pxPerMm);
        $w = (int) round(75 * $pxPerMm);
        $h = (int) round(50 * $pxPerMm);

        $imagick->cropImage($w, $h, $x, $y);
        $imagick->setImagePage(0,0,0,0); // limpiar canvas virtual
        //$pngFile = $_SESSION['directoriobase'] . '/' .$uploadFolder .'credencial_' . $matricula['matriculaasignada'] . '.png';
        $imagick->writeImage($pngFile);


        $data2 = [];
        $data2['carnet'] = $carnetfilename . '.png'; 
        $data2['carnetpdf'] = $carnetfilename . '.pdf' ;

/*
  //      error_log('Graba los nombres de los archivos de credencial ' . $id);
    echo $locuser;
    echo "<br>";
    echo $pngFile;
    echo "<br>";
    echo $pdfFile;
    echo "<br>";
    die();
    */
        //Matricula::CustomQry("UPDATE matriculas SET 'carnet' = $pngFile, 'carnetpdf' = $pdfFile WHERE user_id = $locuser");
        $this->matriculaService->updateCredentialFiles($locuser, $data2);

        header('Content-Type: image/png');
        echo $imagick->getImageBlob();
        $imagick->clear();
        Session::flash('success', 'Matriculado:'. $matricula['matriculaasignada'] .' Credenciales emitidas.');


        header("Location: " . $_SERVER['HTTP_REFERER']);

        //unlink($localfile); // Eliminar el archivo QR temporal

        }


        public function guardarcredencial(Request $request, array $params)
        {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }   
        $id = (int)($params[0] ?? 0);

        //require_once $_SESSION['directoriobase'] . '/lib/fpdf/fpdf.php'; // Ajustar según tu estructura
//require_once $_SESSION['directoriobase'] . '/libs/phpqrcode/qrlib.php'; // Para generar QR
        if ($id == 0) {
            die('Número de matrícula no especificado.');
        }

        $matricula = $this->matriculaService->findByAsignada($id);
        if ($matricula == null) {
            die('Matrícula no encontrada.');
        }

        $locuser = $matricula['user_id'];
        $datos = Matricula::freezedata($locuser);
        $fotocarnet = $matricula['fotocarnet'];
        
        $datos = DatosPersonales::findByUserId( $locuser);
        if ($datos == null) {
            die('Registro de usuario no encontrados.');
        }

        $pdf = new Fpdi('P', 'mm', 'A5');
        $pdf->AddPage();
        
        // --- CONFIGURACIÓN BÁSICA ---
        $x0 = 36; // margen izquierdo del modelo
        $y0 = 20; // margen superior del modelo


        // --- AGREGAR IMAGEN DE FONDO ---
        $imagen_fondo = $_SESSION['directoriobase'] . '/public/img/credencial_fondo.jpeg';
        $pdf->Image($imagen_fondo, $x0, $y0, 75, 50);


        // --- FOTOCARNET ---

        $uploadFolder = $this->documentService->getUserFolder($locuser);
        if (!empty($matricula['fotocarnet'])) {
            $fotocarnet =  $_SESSION['directoriobase'] . '/' .$uploadFolder . '/'.$matricula['fotocarnet'];
            if (file_exists($fotocarnet)) {
                $pdf->Image($fotocarnet, $x0 + 53, $y0 + 5, 19, 19);
            }
        }

        // --- NOMBRE Y APELLIDO ---
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetXY($x0 + 4, $y0 + 18);
        $pdf->MultiCell(46, 4, mb_convert_case($datos['apellido'] . ', ' . $datos['nombre'], MB_CASE_TITLE, 'UTF-8'), 0, 'L');

        // --- MATRÍCULA ASIGNADA ---
        $pdf->SetXY($x0 + 4, $y0 + 28);
        $pdf->Cell(25, 5, $matricula['matriculaasignada'], 0, 0, 'L');

        // --- APROBADO ---
        $pdf->SetXY($x0 + 32, $y0 + 28);
        $fecha_aprobado = date('d/m/Y', strtotime($matricula['aprobado'])) ;
        $pdf->Cell(18, 5, $fecha_aprobado, 0, 0, 'L');
/*
        $tmpdir = '/tmp/';
        // --- QR ---
        $qr_text = 'https://www.coprobilp.org.ar/credencial/' . urlencode($matricula['matriculaasignada']);
        $temp_qr = tempnam( $tmpdir, 'qr_') . '.png';
        
       
        QRcode::png($qr_text, $temp_qr, QR_ECLEVEL_L, 3);

        $pdf->Image($temp_qr, $x0 + 53, $y0 + 28, 19, 19);
        unlink($temp_qr);
*/

        $prot = $_SERVER['HTTPS'] ?? 'off' ? 'https://' : 'http://';
        //$prot = '';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $url = $prot . $host . '/credencial/' . urlencode($matricula['matriculaasignada']);
        $localfile = $_SESSION['directoriobase'] .'/tmp/qr_' . $matricula['matriculaasignada'] . '.png';
        $this->generarQR( $url , $localfile, 2400    );
        $image = imagecreatefrompng($localfile);

        $converted = $_SESSION['directoriobase'] . '/tmp/qr_final.png';

        $bg = imagecreatetruecolor(300, 300);
        $white = imagecolorallocate($bg, 255, 255, 255);
        imagefilledrectangle($bg, 0, 0, 300, 300, $white);
        imagecopyresampled($bg, $image, 0, 0, 0, 0, 300, 300, imagesx($image), imagesy($image));
/*
        echo "URL del QR: $url<br>";
        echo "Archivo local: $localfile<br>";   
        echo "Archivo convertido: $converted<br>";
        echo "bg=" . (file_exists($bg) ? "Sí" : "No") . "<br>";
        echo "image=" . (file_exists($image) ? "Sí" : "No") . "<br>";
        die();
*/
        imagepng($bg, $converted);

        imagedestroy($image);
        imagedestroy($bg);

/*
echo "Ruta del QR: $converted<br>";
echo "Existe: " . (file_exists($converted) ? "Sí" : "No") . "<br>";
echo "Tamaño: " . filesize($converted) . " bytes<br>";
$info = getimagesize($converted);
var_dump($info);
die();
*/
        $pdf->Image($converted, $x0 + 53, $y0 + 28, 19, 19);
/*
QRHelper::embedToPdf(
    $pdf,
    $url ,
    $x0 + 54,
    $y0 + 28,
    18,
    $_SESSION['directoriobase'] . '/public/img/favicon.png' // o null si no querés ícono
);
*/

        //die();
        $pdf->Output('I', 'credencial_' . $matricula['matriculaasignada'] . '.pdf');

        unlink($localfile); // Eliminar el archivo QR temporal


        /*$imagick = new Imagick();
        $imagick->setResolution(150, 150); // Ajustar calidad de la imagen
        $imagick->readImage('archivo.pdf[0]'); // [0] para la primera página
        $imagick->setImageFormat('png');
        $imagick->writeImage('salida.png'); //grabar en el directorio privado del usuario
        $imagick->clear();
        $imagick->destroy();*/
    }


public static function generarQR(string $contenido, string $rutaDestino, int $tamanioPx = 1800): void
{
    $renderer = new ImageRenderer(
        new RendererStyle($tamanioPx),
        new ImagickImageBackEnd()
    );

    $writer = new Writer($renderer);
    file_put_contents($rutaDestino, $writer->writeString($contenido));
}
    public function vercredencial()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
        }

        $id = (int)($_SESSION['user']['id'] ?? 0);

        $localmat = Matricula::findByUserId($id);
        if ($localmat == null) {
            Session::flash('error', 'Matrícula no encontrada.');
            $this->redirect('/dashboard');
        }
        if ($localmat['matriculaasignada'] == null) {
            Session::flash('error', 'Matrícula no asignada.');
            $this->redirect('/dashboard');
        }
        if ($localmat['matriculaasignada'] != 0) {
            $request = new \App\Core\Request();
            $this->emitircredencial( $request, [$localmat['matriculaasignada']]);
            //$this->redirect('/credencial/' . $localmat['matriculaasignada'] );
        } else {
            Session::flash('error', 'Matrícula no asignada.');
            $this->redirect('/dashboard');
        }

    }
    public function browse4matricula(Request $request, array $params = []): void
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

        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        // si es el presi o el vice, el array puede otorgar matricula
        if (Comision::espresidente($user['id']) || Comision::esvicepresidente($user['id'])) {
            $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/otorgar/paraotorgar.php';
        } else {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/dashboard');
        }

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

        $this->pendingquery= str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, 'm.user_id');
        $zcolumns =  Self::mkColumns($campos, $actividades);
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
            'link_id' => 'user_id',
            'scriptjs_data' => $this->pendingquery,
            'scriptjs_columns' => $this->pendingcolumns,
            'zcolumns' => $zcolumns,
            'url_data' => $_SESSION['base_url']. $cfg['url_data'],


            ]);
    }
    
public function padron4matriculaview(Request $request): void
{
    // 1) Informa errores

    // 2) Inicia sesión si es necesario
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];

    $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/otorgar/paraotorgar.php';
    $id_field = $cfgedit['config']['field_id'];

    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
    $order = $cfgedit['QrySpec']['order'] ?? [];
    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';
    $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, 'm.user_id');
    $resultset = $this->documentService->customQuery($query);

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

public function bajasview(Request $request): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];
        
    $cfgedit     = require $_SESSION['directoriobase'] . '/views/matriculas/debaja.php';

    //$cfgedit     = require $_SESSION['directoriobase'] . '/config/datasources/matriculados.php';
    $id_field = $cfgedit['config']['field_id'];


    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
    $order = $cfgedit['QrySpec']['order'] ?? [];
    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';
    $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, 'm.user_id');
    
    
    
    $resultset = $this->documentService->customQuery($query);

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
    public function bajasbrowse(Request $request, array $params = []): void
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

        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        // si es el presi o el vice, el array puede otorgar matricula
        $cfgedit     = require $_SESSION['directoriobase'] . '/views/matriculas/debaja.php';

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

        $this->pendingquery= str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, 'm.user_id');
//        echo ".......................::";
//        die();

//        $jscampos = [];
//        $jscampos = json_encode(quitaaliascampos($campos));

        $zcolumns =  Self::mkcolumns($campos, $actividades);
        $zcolumns =   trim(stripslashes($zcolumns), '"');

        $datos = DatosPersonales::CustomQry($this->pendingquery);

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


            ]);
    }







}
