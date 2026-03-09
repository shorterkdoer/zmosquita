<h2>Editar cajas de texto para: <?= $this->e($template->name) ?></h2>

<table class="table table-bordered">
  <thead>
    <tr>
      <th>Etiqueta</th>
      <th>Variable</th>
      <th>Posición</th>
      <th>Dimensiones</th>
      <th>Fuente</th>
      <th>Alineación</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($textboxes as $box): ?>
    <tr>
      <td><?= $box->label ?></td>
      <td><?= $box->variable ?></td>
      <td><?= $box->x ?>, <?= $box->y ?></td>
      <td><?= $box->width ?>×<?= $box->height ?></td>
      <td><?= $box->font ?> (<?= $box->size ?>pt)</td>
      <td><?= $box->align ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<a href="/document_templates/<?= $template->id ?>/textboxes/add" class="btn btn-primary">Agregar nueva caja</a>
