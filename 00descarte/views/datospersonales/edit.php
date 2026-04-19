<?php $this->layout('layout', ['title' => 'Editar Datos Personales']) ?>

<h4>Editar Datos Personales</h4>
<div class="container">
    <form class="row g-4 p-4 bg-light rounded shadow-sm" action="/datospersonales/update" method="post">
        <div class="col-12 col-lg-6">
            
            <div class="row mb-3">
                <label class="form-label">
                    Nombre:
                    <input type="text" name="nombre" class="form-control" value="<?= $this->e($datospersonales['nombre'] ?? '') ?>">
                </label>
                <br>
                <label class="form-label">
                    Apellido:
                    <input type="text" name="apellido" class="form-control" value="<?= $this->e($datospersonales['apellido'] ?? '') ?>">
                </label class="form-label">
                <br>
                <label class="form-label">
                    DNI:
                    <input type="text" name="dni" class="form-control" value="<?= $this->e($datospersonales['dni'] ?? '') ?>">
                </label>
                <br>
                <label class="form-label">
                    Calle:
                    <input type="text" name="direccion_calle" class="form-control" value="<?= $this->e($datospersonales['direccion_calle'] ?? '') ?>">
                </label class="form-label">
                <br>
                <label class="form-label">
                    Número:
                    <input type="text" name="direccion_numero" class="form-control" value="<?= $this->e($datospersonales['direccion_numero'] ?? '') ?>">
                </label class="form-label">
                <br>   
                <label class="form-label">
                    Piso:
                    <input type="text" name="direccion_piso" class="form-control" value="<?= $this->e($datospersonales['direccion_piso'] ?? '') ?>">
                </label>
                <br>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            
            <div class="row mb-3">
                <label class="form-label">
                    Departamento:
                    <input type="text" name="direccion_depto" class="form-control" value="<?= $this->e($datospersonales['direccion_depto'] ?? '') ?>">
                </label>
                <br>    
                <label class="form-label">
                    Código Postal:
                    <input type="text" name="direccion_cp" class="form-control" value="<?= $this->e($datospersonales['direccion_cp'] ?? '') ?>"> 
                </label>
                <br>
                
                <br></br>
                <label class="form-label">
                    Localidad:
                    <div class="form-control"><?php echo buildSelect('ciudad_id', $ciudades, $datospersonales['ciudad_id'] ?? null); ?> </div>
                </label>
                <br>
                <label class="form-label">
                    Provincia: 
                    <div class="form-control"><?php echo buildSelect('provincia_id', $provincias, $datospersonales['provincia_id'] ?? null);?></div>
                </label>
                
                <br>    
                <label class="form-label">
                    Teléfono:
                    <input type="text" name="telefono" class="form-control" value="<?= $this->e($datospersonales['telefono'] ?? '') ?>">
                </label>
                <br>
                <label class="form-label">
                    Celular:
                    <input type="text" name="celular" class="form-control" value="<?= $this->e($datospersonales['celular'] ?? '') ?>">
                </label>
                <br>

            </div>
        </div>
        <br>
        <div class="row mb-3">
                <button type="submit">Guardar Cambios</button>
                <button type="button" onclick="window.history.back();">Cancelar</button>
        </div>

    </form>
</div>