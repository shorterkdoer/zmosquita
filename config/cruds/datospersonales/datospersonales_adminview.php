<?php    //edit view
return [    'config' => [
                'title' => 'Datos Personales',
                'subtitle' => 'Modificar',
                'action' => 'update',
                'url_action' => '/datospersonales/update',
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
                    'style' => 'width: 100%;',
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
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'dni' => [
                    'nombre' => 'dni',
                    'type' => 'text',
                    'label' => 'DNI',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'DNI',
                    //'help' => 'Ingrese DNI',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[0-9]{1,255}'
                ],
                'direccion_calle' => [
                    'nombre' => 'direccion_calle',
                    'type' => 'text',
                    'label' => 'Calle',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Calle',
                    //'help' => 'Ingrese calle',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'direccion_numero' => [
                    'nombre' => 'direccion_numero',
                    'type' => 'text',
                    'label' => 'Número',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Número',
                    //'help' => 'Ingrese número',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[0-9]{1,255}'
                ],
                'direccion_piso' => [
                    'nombre' => 'direccion_piso',
                    'type' => 'text',
                    'label' => 'Piso',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Piso',
                    //'help' => 'Ingrese piso',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[0-9]{1,255}'
                ],
                'direccion_depto' => [
                    'nombre' => 'direccion_depto',
                    'type' => 'text',
                    'label' => 'Departamento',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Departamento',
                    //'help' => 'Ingrese departamento',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'direccion_cp' => [
                    'nombre' => 'direccion_cp',
                    'type' => 'text',
                    'label' => 'Código Postal',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Código Postal',
                    //'help' => 'Ingrese código postal',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
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
                    'style' => 'width: 100%;',
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
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'options' => []
                ],
                
                'telefono' => [
                    'nombre' => 'telefono',
                    'type' => 'text',
                    'label' => 'Teléfono',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Teléfono',
                    //'help' => 'Ingrese teléfono',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[0-9]{1,255}'
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
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[0-9]{1,255}'
                ],
                'mailparticular' => 
            ['nombre' => 'mailparticular', 
            'type' => 'mail', 
            'label' => 'Mail particular', 
            'placeholder' => 'Ingrese su dirección de e-mail', 
            'readonly' => true, 
            'hidden' => false, 
            'help' => 'Ingrese mail de uso personal', 
            'class' => 'form-control', 
            'style' => 'width: 100%;', 
            'autocomplete' => 'off', 
            //'pattern' => '[A-Za-z0-9 ]{1,255}', 
            'options' => [], 
            'columna_rel' => '', 
            'default' => 'NULL', 
            'tabla_rel' => ''
            ],
	'maillaboral' => 
            ['nombre' => 'maillaboral', 
            'type' => 'mail', 
            'label' => 'Mail laboral', 
            'placeholder' => 'Ingrese dirección de e-mail (laboral)', 
            'readonly' => true, 
            'hidden' => false, 
            'help' => 'Ingrese mail laboral', 
            'class' => 'form-control', 
            'style' => 'width: 100%;', 
            'autocomplete' => 'off', 
            //'pattern' => '[A-Za-z0-9 ]{1,255}', 
            'options' => [], 
            'columna_rel' => '', 
            'default' => 'NULL', 
            'tabla_rel' => ''
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

            ],
];

/*
<div class="d-flex justify-content-center gap-2 mt-4">
<div >
  <button type="submit" class="btn btn-gradient btn-rounded">
  <i class="bi bi-check-circle me-1"></i>><?= $this->e($buttons['submit'] ?? 'Guardar') ?></button>
</div>
<div >
  <button type="button" class="btn btn-outline-secondary btn-rounded" onclick="window.history.back();">
  <?= $this->e($buttons['cancel'] ?? 'Cancelar') ?>  <i class="bi bi-x-circle me-1"></i>
  </button>
</div>
</div>
</div>
*/
