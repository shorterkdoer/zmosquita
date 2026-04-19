<?php 


return [    
  'landing' => [
        'header'=>[
            'headtagtitulo'=>'h2',
            'headtagclass'=>'text-center text-primary font-weight-bold',
            'titulo'=>'Recursos adicionales',
            'headtagsubtitulo'=>'h4',
            'headtagclasssubt'=>'text-center text-primary font-weight-bold',
            'subtitulo'=>'Formularios, descargas, links y otros recursos',

                ],
        'botones'=>[
                    [
                        'link' => $_SESSION['base_url'] . '/storage/uploads/Nota_solicitud_de_matricula.pdf',
                        'icon' => 'glyphicon glyphicon-globe',
                        'text' => 'Nota de solicitud de matrícula',
                        'url_id' => false,
                        'target' => '_blank',
                    ],

                    [
                    'link' => 'https://www.argentina.gob.ar/justicia/reincidencia/antecedentespenales',
                        'icon' => 'glyphicon glyphicon-globe',
                        'text' => 'Certificado de antecedentes penales',
                        'url_id' => false,
                        'target' => '_blank',
                        ],
                    [
                        'link' => 'https://portaldetramites.lapampa.gob.ar/tramites/14',
                        'icon' => 'glyphicon glyphicon-globe',
                        'text' => 'Libre deuda alimentario',
                        'url_id' => false,
                        'target' => '_blank',

                        ],
                    [
                        'link' => 'https://seti.afip.gob.ar/padron-puc-constancia-internet/ConsultaConstanciaAction.do',
                        'icon' => 'glyphicon glyphicon-globe',
                        'text' => 'Constancia de CUIL',
                        'url_id' => false,
                        'target' => '_blank',
                    ],
                    [
                    'link' => 'https://salud.lapampa.gob.ar/mds/',
                        'icon' => 'glyphicon glyphicon-globe',
                        'text' => 'Ministerio de salud de La Pampa',
                        'url_id' => false,
                        'target' => '_blank',
                        ],
                    [
                        'link' => 'https://www.argentina.gob.ar/salud',
                        'icon' => 'glyphicon glyphicon-globe',
                        'text' => 'Ministerio de salud de la Nación',
                        'url_id' => false,
                        'target' => '_blank',

                        ],
                    [
                        'link' => 'https://www.argentina.gob.ar/anmat',
                        'icon' => 'glyphicon glyphicon-globe',
                        'text' => 'Anmat',
                        'url_id' => false,
                        'target' => '_blank',
                    ],
                ]
            ]
        ]





?>
