<?php $this->layout('layout', ['title' => 'Registro iniciado']) ?>

<h2>Solicitud de registro </h2>


<div class=".form-group">
    <p class="text-danger"><?= $this->e($error) ?></p>
    <div class="input-group mb-3">
    <p>Su inscripción quedó sujeta a revisión.</p>
    <p>Como paso siguiente recibirá un correo electrónico a la dirección informada</p>
    <p>con un link de activación.</p>
    <br>
    </div>

    <div class="col text-center">
      <a href="/matriculas/edit/<?= $this->e($user['id']) ?>"  class="btn btn-outline-primary d-flex flex-column align-items-center py-3 h-100">
        <i class="bi bi-card-list mb-2" style="font-size: 3rem;"></i>
        <Strong>Mi Matrícula</strong>
      </a>
    </div>
</div>



