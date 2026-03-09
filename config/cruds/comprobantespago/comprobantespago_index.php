<?php    //index view
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
return [    
    'config' => 
        [
        'titulo' => 'Mis comprobantes de pago',
        'subtitulo' => 'Vista general',
        'title' => 'Mis comprobantes de pago',
        'subtitle' => 'Vista general',
        'field_id' => 'id',
        'tipo' => 'table',
        'action' => '',
        'url_action' => '',
        'method' => '',
        'divname' => 'miscomprobantes',
        'url_data' => '/api/comprobantespago/data',
        'link_id' => 'user_id',
        'field_id' => 'id',

                //'link_id' => 'user_id',

                
                
        ],

    'QrySpec' => 
        [
            'tables' => [ 'comprobantespago'],
            'joincond' => '',
            'filter' => '',
            'order' => ['fecha DESC'],
        ],
    'comandos' => [
                'Nueva' => [
                    'text' => 'Nuevo comprobante',
                    'url' => '/comprobantespago/create' , 
                    'icon' => 'bi bi-add',
                    'class' => 'btn btn-primary',
                    'url_id' => true,
               ],
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
                'monto' => [
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

                'observaciones' => [
                    'nombre' => 'observaciones',
                    'type' => 'text',
                    'label' => 'Observaciones',
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Observaciones',
                    'help' => 'Observaciones',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'searchable' => true,
                    'sortable' => true,
                    'autocomplete' => 'off',
                    //'pattern' => '[0-9]{1,255}'

                    ]

            ],
    'actividades' => [
                'Datos' => [
                    'text' => 'Datos',
                    'url' => '/vercomprobante',  // Removed trailing slash
                    'url_params' => true, // Indicates that the URL will include the ID
                    'param_field' => 'id', // Specify the field to use as a parameter
                    'icon' => 'bi bi-paperclip',
                    'class' => 'btn btn-warning'
                ],
                'Borrar' => [
                    'text' => 'Borrar',
                    'url' => '/quitarpago',  //                /matricula/admin_view    Also removed trailing slash for consistency
                    'url_params' => true, // Indicates that the URL will include the ID
                    'icon' => 'bi bi-trash',
                    'param_field' => 'id', // Specify the field to use as a parameter
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


