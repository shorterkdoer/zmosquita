<?php    //edit view adiciona analitico 
return [    'config' => [
                'titulo' => 'Matricula',
                'subtitulo' => 'Adjuntar (PNG, JPG, JPEG o PDF)',
                'action' => 'update',
                'url_action' => '/matriculas/update',
                'method' => 'POST',
                'tipo' => 'form',
                'field_id' => 'user_id',
            ],
            'comandos' => [
                
               ],
               
            
            'campos' => [
                'fotoregistrodegraduados' => [
                    'nombre' => 'fotoregistrodegraduados',
                    'type' => 'file',
                    'label' => 'Analítico',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Analítico',
                    'help' => 'Adjunte el Analítico (campo no obligatorio/opcional)',
                    'link' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],

            ],
            'actividades' => [                
            ],

            
            'buttons' => [
                'cancel' => [
                    'type' => 'button',
                    'text' => 'Volver al menú anterior',
                    'url' => '',
                    'class' => 'btn btn-outline-secondary btn-rounded',
                    'icon' => 'bi bi-arrow-left',
                    'backbutton' => true, // para que funcione el botón de volver

                ],

                'submit' => [
                    'type' => 'submit',
                    'text' => 'Guardar y seguir',
                    'class' => 'btn btn-gradient btn-rounded',
                    'icon' => 'bi bi-check-circle me-1',
                ],
                'Revisar' => [
                    'type' => 'button',
                    'text' => 'Solicitar revisión',
                    'url' => '/datarev01',
                    'class' => 'btn btn-outline-secondary btn-rounded',
                    'icon' => 'bi bi-ui-checks',

                ],

            ],
];


