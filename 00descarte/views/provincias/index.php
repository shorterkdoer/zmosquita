<?php $this->layout('layout', ['title' => 'Listado de Provincias']) ?>

<h2>Listado de Provincias</h2>

<?php if (!empty($success = \App\Core\Session::flash('success'))): ?>
    <div style="color: green;"><?= $this->e($success) ?></div>
<?php endif; ?>

<?php if (!empty($error = \App\Core\Session::flash('error'))): ?>
    <div style="color: red;"><?= $this->e($error) ?></div>
<?php endif; ?>

<a href="/provincias/create">Nueva Provincia</a>
<table border="1" cellspacing="0" cellpadding="5">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($provincias as $provincia): ?>
            <tr>
                <td><?= $this->e($provincia['id']) ?></td>
                <td><?= $this->e($provincia['nombre']) ?></td>
                <td>
                    <a href="/provincias/edit/<?= $this->e($provincia['id']) ?>">Editar</a>
                    <form action="/provincias/delete/<?= $this->e($provincia['id']) ?>" method="POST" style="display:inline;">
                        <button type="submit" onclick="return confirm('¿Seguro eliminar?')">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
