<?php
if (!function_exists('buildSelect')) {
    /**
     * Construye un elemento select a partir de un array de datos.
     *
     * @param string $name El name e id del select.
     * @param array $options Array de opciones, cada opción debe ser asociativa con 'id' y 'nombre'.
     * @param mixed $selected Valor seleccionado.
     * @return string HTML del select.
     */
    /* previo  al cambio del tío
    function buildSelect(string $name, array $options, $selected = null, bool $disabled = false): string {
        // Escapar el nombre del campo para asignarlo a "name" e "id"
        $ronly = '';
        if ($disabled) {
            $ronly = ' disabled';
        }

        $html = "<label for=\"" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "\">" . ucfirst($name) . ":</label>\n";
        $html .= "<select name=\"" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . $ronly .
                    "\" id=\"" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "\">\n";
        foreach ($options as $option) {
            // Escapar el valor y la etiqueta de la opción
            $optValue = htmlspecialchars($option['id'], ENT_QUOTES, 'UTF-8');
            $optText  = htmlspecialchars($option['nombre'], ENT_QUOTES, 'UTF-8');
            // Se compara el valor actual con $selected y se establece el atributo selected si coincide.
            $isSelected = ($selected !== null && $selected == $option['id']) ? ' selected' : '';
            $html .= "<option value=\"{$optValue}\"{$isSelected}>{$optText}</option>\n";
        }
        $html .= "</select>\n";
        return $html;
    }
        */

function buildSelect(string $name, array $options, $selected = null, bool $disabled = false): string {
    $ronly = $disabled ? ' disabled' : '';

    // Placeholder opcional
    $placeholder = $options['placeholder'] ?? null;

    // Modo A (legacy): $options es una lista simple de opciones con ['id','nombre']
    $isLegacyList = isset($options[0]) && is_array($options[0]) && array_key_exists('id', $options[0]);

    // Modo B (compuesto): $options['data'] = filas; $options['mostrarcampo'] = ['campo1','campo2']
    $isComposed = isset($options['data']) && is_array($options['data']);

    $html  = "<label for='" . htmlspecialchars($name) . "'>" . ucfirst($name) . ":</label>\n";
    $html .= "<select name='" . htmlspecialchars($name) . "'$ronly id='" . htmlspecialchars($name) . "'>\n";

    if ($placeholder) {
        $html .= "<option value=''>" . htmlspecialchars($placeholder) . "</option>\n";
    }
/*
    if ($isLegacyList) {
        // --- LEGACY: lista simple ['id','nombre'] ---
        foreach ($options as $option) {
            $optValue   = htmlspecialchars($option['id']);
            $optText    = htmlspecialchars($option['nombre']);
            $isSelected = ($selected !== null && (string)$selected === (string)$option['id']) ? ' selected' : '';
            $html      .= "<option value='{$optValue}'{$isSelected}>{$optText}</option>\n";
        }

    } elseif ($isComposed) {
        // --- COMPUESTO: data + mostrarcampo ---
        */
        $data        = $options['data'];
        $idField     = $options['id_field']      ?? 'id';
        $mostrar     = $options['mostrarcampo']  ?? ['nombre']; // lista de campos para armar la etiqueta
        $separator   = $options['separator']     ?? ' ';        // separador entre campos
        $trimEmpty   = $options['trim_empty']    ?? true;       // descartar partes vacías

        foreach ($data as $row) {
            // Valor de <option>
            $val = $row[$idField] ?? null;

            // Etiqueta compuesta
            $parts = [];
            foreach ($mostrar as $m) {
                $parts[] = isset($row[$m]) ? (string)$row[$m] : '';
            }
            if ($trimEmpty) {
                $parts = array_filter($parts, fn($x) => $x !== '' && $x !== null);
            }
            $label = implode($separator, $parts);

            $isSelected = ($selected !== null && (string)$selected === (string)$val) ? ' selected' : '';
            $html .= "<option value='" . htmlspecialchars((string)$val) . "'{$isSelected}>"
                  .  htmlspecialchars($label) . "</option>\n";
        }
/*
    } else {
        // --- Caso defensivo: no hay datos ---
        // (conservamos compatibilidad; no rompemos)
    }
*/
    $html .= "</select>\n";
    return $html;
}



}
