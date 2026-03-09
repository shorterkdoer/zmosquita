<?php
$theme = require __DIR__ . '/../styles/themes/activo.php';
require_once __DIR__ . '/../helpers/StyleHelper.php';
echo StyleHelper::renderCSS($theme);
?>
