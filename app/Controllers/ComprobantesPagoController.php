<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Session;
//use App\Core\Helpers\noaliascampos;
use App\Core\Controller;
use App\Models\Matricula;
use App\Core\Helpers\DateHelper;
use App\Models\ComprobantesPago;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;



class ComprobantesPagoController extends Controller
{
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
    $resultset = ComprobantesPago::CustomQry($query);

    $this->pendingquery = $query; // Guardamos la consulta pendiente para usarla en el script JS
    $this->pendingcolumns = json_encode($campos); // Guardamos los campos pendientes para usarlos en el script JS
    // $this->pendingcolumns = $campos;
    $comandos = $cfgedit['comandos'] ?? [];
    $buttons = $cfgedit['buttons'] ?? [];
        // Ejecuta la consulta y obtiene los datos

    $datos = ComprobantesPago::CustomQry($query);
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
    $resultset = ComprobantesPago::CustomQry($query);

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

        $datos = ComprobantesPago::findById($id);
        if (!$datos) {
            Session::flash('error', 'Comprobante no encontrado.');
            $this->redirect('/miscomprobantes');
            return;
        }
        $datos['userfolder'] = $this->getUserFolder($datos['user_id']); // Get user upload folder
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

public function store(Request $request, array $params)
{
    $id = $params[0] ?? null;
    if (!$id) {
        Session::flash('error', 'Algo salió mal!');
        return $this->redirect('/miscomprobantes');
    }

    // Validaciones básicas del upload
    if (
        !isset($_FILES['comprobante']) ||
        $_FILES['comprobante']['error'] !== UPLOAD_ERR_OK ||
        !is_uploaded_file($_FILES['comprobante']['tmp_name'])
    ) {
        throw new \Exception('No se ha seleccionado ningún archivo válido.');
    }

    // Config
    $allowedExt = ['pdf','png','jpg','jpeg']; // ajustá a tus necesidades
    $maxBytes   = 15 * 1024 * 1024; // 15 MB, ejemplo

    $file      = $_FILES['comprobante'];
    $origName  = $file['name'] ?? 'archivo';
    $tmpPath   = $file['tmp_name'];
    $uploadDir = $this->getUserUploadFolder($id);

    // Crear dir si no existe
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
            throw new \RuntimeException('No se pudo crear el directorio de subida.');
        }
        @chmod($uploadDir, 0777);
    }

    // Chequeos de tamaño / extensión
    if ($file['size'] > $maxBytes) {
        throw new \Exception('El archivo supera el tamaño máximo permitido.');
    }

    $sanitized  = $this->sanitizeFilename($origName);
    $ext        = strtolower(pathinfo($sanitized, PATHINFO_EXTENSION));
    if ($ext === '') {
        throw new \Exception('El archivo no tiene extensión.');
    }
    if (!in_array($ext, $allowedExt, true)) {
        throw new \Exception('Tipo de archivo no permitido.');
    }

    // Generar nombre único en el directorio destino
    $uniqueName = $this->uniqueFilename($uploadDir, $sanitized);

    // Mover el archivo primero (para asegurar que el nombre está disponible)
    $destPath = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $uniqueName;
    if (!move_uploaded_file($tmpPath, $destPath)) {
        throw new \Exception('Error al subir el archivo.');
    }

    // (Opcional) Ajustar permisos del archivo subido
    @chmod($destPath, 0644);

    // Preparar datos a insertar (usando el nombre final)
    $zzdata = [
        'user_id'       => $id,
        'comprobante'   => $uniqueName,                 // <-- nombre único definitivo
        'monto'         => $_POST['monto'] ?? null,
        'fecha'         => $_POST['fecha'] ?? null,
        'observaciones' => $_POST['observaciones'] ?? null,
    ];

    // Insertar en DB; si falla, borrar el archivo para no dejarlo huérfano
    try {
        ComprobantesPago::create($zzdata);
    } catch (\Throwable $e) {
        @unlink($destPath);
        throw $e;
    }

    Session::flash('success', 'Comprobante guardado exitosamente.');
    return $this->redirect('/miscomprobantes');
}

/**
 * Sanitiza el nombre de archivo: quita ruta, normaliza espacios/caracteres, mantiene extensión.
 */
private function sanitizeFilename(string $name): string
{
    // Quitar cualquier componente de ruta
    $name = basename($name);

    // Separar base/ext
    $ext  = pathinfo($name, PATHINFO_EXTENSION);
    $base = pathinfo($name, PATHINFO_FILENAME);

    // Normalizar: quitar acentos, espacios->_, solo [a-z0-9._-]
    $base = iconv('UTF-8', 'ASCII//TRANSLIT', $base);
    $base = preg_replace('/[^A-Za-z0-9._-]+/', '_', $base);
    $base = trim($base, '._-');
    if ($base === '') {
        $base = 'archivo';
    }

    $ext = strtolower($ext);
    return $ext ? ($base . '.' . $ext) : $base;
}

/**
 * Devuelve un nombre único preservando la extensión.
 * Formato: {base}-{YYYYMMDD-HHMMSS}-{8hex}.{ext}
 */
private function uniqueFilename(string $dir, string $sanitized): string
{
    $ext  = pathinfo($sanitized, PATHINFO_EXTENSION);
    $base = pathinfo($sanitized, PATHINFO_FILENAME);

    // Prefijo con timestamp + sufijo aleatorio
    $suffix = date('Ymd-His') . '-' . bin2hex(random_bytes(4));
    $candidate = $base . '-' . $suffix . ($ext ? ('.' . $ext) : '');

    // En el (rarísimo) caso de colisión, iterar
    $full = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $candidate;
    $i = 0;
    while (file_exists($full)) {
        $i++;
        $candidate = $base . '-' . $suffix . '-' . $i . ($ext ? ('.' . $ext) : '');
        $full = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $candidate;
    }
    return $candidate;
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

        if (!ComprobantesPago::find($id)) {
            Session::flash('error', 'Comprobante no encontrado.');
            $this->redirect('/miscomprobantes');
            return;
        }

        ComprobantesPago::update($id, ['nombre' => $nombre]);
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

        $comprob = ComprobantesPago::find($id);
        if (!$comprob) {
            Session::flash('error', 'Comprobante no encontrado.');
            $this->redirect('/miscomprobantes');
            return;
        }

        ComprobantesPago::delete($id);
        Session::flash('success', 'Comprobante eliminado.');
        $this->redirect('/miscomprobantes');
    }

    public function vista(Request $request, array $params): void
    {
        
        $id = $params[0] ?? null; // Get the ID from the URL parameters
        if (!$id) {
            Session::flash('error', 'ID de comprobante no especificado.');
            $this->redirect('/miscomprobantes');
            return;
        }
        
//        $id = $request->input('id');

        $data = ComprobantesPago::find($id);
        if (!$data) {
            Session::flash('error', 'Comprobante no encontrado.');
            $this->redirect('/miscomprobantes');
            return;
        }

        $isEdit = true;

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/comprobantespago/comprobantespago_borrar.php';
        $cfg         = $cfgedit['config']    ?? [];
        $cfg['url_action'] .= '/' . $id; // <— se agrega el id a la url
        $campos      = $cfgedit['campos']    ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos    = $cfgedit['comandos']  ?? [];
        $buttons     = $cfgedit['buttons']   ?? [];
    
        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $campos,     // <— coherente con index/create
            'values'   => $data,       // array simple con claves=>valores
            'actions'  => $actividades,
            'comandos' => $comandos,
            'buttons'  => $buttons,
            'id'      => 'id',
            'user_id' => $_SESSION['user']['id'], // Add user ID for file upload
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

    $datos = ComprobantesPago::CustomQry($query);
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
        }

             
        if ($user['role'] !== 'admin') {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/dashboard');
        }

        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/comprobantespago/mesdecobranzas.php';
        $cfg         = $cfgedit['config']    ?? [];
        $campos      = $cfgedit['campos']    ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos    = $cfgedit['comandos']  ?? [];
        $buttons     = $cfgedit['buttons']   ?? [];
        $campos['mes']['options'] = ComprobantesPago::meses();
        //$campos['year']['options'] = ComprobantesPago::years();
        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $campos,     // <— coherente con index/create
            'values'   => $datos,       // array simple con claves=>valores
            'actions'  => $actividades,
            'comandos' => $comandos,
            'buttons'  => $buttons,
            'id'      => $id,
            'style'    => $style
            
        ]);
  
        //$tablaHTML = renderTablaHTML($config, $datos, $provincias, $ciudades);


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
    $resultset = ComprobantesPago::CustomQry($query);

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

    $datos = ComprobantesPago::CustomQry($query);
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

    $datos = ComprobantesPago::CustomQry($query);
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
    $resultset = ComprobantesPago::CustomQry($query);

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


    // Procesa el formulario y guarda la nueva 

public function storelotecolegio(Request $request, array $params)
{
    $id = $params[0] ?? null;
    if (!$id) {
        Session::flash('error', 'Algo salió mal!');
        return $this->redirect('/miscomprobantes');
    }

    // Validaciones básicas del upload
    if (
        !isset($_FILES['comprobante']) ||
        $_FILES['comprobante']['error'] !== UPLOAD_ERR_OK ||
        !is_uploaded_file($_FILES['comprobante']['tmp_name'])
    ) {
        throw new \Exception('No se ha seleccionado ningún archivo válido.');
    }

    // Config
    $allowedExt = ['pdf','png','jpg','jpeg']; // ajustá a tus necesidades
    $maxBytes   = 15 * 1024 * 1024; // 15 MB, ejemplo

    $file      = $_FILES['comprobante'];
    $origName  = $file['name'] ?? 'archivo';
    $tmpPath   = $file['tmp_name'];
    $uploadDir = $this->getUserUploadFolder($id);

    // Crear dir si no existe
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
            throw new \RuntimeException('No se pudo crear el directorio de subida.');
        }
        @chmod($uploadDir, 0777);
    }

    // Chequeos de tamaño / extensión
    if ($file['size'] > $maxBytes) {
        throw new \Exception('El archivo supera el tamaño máximo permitido.');
    }

    $sanitized  = $this->sanitizeFilename($origName);
    $ext        = strtolower(pathinfo($sanitized, PATHINFO_EXTENSION));
    if ($ext === '') {
        throw new \Exception('El archivo no tiene extensión.');
    }
    if (!in_array($ext, $allowedExt, true)) {
        throw new \Exception('Tipo de archivo no permitido.');
    }

    // Generar nombre único en el directorio destino
    $uniqueName = $this->uniqueFilename($uploadDir, $sanitized);

    // Mover el archivo primero (para asegurar que el nombre está disponible)
    $destPath = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $uniqueName;
    if (!move_uploaded_file($tmpPath, $destPath)) {
        throw new \Exception('Error al subir el archivo.');
    }

    // (Opcional) Ajustar permisos del archivo subido
    @chmod($destPath, 0644);

    // Preparar datos a insertar (usando el nombre final)
    $zzdata = [
        'user_id'       => $id,
        'comprobante'   => $uniqueName,                 // <-- nombre único definitivo
        'monto'         => $_POST['monto'] ?? null,
        'fecha'         => $_POST['fecha'] ?? null,
        'observaciones' => $_POST['observaciones'] ?? null,
    ];

    // Insertar en DB; si falla, borrar el archivo para no dejarlo huérfano
    try {
        ComprobantesPago::create($zzdata);
    } catch (\Throwable $e) {
        @unlink($destPath);
        throw $e;
    }

    Session::flash('success', 'Comprobante guardado exitosamente.');
    return $this->redirect('/miscomprobantes');
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
    $resultset = ComprobantesPago::CustomQry($query);

    $this->pendingquery = $query; // Guardamos la consulta pendiente para usarla en el script JS
    $this->pendingcolumns = json_encode($campos); // Guardamos los campos pendientes para usarlos en el script JS
    // $this->pendingcolumns = $campos;
    $comandos = $cfgedit['comandos'] ?? [];
    $buttons = $cfgedit['buttons'] ?? [];
        // Ejecuta la consulta y obtiene los datos

    $datos = ComprobantesPago::CustomQry($query);
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

    $ok      = 0;
    $errores = [];

    foreach ($rows as $r) {
        $matriculaNumero = (int)$r['matricula'];
        if (!$matriculaNumero) {
            $errores[] = "Fila con matrícula vacía para {$r['nombre']} ({$r['dni']})";
            continue;
        }

        $matr = Matricula::findByAsignada($matriculaNumero);
        if (!$matr) {
            $errores[] = "No se encontró matrícula asignada {$matriculaNumero} ({$r['nombre']}, DNI {$r['dni']})";
            continue;
        }

        $userId = (int)($matr['user_id'] ?? 0);
        if (!$userId) {
            $errores[] = "Matrícula {$matriculaNumero} no tiene user_id asociado.";
            continue;
        }

        $data = [
            'user_id'       => $userId,
            'comprobante'   => 'cobrocolegio.png',                 // <- imagen genérica
            'fecha'         => $fecha,
            'monto'         => $monto,
            'observaciones' => 'Informado por el Colegio',
        ];

        try {
            ComprobantesPago::create($data);
            $ok++;
        } catch (\Throwable $e) {
            $errores[] = "Error guardando matrícula {$matriculaNumero}: " . $e->getMessage();
        }
    }

    $msg = "Se generaron {$ok} comprobantes.";

    if ($errores) {
        $msg .= " Algunos registros no pudieron ser importados:\n" . implode("\n", $errores);
        Session::flash('error', nl2br($msg));
    } else {
        Session::flash('success', $msg);
    }

    // Podés ajustar esta URL a donde listás los comprobantes
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
