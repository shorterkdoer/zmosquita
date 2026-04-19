<?php $this->layout('layout', ['title' => 'User Dashboard']) ?>


<script>


</script>


<h1>Bienvenido, <?= $this->e($user['email']) ?></h1>


<p>Menu para la autogestión de Matrícula</p>





<div class="container my-4">
    
  <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-4 g-3">
    <!-- Repite este bloque por cada actividad -->

    <div class="col text-center">
    <a href="/rematricula" class="btn btn-outline-primary d-flex flex-column align-items-center py-3 h-100">
        <i class="bi bi-award-fill mb-2" style="font-size: 3rem;"></i>
        <Strong>Rematriculación (Matricula actual en la provincia extendida por el Ministerio de Salud)</strong>
      </a>
    </div>

    <div class="col text-center">
    <a href="/primeramatricula" class="btn btn-outline-primary d-flex flex-column align-items-center py-3 h-100">
        <i class="bi bi-bank mb-2" style="font-size: 3rem;"></i>
        <Strong>Primera inscripción - (Inicio de actividades en la provincia)</strong>
      </a>
    </div>
    <div class="col text-center">
    <a href="/previamatricula" class="btn btn-outline-primary d-flex flex-column align-items-center py-3 h-100">
        <i class="bi bi-award mb-2" style="font-size: 3rem;"></i>
        <Strong>Documentación adicional profesionales matriculados previamente en otra jurisdicción</strong>
      </a>
    </div>

    <div class="col text-center">
    <a href="/titulodeotranacion" class="btn btn-outline-primary d-flex flex-column align-items-center py-3 h-100">
        <i class="bi bi-award mb-2" style="font-size: 3rem;"></i>
        <Strong>Documentación adicional profesionales extranjeros</strong>
      </a>
    </div>


  </div>
</div>


