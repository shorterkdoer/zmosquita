<?php

return [
    'labels' => [
        'apellido' => 'Apellido',
        'nombre' => 'Nombre',
        'telefono' => 'Teléfono',
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
            'apellido',
            'nombre',
            'telefono',
            'email',
        ],
    ],

    'fields' => [
        'apellido' => [
            'type' => 'text',
            'rules' => ['required', 'max:100'],
        ],
        'nombre' => [
            'type' => 'text',
            'rules' => ['required', 'max:100'],
        ],
        'telefono' => [
            'type' => 'text',
            'rules' => ['nullable', 'max:50'],
        ],
        'email' => [
            'type' => 'text',
            'rules' => ['nullable', 'email', 'max:190'],
        ],
        'observaciones' => [
            'type' => 'textarea',
            'rules' => ['nullable', 'max:2000'],
        ],
    ],

    'generator' => [
        'controller' => 'PersonasController',
        'model' => 'Persona',
        'overwrite' => false,
    ],
];