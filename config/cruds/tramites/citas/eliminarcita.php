<?php    //index view
return [    'config' => 
                [
                    'titulo' => 'Agenda de citas',
                    'subtitulo' => 'Borrar  cita',
                    'field_id' => 'id',
                    'tipo' => 'form',
                    'action' => 'create',
                    'url_action' => '',
                    'method' => 'POST',
                    'divname' => 'borrarcita',
                    'url_data' => '',
                    'link_id' => '',
                ],
					
            'QrySpec' => 
				[
                'tables' => [ 'agendadecitas a', 'datospersonales d' ],
                'joincond' => 'd.user_id = a.matriculado',
                'filter' => '',
                'order' => [''],
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


                'matriculado' => [
                    'nombre' => 'matriculado',
                    'type' => 'select',
                    'label' => 'matriculado',
                    'searchable' => true,
                    'maxlength' => 255,
                    'readonly' => true,
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
                        'where'        => '',
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
                    'readonly' => true,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Fecha',
                    'help' => 'Ingrese la fecha',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[0-9]{4}-[0-9]{2}-[0-9]{2}'
                ],
                'hora' => [
                    'nombre' => 'hora',
                    'type' => 'text',
                    'label' => 'Hora',
                    'searchable' => true,
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Hora',
                    'help' => 'Ingrese la hora',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[0-9]{2}:[0-9]{2}'
                ],
                'motivo' => [
                    'nombre' => 'motivo',
                    'type' => 'text',
                    'label' => 'Motivo',
                    'searchable' => true,
                    'maxlength' => 255,
                    'readonly' => true,
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
                'cancel' => [
                    'type' => 'button',
                    'text' => 'Volver',
                    'url' => '/agendadecitas',
                    'class' => 'btn btn-outline-secondary btn-rounded',
                    'icon' => 'bi bi-arrow-left',
                    'backbutton' => true, // para que funcione el botón de volver
                ],
            ],
];







