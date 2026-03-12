<?php $this->layout('layout', ['title' => 'Panel de Matrícula']) ?>

<h2 class="text-center text-primary font-weight-bold">Panel de control de matriculado</h2>
<h4 class="text-center text-primary font-weight-bold">Actividades según perfil</h4>

<div class="container my-4">
  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">

    <div class="col">
      <div class="card h-100 border-primary">
        <div class="card-body text-center">
          <i class="bi bi-bank mb-3" style="font-size: 3rem;"></i>
          <h5 class="card-title">Primera Matriculación</h5>
          <p class="card-text">Inicio de actividades en la provincia</p>
          <p class="card-text small text-muted">Profesionales que inician actividades en la provincia y no tienen matrícula en la provincia</p>
          <a href="/primeramatricula" class="btn btn-primary">Comenzar</a>
        </div>
      </div>
    </div>

    <div class="col">
      <div class="card h-100 border-primary">
        <div class="card-body text-center">
          <i class="bi bi-award mb-3" style="font-size: 3rem;"></i>
          <h5 class="card-title">Matriculación por Reciprocidad</h5>
          <p class="card-text">Profesionales matriculados en otra jurisdicción</p>
          <p class="card-text small text-muted">Profesionales que inician actividades en la provincia y tienen matrícula en otra jurisdicción</p>
          <a href="/previamatricula" class="btn btn-primary">Comenzar</a>
        </div>
      </div>
    </div>

    <div class="col">
      <div class="card h-100 border-primary">
        <div class="card-body text-center">
          <i class="bi bi-globe mb-3" style="font-size: 3rem;"></i>
          <h5 class="card-title">Título de otra Nación</h5>
          <p class="card-text">Profesionales extranjeros</p>
          <p class="card-text small text-muted">Profesionales que inician actividades en la provincia y están titulados fuera de Argentina</p>
          <a href="/titulodeotranacion" class="btn btn-primary">Comenzar</a>
        </div>
      </div>
    </div>

  </div>
</div>

<div class="container my-4">
  <div class="row">
    <div class="col-12 text-center">
      <a href="/user-dashboard" class="btn btn-outline-secondary">Volver al Dashboard</a>
    </div>
  </div>
</div>
