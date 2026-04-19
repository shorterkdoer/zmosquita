<?php    //index view
return [    'config' => 
                [
                    'titulo' => 'Matriculados dados de baja',
                    'subtitulo' => '',
                    'field_id' => 'id',
                    'tipo' => 'table',
                    'subtitle' => '',
                    'action' => '',
                    'url_action' => '',
                    'method' => '',
                    'divname' => 'matriculasdebaja',
                    'url_data' => '/api/matricula/debaja', 
                    'link_id' => 'm.user_id',
                ],
					
            'QrySpec' => 
				[
                'tables' => [ 'datospersonales d', 'matriculas m' ],
                'joincond' => 'm.user_id = d.user_id',
                'filter' => '(m.aprobado is not null)'.
                        'and (m.comisionotorgante is not null) and (m.baja is not null)',
                'order' => ['d.created_at DESC'],
				],
            'comandos' => 
				[
				],
            'campos' => [
                'd.id' => [
                    'nombre' => 'id',
                    'type' => 'text',
                    'label' => 'ID',
                    'readonly' => true,
                    'hidden' => true,
                    'class' => 'form-control',
                ],


                'matriculaasignada' => [
                    'nombre' => 'matriculaasignada', 	
                    'type' => 'text',
                    'label' => 'Matrícula',
                    'searchable' => true,
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Matrícula',
                    'help' => 'Ingrese matrícula',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],

                'nombre' => [
                    'nombre' => 'nombre',
                    'type' => 'text',
                    'label' => 'Nombre',
                    'searchable' => true,
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Nombres',
                    'help' => 'Ingrese el nombre',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'apellido' => [
                    'nombre' => 'apellido',
                    'type' => 'text',
                    'label' => 'Apellido',
                    'searchable' => true,
                    'sortable' => true,
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Apellido',
                    'help' => 'Ingrese apellido',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'celular' => [
                    'nombre' => 'celular',
                    'type' => 'text',
                    'label' => 'Celular',
                    'searchable' => true,
                    'sortable' => true,
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Celular',
                    'help' => 'Ingrese celular',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[0-9]{1,255}'
                ],
                'd.user_id' => [
                    'nombre' => 'user_id',
                    'type' => 'text',
                    'label' => 'ID',
                    'readonly' => true,
                    'hidden' => true,
                    'class' => 'form-control',
                ],


                    ],
            'actividades' => [
                'Datos' => [
                    'text' => 'Datos',
                    'url' => '/verdatospersonales',  // Removed trailing slash
                    'url_params' => true, // Indicates that the URL will include the ID
                    'param_field' => 'd.user_id', // Specify the field to use as a parameter
                    'icon' => 'bi bi-eye',
                    'newpage' => true, // Indicates that this button opens in a new page
                    'class' => 'btn btn-warning'
                ],
                'Docs' => [
                    'text' => 'Docs',
                    'url' => '/verdocumentacion',  //                /matricula/admin_view    Also removed trailing slash for consistency
                    'url_params' => true, // Indicates that the URL will include the ID
                    'param_field' => 'd.user_id', // Specify the field to use as a parameter
                    'icon' => 'bi bi-paperclip',
                    'newpage' => true, // Indicates that this button opens in a new page
                    'class' => 'btn btn-primary'
                ],
                'Pagos' => [
                    'text' => 'Pagos',
                    'url' => '/historialpagos',  //                /matricula/admin_view    Also removed trailing slash for consistency
                    'url_params' => true, // Indicates that the URL will include the ID
                    'param_field' => 'd.user_id', // Specify the field to use as a parameter
                    'icon' => 'bi bi-currency-dollar',
                    'newpage' => true, // Indicates that this button opens in a new page
                    'class' => 'btn btn-primary'
                ],
                'Legajo' => [
                    'text' => 'Legajo',
                    //hay que crear la actividad y poner url_params a true
                    'url' => '/verlegajo',  // Also removed trailing slash for consistency
                    'url_params' => true, // Indicates that the URL will include the ID
                    'param_field' => 'd.user_id', // Specify the field to use as a parameter
                    'icon' => 'bi bi-journal-text',
                    'newpage' => true, // Indicates that this button opens in a new page
                    'class' => 'btn btn-warning'
                ],
                'Info' => [
                    'text' => 'Info',
                    //hay que crear la actividad y poner url_params a true
                    'url' => '/carnet',  // Also removed trailing slash for consistency
                    'url_params' => true, // Indicates that the URL will include the ID
                    'param_field' => 'matriculaasignada', // Specify the field to use as a parameter
                    'icon' => 'bi bi-info-square',
                    'newpage' => true, // Indicates that this button opens in a new page
                    'class' => 'btn btn-warning'
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







