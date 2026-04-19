<?php

/**
 * Dashboard Menu Configuration
 *
 * Defines menu items for each user role. The menu structure is:
 * - header: Title and subtitle configuration
 * - botones: Array of menu buttons with link, icon, text, and hint
 *
 * Menu button structure:
 * - link: URL path (empty string for disabled)
 * - icon: Bootstrap icon class (bi-*)
 * - text: Button label
 * - hint: Tooltip/hint text
 * - url_id: Whether to append user ID to URL (for future use)
 */

return [
    'superuser' => [
        'header' => [
            'headtagtitulo' => 'h1',
            'headtagclass' => 'text-center text-primary font-weight-bold',
            'titulo' => 'Panel de Superusuario',
            'headtagsubtitulo' => 'h3',
            'headtagclasssubt' => 'text-center text-primary font-weight-bold',
            'subtitulo' => 'Control total del sistema',
        ],
        'botones' => [
            [
                'link' => '/controlinscripciones',
                'icon' => 'bi-files',
                'text' => 'Control de Inscripciones',
                'url_id' => false,
                'hint' => 'Inscripciones y control de documentos',
            ],
            [
                'link' => '/controlcobros',
                'icon' => 'bi-currency-dollar',
                'text' => 'Control de Cobranzas',
                'url_id' => false,
                'hint' => 'Control de movimientos de cobranzas',
            ],
            [
                'link' => '/menubajas',
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
                'link' => '/usuarios/roles',
                'icon' => 'bi-shield-lock',
                'text' => 'Gestión de Roles y Permisos',
                'url_id' => false,
                'hint' => 'Administrar roles y permisos del sistema',
            ],
            [
                'link' => '/sistema/configuracion',
                'icon' => 'bi-gear-fill',
                'text' => 'Configuración del Sistema',
                'url_id' => false,
                'hint' => 'Configuración general del sistema',
            ],
            [
                'link' => '/activos',
                'icon' => 'bi-bookmark-check-fill',
                'text' => 'Matriculados Activos',
                'url_id' => false,
                'hint' => 'Profesionales con matrícula activa',
            ],
            [
                'link' => '/padrongeneral',
                'icon' => 'bi-people-fill',
                'text' => 'Padrón General',
                'url_id' => false,
                'hint' => 'Ver todos los profesionales del padrón',
            ],
            [
                'link' => '/sistema/logs',
                'icon' => 'bi-journal-text',
                'text' => 'Logs del Sistema',
                'url_id' => false,
                'hint' => 'Ver registros de actividad del sistema',
            ],
            [
                'link' => '/soporte',
                'icon' => 'bi-patch-question-fill',
                'text' => 'Reporte de Inconvenientes',
                'url_id' => false,
                'hint' => 'Soporte para el sistema de matriculación',
            ],
            [
                'link' => '/logout',
                'icon' => 'bi-house-down',
                'text' => 'Cerrar Sesión',
                'url_id' => false,
                'hint' => 'Cerrar sesión del sistema',
            ],
        ],
    ],

    'admin' => [
        'header' => [
            'headtagtitulo' => 'h1',
            'headtagclass' => 'text-center text-primary font-weight-bold',
            'titulo' => 'Panel de Administración',
            'headtagsubtitulo' => 'h3',
            'headtagclasssubt' => 'text-center text-primary font-weight-bold',
            'subtitulo' => 'Gestión administrativa',
        ],
        'botones' => [
            [
                'link' => '/controlinscripciones',
                'icon' => 'bi-files',
                'text' => 'Control de Inscripciones',
                'url_id' => false,
                'hint' => 'Inscripciones y control de documentos',
            ],
            [
                'link' => '/controlcobros',
                'icon' => 'bi-currency-dollar',
                'text' => 'Control de Cobranzas',
                'url_id' => false,
                'hint' => 'Control de movimientos de cobranzas',
            ],
            [
                'link' => '/menubajas',
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
                'link' => '/activos',
                'icon' => 'bi-bookmark-check-fill',
                'text' => 'Matriculados',
                'url_id' => false,
                'hint' => 'Profesionales con matrícula activa',
            ],
            [
                'link' => '/padrongeneral',
                'icon' => 'bi-people-fill',
                'text' => 'Padrón General',
                'url_id' => false,
                'hint' => 'Ver todos los profesionales del padrón',
            ],
            [
                'link' => '/soporte',
                'icon' => 'bi-patch-question-fill',
                'text' => 'Reporte de Inconvenientes',
                'url_id' => false,
                'hint' => 'Soporte para el sistema de matriculación',
            ],
            [
                'link' => '/logout',
                'icon' => 'bi-house-down',
                'text' => 'Cerrar Sesión',
                'url_id' => false,
                'hint' => 'Cerrar sesión del sistema',
            ],
        ],
    ],

    'user' => [
        'header' => [
            'headtagtitulo' => 'h1',
            'headtagclass' => 'text-center text-primary font-weight-bold',
            'titulo' => 'Panel de Usuario',
            'headtagsubtitulo' => 'h3',
            'headtagclasssubt' => 'text-center text-primary font-weight-bold',
            'subtitulo' => 'Gestión de tu matrícula',
        ],
        'botones' => [
            [
                'link' => '/matriculas/',
                'icon' => 'bi-cash-stack',
                'text' => 'Matriculación',
                'url_id' => false,
                'hint' => 'Gestionar tu proceso de matriculación',
            ],
            [
                'link' => '/datospersonales/edit/{id}',
                'icon' => 'bi-card-checklist',
                'text' => 'Mis Datos Personales',
                'url_id' => true,
                'hint' => 'Mantener tus datos actualizados',
            ],
            [
                'link' => '/matriculas/edit/{id}',
                'icon' => 'bi-card-list',
                'text' => 'Mi Matrícula',
                'url_id' => true,
                'hint' => 'Ver tu credencial de matriculación',
            ],
            [
                'link' => '/descargas',
                'icon' => 'bi-file-medical',
                'text' => 'Descargas y Recursos',
                'url_id' => false,
                'hint' => 'Documentos y recursos descargables',
            ],
            [
                'link' => '/comprobantespago/create/{id}',
                'icon' => 'bi-currency-dollar',
                'text' => 'Notificar Pago',
                'url_id' => true,
                'hint' => 'Subir comprobante de pago',
            ],
            [
                'link' => '/miscomprobantes/',
                'icon' => 'bi-cash-stack',
                'text' => 'Mis Comprobantes de Pago',
                'url_id' => false,
                'hint' => 'Ver historial de pagos',
            ],
            [
                'link' => '/logout',
                'icon' => 'bi-house-down',
                'text' => 'Cerrar Sesión',
                'url_id' => false,
                'hint' => 'Cerrar sesión del sistema',
            ],
        ],
    ],

    'guest' => [
        'header' => [
            'headtagtitulo' => 'h1',
            'headtagclass' => 'text-center text-primary font-weight-bold',
            'titulo' => 'Bienvenido',
            'headtagsubtitulo' => 'h3',
            'headtagclasssubt' => 'text-center text-primary font-weight-bold',
            'subtitulo' => 'Sistema de Matriculación',
        ],
        'botones' => [
            [
                'link' => '/login',
                'icon' => 'bi-box-arrow-in-right',
                'text' => 'Iniciar Sesión',
                'url_id' => false,
                'hint' => 'Acceder al sistema con tu cuenta',
            ],
            [
                'link' => '/register',
                'icon' => 'bi-person-plus',
                'text' => 'Registrarse',
                'url_id' => false,
                'hint' => 'Crear una nueva cuenta',
            ],
            [
                'link' => '/requisitos',
                'icon' => 'bi-info-circle',
                'text' => 'Requisitos de Matriculación',
                'url_id' => false,
                'hint' => 'Ver los requisitos para matricularse',
            ],
            [
                'link' => '/institucional',
                'icon' => 'bi-building',
                'text' => 'Información Institucional',
                'url_id' => false,
                'hint' => 'Conocer más sobre la institución',
            ],
        ],
    ],
];
