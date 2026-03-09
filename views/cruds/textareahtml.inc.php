<?php

function buildTextHtml($f, $values) {
  $readonly = ($f['readonly'] ?? false) ? 'readonly' : '';
  $attr_required = ($f['required'] ?? false) ? 'required' : '';
  $placehold = ($f['placeholder'] ?? false) ? $f['placeholder'] : '';



    $myfield = '<textarea id="' . htmlspecialchars($f['nombre']) . '" 
            name="' . htmlspecialchars($f['nombre']) . '" 
            class="' . htmlspecialchars($f['class']) . '" 
            placeholder="' . htmlspecialchars($placehold ?? '') . '" 
            ' . $attr_required . '  
            ' . ($f['readonly'] ? 'readonly' : '') . ' 
            maxlength="' . htmlspecialchars($f['maxlength'] ?? '') . '">' .
            htmlspecialchars($values[$f['nombre']] ?? '') .
            '</textarea>';
    return $myfield;
}






/*

  <textarea id="<?= $this->e($f['nombre']) ?>" 
                    name="<?= $this->e($f['nombre']) ?>" 
                    class="<?= $this->e($f['class']) ?>" 
                    placeholder="<?= $this->e($f['placeholder'] ?? '') ?>" 
                    <?= !empty($f['required']) ? 'required' : '' ?>  
                    <?php echo ($this->e($f['readonly']== true)) ? 'readonly' : '' ; ?>
                    maxlength="<?= $this->e($f['maxlength'] ?? '') ?>">
                    <?= $this->e($values[$f['nombre']] ?? '') ?>
                </textarea>



*/

?>