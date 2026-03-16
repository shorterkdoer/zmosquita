<?php    //edit view
return [
    'config' => [
        'title' => 'Cargos',
        'subtitle' => 'Modificar registro',
        'action' => 'update',
        'url_action' => '/cargos/update',
        'method' => 'POST',
        'tipo' => 'form',
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
        'delete' => [
            'text' => 'Eliminar',
            'url' => '/cargos/delete/',
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
        'submit' => [
            'type' => 'submit',
            'text' => 'Guardar',
            'class' => 'btn btn-gradient btn-rounded',
            'icon' => 'bi bi-check-circle me-1',
        ],
    ],
];
