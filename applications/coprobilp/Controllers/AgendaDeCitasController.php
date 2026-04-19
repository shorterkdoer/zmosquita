<?php

namespace App\Controllers;

use App\Core\Controller;
use Foundation\Core\Request;
use Foundation\Core\Session;
use App\Support\Sanitizer;
use App\Services\CitaService;
use App\Services\EmailService;
use Exception;

/**
 * AgendaDeCitasController - Handles appointment scheduling UI
 *
 * Refactored to use Service Layer for business logic
 */
class AgendaDeCitasController extends Controller
{
    protected CitaService $citaService;
    protected EmailService $emailService;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->citaService = new CitaService();
        $this->emailService = new EmailService();
    }
    
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

    $cfgedit = require $_SESSION['directoriobase'] . '/config/cruds/tramites/citas/agendadecitas.php';
    $id_field = $cfgedit['config']['field_id'];
    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
    $order = $cfgedit['QrySpec']['order'] ?? [];

    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';

    try {
        $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, $id_field);

        error_log("Query de agenda: $query", 0);

        $resultset = $this->citaService->customQuery($query);

        $results = [
            "sEcho" => 1,
            "iTotalRecords" => count($resultset),
            "iTotalDisplayRecords" => count($resultset),
            "aaData" => $resultset,
            "data" => $resultset
        ];

        header('Content-Type: application/json');
        echo json_encode($results);
        exit;

    } catch (Exception $e) {
        error_log("Error in agendaview: " . $e->getMessage(), 0);

        $sqlError = $this->citaService->getLastError();
        error_log("SQL Error: " . $sqlError, 0);

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
        return;
    }
    if ($user['role'] <> 'admin') {
        Session::flash('error', 'No tiene permiso para editar estos datos.');
        $this->redirect('/user-dashboard');
        return;
    }

    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];

    $cfgedit = require $_SESSION['directoriobase'] . '/config/cruds/tramites/citas/nuevacita.php';
    $cfg = $cfgedit['config'] ?? [];
    $id_field = $cfgedit['config']['field_id'];

    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $comandos = $cfgedit['comandos'] ?? [];
    $buttons = $cfgedit['buttons'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
    $order = $cfgedit['QrySpec']['order'] ?? [];

    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';

    $datos = [];

    // Generate dropdown lists using service
    $matriculadosList = $this->citaService->getMatriculadosDropdown($campos['matriculado']['options']);
    $campos['matriculado']['listavalores'] = $matriculadosList;

    $this->pendingquery = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, $id_field);

    $zcolumns = Self::mkColumns($campos, $actividades);
    $zcolumns = trim(stripslashes($zcolumns), '"');

    $this->view('cruds/index', [
        'cfg' => $cfg,
        'fields' => $campos,
        'style' => $style,
        'values' => $datos,
        'actions' => $actividades,
        'comandos' => $comandos,
        'buttons' => $buttons,
        'divname' => $cfg['divname'],
        'id' => 'id',
        'link_id' => 'user_id',
        'scriptjs_data' => $this->pendingquery,
        'scriptjs_columns' => $this->pendingcolumns,
        'zcolumns' => $zcolumns,
        'url_data' => $_SESSION['base_url'] . $cfg['url_data'],
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
            return;
        }

        if ($user['role'] <> 'admin') {
            Session::flash('error', 'No tiene permiso para crear citas.');
            $this->redirect('/user-dashboard');
            return;
        }

        $data = [
            'funcionario' => $user['id'],
            'matriculado' => $_POST['matriculado'] ?? '',
            'fecha' => $_POST['fecha'] ?? '',
            'hora' => $_POST['hora'] ?? '',
            'motivo' => Sanitizer::text($_POST['motivo'] ?? ''),
        ];

        $result = $this->citaService->createWithNotification($data, $this->emailService);

        if ($result['success']) {
            Session::flash('success', 'Cita agendada exitosamente.');
            $this->redirect('/agendarcita');
        } else {
            Session::flash('error', $result['error'] ?? 'Error al agendar la cita.');
            $this->redirect('/agendadecitas/create');
        }
    }


public function reenviarcita(Request $request, array $params = []): void
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

    if ($user['role'] <> 'admin') {
        Session::flash('error', 'No tiene permiso.');
        $this->redirect('/dashboard');
        return;
    }

    $idcita = (int)($params[0] ?? 0);

    $result = $this->citaService->resendNotification($idcita, $this->emailService);

    if ($result['success']) {
        Session::flash('success', 'Mail enviado.');
    } else {
        Session::flash('error', $result['error'] ?? 'Error al enviar email.');
    }

    $this->redirect('/agendarcita');
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
        return;
    }
    if ($user['role'] <> 'admin') {
        Session::flash('error', 'No tiene permiso para editar estos datos.');
        $this->redirect('/user-dashboard');
        return;
    }

    $idcita = (int)($params[0] ?? 0);

    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];

    $cfgedit = require $_SESSION['directoriobase'] . '/config/cruds/tramites/citas/detallecita.php';
    $cfg = $cfgedit['config'] ?? [];
    $id_field = $cfgedit['config']['field_id'];

    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $comandos = $cfgedit['comandos'] ?? [];
    $buttons = $cfgedit['buttons'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
    $order = $cfgedit['QrySpec']['order'] ?? [];

    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';

    $datos = $this->citaService->find($idcita);

    // Generate dropdown lists using service
    $matriculadosList = $this->citaService->getMatriculadosDropdown($campos['matriculado']['options']);
    $campos['matriculado']['listavalores'] = $matriculadosList;

    $this->pendingquery = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, $id_field);

    $zcolumns = Self::mkColumns($campos, $actividades);
    $zcolumns = trim(stripslashes($zcolumns), '"');

    $this->view('cruds/index', [
        'cfg' => $cfg,
        'fields' => $campos,
        'style' => $style,
        'values' => $datos,
        'actions' => $actividades,
        'comandos' => $comandos,
        'buttons' => $buttons,
        'divname' => $cfg['divname'],
        'id' => 'id',
        'link_id' => 'user_id',
        'scriptjs_data' => $this->pendingquery,
        'scriptjs_columns' => $this->pendingcolumns,
        'zcolumns' => $zcolumns,
        'url_data' => $_SESSION['base_url'] . $cfg['url_data'],
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
        return;
    }
    if ($user['role'] <> 'admin') {
        Session::flash('error', 'No tiene permiso para editar estos datos.');
        $this->redirect('/user-dashboard');
        return;
    }

    $idcita = (int)($params[0] ?? 0);

    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];

    $cfgedit = require $_SESSION['directoriobase'] . '/config/cruds/tramites/citas/eliminarcita.php';
    $cfg = $cfgedit['config'] ?? [];
    $id_field = $cfgedit['config']['field_id'];

    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $comandos = $cfgedit['comandos'] ?? [];
    $buttons = $cfgedit['buttons'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
    $order = $cfgedit['QrySpec']['order'] ?? [];

    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';

    $datos = $this->citaService->find($idcita);

    // Generate dropdown lists using service
    $matriculadosList = $this->citaService->getMatriculadosDropdown($campos['matriculado']['options']);
    $campos['matriculado']['listavalores'] = $matriculadosList;

    $this->pendingquery = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, $id_field);

    $zcolumns = Self::mkColumns($campos, $actividades);
    $zcolumns = trim(stripslashes($zcolumns), '"');

    $this->view('cruds/index', [
        'cfg' => $cfg,
        'fields' => $campos,
        'style' => $style,
        'values' => $datos,
        'actions' => $actividades,
        'comandos' => $comandos,
        'buttons' => $buttons,
        'divname' => $cfg['divname'],
        'id' => 'id',
        'link_id' => 'user_id',
        'scriptjs_data' => $this->pendingquery,
        'scriptjs_columns' => $this->pendingcolumns,
        'zcolumns' => $zcolumns,
        'url_data' => $_SESSION['base_url'] . $cfg['url_data'],
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
            return;
        }

        if ($user['role'] <> 'admin') {
            Session::flash('error', 'No tiene permiso para borrar citas.');
            $this->redirect('/user-dashboard');
            return;
        }

        $idcita = (int)($params[0] ?? 0);

        $result = $this->citaService->delete($idcita);

        if ($result['success']) {
            Session::flash('success', 'Cita borrada exitosamente.');
        } else {
            Session::flash('error', $result['error'] ?? 'Error al borrar la cita.');
        }

        $this->redirect('/agendarcita');
    }
}
