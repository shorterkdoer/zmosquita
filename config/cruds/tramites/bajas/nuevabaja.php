<?php    //index view
return [    'config' => 
                [
                    'titulo' => 'Baja de matrícula',
                    'subtitulo' => '',
                    'field_id' => 'matriculado',
                    'tipo' => 'form',
                    'action' => 'create',
                    'url_action' => '/matriculas/baja',
                    'method' => 'POST',
                    'divname' => 'bajas',
                    'url_data' => '',
                    'link_id' => '',
                ],
					
            'QrySpec' => 
				[
                'tables' => [ 'datospersonales d', 'matriculas m' ],
                'joincond' => 'd.user_id = m.user_id',
                'filter' => '(m.aprobado is not null) and (m.comisionotorgante is not null) and (m.baja is null)',
                
                'order' => [''],
				],
            'comandos' => [],    
            'campos' => [
                'id' => [
                    'nombre' => 'id',
                    'type' => 'text',
                    'label' => 'ID',
                    'readonly' => true,
                    'hidden' => true,
                    'class' => 'form-control',
                ],


                'matriculado' => [
                    'nombre' => 'matriculado',
                    'type' => 'select',
                    'label' => 'matriculado',
                    'searchable' => true,
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'matriculado',
                    'help' => 'Ingrese matriculado',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}',
                    'options' => 
			            [
                        'datasource'   => 'datospersonales',
                        'id_field'     => 'user_id',
                        'mostrarcampo' => ['apellido','nombre'], // sumá campos a gusto
                        'separator'    => ', ',
                        'where'        => 'apellido IS NOT NULL and user_id in (select user_id from matriculas m where (m.aprobado is not null) and (m.comisionotorgante is not null) and (m.baja is null))', // condición para traer solo matriculados activos
                        'params'       => [],
                        'order_by'     => 'label',
                        'collate'      => 'utf8mb4_spanish_ci',
                        ],
                ],

                'fecha' => [
                    'nombre' => 'fecha',
                    'type' => 'date',
                    'label' => 'Fecha',
                    'searchable' => true,
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Fecha',
                    'help' => 'Ingrese la fecha',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[0-9]{4}-[0-9]{2}-[0-9]{2}'
                ],
                'motivo' => [
                    'nombre' => 'motivo',
                    'type' => 'text',
                    'label' => 'Motivo',
                    'searchable' => true,
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Motivo',
                    'help' => 'Ingrese motivo',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}',

                    ],
            ],
            'actividades' => [

            ],

            'buttons' => [
                'submit' => [
                    'type' => 'submit',
                    'text' => 'Dar de baja',
                    
                    'class' => 'btn btn-primary btn-rounded',
                    'icon' => 'bi bi-person-down',
                ],
                'cancel' => [
                    'type' => 'button',
                    'text' => 'Volver',
                    'url' => '/menubajas',
                    'class' => 'btn btn-outline-secondary btn-rounded',
                    'icon' => 'bi bi-arrow-left',
                    'backbutton' => true, // para que funcione el botón de volver
                ],
            ],
];







