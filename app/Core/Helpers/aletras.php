<?php 

function numeroALetras($numero) {
    $unidades = [
        'cero', 'uno', 'dos', 'tres', 'cuatro', 'cinco',
        'seis', 'siete', 'ocho', 'nueve', 'diez',
        'once', 'doce', 'trece', 'catorce', 'quince',
        'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve'
    ];

    $decenas = [
        '', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta',
        'sesenta', 'setenta', 'ochenta', 'noventa'
    ];

    if ($numero < 20) {
        return $unidades[$numero];
    }

    $d = intdiv($numero, 10);
    $u = $numero % 10;

    if ($numero < 30 && $u != 0) {
        // Para los del tipo veintiuno, veintidós, etc.
        return 'veinti' . $unidades[$u];
    } elseif ($u == 0) {
        return $decenas[$d];
    } else {
        return $decenas[$d] . ' y ' . $unidades[$u];
    }
}
?>