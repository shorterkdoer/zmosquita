<?php
use App\Core\Helpers\renderTablaHTML;

//use App\Core\Helpers\buildSelect;
?>

<?php $this->layout('layout', ['title' => 'User Dashboard']) ?>

<script>


</script>


<h1>Bienvenido, <?= $this->e($user['email']) ?></h1>


<p>Este es tu panel de usuario. Aquí podrás acceder a tus datos personales, ver notificaciones, actualizar tu perfil, etc.</p>





<div class="container my-4">

  <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-4 g-3">
    <!-- Repite este bloque por cada actividad -->


    <div class="col text-center">
    <a href="/matriculas/" class="btn btn-outline-primary d-flex flex-column align-items-center py-3 h-100">
        <i class="bi bi-cash-stack mb-2" style="font-size: 3rem;"></i>
        <Strong>Matriculación</strong>
      </a>
    </div>

    <div class="col text-center">
      <a href="/datospersonales/edit/<?= $this->e($user['id']) ?>" class="btn btn-outline-primary d-flex flex-column align-items-center py-3 h-100">
        <i class="bi bi-card-checklist mb-2" style="font-size: 3rem;"></i>
        <Strong>Mis datos personales (Mantener actualizados!)</strong>
      </a>
    </div>

    <div class="col text-center">
    <a href="/matriculas/edit/<?= $this->e($user['id']) ?>"  class="btn btn-outline-primary d-flex flex-column align-items-center py-3 h-100">
        <i class="bi bi-card-list mb-2" style="font-size: 3rem;"></i>
        <Strong>Mi Matrícula</strong>
      </a>
    </div>


    <div class="col text-center">
    <a href="" class="btn btn-outline-primary d-flex flex-column align-items-center py-3 h-100">
        <i class="bi bi-file-medical mb-2" style="font-size: 3rem;"></i>
        <Strong>Descargas y otros recursos</strong>
      </a>
    </div>


    <div class="col text-center">
    <a href="/comprobantespago/create/" class="btn btn-outline-primary d-flex flex-column align-items-center py-3 h-100">
        <i class="bi bi-currency-dollar mb-2" style="font-size: 3rem;"></i>
        <Strong>Notificar pago</strong>
      </a>
    </div>

    <div class="col text-center">
    <a href="/miscomprobantes/" class="btn btn-outline-primary d-flex flex-column align-items-center py-3 h-100">
        <i class="bi bi-cash-stack mb-2" style="font-size: 3rem;"></i>
        <Strong>Mis comprobantes de pago</strong>
      </a>
    </div>


    
    <div class="col text-center">
      <a href="/logout" class="btn btn-outline-success d-flex flex-column align-items-center py-3 h-100">
        <i class="bi bi-house-down mb-2" style="font-size: 3rem;"></i>
        <small>Cerrar sesión</small>
      </a>
    </div>
  </div>
</div>


