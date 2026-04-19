<?php
require_once $_SESSION['directoriobase'] . '/includes/head.inc.php';
?>

<div class="container mt-4">
  <h2>Vista previa del tema activo</h2>

  <div class="form-container mt-3 p-3">
    <h4>Formulario de ejemplo</h4>
    <form>
      <div class="mb-3">
        <label for="nombre" class="form-label">Nombre</label>
        <input type="text" class="form-control" id="nombre" placeholder="Ingrese su nombre">
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Correo electrónico</label>
        <input type="email" class="form-control" id="email" placeholder="nombre@ejemplo.com">
      </div>
      <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
  </div>

  <div class="mt-5">
    <h4>Tabla de ejemplo</h4>
    <table class="table table-bordered mt-2">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Correo</th>
          <th>Rol</th>
        </tr>
      </thead>
      <tbody>
        <tr><td>María Pérez</td><td>maria@example.com</td><td>Administradora</td></tr>
        <tr><td>Juan Gómez</td><td>juan@example.com</td><td>Usuario</td></tr>
        <tr><td>Luis Sosa</td><td>luis@example.com</td><td>Moderador</td></tr>
      </tbody>
    </table>
  </div>
</div>
