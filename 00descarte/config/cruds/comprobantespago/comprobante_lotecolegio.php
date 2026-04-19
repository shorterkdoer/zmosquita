<?php

return [
    'config' => [
        'titulo'      => 'Lote de cobranzas',
        'subtitulo'   => 'Informados por el Colegio Profesional',
        'action'      => 'create',
        'url_action'  => '/comprobantespago/lote-colegio/preview',
        'method'      => 'POST',
        'tipo'        => 'form',
        'class_div'   => "p-4 bg-light rounded shadow-sm h-100",
        'class_form'  => 'row g-4 p-4 bg-light rounded shadow-sm',
        'class_tr'    => 'p-4 bg-light rounded shadow-sm h-100',
        'class_th'    => 'text-center',
        'class_td'    => 'text-center',
        'enctype'     => 'multipart/form-data', // IMPORTANTE para subir el xlsx
    ],

    // No necesitamos user_id acá; se resuelve por matrícula.
    'campos' => [
        'fecha' => [
            'nombre'       => 'fecha',
            'type'         => 'date',
            'label'        => 'Fecha de imputación',
            'maxlength'    => 10,
            'readonly'     => false,
            'hidden'       => false,
            'required'     => true,
            'placeholder'  => '',
            'help'         => 'Fecha que se usará como fecha del comprobante',
            'class'        => 'form-control',
            'style'        => 'width: 100%;',
            'autocomplete' => 'off',
        ],

        'monto' => [
            'nombre'       => 'monto',
            'type'         => 'decimal',
            'step'         => '0.01',
            'label'        => 'Importe de la cuota',
            'maxlength'    => 255,
            'readonly'     => false,
            'hidden'       => false,
            'required'     => true,
            'placeholder'  => 'Importe de la cuota',
            'help'         => 'Importe que se aplicará a cada matrícula del lote',
            'class'        => 'form-control',
            'style'        => 'width: 100%;',
            'autocomplete' => 'off',
        ],

        'archivo' => [
            'nombre'       => 'archivo',
            'type'         => 'file',
            'label'        => 'Archivo Excel (.xlsx)',
            'maxlength'    => 255,
            'readonly'     => false,
            'hidden'       => false,
            'required'     => true,
            'placeholder'  => '',
            'help'         => 'Seleccione la planilla de Excel enviada por el Colegio',
            'class'        => 'form-control',
            'style'        => 'width: 100%;',
            'accept'       => '.xlsx',
        ],
    ],

    'actividades' => [
        // por ahora vacío
    ],

    'buttons' => [
        'cancel' => [
            'type'       => 'button',
            'text'       => 'Volver',
            'url'        => '',
            'class'      => 'btn btn-outline-secondary btn-rounded',
            'icon'       => 'bi bi-arrow-left',
            'backbutton' => true,
        ],

        'submit' => [
            'type'  => 'submit',
            'text'  => 'Revisar detalle',
            'class' => 'btn btn-gradient btn-rounded',
            'icon'  => 'bi bi-search',
        ],
    ],
];




