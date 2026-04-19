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
            'titulo'=>'Control de Inscripciones',
            'headtagsubtitulo'=>'h3',
            'headtagclasssubt'=>'text-center text-primary font-weight-bold',
            'subtitulo'=>'Control de inscripciones en curso',

                ],
        'botones'=>[ 
                    [
                    'link' => '/padrongeneral',
                        'icon' => 'bi-person-add',
                        'text' => 'Inscripciones iniciadas',
                        'url_id' => false,
                        'hint' => 'Profesionales que iniciaron inscripción',
                        ],
                    [
                    'link' => '/aspirantes',
                        'icon' => 'bi-eye-fill',
                        'text' => 'Asignar revisor fase 1 ',
                        'url_id' => false,
                        'hint' => 'Profesionales que pidieron verificación.',
                        ],
                    [
                    'link' => '/solicitantes',
                        'icon' => 'bi-eyeglasses',
                        'text' => 'Asignar verificador fase 2',
                        'url_id' => false,
                        'hint' => 'Solicitaron revisión de documentación',
                        ],
                        [
                    'link' => '/acontrolfisico',
                        'icon' => 'bi-check2-square',
                        'text' => 'Verificación física',
                        'url_id' => false,
                        'hint' => 'Registrar resultado verificación física',
                        ],


                        [
                        'link' => '/agendarcita', ///agendarcita
                        'icon' => 'bi-calendar-date',
                        'text' => 'Agenda de citas',
                        'url_id' => false,
                        'hint' => 'Agendar cita',
                        ],


                    [
                        'link' => '/solicitudes',
                        'icon' => 'bi-card-checklist',
                        'text' => 'Usuarios con mail de activación vencido',
                        'url_id' => false,
                        'hint' => 'A quienes les falta el mail de activación',
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
                    'link' => '/otorgamatricula',
                        'icon' => 'bi-check2-all',
                        'text' => 'Otorgar la matrícula',
                        'url_id' => false,
                        'hint' => 'Aprobar el trámite de matriculación',
                        ],
                    [
                    'link' => '/dardebaja',
                        'icon' => 'bi-hand-thumbs-down',
                        'text' => 'Baja de matrícula',
                        'url_id' => false,
                        'hint' => 'Dar de baja una matrícula',
                        ],
                    [
                    'link' => '/otrasacciones',
                        'icon' => 'bi-person-exclamation',
                        'text' => 'Otras acciones administrativas',
                        'url_id' => false,
                        'hint' => 'Realizar otras acciones administrativas',
                        ],

                        ]
            ]
        ]





?>
