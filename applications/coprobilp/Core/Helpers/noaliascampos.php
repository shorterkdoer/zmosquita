<?php


function quitaaliascampos(array $campos): array
    {
    

    $result = [];
    /*foreach ($campos as $key => $campo) {
        // Si el campo tiene un alias, lo quitamos
        echo "Campo: ". $key . " \n";
    */
    foreach ($campos as $key => $campo) {
        // Si el campo tiene un alias, lo quitamos
        if (strpos($key, '.') !== false) {
            $parts = explode('.', $key);
            $result[$parts[1]] = $campo; // Usamos solo la parte después del punto como clave
        } else {
            $result[$key] = $campo; // Si no tiene alias, lo dejamos como está
        }    
    }
    return $result;

}