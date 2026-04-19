<?php    //index view

return [    
    'config' => 
        [
        'titulo' => 'Cobranzas',
        'subtitulo' => 'Vista general',
        'title' => 'Cobranzas',
        'subtitle' => 'Vista general',
        'field_id' => 'c.id',
        'tipo' => 'table',
        'action' => '',
        'url_action' => '',
        'method' => '',
        'divname' => 'CobranzasProfesionalFfm',
        'url_data' => '/api/historialpagos/data',
        'link_id' => 'user_id',
        'field_id' => 'c.id',

                //'link_id' => 'user_id',

                
                
        ],

    'QrySpec' => 
        [
            'tables' => [ 'comprobantespago c', 'datospersonales d' ],
            'joincond' => '(c.user_id = d.user_id)',
            'filter' => '',
            'order' => ['fecha DESC'],
        ],
    'comandos' => [
            ],
    'dynfiltros' => [
                'fecha' => [
                    'nombre' => 'fecha',
                    'type' => 'date',
                    'label' => 'Fecha',
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Fecha',
                    'help' => 'Fecha',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'searchable' => true,
                    'sortable' => true,
                    //'pattern' => '[0-9]{1,255}'
                ],
            ],
    'campos' => [
                'c.id' => [
                    'nombre' => 'id',
                    'type' => 'text',
                    'label' => 'ID',
                    'readonly' => true,
                    'hidden' => true,
                    'class' => 'form-control',
                ],
                'c.fecha' => [
                    'nombre' => 'fecha',
                    'type' => 'date',
                    'label' => 'Fecha',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'fehca',
                    'help' => 'Fecha',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'searchable' => true,
                    'sortable' => true,
                    'autocomplete' => 'off',
                    'pattern' => '[0-9]{1,255}'
                ],
                'd.apellido' => [
                    'nombre' => 'apellido',
                    'type' => 'text',
                    'label' => 'apellido',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'apellido',
                    'help' => 'Fecha',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'searchable' => true,
                    'sortable' => true,
                    'autocomplete' => 'off',
                    'pattern' => '[0-9]{1,255}'
                ],
                'd.nombre' => [
                    'nombre' => 'nombre',
                    'type' => 'text',
                    'label' => 'nombre',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'nombre',
                    'help' => 'Fecha',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'searchable' => true,
                    'sortable' => true,
                    'autocomplete' => 'off',
                    'pattern' => '[0-9]{1,255}'
                ],
                'c.monto' => [
                    'nombre' => 'monto',
                    'type' => 'decimal',
                    'label' => 'Importe pagado',
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Importe',
                    'help' => 'Importe',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    //'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],


            ],
    'actividades' => [
                'Datos' => [
                    'text' => 'Datos',
                    'url' => '/vercomprobante',  // Removed trailing slash
                    'url_params' => true, // Indicates that the URL will include the ID
                    'param_field' => 'c.id', // Specify the field to use as a parameter
                    'icon' => 'bi bi-paperclip',
                    'class' => 'btn btn-warning'
                ],
                'Borrar' => [
                    'text' => 'Borrar',
                    'url' => '/quitarpago',  //                /matricula/admin_view    Also removed trailing slash for consistency
                    'url_params' => true, // Indicates that the URL will include the ID
                    'icon' => 'bi bi-trash',
                    'param_field' => 'c.id', // Specify the field to use as a parameter
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


