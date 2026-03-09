<?php    //index view
return [    'config' => 
                [
                    'titulo' => 'Agenda de citas',
                    'subtitulo' => 'Nueva Cita',
                    'field_id' => 'id',
                    'tipo' => 'form',
                    'subtitle' => '',
                    'action' => '',
                    'url_action' => '/agendadecitas/store',
                    'method' => '',
                    'divname' => 'Matri4Rev',
                    'url_data' => '',
                    'link_id' => '',
                ],
					
            'QrySpec' => 
				[
                'tables' => [  'agendadecitas a'  ],
                'joincond' => '',
                'filter' => '',
                /*(not (m.freezedata is null)) '.
                        'and (m.verificado is not null) '.
                        'and (m.aprobado is null) and (m.baja is null)',*/
                'order' => ['fecha DESC'],
				],
            'comandos' => 
				[

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
                'fecha' => [
                    'nombre' => 'fecha',
                    'type' => 'date',
                    'label' => 'Fecha',
                    'readonly' => false,
                    'hidden' => false,
                    'class' => 'form-control',
                ],


                'hora' => [
                    'nombre' => 'hora',
                    'type' => 'text',
                    'label' => 'Hora',
                    'searchable' => true,
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'hora',
                    'help' => 'Ingresehora',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    //'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'Profesional' => [
                    'nombre' => 'Profesional',
                    'type' => 'calc',
                    'label' => 'Profesional',
                    'searchable' => true,
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Profesional',
                    'help' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}',
                    'options' => 
                            [
                            'datasource'   => 'da',
                            'id_field'     => 'user_id',
                            'mostrarcampo' => ['apellido','nombre'], // sumá campos a gusto
                            'separator'    => [', '],
                            ],
                    ],





                /*
                'funcionario' => [
                    'nombre' => 'funcionario',
                    'type' => 'select',
                    'label' => 'Funcionario',
                    'searchable' => true,
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Nombres',
                    'help' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}',
                    'options' => 
			[
			'datasource'   => 'DatosPersonales',
    			'id_field'     => 'user_id',
    			'mostrarcampo' => ['apellido','nombre'], // sumá campos a gusto
    			'separator'    => ', ',
    			'where'        => '',
    			'params'       => [],
    			'order_by'     => 'label',
    			'collate'      => 'utf8mb4_spanish_ci',
			],
                ],

                'matriculado' => [
                    'nombre' => 'matriculado',
                    'type' => 'select',
                    'label' => 'Profesional',
                    'searchable' => true,
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Profesional',
                    'help' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}',
                    'options' => 
                            [
                            'datasource'   => 'DatosPersonales',
                                'id_field'     => 'user_id',
                                'mostrarcampo' => ['apellido','nombre'], // sumá campos a gusto
                                'separator'    => ', ',
                                'where'        => '',
                                'params'       => [],
                                'order_by'     => 'label',
                                'collate'      => 'utf8mb4_spanish_ci',
                            ],
                    ],
*/

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







