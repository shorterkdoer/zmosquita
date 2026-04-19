<?php    //edit view
return [    'config' => [
                'title' => 'Datos Personales',
                'subtitle' => 'Modificar',
                'action' => 'update',
                'url_action' => '/datospersonales/roleupdate',
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
                'field_id' => 'id',
                'link_id' => 'user_id',

            ],
            'comandos' => [
               ],
               
            
            'campos' => [
                'nombre' => [
                    'nombre' => 'nombre',
                    'type' => 'text',
                    'label' => 'Nombre',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Nombres',
                    //'help' => 'Ingrese el nombre',
                    'class' => 'form-control',
                    'style' => 'width: 50%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'apellido' => [
                    'nombre' => 'apellido',
                    'type' => 'text',
                    'label' => 'Apellido',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Apellido',
                    //'help' => 'Ingrese apellido',
                    'class' => 'form-control',
                    'style' => 'width: 50%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'dni' => [
                    'nombre' => 'dni',
                    'type' => 'text',
                    'label' => 'DNI',
                    'maxlength' => 40,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'DNI',
                    //'help' => 'Ingrese DNI',
                    'class' => 'form-control',
                    'style' => 'width: 40%;',
                    'autocomplete' => 'off',
                    'pattern' => '[0-9]{1,255}'
                ],
                'role' => [
                    'nombre' => 'role',
                    'type' => 'text',
                    'label' => 'Rol Actual',
                    'maxlength' => 25,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Rol Actual',
                    //'help' => 'Ingrese teléfono',
                    'class' => 'form-control',
                    'style' => 'width: 40%;',
                    'autocomplete' => 'off',
                    'pattern' => '[0-9]{1,255}'
                ],

                'ciudad' => [
                    'nombre' => 'ciudad',
                    'type' => 'text',
                    'label' => 'Ciudad',
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Ciudad',
                    //'help' => 'Ciudad',
                    'class' => 'form-control',
                    'style' => 'width: 40%;',
                    'autocomplete' => 'off',
                    'options' => []
                ],
                'provincia' => [
                    'nombre' => 'provincia',
                    'type' => 'text',
                    'label' => 'Provincia',
                    'readonly' => true,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Provincia',
                    //'help' => 'Provincia',
                    'class' => 'form-control',
                    'style' => 'width: 40%;',
                    'autocomplete' => 'off',
                    'options' => []
                ],
                
                'celular' => [
                    'nombre' => 'celular',
                    'type' => 'text',
                    'label' => 'Celular',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Celular',
                    //'help' => 'Ingrese celular',
                    'class' => 'form-control',
                    'style' => 'width: 40%;',
                    'autocomplete' => 'off',
                    'pattern' => '[0-9]{1,255}'
                ],


            ],
            'actividades' => [                
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
                    'text' => 'Cambiar Rol',
                    'class' => 'btn btn-gradient btn-rounded',
                    'icon' => 'bi bi-person-workspace me-1',
                ],
                //bi-person-workspace
            ],
];

