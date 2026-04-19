<?php $this->layout('layout', ['title' => 'Listado de Ciudades']) ?>

<h2>Listado de Provincias</h2>

<?php if (!empty($success = \App\Core\Session::flash('success'))): ?>
    <div style="color: green;"><?= $this->e($success) ?></div>
<?php endif; ?>

<?php if (!empty($error = \App\Core\Session::flash('error'))): ?>
    <div style="color: red;"><?= $this->e($error) ?></div>
<?php endif; ?>

<a href="/ciudades/create">Nueva Ciudad</a>
<table border="1" cellspacing="0" cellpadding="5">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($ciudades as $ciudad): ?>
            <tr>
                <td><?= $this->e($ciudad['id']) ?></td>
                <td><?= $this->e($ciudad['nombre']) ?></td>
                <td>
                    <a href="/ciudades/edit/<?= $this->e($ciudad['id']) ?>">Editar</a>
                    <form action="/ciudades/delete/<?= $this->e($ciudad['id']) ?>" method="POST" style="display:inline;">
                        <button type="submit" onclick="return confirm('¿Seguro eliminar?')">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
