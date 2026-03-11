<?php

/**
 * Provincia Entity Configuration
 * Consolidated from config/cruds/provincias/*.php
 */

return [
    'entity' => 'Provincia',
    'table' => 'provincias',
    'route_prefix' => 'provincias',
    'title' => 'Provincias',
    'subtitle' => 'Gestión de provincias',
    'field_id' => 'id',

    // Index view configuration
    'index' => [
        'title' => 'Provincias',
        'subtitle' => 'Vista general',
        'url_action' => '',
        'method' => '',
        'tipo' => 'table',
        'comandos' => [
            'create' => [
                'text' => 'Nueva Provincia',
                'url' => '/provincias/create',
                'icon' => 'bi bi-add',
                'class' => 'btn btn-primary'
            ],
        ],
        'actividades' => [
            'edit' => [
                'text' => 'Editar',
                'url' => '/provincias/edit',
                'icon' => 'bi bi-pencil',
                'class' => 'btn btn-warning btn-sm'
            ],
            'delete' => [
                'text' => 'Eliminar',
                'url' => '/provincias/vista',
                'icon' => 'bi bi-trash',
                'class' => 'btn btn-danger btn-sm'
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
        'campos' => [
            'id' => [
                'nombre' => 'id',
                'type' => 'text',
                'label' => 'ID',
                'readonly' => true,
                'hidden' => true,
                'class' => 'form-control',
            ],
            'nombre' => [
                'nombre' => 'nombre',
                'type' => 'text',
                'label' => 'Nombre',
                'maxlength' => 255,
                'readonly' => true,
                'hidden' => false,
                'required' => true,
                'placeholder' => 'Nombre de la provincia',
                'help' => 'Ingrese el nombre de la provincia',
                'class' => 'form-control',
                'style' => 'width: 100%;',
                'autocomplete' => 'off',
                'pattern' => '[A-Za-z0-9 ]{1,255}'
            ]
        ],
    ],

    // Create view configuration
    'create' => [
        'title' => 'Provincias',
        'subtitle' => 'Nueva provincia',
        'action' => 'create',
        'url_action' => '/provincias/store',
        'method' => 'POST',
        'tipo' => 'form',
        'comandos' => [],
        'actividades' => [],
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
        'campos' => [
            'nombre' => [
                'nombre' => 'nombre',
                'type' => 'text',
                'label' => 'Nombre',
                'maxlength' => 255,
                'readonly' => false,
                'hidden' => false,
                'required' => true,
                'placeholder' => 'Nombre de la provincia',
                'help' => 'Ingrese el nombre de la provincia',
                'class' => 'form-control',
                'style' => 'width: 100%;',
                'autocomplete' => 'off',
                'pattern' => '[A-Za-z0-9 ]{1,255}'
            ]
        ],
    ],

    // Edit view configuration
    'edit' => [
        'title' => 'Provincias',
        'subtitle' => 'Editar provincia',
        'action' => 'update',
        'url_action' => '/provincias/update',
        'method' => 'POST',
        'tipo' => 'form',
        'comandos' => [],
        'actividades' => [],
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
                'text' => 'Actualizar',
                'class' => 'btn btn-gradient btn-rounded',
                'icon' => 'bi bi-check-circle me-1',
            ],
        ],
        'campos' => [
            'nombre' => [
                'nombre' => 'nombre',
                'type' => 'text',
                'label' => 'Nombre',
                'maxlength' => 255,
                'readonly' => false,
                'hidden' => false,
                'required' => true,
                'placeholder' => 'Nombre de la provincia',
                'help' => 'Ingrese el nombre de la provincia',
                'class' => 'form-control',
                'style' => 'width: 100%;',
                'autocomplete' => 'off',
                'pattern' => '[A-Za-z0-9 ]{1,255}'
            ]
        ],
    ],

    // Delete view configuration
    'delete' => [
        'title' => 'Provincias',
        'subtitle' => 'Eliminar provincia',
        'action' => 'delete',
        'url_action' => '/provincias/delete',
        'method' => 'POST',
        'tipo' => 'form',
        'comandos' => [],
        'actividades' => [],
        'buttons' => [
            'cancel' => [
                'type' => 'button',
                'text' => 'Cancelar',
                'url' => '',
                'class' => 'btn btn-outline-secondary btn-rounded',
                'icon' => 'bi bi-arrow-left',
                'backbutton' => true,
            ],
            'submit' => [
                'type' => 'submit',
                'text' => 'Eliminar',
                'class' => 'btn btn-danger btn-rounded',
                'icon' => 'bi bi-trash me-1',
            ],
        ],
        'campos' => [
            'nombre' => [
                'nombre' => 'nombre',
                'type' => 'text',
                'label' => 'Nombre',
                'readonly' => true,
                'hidden' => false,
                'class' => 'form-control',
                'disabled' => true,
            ]
        ],
    ],
];
