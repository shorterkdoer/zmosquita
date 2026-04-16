<?php

declare(strict_types=1);

use ZMosquita\Core\Support\Facades\Authz;
?>
<h1>{{ resource_title }}</h1>

<?php if (Authz::can('{{ create_permission }}')): ?>
    <p><a href="{{ route_base }}/create">Nuevo</a></p>
<?php endif; ?>

<table border="1" cellpadding="6" cellspacing="0">
    <thead>
    <tr>
        {{ thead }}
        <th>Acciones</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach (($rows ?? []) as $row): ?>
        <tr>
            {{ relation_lookups }}
            {{ tbody_cells }}
            <td>
                <?php if (Authz::can('{{ edit_permission }}')): ?>
                    <a href="{{ route_base }}/<?= (int)$row['{{ primary_key }}'] ?>/edit">Editar</a>
                <?php endif; ?>

                <?php if (Authz::can('{{ delete_permission }}')): ?>
                    <form method="post" action="{{ route_base }}/<?= (int)$row['{{ primary_key }}'] ?>/delete" style="display:inline;">
                        <button type="submit">Borrar</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>