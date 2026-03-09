<?php

return ['edit' => 
            [
            'title' => 'Gestión de Provincias',
            'subtitle' => 'Formulario de edición de provincias',
            'action' => '/provincias/update',
            'method' => 'POST',
            'campos' => [
                'nombre' => [
                    'type' => 'text',
                    'label' => 'Nombre',
                    'maxlength' => 255,
                    'required' => true, 
                    'readonly' => false,
                    'placeholder' => 'Nombre de la provincia',
                    'help' => 'Ingrese el nombre de la provincia',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}',
                    ]
                ],
            'buttons' => [
                'submit' => 'Modificar',
                'cancel' => 'Cancelar'
                ],
            ],
            'index' => 
            [
            'title' => 'Provincias',
            'subtitle' => 'Vista general',
            'action' => '',
            'method' => '',
            'campos' => [
                'id' => [
                    'type' => 'text',
                    'label' => 'ID',
                    'readonly' => true,
                    'class' => 'form-control',
                    'hidden' => true,

                ],
                


                "nombre"=> [
                    'type' => 'text',
                    'label' => 'Nombre',
                    'maxlength' => 255,
                    'required' => true,
                    'readonly' => true,
                    'hidden' => false,
                    'relative' => '',
                    'placeholder' => 'Nombre',
                    'help' => 'Ingrese su nombre',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                "apellido" => [
                    'type' => 'text',
                    'label' => 'Nombre',
                    'maxlength' => 255,
                    'required' => true,
                    'readonly' => true,
                    'hidden' => false,
                    'relative' => '',
                    'placeholder' => 'Apellido',
                    'help' => 'Ingrese su apellido',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                "dni"=> [
                    'type' => 'text',
                    'label' => 'DNI',
                    'maxlength' => 255,
                    'required' => true,
                    'readonly' => true,
                    'hidden' => false,
                    'relative' => '',
                    'placeholder' => 'Documento Nacional de Identidad',
                    'help' => 'Ingrese su DNI',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                "direccion_calle"=> [
                    'type' => 'text',
                    'label' => 'Nombre',
                    'maxlength' => 255,
                    'required' => true,
                    'readonly' => true,
                    'hidden' => false,
                    'relative' => '',
                    'placeholder' => 'Calle',
                    'help' => 'Ingrese Nombre de la calle',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                "direccion_numero"=> [
                    'type' => 'text',
                    'label' => 'Número',
                    'maxlength' => 255,
                    'required' => true,
                    'readonly' => true,
                    'hidden' => false,
                    'relative' => '',
                    'placeholder' => 'Número',
                    'help' => 'Ingrese Número (Altura de la calle)',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                "direccion_piso" => [
                    'type' => 'text',
                    'label' => 'Piso',
                    'maxlength' => 255,
                    'required' => true,
                    'readonly' => true,
                    'hidden' => false,
                    'relative' => '',
                    'placeholder' => 'Piso',
                    'help' => 'Ingrese piso',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                "direccion_depto"=> [
                    'type' => 'text',
                    'label' => 'Departamento',
                    'maxlength' => 255,
                    'required' => true,
                    'readonly' => true,
                    'hidden' => false,
                    'relative' => '',
                    'placeholder' => 'Departamento',
                    'help' => 'Ingrese departamento',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                "direccion_cp"=> [
                    'type' => 'text',
                    'label' => 'Nombre',
                    'maxlength' => 255,
                    'required' => true,
                    'readonly' => true,
                    'hidden' => false,
                    'relative' => '',
                    'placeholder' => 'Código Postal',
                    'help' => 'Ingrese código postal',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                "ciudad_id"=> [
                    'type' => 'text',
                    'label' => 'Ciudad',
                    'maxlength' => 255,
                    'required' => true,
                    'readonly' => true,
                    'hidden' => false,
                    'relative' => 'ciudad',
                    'relative_desc' => 'nombre',
                    'placeholder' => 'Ciudad',
                    'help' => 'Ciudad',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                "provincia_id"=> [
                    'type' => 'text',
                    'label' => 'Nombre',
                    'maxlength' => 255,
                    'required' => true,
                    'readonly' => true,
                    'hidden' => false,
                    'relative' => 'provincias',
                    'relative_desc' => 'nombre',
                    'placeholder' => 'Apellido',
                    'help' => 'Ingrese su apellido',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                "telefono"=> [
                    'type' => 'text',
                    'label' => 'Telefono',
                    'maxlength' => 255,
                    'required' => true,
                    'readonly' => true,
                    'hidden' => false,
                    'relative' => '',
                    'placeholder' => 'Teléfono fijo',
                    'help' => 'Ingrese teléfono fijo',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                "celular"=> [
                    'type' => 'text',
                    'label' => 'Celular',
                    'maxlength' => 255,
                    'required' => true,
                    'readonly' => true,
                    'hidden' => false,
                    'relative' => '',
                    'placeholder' => 'Celular',
                    'help' => 'Ingrese celular de contacto',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                

            ],
            'actividades' => [
                'edit' => [
                    'text' => 'Editar',
                    'url' => '/provincias/edit/',
                    'icon' => 'fa fa-edit',
                    'class' => 'btn btn-sm btn-warning',
                ],
                'delete' => [
                    'text' => 'Eliminar',
                    'url' => '/provincias/delete/',
                    'icon' => 'fa fa-trash',
                    'class' => 'btn btn-sm btn-danger',
                ]
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
            ],

 
            
];


