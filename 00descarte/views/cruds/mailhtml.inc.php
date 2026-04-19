<?php

function buildMail($name, $value = '', $class = '', $placeholder = '', $required = false, 
    $readonly = false, $maxlength = '') {
        
        //if($readonly){
            
            return '<input type=email"' . '" 
            id="' . htmlspecialchars($name) . '" 
            name="' . htmlspecialchars($name) . '" 
            value="' . htmlspecialchars($value) . '" 
            class="' . htmlspecialchars($class) . '" 
            placeholder="' . htmlspecialchars($placeholder) . '" 
            ' . ($readonly ? 'readonly' : '') . ' 
            ' . ($required ? 'required' : '') . ' 
            maxlength="' . htmlspecialchars($maxlength) . '">';


}