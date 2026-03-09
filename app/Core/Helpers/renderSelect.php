<?php
function renderSelect(array $rows, array $options = [], $selected = null): string
{
    $attrs = $options['attrs'] ?? [];
    $blank = array_key_exists('blank', $options) ? $options['blank'] : '-- Seleccione una opción --';

    $attrPairs = [];
    foreach ($attrs as $k => $v) {
        $attrPairs[] = htmlspecialchars($k, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8') . '"';
    }
    $attrStr = implode(' ', $attrPairs);

    $html  = "<select {$attrStr}>\n";
    if ($blank !== false && $blank !== null) {
        $html .= '  <option value="">' . htmlspecialchars((string)$blank, ENT_QUOTES, 'UTF-8') . "</option>\n";
    }

    if (!empty($rows)) {
        foreach ($rows as $row) {
            $value = $row['id'] ?? '';
            $label = $row['label'] ?? '';
            $isSelected = ((string)$value === (string)$selected) ? ' selected' : '';
            $html .= '  <option value="' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '"' . $isSelected . '>'
                  .  htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
                  .  "</option>\n";
        }
    } else {
        $html .= "  <option value=\"\">No hay datos disponibles</option>\n";
    }

    $html .= "</select>\n";
    return $html;
}
