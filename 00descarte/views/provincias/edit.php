<?php $this->layout('layout', ['title' => 'Registrar nueva provincia']) ?>

<h4>Provincia</h4>
<div class="container">
    <form class="row g-4 p-4 bg-light rounded shadow-sm" action="/provincias/create" method="post">
        <div class="col-12 col-lg-6">
            
            <div class="row mb-3">
                <label class="form-label">
                    Nombre:
                    <input type="text" name="nombre" class="form-control" value="<?= $this->e($datospersonales['nombre'] ?? '') ?>">
                </label>
                <br>
            </div>
        </div>
    </form>
</div>



