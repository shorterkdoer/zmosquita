<?php

namespace App\Controllers;

use Foundation\Core\Request;
use Foundation\Core\Session;
use App\Core\Controller;
use App\Models\Matricula;
use App\Services\PaymentService;

/**
 * ComprobantesPagoController - Handles payment receipts management
 *
 * Refactored to use Service Layer for business logic
 */
class ComprobantesPagoController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Initialize service
        $this->paymentService = new PaymentService();
    }
    // Muestra la lista de comprobantes de pago
    public function index(Request $request): void
    {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];
    $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/comprobantespago/comprobantespago_index.php';
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
    $resultset = $this->paymentService->customQuery($query);

    $this->pendingquery = $query; // Guardamos la consulta pendiente para usarla en el script JS
    $this->pendingcolumns = json_encode($campos); // Guardamos los campos pendientes para usarlos en el script JS
    // $this->pendingcolumns = $campos;
    $comandos = $cfgedit['comandos'] ?? [];
    $buttons = $cfgedit['buttons'] ?? [];
        // Ejecuta la consulta y obtiene los datos

    $datos = $this->paymentService->customQuery($query);
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


    public function vermiscomprobantes(Request $request): void
    {


    // 1) Informa errores

    // 2) Inicia sesión si es necesario
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];

    $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/comprobantespago/comprobantespago_index.php';
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
    $resultset = $this->paymentService->customQuery($query);

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

    public function edit(Request $request, array $params): void
    {
         $id = $params[0] ?? null; // Get the ID from the URL parameters
        if (!$id) {
            Session::flash('error', 'ID de comprobante no especificado.');
            $this->redirect('/miscomprobantes');
            return;
        }

        $datos = $this->paymentService->findById((int)$id);
        if (!$datos) {
            Session::flash('error', 'Comprobante no encontrado.');
            $this->redirect('/miscomprobantes');
            return;
        }
        $datos['userfolder'] = $this->paymentService->getUserFolderRelative($datos['user_id']);
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];
        $cfgcreate = require $_SESSION['directoriobase'] . '/config/cruds/comprobantespago/comprobantespago_ver.php';
        $cfg       = $cfgcreate['config']      ?? [];
        
        //$cfg['url_action'] .= '/' . $id; // <— se agrega el id a la url

        $campos    = $cfgcreate['campos']      ?? [];
        $actividades = $cfgcreate['actividades'] ?? [];
        $comandos    = $cfgcreate['comandos']  ?? [];
        $buttons     = $cfgcreate['buttons']   ?? [];
    
        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'style'    => $style,
            'fields'   => $campos,
            'values'   => $datos,
            'actions'  => $actividades,
            'comandos' => $comandos,
            'buttons'  => $buttons,
            'id'       => $id , // Add the ID here
            'user_id' => $_SESSION['user']['id'], // Add user ID for file upload
        ]);

    }



    // Muestra el formulario para crear una nueva 
    public function create(Request $request, array $params): void
    {
        $id = $params[0] ?? null; // Get the ID from the URL parameters
        if (!$id) {
            Session::flash('error', 'ID de comprobante no especificado.');
            $this->redirect('/miscomprobantes');
            return;
        }
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];
        $cfgcreate = require $_SESSION['directoriobase'] . '/config/cruds/comprobantespago/comprobantespago_create.php';
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


    // Procesa el formulario y guarda la nueva
    public function store(Request $request, array $params): void
    {
        $id = $params[0] ?? null;
        if (!$id) {
            Session::flash('error', 'Algo salió mal!');
            $this->redirect('/miscomprobantes');
            return;
        }

        // Check file is present
        if (!isset($_FILES['comprobante'])) {
            Session::flash('error', 'No se ha seleccionado ningún archivo.');
            $this->redirect('/miscomprobantes');
            return;
        }

        $monto = $_POST['monto'] ?? null;
        $fecha = $_POST['fecha'] ?? null;
        $observaciones = $_POST['observaciones'] ?? null;

        $result = $this->paymentService->uploadWithFile((int)$id, $_FILES['comprobante'], $monto, $fecha, $observaciones);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            $this->redirect('/miscomprobantes');
            return;
        }

        Session::flash('success', 'Comprobante guardado exitosamente.');
        $this->redirect('/miscomprobantes');
    }

    // Procesa el formulario de edición y actualiza la
    public function update(Request $request, array $params): void
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

        $comprobante = $this->paymentService->findById((int)$id);
        if (!$comprobante) {
            Session::flash('error', 'Comprobante no encontrado.');
            $this->redirect('/miscomprobantes');
            return;
        }

        $result = $this->paymentService->update((int)$id, ['nombre' => $nombre]);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            $this->redirect('/miscomprobantes');
            return;
        }

        Session::flash('success', 'Comprobante actualizada.');
        $this->redirect('/miscomprobantes');
    }

    // Elimina una
    public function delete(Request $request, array $params): void
    {
        $id = $params[0] ?? null;
        if (!$id) {
            Session::flash('error', 'ID de comprobante no especificado.');
            $this->redirect('/miscomprobantes');
            return;
        }

        $result = $this->paymentService->delete((int)$id);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            $this->redirect('/miscomprobantes');
            return;
        }

        Session::flash('success', 'Comprobante eliminado.');
        $this->redirect('/miscomprobantes');
    }

    public function vista(Request $request, array $params): void
    {
        $id = $params[0] ?? null;
        if (!$id) {
            Session::flash('error', 'ID de comprobante no especificado.');
            $this->redirect('/miscomprobantes');
            return;
        }

        $data = $this->paymentService->findById((int)$id);
        if (!$data) {
            Session::flash('error', 'Comprobante no encontrado.');
            $this->redirect('/miscomprobantes');
            return;
        }

        $isEdit = true;

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/comprobantespago/comprobantespago_borrar.php';
        $cfg         = $cfgedit['config']    ?? [];
        $cfg['url_action'] .= '/' . $id;
        $campos      = $cfgedit['campos']    ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos    = $cfgedit['comandos']  ?? [];
        $buttons     = $cfgedit['buttons']   ?? [];

        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $campos,
            'values'   => $data,
            'actions'  => $actividades,
            'comandos' => $comandos,
            'buttons'  => $buttons,
            'id'      => 'id',
            'user_id' => $_SESSION['user']['id'],
        ]);
    }
    public function menucobranzas(Request $request): void
    {
        $user = Session::get('user');
        if (!$user || !isset($user['id'])) {
            Session::flash('error', 'Debe iniciar sesión para acceder al panel de control');
            $this->redirect('/login');
            return;
        }

        $userId = $user['id'];

        // 2) Cargar configuración de landing
        $cfgdash     = require $_SESSION['directoriobase'] . '/views/dashboard/ctrlcobranzas.php';

        $landingCfg  = $cfgdash['landing']    ?? [];
        $cfgHeader   = $landingCfg['header']  ?? [];
        $buttons     = $landingCfg['botones'] ?? [];

        $cfgstyle   = require $_SESSION['directoriobase'] . '/config/cruds/defaults/landingstyle.php';
        $landinCSS  = $cfgstyle['styles']    ?? [];
        // 3) Renderizar con Plates
        $this->view('dashboard/mylandingpage', [
            'cfgHeader'  => $cfgHeader,
            'estilos'    => $landinCSS,
            'buttons'    => $buttons,
            'userId'     => $userId,
        ]);

    }
    public function vercobranzas(Request $request): void
    {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];
    
    
    $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/comprobantespago/cobranzastodas.php';
    $cfg         = $cfgedit['config']    ?? [];
    $id_field = $cfgedit['config']['field_id'];
    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
    //$filter = ""; //(user_id = " . $_SESSION['user']['id'] . ")
    $order = $cfgedit['QrySpec']['order'] ?? [];
    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';

    $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, $id_field);

    //$resultset = ComprobantesPago::CustomQry($query);

    $this->pendingquery = $query; // Guardamos la consulta pendiente para usarla en el script JS
    $jscampos = [];

    
    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/noaliascampos.php';
    $jscampos = quitaaliascampos($campos); // Eliminamos los alias de los campos para que se muestren correctamente en el script JS
    $this->pendingcolumns = json_encode($jscampos); // Guardamos los campos pendientes para usarlos en el script JS
    // $this->pendingcolumns = $campos;

    $comandos = $cfgedit['comandos'] ?? [];
    $buttons = $cfgedit['buttons'] ?? [];
        // Ejecuta la consulta y obtiene los datos

    $datos = $this->paymentService->customQuery($query);
    $zcolumns =  Self::mkcolumns($jscampos, $actividades);
    $zcolumns =   trim(stripslashes($zcolumns), '"');
    $this->pendingcolumns = $zcolumns; // Guardamos las columnas pendientes para usarlas en el script JS
    $this->view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $jscampos,     // <— coherente con index/create
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



    public function cobrosxmes(Request $request): void
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
            $this->redirect('/dashboard');
            return;
        }

        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/comprobantespago/mesdecobranzas.php';
        $cfg         = $cfgedit['config']    ?? [];
        $campos      = $cfgedit['campos']    ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos    = $cfgedit['comandos']  ?? [];
        $buttons     = $cfgedit['buttons']   ?? [];
        $campos['mes']['options'] = $this->paymentService->getMonths();

        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $campos,
            'values'   => $datos ?? [],
            'actions'  => $actividades,
            'comandos' => $comandos,
            'buttons'  => $buttons,
            'id'      => $id ?? null,
            'style'    => $style
        ]);
    }
    public function datacobranzas(Request $request): void
    {


    // 1) Informa errores

    // 2) Inicia sesión si es necesario
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];

    $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/comprobantespago/cobranzastodas.php';
    $id_field = $cfgedit['config']['field_id'];

    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
    //$filter = "(user_id = " . $_SESSION['user']['id'] . ")";
    $order = $cfgedit['QrySpec']['order'] ?? [];
    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';
    $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, $id_field);
    $resultset = $this->paymentService->customQuery($query);

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

//agregar boton para ver cobranzas simples por matriculado
    public function vercobranzassimple(Request $request, array $params): void
    {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];
    
    
    $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/comprobantespago/cobranzastodas.php';
    $cfg         = $cfgedit['config']    ?? [];
    $id_field = $cfgedit['config']['field_id'];
    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
    //$filter = ""; //(user_id = " . $_SESSION['user']['id'] . ")
    $order = $cfgedit['QrySpec']['order'] ?? [];
    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';

    $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, $id_field);

    //$resultset = ComprobantesPago::CustomQry($query);

    $this->pendingquery = $query; // Guardamos la consulta pendiente para usarla en el script JS
    $jscampos = [];

    
    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/noaliascampos.php';
    $jscampos = quitaaliascampos($campos); // Eliminamos los alias de los campos para que se muestren correctamente en el script JS
    $this->pendingcolumns = json_encode($jscampos); // Guardamos los campos pendientes para usarlos en el script JS
    // $this->pendingcolumns = $campos;

    $comandos = $cfgedit['comandos'] ?? [];
    $buttons = $cfgedit['buttons'] ?? [];
        // Ejecuta la consulta y obtiene los datos

    $datos = $this->paymentService->customQuery($query);
    $zcolumns =  Self::mkcolumns($jscampos, $actividades);
    $zcolumns =   trim(stripslashes($zcolumns), '"');
    $this->pendingcolumns = $zcolumns; // Guardamos las columnas pendientes para usarlas en el script JS
    $this->view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $jscampos,     // <— coherente con index/create
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

    public function cobrosxprofesional(Request $request, array $params): void
    {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

        $matriculado = $params[0] ?? null; // Get the ID from the URL parameters
        if (!$matriculado) {
            Session::flash('error', 'Profesional no indicado.');
            $this->redirect('/miscomprobantes');
            return;
        }

    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];
    
    
    $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/comprobantespago/cobranzasxmatric.php';
    $cfg         = $cfgedit['config']    ?? [];
    $id_field = $cfgedit['config']['field_id'];
    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
    $filter .= " (c.user_id = " . $matriculado . ")";
    $order = $cfgedit['QrySpec']['order'] ?? [];
    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';

    $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, $id_field);
    //$resultset = ComprobantesPago::CustomQry($query);

    $this->pendingquery = $query; // Guardamos la consulta pendiente para usarla en el script JS
    $jscampos = [];

    
    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/noaliascampos.php';
    $jscampos = quitaaliascampos($campos); // Eliminamos los alias de los campos para que se muestren correctamente en el script JS
    $this->pendingcolumns = json_encode($jscampos); // Guardamos los campos pendientes para usarlos en el script JS
    // $this->pendingcolumns = $campos;

    $comandos = $cfgedit['comandos'] ?? [];
    $buttons = $cfgedit['buttons'] ?? [];
        // Ejecuta la consulta y obtiene los datos

    $datos = $this->paymentService->customQuery($query);
    $_SESSION['matriculado4cobros'] = " (c.user_id = " . $matriculado . ")";
    $zcolumns =  Self::mkcolumns($jscampos, $actividades);
    $zcolumns =   trim(stripslashes($zcolumns), '"');
    $this->pendingcolumns = $zcolumns; // Guardamos las columnas pendientes para usarlas en el script JS
    $this->view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $jscampos,     // <— coherente con index/create
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





    public function datacobrosxprof(Request $request): void
    {


    // 1) Informa errores

    // 2) Inicia sesión si es necesario
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];

    $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/comprobantespago/cobranzasxmatric.php';
    $id_field = $cfgedit['config']['field_id'];

    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
    $filter .= $_SESSION['matriculado4cobros'] ?? '';
    $order = $cfgedit['QrySpec']['order'] ?? [];
    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';
    $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, $id_field);
    $resultset = $this->paymentService->customQuery($query);

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

    public function createlotecolegio(Request $request, array $params): void
    {
        $id = $params[0] ?? null; // Get the ID from the URL parameters
        if (!$id) {
            Session::flash('error', 'ID de comprobante no especificado.');
            $this->redirect('/miscomprobantes');
            return;
        }
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];
        $cfgcreate = require $_SESSION['directoriobase'] . '/config/cruds/comprobantespago/comprobante_lotecolegio.php';
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


    // Procesa el formulario y guarda la nueva (lote colegio)
    public function storelotecolegio(Request $request, array $params): void
    {
        $id = $params[0] ?? null;
        if (!$id) {
            Session::flash('error', 'Algo salió mal!');
            $this->redirect('/miscomprobantes');
            return;
        }

        // Check file is present
        if (!isset($_FILES['comprobante'])) {
            Session::flash('error', 'No se ha seleccionado ningún archivo.');
            $this->redirect('/miscomprobantes');
            return;
        }

        $monto = $_POST['monto'] ?? null;
        $fecha = $_POST['fecha'] ?? null;
        $observaciones = $_POST['observaciones'] ?? null;

        $result = $this->paymentService->uploadWithFile((int)$id, $_FILES['comprobante'], $monto, $fecha, $observaciones);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            $this->redirect('/miscomprobantes');
            return;
        }

        Session::flash('success', 'Comprobante guardado exitosamente.');
        $this->redirect('/miscomprobantes');
    }

    public function detallelotecolegio(Request $request): void
    {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];
    $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/comprobantespago/comprobantespago_index.php';
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
    $resultset = $this->paymentService->customQuery($query);

    $this->pendingquery = $query; // Guardamos la consulta pendiente para usarla en el script JS
    $this->pendingcolumns = json_encode($campos); // Guardamos los campos pendientes para usarlos en el script JS
    // $this->pendingcolumns = $campos;
    $comandos = $cfgedit['comandos'] ?? [];
    $buttons = $cfgedit['buttons'] ?? [];
        // Ejecuta la consulta y obtiene los datos

    $datos = $this->paymentService->customQuery($query);
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

public function loteColegioForm(Request $request, array $params = []): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style     = $crudstyle['style'] ?? [];

    $cfgcreate = require $_SESSION['directoriobase'] . '/config/cruds/comprobantespago/comprobante_lotecolegio.php';
    $cfg       = $cfgcreate['config'] ?? [];
    $campos    = $cfgcreate['campos'] ?? [];
    $comandos  = $cfgcreate['comandos'] ?? [];
    $buttons   = $cfgcreate['buttons'] ?? [];

    $values = [
        'fecha'  => date('Y-m-d'),
        'monto'  => '',
        'archivo'=> '',
    ];


    $this->view('cruds/index', [
        'cfg'      => $cfg,
        'fields'   => $campos,
        'style'    => $style,
        'values'   => $values,
        'actions'  => [],
        'comandos' => $comandos,
        'buttons'  => $buttons,
        //'divname'  => $cfg['divname'] ?? 'lote_colegio',
        'id'       => 'id',
        'link_id'  => null,
        'user_id' => $_SESSION['user']['id']
    ]);
}

public function loteColegioPreview(Request $request, array $params = []): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $fecha = $_POST['fecha'] ?? null;
    $monto = $_POST['monto'] ?? null;

    if (!$fecha || !$monto) {
        Session::flash('error', 'Debe indicar fecha e importe.');
        $this->redirect('/comprobantespago/lote-colegio');
        return;
    }

    if (
        !isset($_FILES['archivo']) ||
        $_FILES['archivo']['error'] !== UPLOAD_ERR_OK ||
        !is_uploaded_file($_FILES['archivo']['tmp_name'])
    ) {
        Session::flash('error', 'Debe seleccionar un archivo Excel válido.');
        $this->redirect('/comprobantespago/lote-colegio');
        return;
    }

    $tmpPath  = $_FILES['archivo']['tmp_name'];

    try {
        $rows = $this->parseColegioExcel($tmpPath);
    } catch (\Throwable $e) {
        Session::flash('error', 'Error leyendo el Excel: ' . $e->getMessage());
        $this->redirect('/comprobantespago/lote-colegio');
        return;
    }

    if (empty($rows)) {
        Session::flash('error', 'El archivo no contiene registros válidos.');
        $this->redirect('/comprobantespago/lote-colegio');
        return;
    }

    // Guardamos en sesión la previsualización
    $_SESSION['lote_colegio_preview'] = [
        'fecha' => $fecha,
        'monto' => (float)$monto,
        'rows'  => $rows,
        'time'  => time(),
    ];

    $this->view('comprobantespago/detallelotecolegio');
}

public function loteColegioConfirm(Request $request, array $params = []): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $preview = $_SESSION['lote_colegio_preview'] ?? null;
    if (!$preview) {
        Session::flash('error', 'No hay lote pendiente de confirmación.');
        $this->redirect('/comprobantespago/lote-colegio');
        return;
    }

    $fecha = $preview['fecha'];
    $monto = (float)$preview['monto'];
    $rows  = $preview['rows'];

    unset($_SESSION['lote_colegio_preview']);

    $result = $this->paymentService->batchCreateFromColegio($fecha, $monto, $rows);

    $msg = "Se generaron {$result['created']} de {$result['total']} comprobantes.";

    if ($result['errors']) {
        $msg .= " Algunos registros no pudieron ser importados:\n" . implode("\n", $result['errors']);
        Session::flash('error', nl2br($msg));
    } else {
        Session::flash('success', $msg);
    }

    $this->redirect('/miscomprobantes');
}

public function loteColegioCancel(Request $request, array $params = []): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    unset($_SESSION['lote_colegio_preview']);
    Session::flash('info', 'Lote de cobranzas cancelado.');
    $this->redirect('/comprobantespago/lote-colegio');
}
/**
 * Lee la planilla del Colegio y devuelve:
 * [
 *   ['matricula' => 189, 'nombre' => 'APELLIDO, Nombre', 'dni' => '12345678'],
 *   ...
 * ]
 */
private function parseColegioExcel(string $filePath): array
{
    /*
    $spreadsheet = IOFactory::load($filePath);

    // Hoja ADHERENTES; si cambian el nombre, usamos la activa.
    $sheet = $spreadsheet->getSheetByName('ADHERENTES') ?? $spreadsheet->getActiveSheet();

    $rows = [];

    // En el archivo: fila 8 es encabezado, datos desde fila 9.
    $startRow   = 9;
    $highestRow = $sheet->getHighestRow();

    for ($row = $startRow; $row <= $highestRow; $row++) {
        // Columnas (1-based):
        // A (1): APELLIDO y NOMBRE/S
        // C (3): N°DOC. (texto "DNI xxxxxxxx")
        // D (4): M.P
        

        $nombre = trim((string)$sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue());
        $doc    = trim((string)$sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue());
        $mp     = trim((string)$sheet->getCellByColumnAndRow(4, $row)->getCalculatedValue());

        if ($nombre === '' && $mp === '') {
            continue; // fila vacía
        }

        // extraer solo dígitos del campo DNI
        $dniDigits = preg_replace('/\D+/', '', $doc);

        $rows[] = [
            'matricula' => (int)$mp,
            'nombre'    => $nombre,
            'dni'       => $dniDigits,
        ];
    }

    return $rows;
*/
    }

}
