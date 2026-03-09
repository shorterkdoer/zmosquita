<?php 
include_once $_SESSION['directoriobase'] . '/views/cruds/inputhtml.inc.php'; 
include_once $_SESSION['directoriobase'] . '/views/cruds/filehtml.inc.php';
include_once $_SESSION['directoriobase'] . '/views/cruds/datehtml.inc.php';
include_once $_SESSION['directoriobase'] . '/views/cruds/textareahtml.inc.php';
include_once $_SESSION['directoriobase'] . '/views/cruds/mailhtml.inc.php';
$rowcnt = 0;
?>

<form action="<?= $this->e($cfg['url_action']) ?>" method="<?= strtoupper($this->e($cfg['method'] ?? 'POST')) ?>" 
    class="<?= $this->e($style['class_form']) ?>" enctype="multipart/form-data">

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var triggers = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    triggers.forEach(function (el) {
      new bootstrap.Tooltip(el);
    });
  });
</script>

<div class="row g-3">
<?php foreach ($fields as $key => $f): 
    $rowcnt++;
    $bgClass = ($rowcnt % 2 === 0) ? 'bg-white' : 'bg-light';

    // Evaluar campos especiales (computed o textstatic)
    if (str_starts_with($key, '#') || in_array($f['type'] ?? '', ['computed', 'textstatic'])) {
        $contenido = evaluarExpresion($f['valor'] ?? '', $values);
        if (!($f['hidden'] ?? false)) {
            echo "<div class='col-12 {$bgClass}'>";
            echo "<div class='" . ($f['class'] ?? 'alert alert-secondary') . "'>";
            echo htmlspecialchars($contenido);
            echo "</div></div>";
        }
        $values[$key] = $contenido;
        continue;
    }
?>

<?php if (($f['hidden']) == true): ?>
    <input type="hidden" id="<?= $this->e($f['nombre']) ?>" name="<?= $this->e($f['nombre']) ?>" 
        value="<?= $this->e($values[$f['nombre']] ?? '') ?>" > 
<?php else: ?>
    <div class="col-<?= $f['col'] ?? 12 ?> <?= $bgClass ?>">
        <label for="<?= $this->e($f['nombre']) ?>" class="form-label"><?= $this->e($f['label']) ?></label>

        <?php switch ($f['type']): 
            case 'textarea': 
                echo buildTextHtml($f, $values);
                break; 
            case 'select':?>
                <?php //= renderSelect($f['options'] ?? [], ['attrs' => ['class' => 'form-select'], 'blank' => '-- Seleccione una opción --'], $values[$f['nombre']] ?? null) 
                $dropcampo = $f['nombre'];
                $droplist = $f['listavalores'] ?? [];
                $dropvalor = $values[$f['nombre']] ?? null;
                $dropreadonly = $f['readonly'] ?? false;    
                echo buildSelect($dropcampo, $droplist, $dropvalor, $dropreadonly);
                ?>

                <? // = buildSelect($f['nombre'], $f['nombre']['listavalores'] ?? [], $values[$f['nombre']] ?? null, $f['readonly']) ?>
            <?php break; 
            case 'checkbox': ?>
                <input type="checkbox" class="form-check-input" name="<?= $f['nombre'] ?>" id="<?= $f['nombre'] ?>" 
                <?= $values[$f['nombre']] ? 'checked' : '' ?> >
            <?php break;   
            case 'decimal': 
                if ($f['readonly'] ?? false) {
                    $readonly = ($f['readonly'] ?? false) ? 'readonly' : '';
                    $attr_required = '';
                    $placehold =  '';

                } else {
                    $readonly = ($f['readonly'] ?? false) ? 'readonly' : '';
                    $attr_required = ($f['required'] ?? false) ? 'required' : '';
                    $placehold = ($f['placeholder'] ?? false) ? $f['placeholder'] : '';
                }

                $attr_class = $f['class'] ?? 'form-control';
                $attr_class = str_replace('form-control', '', $attr_class);
                $attr_class = trim($attr_class);
                $attr_step = ($f['step'] ?? false) ? "step='{$f['step']}'" : '';

                ?>
                <input type="number" <?= $attr_step ?> class="form-control" name="<?= $f['nombre'] ?>" id="<?= $f['nombre'] ?>" 
                    value="<?= $values[$f['nombre']] ?>" <?= $attr_required ?> <?= $readonly ?> <?= $placehold ?> >

            <?php break;   
            case 'number': 
                if ($f['readonly'] ?? false) {
                    $readonly = ($f['readonly'] ?? false) ? 'readonly' : '';
                    $attr_required = '';
                    $placehold =  '';

                } else {
                    $readonly = ($f['readonly'] ?? false) ? 'readonly' : '';
                    $attr_required = ($f['required'] ?? false) ? 'required' : '';
                    $placehold = ($f['placeholder'] ?? false) ? $f['placeholder'] : '';
                }

                $attr_class = $f['class'] ?? 'form-control';
                $attr_class = str_replace('form-control', '', $attr_class);
                $attr_class = trim($attr_class);


                ?>
                <input type="number" class="form-control" name="<?= $f['nombre'] ?>" id="<?= $f['nombre'] ?>" 
                    value="<?= $values[$f['nombre']] ?>" <?= $attr_required ?> <?= $readonly ?> <?= $placehold ?> >
            <?php break;   
            case 'time': ?>
                <input type="time" class="form-control" name="<?= $f['nombre'] ?>" id="<?= $f['nombre'] ?>" 
                    value="<?= $values[$f['nombre']] ?>">
            <?php break;  
            case 'date': 
                echo buildDateInput($this->e($f['nombre']), $this->e($values[$f['nombre']] ?? ''), 
                    $this->e($f['class']), $this->e($f['placeholder'] ?? ''), 
                    !empty($f['required']), $this->e($f['readonly'] ?? false), 
                    $this->e($f['min'] ?? ''), $this->e($f['max'] ?? '')); 
                break; 
            case 'image': ?> 
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div>
                        <?php echo buildInputFile($f, $values[$f['nombre']], $f['readonly']); ?>
                    </div>
                    <div>
                        <?php 
                        $originalName = $values[$f['nombre']] ?? '';
                        if (isset($values['userfolder'])) {
                            $ruta_thumb = $values['userfolder'];
                        } else {
                            $ruta_thumb = getFolder($user_id);
                        }
                        
                        if (!empty($values[$f['nombre']])) {
                            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                            if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                                $imagen_previa = $ruta_thumb . $values[$f['nombre']] ?? '';
                                echo "<a href='" . htmlspecialchars($imagen_previa) . "' target='_blank'><img src='" . htmlspecialchars($imagen_previa) . "' alt='Vista previa' style='max-width: 150px; max-height: 150px;'></a>";
                            }
                        }
                        ?>
                    </div>
                </div>
            <?php break;
            case 'file': ?> 
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div>
                        <?php echo buildInputFile($f, $values[$f['nombre']], $f['readonly']); ?>
                    </div>
                    <div>
                        <?php 
                        $originalName = $values[$f['nombre']] ?? '';
                        if (isset($values['userfolder'])) {
                            $ruta_thumb = $values['userfolder'];
                        } else {
                            $ruta_thumb = getFolder($user_id);
                        }
                        
                        if (!empty($values[$f['nombre']])) {
                            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                            if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                                $imagen_previa = $ruta_thumb . $values[$f['nombre']] ?? '';
                                echo "<a href='" . htmlspecialchars($imagen_previa) . "' target='_blank'><img src='" . htmlspecialchars($imagen_previa) . "' alt='Vista previa' style='max-width: 150px; max-height: 150px;'></a>";
                            }else {
                                $pdf_previo = $ruta_thumb . $values[$f['nombre']] ?? '';
                                echo "<a href='" . htmlspecialchars($pdf_previo) . "' target='_blank'><img src='/icons/pdf.png' alt='PDF' style='width: 32px; height: 32px; margin-right: 8px;'>Ver PDF</a>";
                            }
                        }
                        ?>
                    </div>
                </div>

            <?php break; 
            case 'mail':
                echo buildMail($f['nombre'], $values[$f['nombre']] ?? '', $f['class'], $f['placeholder'] ?? '', 
                    !empty($f['required']), $f['readonly'] ?? false, $f['maxlength'] ?? '');
                break;
            case 'calculated':
                // Muestra el valor proveniente del SELECT (alias = $f['nombre'])
                echo buildTextHtml($f, $values);
                ?>
                <?php
                break;
            case 'calc':
                // Muestra el valor proveniente del SELECT (alias = $f['nombre'])
                echo buildTextHtml($f, $values);
                ?>
                <?php
                break;
            case 'sql':
                // Muestra el valor proveniente del SELECT (alias = $f['nombre'])
                echo buildTextHtml($f, $values);
                ?>
                <?php
            break;

                default: 
                echo buildInput($f['nombre'], $f['type'], $values[$f['nombre']] ?? '', $f['class'], $f['placeholder'] ?? '', 
                    !empty($f['required']), $f['readonly'] ?? false, $f['maxlength'] ?? '', $f['pattern'] ?? '');
        endswitch; ?>

        <?php if (!empty($f['help'] ?? '')): ?><div class="form-text"><?= $this->e($f['help']) ?></div><?php endif; ?>
        <div class="form-text">
        <?php if (!empty($f['link'] ?? '')): ?>
            <a href="<?= htmlspecialchars($f['link']) ?>" target="_blank" style="display: flex; align-items: center; text-decoration: none;">
                <img src="/icons/link.png" alt="Enlace" style="width: 32px; height: 32px; margin-right: 8px;">Visitar enlace</a>
        <?php endif; ?>
        </div>
    </div>
<?php endif; endforeach; ?>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
<?php foreach ($buttons as $button): 
    if ($button['type'] == 'submit'): ?>
        <button type="submit" class="<?= $this->e($button['class']) ?>">
            <i class="<?= $this->e($button['icon']) ?>"></i> <?= $this->e($button['text']) ?> 
        </button>
    <?php elseif ($button['backbutton'] == true): ?>
        <button type="button" class="<?= $this->e($button['class']) ?>" onclick="window.history.go(-1);">
            <i class="<?= $this->e($button['icon']) ?>"></i> <?= $this->e($button['text']) ?>
        </button>
    <?php else: ?>
        <a href="<?= $this->e($button['url']) ?>" class="<?= $this->e($button['class']) ?>">
            <i class="<?= $this->e($button['icon']) ?>"></i> <?= $this->e($button['text']) ?>
        </a>
    <?php endif; endforeach; ?>
</div>
</form>

<?php



function buildSelect(string $name, array $lista, $selected = null, bool $disabled = false): string {
        // Escapar el nombre del campo para asignarlo a "name" e "id"
        $ronly = '';
        if ($disabled) {
            $ronly = ' disabled';
        }

        $html = "<label for=\"" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "\">"  . ":</label>\n";
        $html .= "<select name=\"" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . $ronly .
                "\" id=\"" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "\">\n";
        foreach ($lista as $option) {
            // Escapar el valor y la etiqueta de la opción
            $optValue = htmlspecialchars($option['id'], ENT_QUOTES, 'UTF-8');
            $optText  = htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8');
            // Se compara el valor actual con $selected y se establece el atributo selected si coincide.
            $isSelected = ($selected !== null && $selected == $option['id']) ? ' selected' : '';
            $html .= "<option value=\"{$optValue}\"{$isSelected}>{$optText}</option>\n";
        }
        $html .= "</select>\n";
        return $html;
    }

    
function getFolder(int $userId): string {
    $baseFolder = '/storage/uploads/';
    $gconfig = require $_SESSION['directoriobase'].'/config/settings.php';
    $secretword = $gconfig['basellave'];
    $folderName = md5($userId . $secretword);
    return $baseFolder . $folderName . DIRECTORY_SEPARATOR;
}

function evaluarExpresion(string $expr, array $values): string {
    $evaluado = preg_replace_callback('/@val\(([^)]+)\)/', function ($match) use ($values) {
        $campo = $match[1];
        return isset($values[$campo]) ? $values[$campo] : 0;
    }, $expr);

    $result = 0;
    try {
        if (preg_match('/^[0-9\.+\-\*\/\(\) ]+$/', $evaluado)) {
            eval('$result = ' . $evaluado . ';');
        } else {
            $result = $evaluado;
        }
    } catch (\Throwable $e) {
        $result = 'Error';
    }
    return $result;
}


