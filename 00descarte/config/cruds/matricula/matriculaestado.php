<?php    //edit view
return [    'config' => [
                'titulo' => 'Matricula',
                'subtitulo' => 'Modificar',
                'action' => 'update',
                'url_action' => '/setmatricula', 
                'method' => 'POST',
                'tipo' => 'form',
                'field_id' => 'user_id',
            ],
            'comandos' => 
            [
                'Calcular número de matrícula' => [     //asignar número de matrícula al campo correspondiente
                    'text' => 'Número Matrícula',
                    'url' => '/matriculanro',
                    'icon' => 'bi bi-file-earmark-pdf',
                    'class' => 'btn btn-outline-secondary',
                    'target' => '_blank',
                ],
                
               ],
               
            
            'campos' => [
                'matriculaministerio' => [
                    'nombre' => 'matriculaministerio',
                    'type' => 'text',
                    'label' => 'Número de Matrícula del Ministerio',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Matrícula otorgada oportunamente por el Ministerio',
                    'help' => 'Matricula Ministerio',
                    'link' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'matriculaasignada' => [
                    'nombre' => 'matriculaasignada',
                    'type' => 'number',
                    'label' => 'Número de Matrícula otorgado por CoProBiLP',
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Matrícula ',
                    'help' => 'Matricula',
                    'link' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'aprobado' => [
                    'nombre' => 'aprobado',
                    'type' => 'date',
                    'label' => 'Fecha de otorgamiento de Matrícula',
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => '',
                    'help' => 'Fecha de aprobación de la matrícula',
                    'class' => 'form-control',
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
                    'text' => 'Otorgar Matrícula',
                    'class' => 'btn btn-gradient btn-rounded',
                    'icon' => 'bi bi-check-circle me-1',
                ],
            ],
];

