<?php

function str4qry(array $tables, 
        array $fields, 
        array $acciones, 
        string $filter, 
        string $joinconditions, 
        array $order,
        string $id_field):string 
{
    $str_fields = '';
    foreach ($fields as $key => $field) {
        if (!empty($field['hidden'])) {
            continue;
        }
        $result_string = '';
        if($field['type'] == 'calc'){
/*
                    'options' => 
                    [
                    'datasource'   => 'df',
                        'id_field'     => 'user_id',
                        'mostrarcampo' => ['apellido','nombre'], // sumá campos a gusto
                        'separator'    => ', ',
                    ],


*/
            $mostrar = $field['options']['mostrarcampo'];
            $separata = $field['options']['separator'];
            $ds = $field['options']['datasource'];
            for ($i = 0; $i < count($mostrar); $i++) {
                $result_string .= $ds . '.' . $mostrar[$i];

            // Si hay un separador disponible para la posición actual, lo añadimos.
            // La condición es que el índice del separador ($i) sea menor que el número de elementos en $array2
            // y que no estemos en el último elemento de $array1.
                if (isset($separata[$i]) && $i < count($mostrar) - 1) {
                    $result_string .= $separata[$i];
                }
            }
            $key = 'concat(' . $result_string . ') as ' . $field['nombre'];

        }

        $str_fields .=  $key . ', ';
        
    }
    //$buttons = '';
    $buttons = '';
     
    foreach ($acciones as $key => $clave) {
        if ($clave['url_params']) {


            $localparams = $clave['param_field'];

            $xxsql = "concat('<a href=\"". trim($clave['url']) . "/', $localparams , '\"' , ' class=\"{$clave['class']}\" data-toggle=\"modal\">";
            //$xxsql .=  $clave['text'] . "</a>') as " . $clave['text'] .", ";
            $xxsql .= "<span class=\"". $clave['icon'] ."\"></span>". $clave['text'] . "</a>') as {$clave['text']}, ";
//            $xxsql .= "<span class=\"{$clave['icon']}\"></span> {$clave['text']}</a>') as {$clave['text']}, ";
        } else {
            $xxsql = "concat('<a href=\"". trim($clave['url']) . "\" class=\"{$clave['class']}\" data-toggle=\"modal\">";
            //$xxsql .=  $clave['text'] . "</a>') as " . $clave['text'] .", ";
            $xxsql .= "<span class=\"". $clave['icon'] ."\"></span>". $clave['text'] . "</a>') as {$clave['text']}, ";
        }
        $buttons .= $xxsql;

    }
 
	if($buttons == '') $str_fields = rtrim($str_fields, ', ');

    $str_fields2 = '';
    if($str_fields2 == '') $buttons = rtrim($buttons, ', ');

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

    $queryres = "SELECT " . $str_fields . $buttons . $str_fields2 ." FROM " . $str_from . " " . $where . " ". $str_order;


    return $queryres;
}



?>