<?php
return [
    'config' => [
        'titulo' => 'Rechazar revisión',
        'subtitulo' => 'Control físico no satisfactorio',

        'field_id' => 'user_id',
        'tipo' => 'form',
        'icon' => 'person',
        'action' => 'post', // opcional, para los formularios
        'url_action' => '/rechazarverificacion', // opcional, para los formularios
        'method' => 'post',     // opcional, para los formularios
        'divname' => 'RechazoRevisionfrm', //nombre para el div del listado, si se usa AJAX
        'url_data' => '', // URL para obtener los datos (opcional, si se usa AJAX) debe estar en config/routes.php
        'link_id' => '', 
        ],
'QrySpec' => 
				[
                'tables' => [ 'datospersonales d', 'matriculas m', 'users u' ],
                'joincond' => 'm.user_id = d.user_id and m.user_id = u.id',
                'filter' => '(m.freezedata is not null) '.
                        'and (m.revision is null) and (m.verificado is null) '.
                        'and (m.aprobado is null) and (m.baja is null)',
                'order' => ['d.created_at DESC'],
				],

    'comandos' => [
               ],

    'campos' => [
                'nombre' => 
					[
					'nombre' => 'nombre', 
					'type' => 'text', 
					'label' => 'Nombre', 
					'placeholder' => 'Ingrese nombre', 
					'readonly' => true, 
					'hidden' => false, 
					'help' => 'Ingrese el nombre', 
					'class' => 'form-control', 
					'style' => 'width: 100%;', 
					'autocomplete' => 'off', 
					'pattern' => '[A-Za-z0-9 ]{1,255}', 
					'options' => [], 
					'columna_rel' => '', 
					'default' => 'NULL', 
					'tabla_rel' => ''
					],
				'apellido' => 
					[
					'nombre' => 'apellido', 
					'type' => 'text', 
					'label' => 'Apellido', 
					'placeholder' => 'Ingrese apellido', 
					'readonly' => true, 
					'hidden' => false, 
					'help' => 'Ingrese el nombre', 
					'class' => 'form-control', 
					'style' => 'width: 100%;', 
					'autocomplete' => 'off', 
					'pattern' => '[A-Za-z0-9 ]{1,255}', 
					'options' => [], 
					'columna_rel' => '', 
					'default' => 'NULL', 
					'tabla_rel' => ''
					],
				'dni' => 
					['nombre' => 'dni', 
					'type' => 'text', 
					'label' => 'Dni', 
					'placeholder' => 'Ingrese dni', 
					'readonly' => true, 
					'hidden' => false, 
					'help' => 'Ingrese el nombre', 
					'class' => 'form-control', 
					'style' => 'width: 100%;', 
					'autocomplete' => 'off', 
					'pattern' => '[A-Za-z0-9 ]{1,255}', 
					'options' => [], 
					'columna_rel' => '', 
					'default' => 'NULL', 
					'tabla_rel' => ''
					],
				'observaciones' => 
					['nombre' => 'observaciones', 
					'type' => 'text', 
					'label' => 'Motivo', 
					'placeholder' => 'Ingrese motivo', 
					'readonly' => false, 
					'hidden' => false, 
					'help' => 'Ingrese el motivo', 
					'class' => 'form-control', 
					'style' => 'width: 100%;', 
					'autocomplete' => 'off', 
					'pattern' => '[A-Za-z0-9\s\$\@\#\!\%\&\(\)\+\-\?.,;:]*', 
					'options' => [], 
					'columna_rel' => '', 
					'default' => 'NULL', 
					'tabla_rel' => ''
					],
        ],
    'buttons' => [
        'cancel' => [
            'type' => 'button',
            'text' => 'Volver',
            'url' => '/dashboard',
            'class' => 'btn btn-outline-secondary btn-rounded',
            'icon' => 'bi bi-arrow-left',

            ],
        'Informar rechazo' => [
            'type' => 'submit',
            'text' => 'Informar rechazo',
            'class' => 'btn btn-outline-secondary btn-rounded',
            'icon' => 'bi bi-x-octagon-fill',

            ],

        ],

];