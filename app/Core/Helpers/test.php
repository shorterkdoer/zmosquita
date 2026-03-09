<?php


        $cfgedit     = require '/var/www/copro3/Matricu22k/config/cruds/users/users_pending.php';
    
        $cfg         = $cfgedit['config']    ?? [];
        $campos      = $cfgedit['campos']    ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos    = $cfgedit['comandos']  ?? [];
        $buttons     = $cfgedit['buttons']   ?? [];
        $tables      = $cfgedit['QrySpec']['tables'] ?? [];
        $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
        $filter      = $cfgedit['QrySpec']['filter'] ?? '';
        $order       = $cfgedit['QrySpec']['order'] ?? [];
        //require_once '/var/www/copro3/Matricu22k/app/Core/Helpers/string4query.php';
        


        $pendingcolumns = buildDataTablesScript($campos, $actividades, $cfg['url_data'], 'usrpendientes');
        echo "..............." . $pendingcolumns ."==============";


        return;
  
function buildDataTablesScript(array $fields, array $acciones, string $ajaxUrl, string $tableSelector = '#datatable'): string
    {
        $columnsJs = implode(",\n                ", calcJSColumns($fields, $acciones));

        return <<<JS

\$(document).ready(function() {
    \$('$tableSelector').DataTable({
        processing: true,
        serverSide: true,
        ajax: '$ajaxUrl',
        columns: [
                $columnsJs
        ]
    });
});

JS;
}
 function calcJSColumns(array $fields, array $acciones): array
{
    $cols = [];
    foreach ($fields as $key => $field) {
        if (empty($field['hidden'])) {
            $cols[] = "{ data: '$key', name: '$key', searchable: " . (!empty($field['searchable']) ? 'true' : 'false') . ", orderable: " . (!empty($field['orderable']) ? 'true' : 'false') . " }";
        }
    }
    // Agregar columna de acciones
    foreach ($acciones as $key => $accion) {
        {
            $cols[] = "{ data: '$key', name: '$key' }";
        }
    }

    //$cols[] = "{ data: 'acciones', orderable: false, searchable: false }";
    return $cols;
}

        ?>
