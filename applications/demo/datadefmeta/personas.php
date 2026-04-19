<?php

return [
    'labels' => [
        'id' => 'Id',
        'apellido' => 'Apellido',
        'nombre' => 'Nombre',
        'telefono' => 'Telefono',
        'email' => 'Correo electrónico',
        'observaciones' => 'Observaciones',
    ],

    'form' => [
        'fields' => [
            'apellido',
            'nombre',
            'telefono',
            'email',
            'observaciones',
        ],
    ],

    'table' => [
        'columns' => [
            'id',
            'apellido',
            'nombre',
            'telefono',
            'email',
            'observaciones',
        ],
    ],

    'fields' => [
        'id' => [
            'type' => 'number',
            'rules' => ['required', 'integer'],
        ],
        'apellido' => [
            'type' => 'text',
            'rules' => ['required', 'max:100'],
        ],
        'nombre' => [
            'type' => 'text',
            'rules' => ['required', 'max:100'],
        ],
        'telefono' => [
            'type' => 'tel',
            'rules' => ['nullable', 'max:50'],
        ],
        'email' => [
            'type' => 'email',
            'rules' => ['nullable', 'max:190'],
        ],
        'observaciones' => [
            'type' => 'textarea',
            'rules' => ['nullable', 'max:65535'],
        ],
    ],

    'generator' => [
        'controller' => 'PersonasController',
        'model' => 'Persona',
        'overwrite' => false,
    ],
];
