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


    $buttons = '';
    foreach ($actividades as $key => $clave) {
		$xstr0 = "concat('";
        $xstr1 = '<a href="' . $clave['url'] . '"';
        $xstr2 = ' class="' . $clave['class'] . '" data-toggle='. '"'. 'modal"';
        
        $xstr3 = '><span class="' . $clave['icon'] . '"></span> ' ;
        $xstr4 = $clave['text'] . '</a>\') as ' . $clave['text'] . ', ';
        $boton = $xstr1 . $xstr2 . $xstr3 . $xstr4;
        //echo $xstr1 . "\n";
        //echo $xstr2 . "\n";
        //echo $xstr3 . "\n";
        //echo $xstr4 . "\n"; 
        
        $boton = $xstr0 . $xstr1 . $xstr2 . $xstr3 . $xstr4 ;
        $buttons .= $boton;
        //echo $boton;
        //echo "---------------------\n";

	}
echo $buttons;
die();

        echo str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order);
        
        
return;





function str4qry(array $tables, 
        array $fields, 
        array $acciones, 
        string $filter, 
        string $joinconditions, 
        array $order):string 
{
    
    $str_fields = '';

    foreach ($fields as $key => $field) {
        if (!empty($field['hidden'])) {
            continue;
        }
        $str_fields .= $key . ', ';
    }
    $buttons = '';
    
    foreach ($acciones as $key => $clave) {
		

		$buttons .= 'concat(\'<a href="' . $clave['url'] . '"' .
        ' class="' . $clave['class'] . '" data-toggle='. '"'. 'modal"'. '><span class="' . 
        $clave['icon'] . '"></span> ' . 
        $clave['text'] . '</a>\') as ' . $clave['text'] . ', ';

	}
    $buttons = rtrim($buttons, ', ');
	
	if($buttons == '') $str_fields = rtrim($str_fields, ', ');

    
    $str_from = '';
    foreach ($tables as $table) {
        $str_from .= $table . ', ';
    }
    $str_from = rtrim($str_from, ', ');
    
    $str_join = $joinconditions;
    $str_order = '';
    if (!empty($order)) {
        $str_order = ' ORDER BY ';
        foreach ($order as $ord) {
            $str_order .= $ord . ', ';
        }
        $str_order = rtrim($str_order, ', ');
    }

    $where = '';

    if ($str_join && $filter) {
        $where = "WHERE $str_join AND $filter";
    } elseif ($str_join) {
        $where = "WHERE $str_join";
    } elseif ($filter) {
        $where = "WHERE $filter";
    }
    // Y luego:
    $queryres = "SELECT " . $str_fields . " FROM " . $str_from . " " . $where . " ". $str_order;

    return $queryres;
}
