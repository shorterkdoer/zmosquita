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
         $bgClass = ($rowcnt % 2 === 0)               // par = blanco
            ? 'bg-white'
            : 'bg-light'; 
        ?>

        <?php if (($f['hidden']) == true): 
            { ?>
                <input type="hidden" id="<?= $this->e($f['nombre']) ?>" name="<?= $this->e($f['nombre']) ?>" 
                    value="<?= $this->e($values[$f['nombre']] ?? '') ?>" > 
            <?php 
            }else:
            ?>
        <div class="col-<?= $f['col'] ?? 12 ?> <?= $bgClass ?>">
            <label for="<?= $this->e($f['nombre']) ?>" class="form-label"><?= $this->e($f['label']) ?></label>

            <?php switch ($f['type']): 
                case 'textarea': 
                    echo buildTextHtml($f, $values);
                    break; 
                case 'select': ?> 
                    <?= buildSelect($f['nombre'], $f['options'] ?? [], $values[$f['nombre']] ?? null, $f['readonly']) ?>
                <?php break; 
                case 'checkbox': ?>
                    <input type="checkbox" class="form-check-input" name="<?= $f['nombre'] ?>" id="<?= $f['nombre'] ?>" 
                    <?= $values[$f['nombre']] ? 'checked' : '' ?>>
                <?php break;   
                case 'number': 
                    if ($f['readonly'] ?? false) {
                        $readonly = 'readonly';
                    } else {
                        $readonly = '';
                    }
                    $required = $f['required'] ?? false;
                    $attr_required = $required ? 'required' : '';
                    ?>
                    <input type="number" class="form-control" name="<?= $f['nombre'] ?>" id="<?= $f['nombre'] ?>" 
                        value="<?= $values[$f['nombre']] ?>" <?= $attr_required ?> <?= $readonly ?> <?= $this->e($f['placeholder']) ?>>
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
                case 'file':
                        ?> 
                        <div style="display: flex; align-items: center; gap: 1rem;">
                        <!-- Input file -->
                            <div>
                                <?php echo buildInputFile($f, $values[$f['nombre']], $f['readonly']); ?>
                            </div>
                        <!-- Thumbnail (solo visible si hay imagen previa) -->
                            <div>
                                <!-- Esto solo se muestra si hay un valor previo -->
                                <?php 
                                $originalName = $values[$f['nombre']] ?? '';
                                $ruta_thumb = getFolder($user_id);
                                //$xxxlink = "/documentview/" . $id . "/$originalName";
                                if (!empty($values[$f['nombre']])){
                                    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                                    if (in_array($extension, ['jpg', 'jpeg', 'png'])) {

                                        $imagen_previa = $ruta_thumb . $values[$f['nombre']] ?? '';
                                        ?>
                                        <a href="<?= htmlspecialchars($imagen_previa) ?>" target="_blank" style="display: flex; align-items: center; text-decoration: none;">
                                        <img src="<?= htmlspecialchars($imagen_previa) ?>" alt="Vista previa" style="max-width: 150px; max-height: 150px;">
                                <?php 
                                        }
                                    
                                    if (in_array($extension, ["pdf"])) {
                                        $pdf_previo = $ruta_thumb . $values[$f['nombre']] ?? '';
                                         ?>
                                        <a href="<?= htmlspecialchars($pdf_previo) ?>" target="_blank" style="display: flex; align-items: center; text-decoration: none;">
                                        <img src="/icons/pdf.png" alt="PDF" style="width: 32px; height: 32px; margin-right: 8px;">
                                        Ver PDF</a>

                                <?php
                                    }
                                }
                            
                            
                             ?>
                            </div>
                        </div>

                    
                 <?php
                    break; 
                case 'mail':
                        echo buildMail($f['nombre'], $values[$f['nombre']] ?? '', $f['class'], $f['placeholder'] ?? '', 
                            !empty($f['required']), $f['readonly'] ?? false, $f['maxlength'] ?? '');
                        break;

                    
                    default: 
                        echo buildInput($f['nombre'], $f['type'], $values[$f['nombre']] ?? '', $f['class'], $f['placeholder'] ?? '', 
                            !empty($f['required']), $f['readonly'] ?? false, $f['maxlength'] ?? '', $f['pattern'] ?? '');
            endswitch; ?>


            <?php 
            $zhelp = $f['help'] ?? '';
            if (!empty($zhelp)): ?><div class="form-text"><?= $this->e($f['help']) ?></div><?php endif; ?>
            <div class="form-text">
            <?php 
                $zlink = $f['link'] ?? '';
                if (!empty($zlink)){ 
                    ?>
                    <a href="<?= htmlspecialchars($zlink) ?>" target="_blank" 
                            style="display: flex; align-items: center; text-decoration: none;">
                            <img src="/icons/link.png" alt="Enlace" style="width: 32px; height: 32px; margin-right: 8px;">
                            Visitar enlace</a>
                <?php }
                endif;
                 ?>
            </div>
    <?php endforeach; ?>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">

    
        <?php foreach ($buttons as $button): 
            if ($button['type'] == 'submit') {
                
                ?>
                <button type="submit" class="<?= $this->e($button['class']) ?>">
                    <i class="<?= $this->e($button['icon']) ?>"></i>
                    <?= $this->e($button['text']) ?> 
                </button>
            <?php
            } elseif ($button['backbutton'] == true) {
                ?>
                <button type="button" class="<?= $this->e($button['class']) ?>" 
                    onclick="window.history.go(-1);">
                    <i class="<?= $this->e($button['icon']) ?>"></i>
                    <?= $this->e($button['text']) ?>
                </button>

            <?php
            }else {
                ?>
                <a href="<?= $this->e($button['url']) ?>" 
                    class="<?php echo $this->e($button['class']); ?>"> <i class="<?= $this->e($button['icon']) ?>"></i>
                    <?= $this->e($button['text']) ?>
                </a>
            <?php
            }
            endforeach; ?>
            
        
    
    </div>
</form>
<?php

function evaluarExpresion(string $expr, array $values): string {
    $evaluado = preg_replace_callback('/@val\(([^)]+)\)/', function ($match) use ($values) {
        $campo = $match[1];
        return isset($values[$campo]) ? $values[$campo] : 0;
    }, $expr);

    // Evaluar expresiones aritméticas seguras
    $result = 0;
    try {
        // Solo permitimos números y operaciones aritméticas simples
        if (preg_match('/^[0-9\.\+\-\*\/\(\) ]+$/', $evaluado)) {
            eval('$result = ' . $evaluado . ';');
        } else {
            $result = $evaluado; // si no es una fórmula numérica, devolver tal cual
        }
    } catch (\Throwable $e) {
        $result = 'Error';
    }
    return $result;
}

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

function getFolder(int $userId): string
    {
        // Define la carpeta base para uploads. Se recomienda que "storage" esté fuera del webroot.
        $baseFolder = '/storage/uploads/';
        // Genera un nombre de carpeta utilizando un hash (con una "sal" secreta).

        $gconfig = require $_SESSION['directoriobase'].'/config/settings.php';
        $secretword = $gconfig['basellave'];

        $folderName = md5($userId . $secretword);
        $fullPath = $baseFolder . $folderName . DIRECTORY_SEPARATOR;
        //echo $fullPath;
        
        return $fullPath;
    }


?>