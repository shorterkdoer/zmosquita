<?php 


return [    
  'landing' => [
        'header'=>[
            'Titulo' => 'Panel de control - Usuarios CoProBiLP',
            'Subtitulo' => 'Inconvenientes? Enviar correo a soportesistema@coprobilp.org.ar',
            'headtagtitulo'=>'h1',
            'headtagclass'=>'text-center text-primary font-weight-bold',
            'titulo'=>'Panel de control de matriculado',
            'headtagsubtitulo'=>'h3',
            'headtagclasssubt'=>'text-center text-primary font-weight-bold',
            'subtitulo'=>'Panel de control de matriculado',

                ],
        'botones'=>[

                    [
                    'link' => '/matriculas',
                        'icon' => 'bi-cash-stack',
                        'text' => 'Matriculación',
                        'url_id' => false,
                        'hint' => 'Proceso de matriculación para todos los profesionales que ejercen o desean ejercer en a provincia de La Pampa',
                        ],
                    [
                        'link' => '/datospersonales/edit',
                        'icon' => 'bi-card-checklist',
                        'text' => 'Mis datos personales (Mantener actualizados!)',
                        'url_id' => true,
                        'hint' => 'Mantener actualizados los datos personales es fundamental para la comunicación con el profesional',
                        ],
                    [
                    'link' => '/cuota',
                        'icon' => 'bi-cash-coin',
                        'text' => 'Información de pago de cuota',
                        'url_id' => false,
                        'hint' => 'Valores e información bancaria para pagos',
                        ],

                    /*[
                        'link' => '/micredencial', // '/matriculas/edit',
                        'url_id' => '', // va en true
                        'icon' => 'bi-card-list',
                        'text' => 'Mi Matrícula',
                        
                        'hint' => 'Ver mi credencial de matriculación',
                        ],
                    */
                    [
                        'link' => '/comprobantespago/create',
                        'icon' => 'bi-currency-dollar',
                        'text' => 'Notificar pago',
                        'url_id' => true,
                        'hint' => 'Notificar el pago de la matrícula o de otros servicios',
                    ],
                    [
                        'link' => '/miscomprobantes',
                        'icon' => 'bi-cash-stack',
                        'text' => 'Mis comprobantes de pago',
                        'url_id' => false,
                        'hint' => 'Consultar los comprobantes de pago informados al sistema',
                    ],
 
                    [
                        'link' => '/descargas',
                        'icon' => 'bi-file-medical',
                        'text' => 'Descargas y otros recursos',
                        'url_id' => false,
                        'hint' => 'Recursos adicionales, formularios y otros links de interés',
                    ],
                    [
                        'link' => '/soporte',
                        'icon' => 'bi-patch-question-fill',
                        'text' => 'Reporte de inconvenientes con el sistema',
                        'url_id' => false,
                        'hint' => 'Soporte para el sistema de matriculación',
                    ],

                    [
                        'link' => '/logout',
                        'icon' => 'bi-house-down',
                            'text' => 'Cerrar sesión',
                            'url_id' => false,
                            'hint' => 'Cerrar sesión del sistema',

                    ],
                ]
            ]
        ]


//patch-question-fill


?>
