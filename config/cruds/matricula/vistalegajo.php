<?php
return [
    'config' => [
        'title' => 'Matriculas',
        'titulo' => 'Documentación presentada',
        'field_id' => 'id',
        'tipo' => 'form',
        'icon' => 'award',
        'subtitle' => '', // opcional
        'action' => '', // opcional, para los formularios
        'url_action' => '', // opcional, para los formularios
        'method' => '',     // opcional, para los formularios
        'divname' => 'verlegajo', //nombre para el div del listado, si se usa AJAX
        'url_data' => '', // URL para obtener los datos (opcional, si se usa AJAX) debe estar en config/routes.php
        'link_id' => '', 
        ],
    'comandos' => [
               ],

    'campos' => [
                'notaddjj' => 
                [
                    'nombre' => 'notaddjj',
                    'type' => 'file',
                    'label' => 'Notaddjj', 
                    'placeholder' => 'Ingrese notaddjj', 
                    'readonly' => true, 
                    'hidden' => false, 'help' => 'Ingrese el nombre', 
                    'class' => 'form-control', 
                    'style' => 'width: 100%;', 
                    'autocomplete' => 'off', 
                    'pattern' => '[A-Za-z0-9 ]{1,255}', 
                    'options' => [], 
                    'columna_rel' => '', 
                    'default' => 'NULL', 
                    'tabla_rel' => ''],
        'dnifrente' => ['nombre' => 'dnifrente', 'type' => 'file', 'label' => 'Dnifrente', 'placeholder' => 'Ingrese dnifrente', 'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'default' => 'NULL', 'tabla_rel' => ''],
        'dnidorso' => ['nombre' => 'dnidorso', 'type' => 'file', 'label' => 'Dnidorso', 'placeholder' => 'Ingrese dnidorso', 'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'default' => 'NULL', 'tabla_rel' => ''],
        'titulooriginalfrente' => ['nombre' => 'titulooriginalfrente', 'type' => 'file', 'label' => 'Titulooriginalfrente', 'placeholder' => 'Ingrese titulooriginalfrente', 'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'default' => 'NULL', 'tabla_rel' => ''],
        'titulooriginaldorso' => ['nombre' => 'titulooriginaldorso', 'type' => 'file', 'label' => 'Titulooriginaldorso', 'placeholder' => 'Ingrese titulooriginaldorso', 'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'default' => 'NULL', 'tabla_rel' => ''],
        'fotoregistrodegraduados' => ['nombre' => 'fotoregistrodegraduados', 'type' => 'file', 'label' => 'Fotoregistrodegraduados', 'placeholder' => 'Ingrese fotoregistrodegraduados', 'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'default' => 'NULL', 'tabla_rel' => ''],
        'fotocarnet' => ['nombre' => 'fotocarnet', 'type' => 'file', 'label' => 'Fotocarnet', 'placeholder' => 'Ingrese fotocarnet', 'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'default' => 'NULL', 'tabla_rel' => ''],
        'antecedentespenales' => ['nombre' => 'antecedentespenales', 'type' => 'file', 'label' => 'Antecedentespenales', 'placeholder' => 'Ingrese antecedentespenales', 'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'default' => 'NULL', 'tabla_rel' => ''],
        'libredeudaalimentario' => ['nombre' => 'libredeudaalimentario', 'type' => 'file', 'label' => 'Libredeudaalimentario', 'placeholder' => 'Ingrese libredeudaalimentario', 'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'default' => 'NULL', 'tabla_rel' => ''],
        'constanciaCUIL' => ['nombre' => 'constanciaCUIL', 'type' => 'file', 'label' => 'ConstanciaCUIL', 'placeholder' => 'CUIL', 'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'default' => 'NULL', 'tabla_rel' => ''],
        'apostillado' => ['nombre' => 'apostillado', 'type' => 'file', 'label' => 'Apostillado', 'placeholder' => 'Ingrese apostillado', 'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'default' => 'NULL', 'tabla_rel' => ''],
        'matriculaprevia' => ['nombre' => 'matriculaprevia', 'type' => 'file', 'label' => 'Matriculaprevia', 'placeholder' => 'Ingrese matriculaprevia', 'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'default' => 'NULL', 'tabla_rel' => ''],
        'matriculaministerio' => ['nombre' => 'matriculaministerio', 'type' => 'file', 'label' => 'Matriculaministerio', 'placeholder' => 'Ingrese matriculaministerio', 'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'default' => 'NULL', 'tabla_rel' => ''],
        'matriculaasignada' => ['nombre' => 'matriculaasignada', 'type' => 'file', 'label' => 'Matriculaasignada', 'placeholder' => 'Ingrese matriculaasignada', 'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'default' => 'NULL', 'tabla_rel' => ''],
        'certificadoetica' => ['nombre' => 'certificadoetica', 'type' => 'file', 'label' => 'Certificadoetica', 'placeholder' => 'Ingrese certificadoetica', 'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'default' => 'NULL', 'tabla_rel' => ''],
    
        ],
    'buttons' => [
        'cancel' => [
            'type' => 'button',
            'text' => 'Volver',
            'url' => '/dashboard',
            'class' => 'btn btn-outline-secondary btn-rounded',
            'icon' => 'bi bi-arrow-left',

            ],
/*
        'submit' => [
            'type' => 'submit',
            'text' => 'Guardar',
            'class' => 'btn btn-gradient btn-rounded',
            'icon' => 'bi bi-check-circle me-1',
            ], */
        ],

];
