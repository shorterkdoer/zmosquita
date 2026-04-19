<?php    //edit view
return [    'config' => [
                'title' => 'Comisión',
                'subtitle' => 'Modificar comisión',
                'action' => 'update',
                'url_action' => '/comision/update',
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
                'Nueva' => [
                    'text' => 'Nueva comisión',
                    'url' => '/comision/create/',
                    'icon' => 'bi bi-add',
                    'class' => 'btn btn-primary'
               ],
               
            ],
            'campos' => [
                'id' => [
                    'nombre' => 'id',
                    'type' => 'text',
                    'label' => 'Nombre',
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => true,
                    'required' => false,
                    'placeholder' => 'ID de la comisión',
                    'help' => 'ID de la comisión',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                ],
                'user_presi' => [
                    'nombre' => 'user_presi',
                    'type' => 'select',
                    'label' => 'Presidente',
                    'readonly' => false,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Seleccione matriculado',
                    'help' => 'Seleccione matriculado',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'options' => []
                ],
                'user_vice' => [
                    'nombre' => 'user_vice',
                    'type' => 'select',
                    'label' => 'Vicepresidente',
                    'readonly' => false,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Seleccione matriculado',
                    'help' => 'Seleccione matriculado',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'options' => []
                ],
                'user_secre' => [
                    'nombre' => 'user_secre',
                    'type' => 'select',
                    'label' => 'Secretario',
                    'readonly' => false,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Seleccione matriculado',
                    'help' => 'Seleccione matriculado',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'options' => []
                ],
                'carnet_presi' => [
                    'nombre' => 'carnet_presi',
                    'type' => 'file',
                    'label' => 'Modelo de carnet con firma del presidente',
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Carnet firmado por el presidente',    
                    'help' => 'Adjunte modelo de carnet firmado por el presidente ',
                    'link' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
            'inicio' => [
                    'nombre' => 'inicio',
                    'type' => 'date',
                    'label' => 'Fecha',
                    'maxlength' => 25,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Fecha',
                    'help' => 'Ingrese inicio del período de vigencia',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    //'pattern' => '[A-Za-z0-9 ]{1,255}'
            ],
            'fin' => [
                    'nombre' => 'fin',
                    'type' => 'date',
                    'label' => 'Fecha',
                    'maxlength' => 25,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Fecha',
                    'help' => 'Ingrese fin del período de vigencia',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    //'pattern' => '[A-Za-z0-9 ]{1,255}'
            ],



            ],
            'actividades' => [                
                'delete' => [
                    'text' => 'Eliminar',
                    'url' => '/ciudades/delete/',
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
                    'backbutton' => true, // para que funcione el botón de volver

                ],

                'submit' => [
                    'type' => 'submit',
                    'text' => 'Guardar',
                    'class' => 'btn btn-gradient btn-rounded',
                    'icon' => 'bi bi-check-circle me-1',
                ],
            ],
];
