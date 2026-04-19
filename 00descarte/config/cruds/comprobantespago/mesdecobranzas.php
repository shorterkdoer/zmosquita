<?php    //index view
return [    'config' => 
                [
                'titulo' => 'Cobranzas por mes',
                'subtitulo' => 'Consulta',
                'action' => 'update',
                'method' => 'POST',
                'tipo' => 'form',
            ],
            'comandos' => [
                
            ],
            'campos' => [
                'mes' => [
                    'nombre' => 'mes',
                    'type' => 'select',
                    'label' => 'Nombre',
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Período a consultar',
                    'help' => 'Seleccione el período',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    //'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],

            ],
            'actividades' => [
            ],
            'buttons' => [
                'cancel' => [
                    'type' => 'button',
                    'text' => 'Volver',
                    'url' => '',
                    'class' => 'btn btn-outline-secondary btn-rounded',
                    'icon' => 'bi bi-arrow-left',
                    'backbutton' => true, // para que funcione el botón de volver

                ],

                'Borrar' => [
                    'type' => 'submit',
                    'text' => 'Consultar',
                    'class' => 'btn btn-gradient btn-rounded',
                    'icon' => 'bi trash3-fill me-1',
                ],
            ],
];


