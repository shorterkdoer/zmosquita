<?php    //index view
return [    'config' => 
                [
                    'titulo' => 'Detalle del legajo',
                    'subtitulo' => '',
                    'field_id' => 'id',
                    'tipo' => 'table',
                    'subtitle' => '',
                    'action' => '',
                    'url_action' => '',
                    'method' => '',
                    'divname' => 'LegajoFrm',
                    'url_data' => '/api/legajo/data',
                    'link_id' => 'user_id',
                ],
					
            'QrySpec' => 
				[
                'tables' => [ 'tramites t', 'datospersonales d' ], //, 
                'joincond' => '(t.user_id = d.user_id)',
                'filter' => '',

                'order' => ['t.fecha ASC'], //t.fecha DESC
				],
            'comandos' => 
				[
				],
            'campos' => [
                'fecha' => [
                    'nombre' => 'fecha',
                    'type' => 'date',
                    'label' => 'Fecha',
                    'readonly' => true,
                    'hidden' => false,
                    'class' => 'form-control',
                ],
/*
                'Funcionario' => [
                    'nombre' => 'Funcionario',
                    'type' => 'calc',
                    'label' => 'Funcionario',
                    'searchable' => true,
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Funcionario',
                    'help' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}',
                    'options' => 
                            [
                            'datasource'   => 'd',
                            'id_field'     => 'user_id',
                            'mostrarcampo' => ['apellido','nombre'], // sumá campos a gusto
                            'separator'    => [', '],
                            ],
                    ],
  */
                'observaciones' => [
                    'nombre' => 'observaciones',
                    'type' => 'text',
                    'label' => 'Observaciones',
                    'readonly' => true,
                    'hidden' => false,
                    'class' => 'form-control',
                ],


                    ],
            'actividades' => [
                'Datos' => [
                    'text' => 'Datos',
                    'url' => '',  // Removed trailing slash
                    'url_params' => false, // Indicates that the URL will include the ID
                    'param_field' => 'id', // Specify the field to use as a parameter
                    'icon' => 'bi bi-eye',
                    'newpage' => true, // Indicates that this button opens in a new page
                    'class' => 'btn btn-warning'
                ],
                

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



