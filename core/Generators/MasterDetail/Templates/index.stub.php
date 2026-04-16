<?php

declare(strict_types=1);

use ZMosquita\Core\Support\Facades\Authz;
?>
<h1>{{ detail_resource_title }}</h1>

<p><a href="{{ master_route_base }}">Volver</a></p>

<?php if (Authz::can('{{ create_permission }}')): ?>
    <p><a href="{{ master_route_base }}/<?= (int)$masterId ?>/{{ detail_route_segment }}/create">Nuevo</a></p>
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
                    <a href="{{ master_route_base }}/<?= (int)$masterId ?>/{{ detail_route_segment }}/<?= (int)$row['{{ detail_primary_key }}'] ?>/edit">Editar</a>
                <?php endif; ?>

                <?php if (Authz::can('{{ delete_permission }}')): ?>
                    <form method="post" action="{{ master_route_base }}/<?= (int)$masterId ?>/{{ detail_route_segment }}/<?= (int)$row['{{ detail_primary_key }}'] ?>/delete" style="display:inline;">
                        <button type="submit">Borrar</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>