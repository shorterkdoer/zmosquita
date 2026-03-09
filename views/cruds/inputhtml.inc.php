<?php

function buildInput($name, $type, $value = '', $class = '', $placeholder = '', $required = false, 
    $readonly = false, $maxlength = '', $pattern = '') {
        
        //if($readonly){
            

        $myinputfield = '<input type="' . htmlspecialchars($type) . 
            '" id="' . htmlspecialchars($name) . 
            '" name="' . htmlspecialchars($name) . 
            '" value="' . htmlspecialchars($value) . 
            '" class="' . htmlspecialchars($class) . 
            '" placeholder="'  . htmlspecialchars($placeholder) . '" ' ;
            
            if (!empty($readonly))
                $myinputfield .=  ' readonly '  ;
            if (!empty($required)) 
                $myinputfield .=  ' required '  ;
                
            if (!empty($maxlength)) 
                $myinputfield .= ' maxlength="' . htmlspecialchars($maxlength) ;
            

            //maxlength="' . htmlspecialchars($maxlength) . '";
            
            if (!empty($pattern)) 
                $myinputfield .= ' pattern="' . htmlspecialchars($pattern) ;
            
            $myinputfield .= '">';

            return $myinputfield;
            
            
            /*
            '<input type="' . htmlspecialchars($type) . '" 
            id="' . htmlspecialchars($name) . '" 
            name="' . htmlspecialchars($name) . '" 
            value="' . htmlspecialchars($value) . '" 
            class="' . htmlspecialchars($class) . '" 
            placeholder="' . htmlspecialchars($placeholder) . '" 
            ' . ($readonly ? 'readonly' : '') . ' 
            ' . ($required ? 'required' : '') . ' 
            maxlength="' . htmlspecialchars($maxlength) . '" 
            pattern="' . htmlspecialchars($pattern) . '">';
*/
}

/*

<input type="<?= $this->e($f['type']) ?>" 
id="<?= $this->e($f['nombre']) ?>" 
name="<?= $this->e($f['nombre']) ?>" 
value="<?= $this->e($values[$f['nombre']] ?? '') ?>" 
class="<?= $this->e($f['class']) ?>" 
placeholder="<?= $this->e($f['placeholder'] ?? '') ?>" 
<?= !empty($f['required']) ? 'required' : '' ?>  
maxlength="<?= $this->e($f['maxlength'] ?? '') ?>" 
pattern="<?= $this->e($f['pattern'] ?? '') ?>">

*/

?>
