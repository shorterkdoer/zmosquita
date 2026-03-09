<?php    //edit view para primera matricula
return [    'config' => [
                'titulo' => 'Matricula',
                'subtitulo' => 'Profesionales que inician actividades en la provincia - Adjuntar (PNG, JPG, JPEG)',
                'action' => 'update',
                'url_action' => '/primeramatricula',
                'method' => 'POST',
                'tipo' => 'form',
                'field_id' => 'user_id',
            ],
            'comandos' => [
                'NotaDDJJ' => [
                    'text' => 'Descargar Nota Modelo Solicitud de matrícula',
                    'url' => '/storage/uploads/Nota_solicitud_de_matricula.pdf',
                    'icon' => 'bi bi-file-earmark-pdf',
                    'class' => 'btn btn-outline-secondary',
                    'target' => '_blank',
                ],
                'Pago' => [
                    'text' => 'Informar pago',
                    'url' => '/comprobantespago/create' , 
                    'icon' => 'bi bi-add',
                    'class' => 'btn btn-primary',
                    'url_id' => true,
               ],
                
               ],
               
            
            'campos' => [
                'notaddjj' => [
                    'nombre' => 'notaddjj',
                    'type' => 'file',
                    'label' => 'Nota solicitud de matrícula (en PDF)',
                    'maxlength' => 255,
                    'readonly' => false,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Nota solicitud de matrícula',
                    'help' => 'Adjunte la nota solicitud de matrícula',
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
                    'readonly' => false,
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
                    'readonly' => false,
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
                    'readonly' => false,
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
                    'readonly' => false,    
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
                'fotocarnet' => [
                    'nombre' => 'fotocarnet',
                    'type' => 'image',
                    'label' => 'Foto Carnet',
                    'maxlength' => 255,
                    'readonly' => false,
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
                    'readonly' => false,
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
                    'readonly' => false,
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
                    'readonly' => false,
                    'hidden' => false,
                    'required' => false,
                    'placeholder' => 'Constancia de CUIL',
                    'help' => 'Adjunte la Constancia de CUIL emitida por ANSES',
                    'link' => 'https://www.anses.gob.ar/consultas/constancia-de-cuil',
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
                    'text' => 'Volver al menu anterior',
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
                'revisar' => [
                    'type' => 'button',
                    'text' => 'Solicitar revisión',
                    'url' => '/datarevprim',
                    'class' => 'btn btn-outline-secondary btn-rounded',
                    'icon' => 'bi bi-ui-checks',
                ],

            ],
];

