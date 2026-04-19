<?php



return [ 'config' => [
                'titulo' => 'Agregar comprobante de pago',
                'subtitulo' => 'Nuevo comprobante de pago',
                'action' => 'create',
                'url_action' => '/comprobantespago/store',  
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
               
            ],
            'campos' => [
                'user_id' => [
                    'nombre' => 'user_id',
                    'type' => 'hidden',
                    'label' => '',
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => true,
                    'required' => false,
                    'placeholder' => '',
                    'help' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],

                'comprobante' => [
                    'nombre' => 'comprobante',
                    'type' => 'file',
                    'label' => 'Comprobante de Pago',
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Comprobante de Pago',
                    'help' => 'Adjunte el comprobante de pago',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'monto' => [
                    'nombre' => 'monto',
                    'type' => 'decimal',
                    'step' => '0.01',
                    'label' => 'Importe pagado',
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Importe',
                    'help' => 'Importe',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    //'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
            'fecha' => [
                    'nombre' => 'fecha',
                    'type' => 'date',
                    'label' => 'Fecha',
                    'maxlength' => 25,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Fecha',
                    'help' => 'Ingrese la fecha del comprobante',
                    'class' => 'form-control',
                    'style' => 'width: 25%;',
                    'autocomplete' => 'off',
                    //'pattern' => '[A-Za-z0-9 ]{1,255}'
            ],
                'observaciones' => [
                    'nombre' => 'observaciones',
                    'type' => 'text',
                    'label' => 'Observaciones',
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Observaciones',
                    'help' => 'Agregue una referencia o comentario',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'searchable' => true,
                    'sortable' => true,
                    'pattern' => '[A-Za-z0-9\s\$\@\#\!\%\&\(\)\+\-\?.,;:]*',
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
                    'text' => 'Guardar',
                    'class' => 'btn btn-gradient btn-rounded',
                    'icon' => 'bi bi-check-circle me-1',
                ],
            ],                
];


