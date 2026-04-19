<?php    //index view
return [    
    'config' => 
                [
                    'titulo' => 'Inscriptos listos para revisión física',
                    'subtitulo' => 'Control de documentación',
                    'field_id' => 'user_id',
                    'tipo' => 'table',
                    'action' => '',
                    'url_action' => '',
                    'method' => '',
                    'divname' => 'Matri4Fisica',
                    'url_data' => '/api/asp4fisica/data',
                    'link_id' => 'user_id',
                ],
					
            'QrySpec' => 
				[
                'tables' => [ 'datospersonales d', 'matriculas m' ],
                'joincond' => 'm.user_id = d.user_id',
                'filter' => '(not (m.freezedata is null)) '.
                        'and (m.verificado <> null)) '.
                        'and (m.aprobado is null) and (m.baja is null)',
                'order' => ['verificado'],
				],
            'comandos' => 
				[
				],
            'campos' => [
                'verificado' => [
                    'nombre' => 'verificado',
                    'type' => 'date',
                    'label' => 'verificado',
                    'readonly' => true,
                    'hidden' => false,
                    'class' => 'form-control',
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
                            'datasource'   => 'd',
                            'id_field'     => 'user_id',
                            'mostrarcampo' => ['apellido','nombre'], // sumá campos a gusto
                            'separator'    => [', '],
                            ],
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
                'Documentos' => [
                    'text' => 'Documentos',
                    'url' => '/verdocumentacion',  //                /matricula/admin_view    Also removed trailing slash for consistency
                    'url_params' => true, // Indicates that the URL will include the ID
                    'param_field' => 'd.user_id', // Specify the field to use as a parameter
                    'icon' => 'bi bi-paperclip',
                    'newpage' => true, // Indicates that this button opens in a new page
                    'class' => 'btn btn-primary'
                ],
                'Rechazar' => [
                    'text' => 'Rechazar',
                    'url' => '/rechazarfisico',  //                /matricula/admin_view    Also removed trailing slash for consistency
                    'url_params' => true, // Indicates that the URL will include the ID
                    'param_field' => 'd.user_id', // Specify the field to use as a parameter
                    'icon' => 'bi bi-x-octagon-fill', //<i class="bi-credit-card-2-front-fill"></i>
                    'newpage' => true, // Indicates that this button opens in a new page
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







