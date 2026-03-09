<?php    //index view
return [    'config' => 
                [
                    'titulo' => 'Aspirantes aprobados en primera fase',
                    'subtitulo' => 'Control de documentación',
                    'field_id' => 'id',
                    'tipo' => 'table',
                    'subtitle' => '',
                    'action' => '',
                    'url_action' => '',
                    'method' => '',
                    'divname' => 'Matri4Rev',
                    'url_data' => '/api/rev4verificacion/data',
                    'link_id' => 'm.user_id',
                ],
					
            'QrySpec' => 
				[
                'tables' => [ 'datospersonales d', 'matriculas m' ],
                'joincond' => 'm.user_id = d.user_id',
                'filter' => '(d.apellido is not null) and (not (m.freezedata is null)) '.
                        'and (not (m.verificado is null)) '.
                        'and (m.aprobado is null) and (m.baja is null)',
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
                'verificado' => [
                    'nombre' => 'verificado',
                    'type' => 'date',
                    'label' => 'verificado',
                    'readonly' => true,
                    'hidden' => true,
                    'class' => 'form-control',
                ],


                'matriculaministerio' => [
                    'nombre' => 'matriculaministerio',
                    'type' => 'text',
                    'label' => 'matriculaministerio',
                    'searchable' => true,
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'matriculaministerio',
                    'help' => 'Ingrese matriculaministerio',
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
                'Documentos' => [
                    'text' => 'Documentos',
                    'url' => '/verdocumentacion',  //                /matricula/admin_view    Also removed trailing slash for consistency
                    'url_params' => true, // Indicates that the URL will include the ID
                    'param_field' => 'd.user_id', // Specify the field to use as a parameter
                    'icon' => 'bi bi-paperclip',
                    'newpage' => true, // Indicates that this button opens in a new page
                    'class' => 'btn btn-primary'
                ],
                'Revisor' => [
                    'text' => 'Revisor',
                    'url' => '/marcarrevisor',  //                /matricula/admin_view    Also removed trailing slash for consistency
                    'url_params' => true, // Indicates that the URL will include the ID
                    'param_field' => 'd.user_id', // Specify the field to use as a parameter
                    'icon' => 'bi bi-credit-card-2-front-fill', //<i class="bi-credit-card-2-front-fill"></i>
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







