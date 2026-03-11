<?php
namespace App\Controllers;

use App\Core\Controller;
use Foundation\Core\Request;
use Foundation\Core\Session;
use App\Services\AdminService;

/**
 * AdminController - Handles admin dashboard and views
 *
 * Refactored to use Service Layer for business logic
 */
class AdminController extends Controller
{
    protected AdminService $adminService;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->adminService = new AdminService();
    }

    /**
     * Show aspirantes view
     */
    public function aspirantes(Request $request): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        $cfgedit = require $_SESSION['directoriobase'] . '/config/cruds/tramites/aspirantes.php';
        $cfg = $cfgedit['config'] ?? [];
        $id_field = $cfgedit['config']['field_id'];
        $campos = $cfgedit['campos'] ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $tables = $cfgedit['QrySpec']['tables'] ?? [];
        $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
        $filter = $cfgedit['QrySpec']['filter'] ?? '';
        $order = $cfgedit['QrySpec']['order'] ?? [];

        require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';

        $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, $id_field);
        $this->pendingquery = $query;

        require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/noaliascampos.php';
        $jscampos = quitaaliascampos($campos);
        $this->pendingcolumns = json_encode($jscampos);

        $comandos = $cfgedit['comandos'] ?? [];
        $buttons = $cfgedit['buttons'] ?? [];

        $datos = $this->adminService->customQuery($query);
        $zcolumns = Self::mkcolumns($jscampos, $actividades);
        $zcolumns = trim(stripslashes($zcolumns), '"');
        $this->pendingcolumns = $zcolumns;

        $this->view('cruds/index', [
            'cfg' => $cfg,
            'fields' => $jscampos,
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
            'user_id' => $_SESSION['user']['id'],
        ]);
    }

    /**
     * Return aspirantes data as JSON
     */
    public function dataaspirantes(Request $request): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $cfgedit = require $_SESSION['directoriobase'] . '/config/cruds/tramites/aspirantes.php';
        $resultset = $this->adminService->getAspirantes($cfgedit);

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
