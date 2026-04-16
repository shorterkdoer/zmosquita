<?php

declare(strict_types=1);
?>
<h1>{{ detail_resource_title }}</h1>

<form method="post" action="<?= htmlspecialchars((string)$action, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="{{ foreign_key }}" value="<?= htmlspecialchars((string)($item['{{ foreign_key }}'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

    {{ fields }}

    <div class="form-group">
        <button type="submit">Guardar</button>
    </div>
</form>