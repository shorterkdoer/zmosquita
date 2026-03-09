<?php    //index view
return [    'config' => 
					[   
                    'titulo' => 'Solicitudes de registro como usuario ',
                    'subtitulo' => 'Pendientes de aceptación',
                    'action' => '',
                    'url_action' => '',
                    'method' => '',
                    'tipo' => 'table',
                    'divname' => 'usrpendientes',
                    'url_data' => '/api/userpending/data',
                    'field_id' => 'id', // 'id' es el campo que se usa para el link, identifica el registro
                    'link_id' => '',    // 
					],
            'QrySpec' => 
				[
                'tables' => ['users'],
                'joincond' => '',
                'filter' => '(active = 0)',
                'order' => ['created_at DESC'],
				],
            'comandos' => 
				[
/*               'exportar' => 
					[
                    'text' => 'Exportar',
                    'url' => '',
                    'icon' => 'bi bi-file-earmark-spreadsheet',
                    'class' => 'btn btn-success'
                    
					],
                'importar' => 
					[
                    'text' => 'Importar',
                    'url' => '',
                    'icon' => 'bi bi-file-earmark-spreadsheet',
                    'class' => 'btn btn-info'
					]
*/				],
            'campos' => 
				[
                        'id' => [
                            'nombre' => 'id',
                            'type' => 'text',
                            'label' => 'ID',
                            'readonly' => true,
                            'hidden' => true,
                            'class' => 'form-control',
                            
                        ],
                        'email' => [
                            'nombre' => 'email',
                            'type' => 'text',
                            'label' => 'Email',
                            'maxlength' => 255,
                            'readonly' => true,
                            'hidden' => false,
                            'required' => true,
                            'placeholder' => 'Mail',
                            'help' => 'Cuenta de correo',
                            'class' => 'form-control',
                            'style' => 'width: 100%;',
                            'autocomplete' => 'off',
                            'pattern' => '[A-Za-z0-9 ]{1,255}',
                            'searchable' => true,
                            'sortable' => true,
                        ],
                        'created_at' => [
                            'nombre' => 'created_at',
                            'type' => 'date',
                            'label' => 'Fecha',
                            'maxlength' => 255,
                            'readonly' => true,
                            'hidden' => false,
                            'required' => true,
                            'placeholder' => 'Fecha',
                            'help' => 'Fecha de solicitud',
                            'class' => 'form-control',
                            'style' => 'width: 100%;',
                            'autocomplete' => 'off',
                            'pattern' => '[A-Za-z0-9 ]{1,255}',
                            'searchable' => false,
                            'sortable' => true,
                        ],

                    ],
            'actividades' => 
				[
                'Activar' => 
					[
                    'text' => 'Activar',
                    'url' => '', //'/activarusuario',        //  '/datospersonales/vista',  // Removed trailing slash
                    'url_params' => false, // true, // Indicates that the URL will include the ID
                    'icon' => 'bi bi-eye',
                    'class' => 'btn btn-warning'
					],
				'Borrar' => 
						[
						'text' => 'Borrar',
	                    'url' => '', //'/borrarsolicitud',  //                /matricula/admin_view    Also removed trailing slash for consistency
	                    'url_params' => false, //true, // Indicates that the URL will include the ID
	                    'icon' => 'bi bi-paperclip',
	                    'class' => 'btn btn-primary'
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
