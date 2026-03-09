<?php
return [    
  'landing' => [
        'header'=>[
            'headtagtitulo'=>'h1',
            'headtagclass'=>'text-center text-primary font-weight-bold',
            'titulo'=>'Control de Cobranzas',
            'headtagsubtitulo'=>'h3',
            'headtagclasssubt'=>'text-center text-primary font-weight-bold',
            'subtitulo'=>'Control de cobranzas',

                ],
        'botones'=>[ 
                    [
                    'link' => '/cobranzas',
                        'icon' => 'bi-cash-stack',
                        'text' => 'Historial completo de cobranzas',
                        'url_id' => false,
                        'hint' => 'Historial de Cobranzas',
                        ],
                    [
                        'link' => '/cobranzasmes',
                        'icon' => 'bi-card-checklist',
                        'text' => 'Cobranzas por mes',
                        'url_id' => false,
                        'hint' => 'A quienes les falta el mail de activación',
                        ],

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

                ]
            ]
        ]





?>
