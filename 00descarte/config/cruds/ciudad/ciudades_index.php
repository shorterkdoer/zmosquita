<?php    //index view
return [    'config' => 
                [
                'title' => 'Ciudades',
                'subtitle' => 'Vista general',
                'url_action' => '',
                'method' => '',
                'tipo' => 'table',
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
                'field_id' => 'id',
                //'link_id' => 'user_id',
            ],
            'comandos' => [
                'Nueva' => [
                    'text' => 'Nueva Ciudad',
                    'url' => '/ciudades/create',
                    'icon' => 'bi bi-add',
                    'class' => 'btn btn-primary'
               ],
            ],
            'campos' => [
                'id' => [
                    'nombre' => 'id',
                    'type' => 'text',
                    'label' => 'ID',
                    'readonly' => true,
                    'hidden' => true,
                    'class' => 'form-control',
                ],
                'nombre' => [
                    'nombre' => 'nombre',
                    'type' => 'text',
                    'label' => 'Nombre',
                    'maxlength' => 255,
                    'readonly' => true,
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
                'edit' => [
                    'text' => 'Editar',
                    'url' => '/ciudades/edit',
                    'icon' => 'bi bi-pencil',
                    'class' => 'btn btn-warning'
                ],
                'delete' => [
                    'text' => 'Eliminar',
                    'url' => '/ciudades/vista',
                    'icon' => 'bi bi-trash',
                    'class' => 'btn btn-danger'
                ]
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

                ],
];


