<?php    //index view
return [    'config' => 
                [
                    'titulo' => 'Control de documentación',
                    'subtitulo' => 'Profesionales en condiciones de revisión física',
                    'field_id' => 'user_id',
                    'tipo' => 'table',
                    
                    'action' => '',
                    'url_action' => '',
                    'method' => '',
                    'divname' => 'ctrlfisico',
                    'url_data' => '/api/ctrlfisico/data',
                    'link_id' => 'm.user_id',
                ],
					
            'QrySpec' => 
				[
                'tables' => [ 'datospersonales d', 'matriculas m', 'users u' ],
                'joincond' => 'm.user_id = d.user_id and m.user_id = u.id',
                'filter' => '(m.freezedata is not null) '.
                        'and (m.revision is not null) and (m.verificado is not null) '.
                        'and (m.aprobado is null) and (m.baja is null)',
                'order' => ['d.created_at DESC'],
				],
            'comandos' => 
				[
				],
            'campos' => [

                'revision' => [
                    'nombre' => 'revision',
                    'type' => 'date',
                    'label' => 'revision',
                    'readonly' => true,
                    'hidden' => true,
                    'class' => 'form-control',
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
                'd.user_id' => [
                    'nombre' => 'user_id',
                    'type' => 'text',
                    'label' => 'ID',
                    'readonly' => true,
                    'hidden' => true,
                    'class' => 'form-control',
                ],
                'email' => [
                    'nombre' => 'u.email',
                    'type' => 'text',
                    'label' => 'Email',
                    'readonly' => false,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Email',
                    'help' => 'Ingrese email',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
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
                'Adjuntos' => [
                    'text' => 'Adjuntos',
                    'url' => '/verdocumentacion',  //                /matricula/admin_view    Also removed trailing slash for consistency
                    'url_params' => true, // Indicates that the URL will include the ID
                    'param_field' => 'd.user_id', // Specify the field to use as a parameter
                    'icon' => 'bi bi-paperclip',
                    'newpage' => true, // Indicates that this button opens in a new page
                    'class' => 'btn btn-primary'
                ],
                'Citar' => [
                    'text' => 'Citar',
                    'url' => '',  //                /matricula/admin_view    Also removed trailing slash for consistency
                    'url_params' => false, // Indicates that the URL will include the ID
                    'param_field' => 'd.user_id', // Specify the field to use as a parameter
                    'icon' => 'bi bi-calendar-date', //<i class="bi-credit-card-2-front-fill"></i>
                    'newpage' => true, // Indicates that this button opens in a new page
                    'class' => 'btn btn-primary'
                ],

                'Rechazar' => [
                    'text' => 'Rechazar',
                    'url' => '/rechazarfase2',  //                /matricula/admin_view    Also removed trailing slash for consistency
                    'url_params' => true, // Indicates that the URL will include the ID
                    'param_field' => 'd.user_id', // Specify the field to use as a parameter
                    'icon' => 'bi bi-x-octagon', //<i class="bi-credit-card-2-front-fill"></i>
                    'newpage' => true, // Indicates that this button opens in a new page
                    'class' => 'btn btn-primary'
                ],
                'Aprobar' => [
                    'text' => 'Aprobar',
                    'url' => '/aprobarfisico',  //                /matricula/admin_view    Also removed trailing slash for consistency
                    'url_params' => true, // Indicates that the URL will include the ID
                    'param_field' => 'd.user_id', // Specify the field to use as a parameter
                    'icon' => 'bi bi-person-fill-check', //<i class="bi-credit-card-2-front-fill"></i>
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





