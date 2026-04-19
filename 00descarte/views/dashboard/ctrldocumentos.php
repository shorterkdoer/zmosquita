<?php
return [    
  'landing' => [
        'header'=>[
            'headtagtitulo'=>'h1',
            'headtagclass'=>'text-center text-primary font-weight-bold',
            'titulo'=>'Control de Documentación',
            'headtagsubtitulo'=>'h3',
            'headtagclasssubt'=>'text-center text-primary font-weight-bold',
            'subtitulo'=>'Asignar Revisor y/o Verificador',

                ],
        'botones'=>[ 
                    [
                    'link' => '/aspirantes',
                        'icon' => 'bi-cash-stack',
                        'text' => 'Aspirantes (pidieron revisión)',
                        'url_id' => false,
                        'hint' => '',
                        ],
                    [
                        'link' => '/pararevisar',
                        'icon' => 'bi-card-checklist',
                        'text' => 'Aspirantes marcados para revisión (1° fase)',
                        'url_id' => false,
                        'hint' => '',
                        ],

                    [
                        'link' => '/revisados',
                        'icon' => 'bi-card-checklist',
                        'text' => 'Aspirantes aprobados (1° fase)',
                        'url_id' => false,
                        'hint' => '',
                        ],

                    [
                        'link' => '/paraverificar',
                        'icon' => 'bi-card-checklist',
                        'text' => 'Aspirantes marcados para verificación (2° fase)',
                        'url_id' => false,
                        'hint' => '',
                        ],
		[
                        'link' => '/verificados',
                        'icon' => 'bi-card-checklist',
                        'text' => 'Aspirantes aprobados (2° fase)',
                        'url_id' => false,
                        'hint' => '',
                        ],
                ]
            ]
        ]





?>
