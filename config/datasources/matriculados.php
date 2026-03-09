<?php    //index view
return [    'config' => 
                [
                    'title' => 'Padrón general',
                    'field_id' => 'id',
                    'tipo' => 'table',
                    'subtitle' => 'Inscriptos',
                    'action' => '',
                    'url_action' => '',
                    'method' => '',
                    'divname' => 'matriculados',
                    'url_data' => '/api/matriculados/data',
                    'link_id' => 'm.user_id',
                ],
					
            'QrySpec' => 
				[
                'tables' => [ 'datospersonales d', 'matriculas m'],
                'joincond' => 'd.user_id = m.user_id',
                'filter' => '(apellido is not null) and (m.aprobado is not null)',
                'order' => ['d.created_at DESC'],
				],
            'comandos' => 
				[
               'exportar' => 
					[
                    'text' => 'Exportar',
                    'url' => '/',
                    'icon' => 'bi bi-file-earmark-spreadsheet',
                    'class' => 'btn btn-success'
                    
					],
                'importar' => 
					[
                    'text' => 'Importar',
                    'url' => '/',
                    'icon' => 'bi bi-file-earmark-spreadsheet',
                    'class' => 'btn btn-info'
					]
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
                'matriculaasignada' => [
                    'nombre' => 'matriculaasignada',
                    'type' => 'text',
                    'label' => 'matriculaasignada',
                    'searchable' => true,
                    'sortable' => true,
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Matricula',
                    'help' => 'Ingrese matrícula',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[0-9]{1,255}'
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
                'm.user_id' => [
                    'nombre' => 'm.user_id',
                    'type' => 'text',
                    'label' => 'user_id',
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
                    'param_field' => 'm.user_id', // Specify the field to use as a parameter
                    'icon' => 'bi bi-eye',
                    'class' => 'btn btn-warning'
                ],
                'Documentos' => [
                    'text' => 'Documentos',
                    'url' => '/verdocumentacion',  //                /matricula/admin_view    Also removed trailing slash for consistency
                    'url_params' => true, // Indicates that the URL will include the ID
                    'param_field' => 'm.user_id', // Specify the field to use as a parameter
                    'icon' => 'bi bi-paperclip',
                    'class' => 'btn btn-primary'
                ],
                'Carnet' => [
                    'text' => 'Carnet',
                    'url' => '/credencial',  //                /matricula/admin_view    Also removed trailing slash for consistency
                    'url_params' => true, // Indicates that the URL will include the ID
                    'param_field' => 'matriculaasignada', // Specify the field to use as a parameter
                    'icon' => 'bi bi-credit-card-2-front-fill', //<i class="bi-credit-card-2-front-fill"></i>
                    'class' => 'btn btn-primary'
                ],

                'Rol' => [
                    'text' => 'Rol',
                    //hay que crear la actividad y poner url_params a true
                    'url' => '',  // Also removed trailing slash for consistency
                    'url_params' => false, // Indicates that the URL will include the ID
                    'param_field' => 'm.user_id', // Specify the field to use as a parameter
                    'icon' => 'bi bi-person-workspace',
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







