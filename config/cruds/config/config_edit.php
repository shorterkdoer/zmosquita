<?php    //edit view
return [    'config' => [
                'title' => 'Valores',
                'subtitle' => 'Modificar valores',
                'action' => 'update',
                'url_action' => '/valores/update',
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
                'valorunidad' => [
                    'nombre' => 'valorunidad',
                    'type' => 'number',
                    'label' => 'Valor Unidad Bioquímica',
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Valor Unidad Bioquímica',
                    'help' => 'Ingrese el valor de la unidad bioquímica',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    //'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'unidadesmes' => [
                    'nombre' => 'unidadesmes',
                    'type' => 'number',
                    'label' => 'Cuota mensual en Unidades Bioquímicas',
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Cuota mensual en Unidades Bioquímicas',
                    'help' => 'Cuota mensual en Unidades Bioquímicas',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    //'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'unidadesnuevamatric' => [
                    'nombre' => 'unidadesnuevamatric',
                    'type' => 'number',
                    'label' => 'Unidades Bioquímicas para nueva matrícula',
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Unidades Bioquímicas para nueva matrícula',
                    'help' => 'Unidades Bioquímicas para nueva matrícula',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    //'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'alias' => [
                    'nombre' => 'alias',
                    'type' => 'text',
                    'label' => 'Alias para transferencias',
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Alias',
                    'help' => 'Alias para transferencias',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    //'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'CBU' => [
                    'nombre' => 'CBU',
                    'type' => 'text',
                    'label' => 'CBU',
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => true,
                    'placeholder' => 'Clave Bancaria Uniforme (CBU)',
                    'help' => 'Clave Bancaria Uniforme (CBU) para transferencias',
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

                'submit' => [
                    'type' => 'submit',
                    'text' => 'Guardar',
                    'class' => 'btn btn-gradient btn-rounded',
                    'icon' => 'bi bi-check-circle me-1',
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
