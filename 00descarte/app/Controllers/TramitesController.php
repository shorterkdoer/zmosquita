<?php

namespace App\Controllers;

use App\Core\Controller;
use Foundation\Core\Request;
use Foundation\Core\Session;
use App\Models\DatosPersonales;
use App\Models\Tramites;
use App\Support\Sanitizer;
use App\Services\TramiteService;
use App\Services\MatriculaService;
use App\Services\EmailService;

/**
 * TramitesController - Handles tramite-related HTTP requests
 *
 * Refactored to use Service Layer for business logic
 */
class TramitesController extends Controller
{
    protected TramiteService $tramiteService;
    protected MatriculaService $matriculaService;
    protected EmailService $emailService;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Initialize services
        $this->emailService = new EmailService();
        $this->tramiteService = new TramiteService($this->emailService);
        $this->matriculaService = new MatriculaService($this->tramiteService, $this->emailService);
    }

    public function adminaspirantes(Request $request, array $params = []): void
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

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/f1/aspirantes.php';



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

        //$datos = Tramites::CustomQry($this->pendingquery);

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
    
public function aspirantesview(Request $request): void
{
    // 1) Informa errores

    // 2) Inicia sesión si es necesario
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];

    $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/f1/aspirantes.php';
    $id_field = $cfgedit['config']['field_id'];

    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
    $order = $cfgedit['QrySpec']['order'] ?? [];
    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';
    $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, 'm.user_id');
    $resultset = $this->tramiteService->customQuery($query);

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



    public function revisorwrite(Request $request, array $params = [])
    {
//llamar al form para confirmar asignar el revisor

    
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $logg_user = $_SESSION['user'] ?? null;
        if (!$logg_user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
        }

        $id = (int)($params[0] ?? 0); //el id del solicitante
        
        if ($logg_user['role'] == 'user' ) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
        }
        //$user = User::findByEmail($email);

        // Se busca el registro de datospersonales por el user_id.
        $datos = DatosPersonales::findByUserId($id);
        //$revisor = $logg_user['id'];
        /*
        $datos = DatosPersonales::CustomQry(
            "SELECT d.*, u.email FROM datospersonales d, users u WHERE d.user_id = $id and d.user_id = u.id"
        );
*/
        
        if($datos == null) {
            Session::flash('error', 'No se encontraron datos personales para este usuario.');
            $this->redirect('/dashboard');
        }
        
        // Si no existe el registro, podrías crearlo de forma automática.
        // manejar el error si no existe
        
        // Pasar todas las variables a la vista

        $mailsolicitante = User::GetEmail($id);
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/f1/asignarrevisor.php';
        $cfg         = $cfgedit['config']    ?? [];
        $id_field = $cfgedit['config']['field_id'];
        $cfg['url_action'] .= '/' . $id;

        $campos      = $cfgedit['campos']    ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos    = $cfgedit['comandos']  ?? [];
        $buttons     = $cfgedit['buttons']   ?? [];
        $tables      = $cfgedit['QrySpec']['tables'] ?? [];
        $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
        $filter      = $cfgedit['QrySpec']['filter'] ?? '';
        $filter .= " AND m.user_id = " . $id;
        $order       = $cfgedit['QrySpec']['order'] ?? [];

        require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';

        $this->pendingquery= str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, 'm.user_id');
        $zcolumns =  Self::mkcolumns($campos, $actividades);
        $zcolumns =   trim(stripslashes($zcolumns), '"');

        //$datos = Tramites::CustomQry($this->pendingquery);

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
            //'url_data' => $_SESSION['base_url']. $cfg['url_data'],



            
            ]);

/*
        $datos = ['email' => $mailsolicitante];
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
            'user_id' => $id,

        ]);
*/   
        //$tablaHTML = renderTablaHTML($config, $datos, $provincias, $ciudades);
    }

    public function fijarevisor(Request $request, array $params = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
            return;
        }
        if ($user['role'] !== 'admin') {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
            return;
        }

        $matriculado = (int)($params[0] ?? 0);
        $revisor = $_SESSION['user']['id'] ?? 0;

        // Use MatriculaService to assign reviewer
        $result = $this->matriculaService->asignarRevisor($matriculado, $revisor);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            $this->redirect('/controlinscripciones');
            return;
        }

        // Register tramite
        $nombreRevisor = DatosPersonales::GetNombreByUserId($revisor);
        $txttramite = 'Asignación de revisor: ' . ($nombreRevisor ?? 'Administrador');
        $this->tramiteService->registrarTramite($matriculado, $txttramite);

        Session::flash('success', 'Revisor asignado correctamente.');
        $this->redirect('/controlinscripciones');
    }


    public function admin4review(Request $request, array $params = []): void
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

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/f1/asp4revision.php';



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

        //$datos = Tramites::CustomQry($this->pendingquery);

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
   
    
    public function fijarverificador(Request $request, array $params = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
            return;
        }
        if ($user['role'] !== 'admin') {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
            return;
        }

        $matriculado = (int)($params[0] ?? 0);
        $verificador = $_SESSION['user']['id'] ?? 0;

        // Use MatriculaService to assign verifier
        $result = $this->matriculaService->asignarVerificador($matriculado, $verificador);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            $this->redirect('/controlinscripciones');
            return;
        }

        // Register tramite
        $nombreVerificador = DatosPersonales::GetNombreByUserId($verificador);
        $txttramite = 'Asignación de verificador: ' . ($nombreVerificador ?? 'Administrador');
        $this->tramiteService->registrarTramite($matriculado, $txttramite);

        Session::flash('success', 'Verificador asignado correctamente.');
        $this->redirect('/controlinscripciones');
    }


    public function adminagenda(Request $request, array $params = []): void
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

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/citas/agendadecitas.php';



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

        //$datos = Tramites::CustomQry($this->pendingquery);

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
   

public function m4fisico(Request $request): void
{
    // 1) Informa errores

    // 2) Inicia sesión si es necesario
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];

    $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/citas/agendadecitas.php';
    $id_field = $cfgedit['config']['field_id'];

    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
    $order = $cfgedit['QrySpec']['order'] ?? [];
    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';
    $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, 'm.user_id');
    $resultset = $this->tramiteService->customQuery($query);

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



    
public function m4review(Request $request): void

{
    // 1) Informa errores

    // 2) Inicia sesión si es necesario
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];

    $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/f2/asp4revision.php';
    $id_field = $cfgedit['config']['field_id'];

    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
    $order = $cfgedit['QrySpec']['order'] ?? [];
    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';
    $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, 'm.user_id');
    $resultset = $this->tramiteService->customQuery($query);

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







    public function verificadorwrite(Request $request, array $params = [])
    {
   
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $logg_user = $_SESSION['user'] ?? null;
        if (!$logg_user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
        }

        $id = (int)($params[0] ?? 0); //el id del solicitante
        
        if ($logg_user['role'] == 'user' ) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
        }
        //$user = User::findByEmail($email);

        // Se busca el registro de datospersonales por el user_id.
        $datos = DatosPersonales::findByUserId($id);
        //$revisor = $logg_user['id'];
        
        if($datos == null) {
            Session::flash('error', 'No se encontraron datos personales para este usuario.');
            $this->redirect('/dashboard');
        }
        
        // Si no existe el registro, podrías crearlo de forma automática.
        // manejar el error si no existe
        
        // Pasar todas las variables a la vista

        $mailsolicitante = User::GetEmail($id);
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/f2/asignarverificador.php';

        $cfg         = $cfgedit['config']    ?? [];
        $id_field = $cfgedit['config']['field_id'];
        $cfg['url_action'] .= '/' . $id;

        $campos      = $cfgedit['campos']    ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos    = $cfgedit['comandos']  ?? [];
        $buttons     = $cfgedit['buttons']   ?? [];
        $tables      = $cfgedit['QrySpec']['tables'] ?? [];
        $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
        $filter      = $cfgedit['QrySpec']['filter'] ?? '';
        $filter .= " AND m.user_id = " . $id;
        $order       = $cfgedit['QrySpec']['order'] ?? [];

        require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';

        $this->pendingquery= str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, 'm.user_id');
        $zcolumns =  Self::mkcolumns($campos, $actividades);
        $zcolumns =   trim(stripslashes($zcolumns), '"');

        //$datos = Tramites::CustomQry($this->pendingquery);

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
            //'url_data' => $_SESSION['base_url']. $cfg['url_data'],

            ]);

        //$tablaHTML = renderTablaHTML($config, $datos, $provincias, $ciudades);
    }

    public function borrarrevisor(Request $request, array $params = [])
    {
//llamar al form para confirmar asignar el revisor

    
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $logg_user = $_SESSION['user'] ?? null;
        if (!$logg_user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
        }

        $id = (int)($params[0] ?? 0); //el id del solicitante
        
        if ($logg_user['role'] == 'user' ) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
        }
        //$user = User::findByEmail($email);

        // Se busca el registro de datospersonales por el user_id.
        $datos = DatosPersonales::findByUserId($id);
        //$revisor = $logg_user['id'];
        /*
        $datos = DatosPersonales::CustomQry(
            "SELECT d.*, u.email FROM datospersonales d, users u WHERE d.user_id = $id and d.user_id = u.id"
        );
*/
        
        if($datos == null) {
            Session::flash('error', 'No se encontraron datos personales para este usuario.');
            $this->redirect('/dashboard');
        }
        
        // Si no existe el registro, podrías crearlo de forma automática.
        // manejar el error si no existe
        
        // Pasar todas las variables a la vista

        $mailsolicitante = User::GetEmail($id);
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/f1/rechazarrevision.php';
        $cfg         = $cfgedit['config']    ?? [];
        $id_field = $cfgedit['config']['field_id'];
        $cfg['url_action'] .= '/' . $id;

        $campos      = $cfgedit['campos']    ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos    = $cfgedit['comandos']  ?? [];
        $buttons     = $cfgedit['buttons']   ?? [];
        $tables      = $cfgedit['QrySpec']['tables'] ?? [];
        $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
        $filter      = $cfgedit['QrySpec']['filter'] ?? '';
        $filter .= " AND m.user_id = " . $id;
        $order       = $cfgedit['QrySpec']['order'] ?? [];

        require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';

        $this->pendingquery= str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, 'm.user_id');
        $zcolumns =  Self::mkColumns($campos, $actividades);
        $zcolumns =   trim(stripslashes($zcolumns), '"');

        //$datos = Tramites::CustomQry($this->pendingquery);

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
            //'url_data' => $_SESSION['base_url']. $cfg['url_data'],

            ]);

        //$tablaHTML = renderTablaHTML($config, $datos, $provincias, $ciudades);
    }



    public function fijaverificador(Request $request, array $params = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
            return;
        }
        if ($user['role'] !== 'admin') {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
            return;
        }

        $matriculado = (int)($params[0] ?? 0);
        $revisor = $_SESSION['user']['id'] ?? 0;

        // Use MatriculaService to assign verifier
        $result = $this->matriculaService->asignarVerificador($matriculado, $revisor);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            $this->redirect('/controlinscripciones');
            return;
        }

        // Register tramite
        $nombreRevisor = DatosPersonales::GetNombreByUserId($revisor);
        $txttramite = 'Asignación de verificador (documentos físicos): ' . ($nombreRevisor ?? 'Administrador');
        $this->tramiteService->registrarTramite($matriculado, $txttramite);

        Session::flash('success', 'Verificador asignado correctamente.');
        $this->redirect('/controlinscripciones');
    }

    public function rechazarrevision(Request $request, array $params = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
            return;
        }
        if ($user['role'] !== 'admin') {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
            return;
        }

        $matriculado = (int)($params[0] ?? 0);
        $revisor = $_SESSION['user']['id'] ?? 0;
        $txtmotivo = Sanitizer::text($_POST['observaciones'] ?? '');

        // Clear revision status
        $result = $this->matriculaService->clearRevisionStatus($matriculado, $revisor);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            $this->redirect('/controlinscripciones');
            return;
        }

        // Reject revision and notify user
        $result = $this->tramiteService->rechazarRevision($matriculado, $revisor, $txtmotivo, false);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            $this->redirect('/controlinscripciones');
            return;
        }

        Session::flash('success', 'Rechazo registrado correctamente.');
        $this->redirect('/controlinscripciones');
    }

public static function reenviarmails():void
{

    //[[]]
/*
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $datos = Tramites::CustomQry('SELECT t.*, u.email FROM tramites t, users u where t.user_id = u.id and observaciones like'. "'%Revision rechazada%'". 'order by user_id');
    foreach($datos as $dato) {
        $email = $dato['email'];
        $subject = 'Revisión de documentación';
        $txtmotivo = $dato['observaciones'];
        $body = 'Su solicitud de revisión ha sido rechazada. El motivo informado: ' . $txtmotivo;

        //AuthController::GeneralEmail($email, $subject, $body);
        AuthController::GeneralEmail($email, $subject, $body);

    }
*/


}

    public function admin4revi(Request $request, array $params = []): void
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
            $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/f2/asp4revision.php';

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
    

    public function admin4verificacion(Request $request, array $params = []): void
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
            $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/rev4verificacion.php';

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
    
public function m4verificacion(Request $request): void
{
    // 1) Informa errores

    // 2) Inicia sesión si es necesario
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];

    $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/rev4verificacion.php';
    $id_field = $cfgedit['config']['field_id'];

    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
    $order = $cfgedit['QrySpec']['order'] ?? [];
    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';
    $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, 'm.user_id');
    $resultset = $this->tramiteService->customQuery($query);

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











    public function mainview(): void
    {
        /* revisar los paths */
        //$this->makegrid('{$viewPath}/index.php');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];
    $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/index.php';
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
    $resultset = $this->tramiteService->customQuery($query);
    $this->pendingquery = $query; // Guardamos la consulta pendiente para usarla en el script JS
    $this->pendingcolumns = json_encode($campos); // Guardamos los campos pendientes para usarlos en el script JS
    // $this->pendingcolumns = $campos;
    $comandos = $cfgedit['comandos'] ?? [];
    $buttons = $cfgedit['buttons'] ?? [];
        // Ejecuta la consulta y obtiene los datos
    $datos = $this->tramiteService->customQuery($query);
    $zcolumns = Self::mkcolumns($campos, $actividades);
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
            $this->redirect('/tramites'); // Redirect to the main view if no ID is provided
            return;
        }
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];
        $cfgcreate = require $_SESSION['directoriobase'] . '/config/cruds/tramites/create.php';
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
        $datos = DatosPersonales::findByUserId($id);
        // Si no existe el registro, podrías crearlo de forma automática.
        if (!$datos) {
            DatosPersonales::create(['user_id' => $id]);
            $datos = DatosPersonales::findByUserId($id);
        }

        // buscar las tablas relacionadas
        // $provincias = Provincia::all();
        // $ciudades = Ciudad::all();
        
        // Pasar todas las variables a la vista

        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/edit.php';
        $cfg         = $cfgedit['config']    ?? [];
        $cfg['url_action'] .= '/' . $id; // <— se agrega el id a la url
        $campos      = $cfgedit['campos']    ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos    = $cfgedit['comandos']  ?? [];
        $buttons     = $cfgedit['buttons']   ?? [];
        $campos['provincia_id']['options'] = $provincias;
        $campos['ciudad_id']['options']    = $ciudades;

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
            $this->redirect('/tramites/edit/' . $id);
            return;
        }

        if (!$this->tramiteService->find((int)$id)) {
            Session::flash('error', 'Comprobante no encontrado.');
            $this->redirect('/dashboard');
            return;
        }

        $this->tramiteService->update((int)$id, ['nombre' => $nombre]);
        Session::flash('success', 'Comprobante actualizada.');
        $this->redirect('/miscomprobantes');
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
        if ($user['role'] <> 'admin' ) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
        }

        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        // si es el presi o el vice, el array puede otorgar matricula
        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/legajo/legajo.php';

        $cfg         = $cfgedit['config']    ?? [];
        $id_field = $cfgedit['config']['field_id'];
        $_SESSION['legajo'] = (int)($params[0] ?? 0);
        $campos      = $cfgedit['campos']    ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos    = $cfgedit['comandos']  ?? [];
        $buttons     = $cfgedit['buttons']   ?? [];
        $tables      = $cfgedit['QrySpec']['tables'] ?? [];
        $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
        $filter      = $cfgedit['QrySpec']['filter'] ?? '';
        $filter .= " (t.user_id = " . $_SESSION['legajo'] .")" ?? 0;   
        $order       = $cfgedit['QrySpec']['order'] ?? [];

        require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';

        $this->pendingquery= str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, $id_field);
        $zcolumns =  Self::mkcolumns($campos, $actividades);
        $zcolumns =   trim(stripslashes($zcolumns), '"');
        $this->pendingcolumns = $zcolumns;



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
            'link_id' => 'id',
            'scriptjs_data' => $this->pendingquery,
            'scriptjs_columns' => $this->pendingcolumns,
            'zcolumns' => $zcolumns,
            'url_data' => $_SESSION['base_url']. $cfg['url_data'],


            ]);
    }
    
public function legajodata(Request $request): void
{
    // 1) Informa errores

    // 2) Inicia sesión si es necesario
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];

    $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/legajo/legajo.php';
    $id_field = $cfgedit['config']['field_id'];

    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
        $filter .= " (t.user_id = " . $_SESSION['legajo'] .")" ?? 0;   
    $order = $cfgedit['QrySpec']['order'] ?? [];
    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';
    $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, $id_field);
    $resultset = $this->tramiteService->customQuery($query);

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

        $comprob = $this->tramiteService->find((int)$id);
        if (!$comprob) {
            Session::flash('error', 'Comprobante no encontrado.');
            $this->redirect('/miscomprobantes');
            return;
        }

        $this->tramiteService->delete((int)$id);
        Session::flash('success', 'Comprobante eliminado.');
        $this->redirect('/miscomprobantes');
    }
    // para destinos futuros
    public function grid(): void
    {
    }





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

    // *** Aquí NO usamos json_encode, devolvemos el JS tal cual ***
    return implode(",
    ", $columns);
}


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

    return $cols;
}

    public function aspirantes(Request $request): void
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
        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/aspirantes.php';

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
    //$jscampos = quitaaliascampos($campos); // Eliminamos los alias de los campos para que se muestren correctamente en el script JS
    $this->pendingcolumns = json_encode($zcolumns); // Guardamos los campos pendientes para usarlos en el script JS

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



    public function dataaspirantes(Request $request): void
{
    // 1) Informa errores

    // 2) Inicia sesión si es necesario
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];

    $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/aspirantes.php';
    $id_field = $cfgedit['config']['field_id'];

    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
    $order = $cfgedit['QrySpec']['order'] ?? [];
    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';
    $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, 'm.user_id');
    $resultset = $this->tramiteService->customQuery($query);

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

    public function admin4fisico(Request $request, array $params = []): void
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

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/fisica/pararevisionfisica.php';
                                                                                            


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
//echo $this->pendingquery;
//die();
        //$datos = Tramites::CustomQry($this->pendingquery);

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
            'scriptjs_columns' => $zcolumns,
            'zcolumns' => $zcolumns,
            'url_data' => $_SESSION['base_url']. $cfg['url_data'],



            
            ]);
    }
    
public function data4fisico(Request $request): void
{
    // 1) Informa errores

    // 2) Inicia sesión si es necesario
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];

    $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/fisica/pararevisionfisica.php';
    $id_field = $cfgedit['config']['field_id'];

    
    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
    $order = $cfgedit['QrySpec']['order'] ?? [];
    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';
    $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, 'm.user_id');
    $resultset = DatosPersonales::CustomQry($query);

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

    public function paractrlfisico(Request $request, array $params = []): void
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

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/fasefinal/aspirantes.php';



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

        //$datos = Tramites::CustomQry($this->pendingquery);

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
    
public function fisicoview(Request $request): void
{
    // 1) Informa errores

    // 2) Inicia sesión si es necesario
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];

    $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/fasefinal/aspirantes.php';
    $id_field = $cfgedit['config']['field_id'];

    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
    $order = $cfgedit['QrySpec']['order'] ?? [];
    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';
    $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, 'm.user_id');
    $resultset = $this->tramiteService->customQuery($query);

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

    public function borrarverif(Request $request, array $params = [])
    {
//llamar al form para confirmar asignar el revisor

    
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $logg_user = $_SESSION['user'] ?? null;
        if (!$logg_user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
        }

        $id = (int)($params[0] ?? 0); //el id del solicitante
        
        if ($logg_user['role'] == 'user' ) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
        }
        //$user = User::findByEmail($email);

        // Se busca el registro de datospersonales por el user_id.
        $datos = DatosPersonales::findByUserId($id);
        //$revisor = $logg_user['id'];
        /*
        $datos = DatosPersonales::CustomQry(
            "SELECT d.*, u.email FROM datospersonales d, users u WHERE d.user_id = $id and d.user_id = u.id"
        );
*/
        
        if($datos == null) {
            Session::flash('error', 'No se encontraron datos personales para este usuario.');
            $this->redirect('/dashboard');
        }
        
        // Si no existe el registro, podrías crearlo de forma automática.
        // manejar el error si no existe
        
        // Pasar todas las variables a la vista

        $mailsolicitante = User::GetEmail($id);
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/f2/rechazarrevision.php';
        $cfg         = $cfgedit['config']    ?? [];
        $id_field = $cfgedit['config']['field_id'];
        $cfg['url_action'] .= '/' . $id;

        $campos      = $cfgedit['campos']    ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos    = $cfgedit['comandos']  ?? [];
        $buttons     = $cfgedit['buttons']   ?? [];
        $tables      = $cfgedit['QrySpec']['tables'] ?? [];
        $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
        $filter      = $cfgedit['QrySpec']['filter'] ?? '';
        $filter .= " AND m.user_id = " . $id;
        $order       = $cfgedit['QrySpec']['order'] ?? [];

        require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';

        $this->pendingquery= str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, 'm.user_id');
        $zcolumns =  Self::mkColumns($campos, $actividades);
        $zcolumns =   trim(stripslashes($zcolumns), '"');

        //$datos = Tramites::CustomQry($this->pendingquery);

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
            //'url_data' => $_SESSION['base_url']. $cfg['url_data'],

            ]);

        //$tablaHTML = renderTablaHTML($config, $datos, $provincias, $ciudades);
    }

    public function rechazarctrlfisico(Request $request, array $params = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
            return;
        }
        if ($user['role'] !== 'admin') {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
            return;
        }

        $matriculado = (int)($params[0] ?? 0);
        $revisor = $_SESSION['user']['id'] ?? 0;
        $txtmotivo = Sanitizer::text($_POST['observaciones'] ?? '');

        // Clear all verification statuses
        $result = $this->matriculaService->clearAllVerificationStatuses($matriculado, $revisor);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            $this->redirect('/acontrolfisico');
            return;
        }

        // Reject revision with notification
        $result = $this->tramiteService->rechazarRevision($matriculado, $revisor, $txtmotivo, true);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            $this->redirect('/acontrolfisico');
            return;
        }

        Session::flash('success', 'Rechazo registrado correctamente.');
        $this->redirect('/acontrolfisico');
    }


    public function ok2fisico(Request $request, array $params = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
            return;
        }
        if ($user['role'] !== 'admin') {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
            return;
        }

        $matriculado = (int)($params[0] ?? 0);
        $revisor = $_SESSION['user']['id'] ?? 0;
        $txtmotivo = Sanitizer::text($_POST['observaciones'] ?? '');

        // Approve physical verification
        $result = $this->matriculaService->aprobarVerificacionFisica($matriculado, $revisor);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            $this->redirect('/acontrolfisico');
            return;
        }

        // Send notification
        $this->tramiteService->aprobarVerificacionFisica($matriculado, $revisor, $txtmotivo);

        Session::flash('success', 'Aprobación registrada correctamente.');
        $this->redirect('/acontrolfisico');
    }


    public function ok2matricular(Request $request, array $params = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
            return;
        }
        if ($user['role'] !== 'admin') {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
            return;
        }

        $matriculado = (int)($params[0] ?? 0);
        $revisor = $_SESSION['user']['id'] ?? 0;

        // Use MatriculaService to approve physical verification
        $result = $this->matriculaService->aprobarVerificacionFisica($matriculado, $revisor);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            $this->redirect('/acontrolfisico');
            return;
        }

        // Register tramite
        $nombreRevisor = DatosPersonales::GetNombreByUserId($revisor);
        $txttramite = 'Revisor: ' . ($nombreRevisor ?? 'Administrador') . ' Control físico aprobado.';
        $this->tramiteService->registrarTramite($matriculado, $txttramite);

        Session::flash('success', 'Aprobado el control físico de la documentación.');
        $this->redirect('/acontrolfisico');
    }

    public function rechazarfisico(Request $request, array $params = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
            return;
        }
        if ($user['role'] !== 'admin') {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
            return;
        }

        $matriculado = (int)($params[0] ?? 0);
        $revisor = $_SESSION['user']['id'] ?? 0;
        $txtmotivo = Sanitizer::text($_POST['observaciones'] ?? '');

        // Clear revision status
        $result = $this->matriculaService->clearRevisionStatus($matriculado, $revisor);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            $this->redirect('/controlinscripciones');
            return;
        }

        // Reject revision and notify
        $result = $this->tramiteService->rechazarRevision($matriculado, $revisor, $txtmotivo, false);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            $this->redirect('/controlinscripciones');
            return;
        }

        Session::flash('success', 'Rechazo registrado correctamente.');
        $this->redirect('/controlinscripciones');
    }


}