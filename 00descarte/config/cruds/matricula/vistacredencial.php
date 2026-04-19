<?php
return [
    'config' => [
        'title' => 'Credencial de matrícula',
        'titulo' => 'Formatos disponibles',
        'field_id' => 'id',
        'tipo' => 'form',
        'icon' => 'award',
        'subtitle' => '', // opcional
        'action' => '', // opcional, para los formularios
        'url_action' => '', // opcional, para los formularios
        'method' => '',     // opcional, para los formularios
        'divname' => 'vercredenciales', //nombre para el div del listado, si se usa AJAX
        'url_data' => '', // URL para obtener los datos (opcional, si se usa AJAX) debe estar en config/routes.php
        'link_id' => '', 
        ],
    'comandos' => [

               ],

    'campos' => [
            'carnet' => 
                [
                'nombre' => 'carnet', 
                'type' => 'image', 
                'label' => 'Carnet', 
                'placeholder' => 'Carnet', 
                'readonly' => true, 
                'hidden' => false, 
                'help' => 'Carnet en formato imagen', 
                'class' => 'form-control', 
                'style' => 'width: 100%;', 
                'autocomplete' => 'off', 
                'pattern' => '[A-Za-z0-9 ]{1,255}', 
                'options' => [], 
                'columna_rel' => '', 
                'default' => 'NULL', 
                'tabla_rel' => ''
                ],
            'carnetpdf' => 
                [
                'nombre' => 'carnetpdf', 
                'type' => 'file', 
                'label' => 'Carnet en PDF', 
                'placeholder' => 'Carnet', 
                'readonly' => true, 
                'hidden' => false, 
                'help' => 'Carnet en formato documento PDF', 
                'class' => 'form-control', 
                'style' => 'width: 100%;', 
                'autocomplete' => 'off', 
                'pattern' => '[A-Za-z0-9 ]{1,255}', 
                'options' => [], 
                'columna_rel' => '', 
                'default' => 'NULL', 
                'tabla_rel' => ''
                ],

            ],
    'buttons' => [
        'cancel' => [
            'type' => 'button',
            'text' => 'Volver',
            'url' => '/dashboard',
            'class' => 'btn btn-outline-secondary btn-rounded',
            'icon' => 'bi bi-arrow-left',

            ],
        ],

];
