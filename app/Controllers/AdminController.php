<?php
namespace App\Controllers;



use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;

use App\Core\Session;
use App\Models\User;

// Ensure the User model exists in the App\Models namespace
use App\Core\Controller;

class AdminController extends Controller
{

    public function aspirantes(Request $request): void
    {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
    $style = $crudstyle['style'] ?? [];
    
    
    $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/aspirantes.php';
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


}