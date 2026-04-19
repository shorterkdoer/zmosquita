<?php    //index view
return [
    'config' => [
        'title' => 'Cargos',
        'subtitle' => 'Vista general',
        'action' => '',
        'method' => '',
        'tipo' => 'table',
        'class_div' => "p-4 bg-light rounded shadow-sm h-100",
        'class_form' => 'row g-4 p-4 bg-light rounded shadow-sm',
        'class_tr' => 'p-4 bg-light rounded shadow-sm h-100',
        'class_th' => 'text-center',
        'class_td' => 'text-center',
        'class_table' => 'table table-striped table-bordered',
        'class_table_div' => 'row g-4 p-4 bg-light shadow-sm',
        'class_thead' => 'thead-light',
        'class_tbody' => 'container',
        'class_tfoot' => 'thead-light',
        'field_id' => 'id',
    ],
    'comandos' => [
        'Nuevo' => [
            'text' => 'Nuevo Cargos',
            'url' => '/cargos/create/',
            'icon' => 'bi bi-add',
            'class' => 'btn btn-primary'
        ],
    ],
    'campos' => [
                'id' => [
                    'nombre' => 'id',
                    'type' => 'number',
                    'label' => 'Id',
                    'maxlength' => 11,
                    'readonly' => true,
                    'hidden' => true,
                    'required' => false,
                    'placeholder' => 'Ingrese Id',
                    'help' => 'Ingrese Id',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                ],
                'nombre' => [
                    'nombre' => 'nombre',
                    'type' => 'text',
                    'label' => 'Nombre',
                    'maxlength' => 50,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Ingrese Nombre',
                    'help' => 'Ingrese Nombre',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                ],
                'jerarquia' => [
                    'nombre' => 'jerarquia',
                    'type' => 'number',
                    'label' => 'Jerarquia',
                    'maxlength' => 11,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Ingrese Jerarquia',
                    'help' => 'Ingrese Jerarquia',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                ],
    ],
    'actividades' => [
        'edit' => [
            'text' => 'Editar',
            'url' => '/cargos/edit',
            'icon' => 'bi bi-pencil',
            'class' => 'btn btn-warning'
        ],
        'delete' => [
            'text' => 'Eliminar',
            'url' => '/cargos/vista',
            'icon' => 'bi bi-trash',
            'class' => 'btn btn-danger'
        ]
    ],
    'buttons' => [
        'cancel' => [
            'type' => 'button',
            'text' => 'Volver',
            'url' => '',
            'class' => 'btn btn-outline-secondary btn-rounded',
            'icon' => 'bi bi-arrow-left',
            'backbutton' => true,
        ],
    ],
];
