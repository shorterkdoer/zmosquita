<?php 


return [    
  'landing' => [
      'header'=>[
          'headtagtitulo'=>'h2',
          'headtagclass'=>'text-center text-primary font-weight-bold',
          'titulo'=>'Panel de control de matriculado',
          'headtagsubtitulo'=>'h4',
          'headtagclasssubt'=>'text-center text-primary font-weight-bold',
          'subtitulo'=>'Actividades según perfil',

                ],
        'botones'=>[
/*                [
                    'link' => '/rematricula',
                    'icon' => 'bi-award-fill',
                    'text' => 'Rematriculación (Matricula actual en la provincia extendida por el Ministerio de Salud)',
                    'hint' => 'Profesionales que deben cumplir con la rematriculación y están ejerciendo en la provincia autorizados por el Ministerio de Salud',
                    'url_id' => false,
                ],*/
                [
                    'link' => '/primeramatricula',
                    'icon' => 'bi-bank',
                    'text' => 'Primera Matriculación - Inicio de actividades en la provincia',
                    'hint' => 'Profesionales que inician actividades en la provincia y no tienen matrícula en la provincia',
                    'url_id' => false,
                ],
                [
                    'link' => '/previamatricula',
                    'icon' => 'bi-award',
                    'text' => 'Matriculación para profesionales matriculados previamente en otra jurisdicción',
                    'hint' => 'Profesionales que inician actividades en la provincia y tienen matrícula en otra jurisdicción',
                    'url_id' => false,
                ],
                [
                    'link' => '/titulodeotranacion',
                    'icon' => 'bi-award',
                    'text' => 'Matriculación para profesionales extranjeros',
                    'hint' => 'Profesionales que inician actividades en la provincia y están titulados fuera de Argentina',

                    'url_id' => false,
                ],
            ],          
      ]
];


?>
