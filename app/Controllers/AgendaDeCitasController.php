<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\AgendaDeCitas;
use App\Core\Session;
use App\Core\MasterCrud;
use App\Core\AuthMiddleware;
use App\Models\DatosPersonales;
use App\Models\User;
use App\Support\Sanitizer;
use Exception;

class AgendaDeCitasController extends Controller
{
    
    // Muestra la lista de provincias

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

        $cfgedit = require $_SESSION['directoriobase'] . '/config/cruds/tramites/citas/agendadecitas.php';

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
        
        // Initialize datos array
        $datos = [];
        
        // Generate dropdown lists if needed
        $funcionariosList = [];
        $matriculadosList = [];
/*        if (isset($campos['funcionario']['options'])) {
            $funcionariosList = DatosPersonales::HtmlDropDown($campos['funcionario']['options']);
        }*/
        if (isset($campos['nombrematriculado']['options'])) {
            //$matriculadosList = DatosPersonales::HtmlDropDown($campos['nombrematriculado']['options']);
        }

        $this->pendingquery = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, $id_field);
        //error_log("Query de agenda: $this->pendingquery",0, '/var/log/Minimumm/minimumm.log');

        $zcolumns = Self::mkColumns($campos, $actividades);
        $zcolumns = trim(stripslashes($zcolumns), '"');
        $this->pendingcolumns = $zcolumns;

        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $campos,
            'style'    => $style,
            'values'   => $datos,
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
            //'funcionariosList' => $funcionariosList,
            //'matriculadosList' => $matriculadosList,
        ]);
    }




public function agendaview(Request $request): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];
        
    $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/citas/agendadecitas.php';

    //$cfgedit     = require $_SESSION['directoriobase'] . '/config/datasources/matriculados.php';
    $id_field = $cfgedit['config']['field_id'];


    $campos      = $cfgedit['campos']    ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $tables      = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter      = $cfgedit['QrySpec']['filter'] ?? '';
    $order       = $cfgedit['QrySpec']['order'] ?? [];
    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';
        $funcionariosList = [];
        $matriculadosList = [];
        /*if (isset($campos['funcionario']['options'])) {
            $funcionariosList = DatosPersonales::HtmlDropDown($campos['funcionario']['options']);
        }*/
        //if (isset($campos['nombrematriculado']['options'])) {
            //$matriculadosList = DatosPersonales::HtmlDropDown($campos['nombrematriculado']['options']);
        //}

    try {
        //$query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order);
        $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, $id_field);
        
        // Log the query for debugging
        error_log("Query de agenda: $query", 0);
        
        $resultset = AgendaDeCitas::CustomQry($query);
        
        $results = [
            "sEcho" => 1,
            "iTotalRecords" => count($resultset),
            "iTotalDisplayRecords" => count($resultset),
            "aaData" => $resultset,
            "data" => $resultset  // Add data property for newer DataTables versions
        ];

        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
        
    } catch (Exception $e) {
        // Log the error
        error_log("Error in agendaview: " . $e->getMessage(), 0);
        
        // Get SQL error details if available
        $sqlError = AgendaDeCitas::CustomError();
        error_log("SQL Error: " . $sqlError, 0);
        
        // Return error response
        header('Content-Type: application/json');
        echo json_encode([
            "error" => "Database error: " . $e->getMessage(),
            "sql_error" => $sqlError,
            "aaData" => [],
            "data" => []
        ]);
        exit;
    }


    }

public function nuevacita(Request $request, array $params = []): void
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

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/citas/nuevacita.php';
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
        
        // Initialize datos array
        $datos = [];
        
        // Generate dropdown lists if needed
        $matriculadosList = [];
        $matriculadosList = DatosPersonales::HtmlDropDown($campos['matriculado']['options']);

        $campos['matriculado']['listavalores'] = $matriculadosList;
        // Fix function call - add missing id_field parameter
        $this->pendingquery = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, $id_field);

        $zcolumns = Self::mkColumns($campos, $actividades);
        $zcolumns = trim(stripslashes($zcolumns), '"');

        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $campos,
            'style'    => $style,
            'values'   => $datos,
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
            'matriculadosList' => $matriculadosList,
        ]);
    }

    public function store(Request $request, array $params = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
        }
        
        if ($user['role'] <> 'admin') {
            Session::flash('error', 'No tiene permiso para crear citas.');
            $this->redirect('/user-dashboard');
        }
        // Validate required fields
        $matriculado = $_POST['matriculado'] ?? '';
        $fecha = $_POST['fecha'] ?? '';
        $hora = $_POST['hora'] ?? '';
        $motivo = Sanitizer::text($_POST['motivo'] ?? '');
        if (empty($matriculado) || empty($fecha) || empty($hora)) {
            Session::flash('error', 'Todos los campos son obligatorios.');
            $this->redirect('/agendadecitas/create');
            return;
        }

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            Session::flash('error', 'Formato de fecha inválido.');
            $this->redirect('/agendadecitas/create');
            return;
        }

        // Validate time format
        if (!preg_match('/^\d{2}:\d{2}$/', $hora)) {
            Session::flash('error', 'Formato de hora inválido.');
            $this->redirect('/agendadecitas/create');
            return;
        }
        try {
            // Create new appointment
            $data = [
                'funcionario' => $user['id'],
                'matriculado' => $matriculado,
                'fecha' => $fecha,
                'hora' => $hora,
                'motivo' => $motivo,
            ];

            $result = AgendaDeCitas::create($data);
            
            if ($result) {
                $maildelmatriculado = User::GetEmail($matriculado);
                AuthController::GeneralEmail(
                    $maildelmatriculado,
                    'Nueva cita agendada',
                    "Se ha agendado una nueva cita para el día $fecha a las $hora. Motivo: $motivo"
                );
                Session::flash('success', 'Cita agendada exitosamente.');
                $this->redirect('/agendarcita');
            } else {
                Session::flash('error', 'Error al agendar la cita.');
                $this->redirect('/agendadecitas/create');
            }
        } catch (Exception $e) {
            Session::flash('error', 'Error al agendar la cita: ' . $e->getMessage());
            $this->redirect('/agendadecitas/create');
        }
    }


public function reenviarcita(Request $request, array $params = []): void
{
        {
if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
        }
        
        if ($user['role'] <> 'admin') {
            Session::flash('error', 'No tiene permiso.');
            $this->redirect('/dashboard');
        }

        // Validate required fields
        $idcita = (int)($params[0] ?? 0);

        $citareg = AgendaDeCitas::findById($idcita);
        $matriculado = $citareg['matriculado']; 
        $_POST['matriculado'] ?? '';
        $fecha = $citareg['fecha'] ?? '';
        $hora = $citareg['hora'] ?? '';
        $motivo = Sanitizer::text($citareg['motivo'] ?? '');
            
        $maildelmatriculado = User::GetEmail($matriculado);
                AuthController::GeneralEmail(
                    $maildelmatriculado,
                    'Nueva cita agendada',
                    "Se ha agendado una nueva cita para el día $fecha a las $hora. Motivo: $motivo"
                );
                Session::flash('success', 'Mail enviado.');
                $this->redirect('/agendarcita');
            }
}
public function mostrarcita(Request $request, array $params = []): void
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

        $idcita = (int)($params[0] ?? 0);

        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/citas/detallecita.php';
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
        
        // Initialize datos array
        $datos = AgendaDeCitas::findById($idcita);

        // Generate dropdown lists if needed
        $matriculadosList = [];
        $matriculadosList = DatosPersonales::HtmlDropDown($campos['matriculado']['options']);

        $campos['matriculado']['listavalores'] = $matriculadosList;
        // Fix function call - add missing id_field parameter
        $this->pendingquery = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, $id_field);

        $zcolumns = Self::mkColumns($campos, $actividades);
        $zcolumns = trim(stripslashes($zcolumns), '"');

        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $campos,
            'style'    => $style,
            'values'   => $datos,
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
            'matriculadosList' => $matriculadosList,
        ]);
    }

public function borrarcita(Request $request, array $params = []): void
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

        $idcita = (int)($params[0] ?? 0);

        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/citas/eliminarcita.php';
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
        
        // Initialize datos array
        $datos = AgendaDeCitas::findById($idcita);

        // Generate dropdown lists if needed
        $matriculadosList = [];
        $matriculadosList = DatosPersonales::HtmlDropDown($campos['matriculado']['options']);

        $campos['matriculado']['listavalores'] = $matriculadosList;
        // Fix function call - add missing id_field parameter
        $this->pendingquery = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, $id_field);

        $zcolumns = Self::mkColumns($campos, $actividades);
        $zcolumns = trim(stripslashes($zcolumns), '"');

        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $campos,
            'style'    => $style,
            'values'   => $datos,
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
            'matriculadosList' => $matriculadosList,
        ]);
    }

    public function borrar(Request $request, array $params = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
        }
        
        if ($user['role'] <> 'admin') {
            Session::flash('error', 'No tiene permiso para borrar citas.');
            $this->redirect('/user-dashboard');
        }
        $idcita = (int)($params[0] ?? 0);


        $result = AgendaDeCitas::delete($idcita);

        if ($result) {
            Session::flash('success', 'Cita borrada exitosamente.');
            $this->redirect('/agendarcita');
        } else {
            Session::flash('error', 'Error al borrar la cita.');
            $this->redirect('/agendarcita');
        }
    }


}
