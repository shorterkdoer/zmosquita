

<?php

function buildInputFile($f, $value = null) {
    // $id es la clave del registro
    if($f['readonly'] == true){
        
        return '<input type="input" id="' . htmlspecialchars($f['nombre']) . '" 
                name="' . htmlspecialchars($f['nombre']) . '" 
                class="' . htmlspecialchars($f['class']) . '" 
                placeholder=""
                ' . ($f['readonly'] ? 'readonly' : '') . '
                ' . (!empty($f['required']) ? 'required' : '') . '>';
                //placeholder="' . htmlspecialchars($f['nombre'] ?? '') . '"

            }else{
                if ($f['type'] === 'image') {
                    return '<input type="file" id="' . htmlspecialchars($f['nombre']) . '" 
                            name="' . htmlspecialchars($f['nombre']) . '" 
                            class="' . htmlspecialchars($f['class']) . '" 
                            placeholder="archivo..."
                            accept="image/png, image/jpeg"
                            ' . ($f['readonly'] ? 'readonly' : '') . '>';
                } 
                else {         
                    return '<input type="file" id="' . htmlspecialchars($f['nombre']) . '" 
                name="' . htmlspecialchars($f['nombre']) . '" 
                class="' . htmlspecialchars($f['class']) . '" 
                placeholder="archivo..." accept="image/png, image/jpeg, application/pdf"
                ' . '
                ' . (!empty($f['required']) ? 'required' : '') . '>';

                }
            }
        }
        
/*
accept="application/pdf"
<input type="file" id="<?= $this->e($f['nombre']) ?>" 
                name="<?= $this->e($f['nombre']) ?>" 
                class="<?= $this->e($f['class']) ?>" 
                placeholder="<?= $this->e($f['nombre'] ?? '') ?>"
                <?php echo ($this->e($f['readonly']== true)) ? 'readonly' : '' ; ?>
                <?= !empty($f['required']) ? 'required' : '' ?>>
*/
                