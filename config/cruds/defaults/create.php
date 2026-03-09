<?php



return [ 'config' => [
                'title' => 'Ciudades',
                'subtitle' => 'Nueva ciudad',
                'action' => 'create',
                'url_action' => '/ciudades/store',  
                'method' => 'POST',
                'tipo' => 'form',
                'class_div' => "p-4 bg-light rounded shadow-sm h-100",
                'class_form' => 'row g-4 p-4 bg-light rounded shadow-sm',
                'class_tr' => 'p-4 bg-light rounded shadow-sm h-100',
                'class_th' => 'text-center',
                'class_td' => 'text-center',
                'class_table' => 'table table-striped table-bordered',
                'class_table_div' => 'row g-4 p-4 bg-light shadow-sm',
                'class_thead' => 'thead-light',
                'class_tbody' => 'container',
                'class_tfoot' => 'thead-light',
            ],
            'comandos' => [
               
            ],
            'campos' => [
                'nombre' => [
                    'nombre' => 'nombre',
                    'type' => 'text',
                    'label' => 'Nombre',
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Nombre de la ciudad',
                    'help' => 'Ingrese el nombre de la ciudad',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ]
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

                'submit' => [
                    'type' => 'submit',
                    'text' => 'Guardar',
                    'class' => 'btn btn-gradient btn-rounded',
                    'icon' => 'bi bi-check-circle me-1',
                ],
            ],                
];


