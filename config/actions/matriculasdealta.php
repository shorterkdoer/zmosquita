<?php    //edit view
return [    
        'config' => [
                'title' => 'Altas de matriculas',
                'subtitle' => '',
                'action' => 'update',
                'url_action' => '/matricula/reportealtas',
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
                'desde' => [
                    'nombre' => 'fechadesde',
                    'type' => 'date',
                    'label' => 'Desde',
                    'maxlength' => 30,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Desde',
                    //'help' => 'Ingrese el nombre',
                    'class' => 'form-control',
                    'style' => 'width: 50%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                    ],
                'hasta' => [
                    'nombre' => 'fechahasta',
                    'type' => 'date',
                    'label' => 'Hasta',
                    'maxlength' => 30,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => true,
                    'placeholder'=> 'Hasta',
                    //'help' => 'Ingrese el nombre',
                    'class' => 'form-control',
                    'style' => 'width: 50%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
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
                    'text' => 'Mostrar altas',
                    'class' => 'btn btn-gradient btn-rounded',
                    'icon' => 'bi bi-person-workspace me-1',
                ],
                //bi-person-workspace
            ],
];
