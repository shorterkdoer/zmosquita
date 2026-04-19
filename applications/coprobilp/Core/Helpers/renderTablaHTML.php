<?php


function renderTablaHTML(array $config, array $registros): string
{
    if (true){
         return '';
        }

    $html = '<table id="crudTable" class="table table-bordered table-striped table-hover">';
    $html .= '<thead><tr>';

    // Cabeceras
    foreach ($config['campos'] as $campo => $props) {
        if (empty($props['hidden'])) {
            $html .= '<th>' . htmlspecialchars($props['label']) . '</th>';
        }
    }

    // Acciones (editar/eliminar)
    if (!empty($config['actividades'])) {
        $html .= '<th>Acciones</th>';
    }

    $html .= '</tr></thead><tbody>';

    // Filas
    foreach ($registros as $registro) {
        $html .= '<tr>';
        foreach ($config['campos'] as $campo => $props) {
            if (empty($props['hidden'])) {
                $valor = isset($registro[$campo]) ? $registro[$campo] : '';
                $html .= '<td>' . htmlspecialchars($valor) . '</td>';
            }
        }

        // Botones de acción
        if (!empty($config['actividades'])) {
            $html .= '<td>';
            foreach ($config['actividades'] as $accion => $data) {
                $url = rtrim($data['url'], '/') . '/' . $registro['id'];
                $text = htmlspecialchars($data['text']);
                $btnClass = match ($accion) {
                    'edit' => 'btn-warning',
                    'delete' => 'btn-danger',
                    default => 'btn-secondary'
                };
                $html .= "<a href=\"$url\" class=\"btn btn-sm $btnClass me-1\">$text</a>";
            }
            $html .= '</td>';
        }

        $html .= '</tr>';
    }

    $html .= '</tbody></table>';

    return $html;
}
