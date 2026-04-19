<?php


function mkGrid(array $ConfigInfo)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';

    $cfgedit = require $_SESSION['directoriobase'] . '/config/cruds/users/users_pending.php';
    $campos = $cfgedit['campos'] ?? [];
    $actividades = $cfgedit['actividades'] ?? [];
    $tables = $cfgedit['QrySpec']['tables'] ?? [];
    $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
    $filter = $cfgedit['QrySpec']['filter'] ?? '';
    $order = $cfgedit['QrySpec']['order'] ?? [];

    $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order);
    //echo $query;
    //die();
    $resultset = CustomQry($query);

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

?>