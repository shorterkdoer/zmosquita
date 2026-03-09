<?php    //edit view
return [    'config' => [
                'titulo' => 'Soporte',
                'subtitulo' => 'Envíe a nuestra dirección de soporte ',
                'action' => 'update',
                'url_action' => '',
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
                'mensaje' => [
                    'nombre' => 'mensaje',
                    'type' => 'text',
                    'label' => '',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => '',
                    'help' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    //'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'mensaje2' => [
                    'nombre' => 'mensaje2',
                    'type' => 'text',
                    'label' => '',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => '',
                    'help' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    //'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'mensaje3' => [
                    'nombre' => 'mensaje3',
                    'type' => 'text',
                    'label' => '',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => '',
                    'help' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    //'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'mensaje4' => [
                    'nombre' => 'mensaje4',
                    'type' => 'text',
                    'label' => 'Dirección de correo de soporte:',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => '',
                    'help' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    //'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'mensaje5' => [
                    'nombre' => 'mensaje5',
                    'type' => 'text',
                    'label' => '',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => '',
                    'help' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    //'pattern' => '[A-Za-z0-9 ]{1,255}'
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
