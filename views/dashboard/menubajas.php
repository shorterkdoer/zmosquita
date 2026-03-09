<?php
return [    
  'landing' => [
        'header'=>[
            'headtagtitulo'=>'h1',
            'headtagclass'=>'text-center text-primary font-weight-bold',
            'titulo'=>'Gestión de Bajas',
            'headtagsubtitulo'=>'h3',
            'headtagclasssubt'=>'text-center text-primary font-weight-bold',
            'subtitulo'=>'',

                ],
        'botones'=>[ 
                    [
                    'link' => '/iniciarbaja',
                        'icon' => 'bi-back',
                        'text' => 'Dar de baja',
                        'url_id' => false,
                        'hint' => 'Baja de matriculado',
                        ],
                    [
                        'link' => '/consultadebajas',
                        'icon' => 'bi-card-checklist',
                        'text' => 'Matriculados dados de baja',
                        'url_id' => false,
                        'hint' => 'Bajas efectivas',
                        ],
/*
                    [
                        'link' => '/cobromatriculado',
                        'icon' => 'bi-card-checklist',
                        'text' => 'Cobranzas por matriculado',
                        'url_id' => false,
                        'hint' => 'Asignar rol a los usuarios',
                        ],
                    [
                        'link' => '/comprobantespago/lote-colegio',
                        'icon' => 'bi-file-earmark-spreadsheet',
                        'text' => 'Cobranzas vía Colegio',
                        'url_id' => false,
                        'hint' => 'Lotes de cobro informados desde el Colegio',
                        ],
*/
                ]
            ]
        ]





?>
