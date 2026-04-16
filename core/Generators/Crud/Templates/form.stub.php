<?php

declare(strict_types=1);
?>
<h1>{{ resource_title }}</h1>

<form method="post" action="<?= htmlspecialchars((string)$action, ENT_QUOTES, 'UTF-8') ?>">
    {{ fields }}

    <div class="form-group">
        <button type="submit">Guardar</button>
    </div>
</form>