<?php    //edit view
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
                'matriculaministerio' => [
                    'nombre' => 'matriculaministerio',
                    'type' => 'text',
                    'label' => 'Número de Matrícula del Ministerio',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Matrícula otorgada oportdel Ministerio',
                    'help' => 'Adjunte la declaración jurada',
                    'link' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'notaddjj' => [
                    'nombre' => 'notaddjj',
                    'type' => 'file',
                    'label' => 'Nota solicitud de matrícula (en PDF)',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Nota solicitud de matrícula',
                    'help' => 'Nota solicitud de matrícula',
                    'link' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'dnifrente' => [
                    'nombre' => 'dnifrente',
                    'type' => 'file',
                    'label' => 'DNI (frente)',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'DNI (frente) ',    
                    'help' => 'Adjunte el DNI (frente) Vigente (sin brillo/reflejo que distorsione datos y de toma superior con menor cantidad de bordes) ',
                    'link' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'dnidorso' => [
                    'nombre' => 'dnidorso',
                    'type' => 'file',
                    'label' => 'DNI (dorso)',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'DNI (dorso)',
                    'help' => 'Adjunte el DNI (dorso) Vigente (sin brillo/reflejo que distorsione datos y de toma superior con menor cantidad de bordes) ',
                    'link' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'titulooriginalfrente' => [
                    'nombre' => 'titulooriginalfrente',
                    'type' => 'file',
                    'label' => 'Título Original (frente)',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Título Original (frente)',
                    'help' => 'Adjunte el Título Original (frente) foto (sin brillo/reflejo que distorsione datos y de toma superior con menor cantidad de bordes)',
                    'link' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'titulooriginaldorso' => [
                    'nombre' => 'titulooriginaldorso',
                    'type' => 'file',
                    'label' => 'Título Original (dorso)',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Título Original (dorso)',
                    'help' => 'Adjunte el Título Original (dorso) foto (sin brillo/reflejo que distorsione datos y de toma superior con menor cantidad de bordes)',
                    'link' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
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
                'fotocarnet' => [
                    'nombre' => 'fotocarnet',
                    'type' => 'image',
                    'label' => 'Foto Carnet',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Foto Carnet',
                    'help' => 'Adjunte la Foto Carnet 4x4 de frente',
                    'link' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'antecedentespenales' => [
                    'nombre' => 'antecedentespenales',
                    'type' => 'file',
                    'label' => 'Antecedentes Penales',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Antecedentes Penales',
                    'help' => 'Adjunte los Antecedentes Penales emitidos por Nación con menos de 6 meses de antigüedad',
                    'link' => 'https://www.argentina.gob.ar/justicia/reincidencia/antecedentespenales',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'libredeudaalimentario' => [
                    'nombre' => 'libredeudaalimentario',
                    'type' => 'file',
                    'label' => 'Libre Deuda Alimentario',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Libre Deuda Alimentario',
                    'help' => 'Adjunte el Libre Deuda Alimentario emitidos por Nación con menos de 6 meses de antigüedad',
                    'link' => 'https://portaldetramites.lapampa.gob.ar/tramites/14',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'constanciaCUIL' => [
                    'nombre' => 'constanciaCUIL',
                    'type' => 'file',
                    'label' => 'Constancia de CUIL/CUIT',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Constancia de CUIL/CUIT',
                    'help' => 'Adjunte la Constancia de CUIL emitida por ANSES',
                    'link' => 'https://www.anses.gob.ar/consultas/constancia-de-cuil',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'apostillado' => [
                    'nombre' => 'apostillado',
                    'type' => 'file',
                    'label' => 'Apostillado (Sólo para títulos extranjeros)',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Apostillado',
                    'help' => 'Adjunte el Apostillado (solo para títulos extranjeros)',
                    'link' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'matriculaprevia' => [
                    'nombre' => 'matriculaprevia',
                    'type' => 'file',
                    'label' => 'Matrícula Previa (sólo para matriculados en otras jurisdicciones)',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Matrícula Previa',
                    'help' => 'Adjunte la Matrícula Previa',
                    'link' => '',
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'autocomplete' => 'off',
                    'pattern' => '[A-Za-z0-9 ]{1,255}'
                ],
                'certificadoetica' => [
                    'nombre' => 'certificadoetica',
                    'type' => 'file',
                    'label' => 'Certificado de Ética (Sólo para matriculados en otras jurisdicciones)',
                    'maxlength' => 255,
                    'readonly' => true,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Certificado de Ética',
                    'help' => 'Adjunte el Certificado de Ética (sólo para matriculados en otras jurisdicciones)',
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

