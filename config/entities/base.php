<?php

/**
 * Base configuration for all CRUD entities
 * This file contains shared configuration that all entities inherit from
 */

return [
    'style' => [
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
        'headtagtitulo' => 'h2',
        'headtagclass' => 'text-center text-primary font-weight-bold',
        'headtagsubtitulo' => 'h4',
        'headtagclasssubt' => 'text-center text-primary font-weight-bold',
    ],
    // Common field types with their default configurations
    'field_types' => [
        'id' => [
            'type' => 'text',
            'label' => 'ID',
            'readonly' => true,
            'hidden' => true,
            'class' => 'form-control',
        ],
        'nombre' => [
            'type' => 'text',
            'label' => 'Nombre',
            'maxlength' => 255,
            'required' => true,
            'placeholder' => 'Ingrese el nombre',
            'class' => 'form-control',
            'style' => 'width: 100%;',
            'autocomplete' => 'off',
            'pattern' => '[A-Za-z0-9 ]{1,255}'
        ],
        'descripcion' => [
            'type' => 'textarea',
            'label' => 'Descripción',
            'maxlength' => 1000,
            'required' => false,
            'placeholder' => 'Ingrese la descripción',
            'class' => 'form-control',
            'rows' => 3,
        ],
        'email' => [
            'type' => 'email',
            'label' => 'Email',
            'maxlength' => 255,
            'required' => true,
            'placeholder' => 'email@ejemplo.com',
            'class' => 'form-control',
        ],
        'created_at' => [
            'type' => 'datetime',
            'label' => 'Creado',
            'readonly' => true,
            'hidden' => false,
            'class' => 'form-control',
        ],
        'updated_at' => [
            'type' => 'datetime',
            'label' => 'Actualizado',
            'readonly' => true,
            'hidden' => false,
            'class' => 'form-control',
        ],
    ],
    // Common activity templates
    'activities' => [
        'edit' => [
            'text' => 'Editar',
            'url' => '/edit',
            'icon' => 'bi bi-pencil',
            'class' => 'btn btn-warning btn-sm'
        ],
        'delete' => [
            'text' => 'Eliminar',
            'url' => '/delete',
            'icon' => 'bi bi-trash',
            'class' => 'btn btn-danger btn-sm'
        ],
        'view' => [
            'text' => 'Ver',
            'url' => '/view',
            'icon' => 'bi bi-eye',
            'class' => 'btn btn-info btn-sm'
        ],
    ],
    // Common button templates
    'buttons' => [
        'cancel' => [
            'type' => 'button',
            'text' => 'Volver',
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
