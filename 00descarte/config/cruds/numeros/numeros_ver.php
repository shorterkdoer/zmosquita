<?php    //edit view
return [    'config' => [
                'titulo' => 'Números de Matricula',
                'subtitulo' => 'ültimo registrado',
                'action' => 'update',
                'url_action' => '/estadomatricula',
                'method' => 'POST',
                'tipo' => 'form',
                'field_id' => 'id',
            ],
            'comandos' => 
            [
                /*
                'Calcular número de matrícula' => [     //asignar número de matrícula al campo correspondiente
                    'text' => 'Número Matrícula',
                    'url' => '/matriculanro',
                    'icon' => 'bi bi-file-earmark-pdf',
                    'class' => 'btn btn-outline-secondary',
                    'target' => '_blank',
                ],
                */
               ],
               
            
            'campos' => [
                'id' => [
                    'nombre' => 'id',
                    'type' => 'text',
                    'label' => 'Clave',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => true,
                    'required' => false,
                    'placeholder' => 'Numerador',
                    'help' => 'Numerador',
                    'link' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    //'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'rotulo' => [
                    'nombre' => 'rotulo',
                    'type' => 'text',
                    'label' => 'Clave',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Numerador',
                    'help' => 'Numerador',
                    'link' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'valor' => [
                    'nombre' => 'valor',
                    'type' => 'number',
                    'label' => 'Número de Matrícula dado',
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Numero',
                    'help' => 'Numero',
                    'link' => '',
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
                    'text' => 'Confirmar cambios',
                    'class' => 'btn btn-gradient btn-rounded',
                    'icon' => 'bi bi-check-circle me-1',
                    'backbutton' => false, // para que funcione el botón de volver
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

