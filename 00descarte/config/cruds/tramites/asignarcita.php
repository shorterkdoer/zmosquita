<?php
return [
    'config' => [
        'titulo' => 'Asignar Cita',
        'subtitulo' => 'Verificador de la documentación física',

        'field_id' => 'id',
        'tipo' => 'form',
        'icon' => 'person',
        'action' => 'post', // opcional, para los formularios
        'url_action' => '/ponerverificador', // opcional, para los formularios
        'method' => 'post',     // opcional, para los formularios
        'divname' => 'PrimerRevisorfrm', //nombre para el div del listado, si se usa AJAX
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
/*
			'user_id' => 
			['nombre' => 'user_id', 
			'type' => 'text', 
			'label' => 'User id', 
			'placeholder' => 'Ingrese user id', 
		'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'required' => true, 'tabla_rel' => ''],
        'direccion_calle' => ['nombre' => 'direccion_calle', 'type' => 'text', 'label' => 'Direccion calle', 'placeholder' => 'Ingrese direccion calle', 'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'default' => 'NULL', 'tabla_rel' => ''],
        'direccion_numero' => ['nombre' => 'direccion_numero', 'type' => 'text', 'label' => 'Direccion numero', 'placeholder' => 'Ingrese direccion numero', 'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'default' => 'NULL', 'tabla_rel' => ''],
        'direccion_piso' => ['nombre' => 'direccion_piso', 'type' => 'text', 'label' => 'Direccion piso', 'placeholder' => 'Ingrese direccion piso', 'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'default' => 'NULL', 'tabla_rel' => ''],
        'direccion_depto' => ['nombre' => 'direccion_depto', 'type' => 'text', 'label' => 'Direccion depto', 'placeholder' => 'Ingrese direccion depto', 'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'default' => 'NULL', 'tabla_rel' => ''],
        'direccion_cp' => ['nombre' => 'direccion_cp', 'type' => 'text', 'label' => 'Direccion cp', 'placeholder' => 'Ingrese direccion cp', 'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'default' => 'NULL', 'tabla_rel' => ''],
        'telefono' => ['nombre' => 'telefono', 'type' => 'text', 'label' => 'Telefono', 'placeholder' => 'Ingrese telefono', 'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'default' => 'NULL', 'tabla_rel' => ''],
        'celular' => ['nombre' => 'celular', 'type' => 'text', 'label' => 'Celular', 'placeholder' => 'Ingrese celular', 'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'default' => 'NULL', 'tabla_rel' => ''],
        'created_at' => ['nombre' => 'created_at', 'type' => 'text', 'label' => 'Created at', 'placeholder' => 'Ingrese created at', 'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'default' => 'NULL', 'tabla_rel' => ''],
        'updated_at' => ['nombre' => 'updated_at', 'type' => 'text', 'label' => 'Updated at', 'placeholder' => 'Ingrese updated at', 'readonly' => true, 'hidden' => false, 'help' => 'Ingrese el nombre', 'class' => 'form-control', 'style' => 'width: 100%;', 'autocomplete' => 'off', 'pattern' => '[A-Za-z0-9 ]{1,255}', 'options' => [], 'columna_rel' => '', 'default' => 'NULL', 'tabla_rel' => ''],
  */  
        ],
    'buttons' => [
        'cancel' => [
            'type' => 'button',
            'text' => 'Volver',
            'url' => '/dashboard',
            'class' => 'btn btn-outline-secondary btn-rounded',
            'icon' => 'bi bi-arrow-left',

            ],
        'Asignar cita' => [
            'type' => 'submit',
            'text' => 'Asignar Cita',
            'class' => 'btn btn-outline-secondary btn-rounded',
            'icon' => 'bi bi-calendar2-date-fill',

            ],

        ],

];