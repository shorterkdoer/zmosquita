<?php
return [
  'config' => [
    'title' => 'Modelos de documento',
    'field_id' => 'id',
    'field_main' => 'name',
    'table' => 'document_templates',
    'url_action' => '/document_templates',
    'icon' => 'fa fa-file-pdf',
  ],
  'campos' => [
    'id' => ['label' => 'ID', 'visible' => false],
    'name' => ['label' => 'Nombre del modelo', 'type' => 'text'],
    'description' => ['label' => 'Descripción', 'type' => 'textarea'],
    'page_size' => [
      'label' => 'Tamaño',
      'type' => 'select',
      'options' => ['A4' => 'A4', 'Letter' => 'Letter', 'Legal' => 'Legal', 'Custom' => 'Personalizado']
    ],
    'orientation' => [
      'label' => 'Orientación',
      'type' => 'select',
      'options' => ['portrait' => 'Vertical', 'landscape' => 'Horizontal']
    ],
    'background_image_path' => ['label' => 'Imagen de fondo', 'type' => 'text'],
    'watermark_text' => ['label' => 'Marca de agua', 'type' => 'text'],
    'qr_code_position' => [
      'label' => 'QR',
      'type' => 'select',
      'options' => [
        'none' => 'Sin QR',
        'top-left' => 'Arriba izquierda',
        'top-right' => 'Arriba derecha',
        'bottom-left' => 'Abajo izquierda',
        'bottom-right' => 'Abajo derecha',
        'custom' => 'Posición manual'
      ]
    ],
  ],
  'actividades' => [
    'agregar' => true,
    'editar' => true,
    'borrar' => true,
    'ver' => true,
  ],
  'comandos' => [
    'definir_cajas' => [
      'label' => 'Cajas de texto',
      'url' => '/document_templates/{{id}}/textboxes',
      'icon' => 'fa fa-th-large'
    ],
    'vincular_variables' => [
      'label' => 'Vincular datos',
      'url' => '/document_templates/{{id}}/bindings',
      'icon' => 'fa fa-link'
    ],
    'generar' => [
      'label' => 'Generar PDF',
      'url' => '/document_templates/generateview/{{id}}',
      'icon' => 'fa fa-print'
    ]
  ]
];
