<?php 

/*
<ul>
    <li><a href="/provincias">Administrar Provincias</a></li>
    <li><a href="/ciudades">Administrar Ciudades</a></li>
    <li><a href="/usuarios">Administrar Usuarios</a></li>
    <li><a href="/padrongeneral">Padrón</a></li>
    <li><a href="/matriculas/informealtas">Informe de Altas</a></li>
    <li><a href="/matriculas/informebajas">Informe de Bajas</a></li>
    

</ul>
*/
?>
<?php
return [    
  'landing' => [
        'header'=>[
            'headtagtitulo'=>'h1',
            'headtagclass'=>'text-center text-primary font-weight-bold',
            'titulo'=>'Panel de administración',
            'headtagsubtitulo'=>'h3',
            'headtagclasssubt'=>'text-center text-primary font-weight-bold',
            'subtitulo'=>'Panel de control general',

                ],
        'botones'=>[
                    [
                    'link' => '/controlinscripciones', // /cobranzas
                        'icon' => 'bi-files',
                        'text' => 'Control de Inscripciones',
                        'url_id' => false,
                        'hint' => 'Inscripciones y control de documentos',

                    ],

/*                    [
                    'link' => '/controldocumentacion',
                        'icon' => 'bi-files',
                        'text' => 'Control de documentación',
                        'url_id' => false,
                        'hint' => 'Actividades de revisión y verificación de documentos',
                        ],
*/
                        [
                    'link' => '/controlcobros', // /cobranzas
                        'icon' => 'bi-currency-dollar',
                        'text' => 'Control de Cobranzas',
                        'url_id' => false,
                        'hint' => 'Control de movimientos de cobranzas',
                        
                    ],
                    [
                    'link' => '/menubajas', // /cobranzas
                        'icon' => 'bi-hand-thumbs-down',
                        'text' => 'Bajas',
                        'url_id' => false,
                        'hint' => 'Gestión de bajas de profesionales matriculados',
                        
                    ],

                    
                    [
                    'link' => '/valores',
                        'icon' => 'bi-cash-stack',
                        'text' => 'Unidades Bioquímicas, Cuotas y Matrícula',
                        'url_id' => false,
                        'hint' => 'Establecer valores de las cuotas y matrículas',
                        ],

                    [
                        'link' => '',
                        'icon' => 'bi-card-checklist',
                        'text' => 'Gestionar roles de usuarios',
                        'url_id' => false,
                        'hint' => 'Asignar rol a los usuarios',
                        ],
                    [
                    'link' => '/activos',
                        'icon' => 'bi-bookmark-check-fill',
                        'text' => 'Matriculados',
                        'url_id' => false,
                        'hint' => 'Profesionales con matrícula activa',
                        ],


                        
                    [
                        'link' => '/user-dashboard', // '',
                        'icon' => 'bi-people-fill',
                        'text' => 'Menú de usuarios',
                        'url_id' => false,
                        'hint' => 'Actividades generales de los usuarios',
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





?>
