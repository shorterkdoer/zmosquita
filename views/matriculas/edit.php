<?php $this->layout('layout', ['title' => 'Editar Matrícula']) ?>

<h4>Editar Datos Personales</h4>

<?php if ($error = \App\Core\Session::flash('error')): ?>
    <div class="alert alert-danger"><?= $this->e($error) ?></div>
<?php endif; ?>
<div class="container">
    <form class="row g-4 p-4 bg-light rounded shadow-sm" action="/matriculas/update/" method="post" enctype="multipart/form-data">
        <!-- Ejemplo de campo de texto para "notaddjj" -->
        <div class="col-12 col-lg-6">
            <div class="p-4 bg-light rounded shadow-sm h-100">
                

                <label class="form-label">Nota declaración jurada:</label>
                <input type="file" name="notaddjj" class="form-control">
                <?php if (!empty($matricula['notaddjj'])): ?>
                    <p>Archivo actual: <?= $this->e($matricula['notaddjj']) ?></p>
                <?php endif; ?>
                <br><br>    
                <!-- Campo para adjuntar archivo: DNI (Frente) -->
                <label>DNI (frente):</label>
                <input type="file" name="dnifrente">
                <?php if (!empty($matricula['dnifrente'])): ?>
                    <p>Archivo actual: <?= $this->e($matricula['dnifrente']) ?></p>
                <?php endif; ?>
                <br><br>

                <!-- Campo para adjuntar archivo: DNI (Dorso) -->
                <label>DNI (dorso):</label>
                <input type="file" name="dnidorso">
                <?php if (!empty($matricula['dnidorso'])): ?>
                    <p>Archivo actual: <?= $this->e($matricula['dnidorso']) ?></p>
                <?php endif; ?>
                <br><br>
                <!-- Campo para adjuntar archivo: Título Original (Frente) -->
                <label>Título Original (frente):</label>
                <input type="file" name="titulooriginalfrente">
                <?php if (!empty($matricula['titulooriginalfrente'])): ?>
                    <p>Archivo actual: <?= $this->e($matricula['titulooriginalfrente']) ?></p>
                <?php endif; ?> 
                <br><br>
                <!-- Campo para adjuntar archivo: Título Original (Dorso) -->
                <label>Título Original (dorso):</label>
                <input type="file" name="titulooriginaldorso">
                <?php if (!empty($matricula['titulooriginaldorso'])): ?>
                    <p>Archivo actual: <?= $this->e($matricula['titulooriginaldorso']) ?></p>
                <?php endif; ?>
                <br><br>
                <!-- Campo para adjuntar archivo: Foto Registro de Graduados -->
                <label>Analítico:</label>
                <input type="file" name="fotoregistrodegraduados">      
                <?php if (!empty($matricula['fotoregistrodegraduados'])): ?>
                    <p>Archivo actual: <?= $this->e($matricula['fotoregistrodegraduados']) ?></p>
                <?php endif; ?>
                <br><br>    
                <!-- Campo para adjuntar archivo: Foto Carnet -->
                <label>Foto Carnet:</label>
                <input type="file" name="fotocarnet">
                <?php if (!empty($matricula['fotocarnet'])): ?>
                    <p>Archivo actual: <?= $this->e($matricula['fotocarnet']) ?></p>
                <?php endif; ?>
                <br><br>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="p-4 bg-light rounded shadow-sm h-100">
                <!-- Campo para adjuntar archivo: Antecedentes Penales -->
                <label>Antecedentes Penales:</label>
                <input type="file" name="antecedentespenales">
                <?php if (!empty($matricula['antecedentespenales'])): ?>
                    <p>Archivo actual: <?= $this->e($matricula['antecedentespenales']) ?></p>
                <?php endif; ?>
                <br><br>
                <!-- Campo para adjuntar archivo: Libre Deuda Alimentario -->
                <label>Libre Deuda Alimentario:</label>
                <input type="file" name="libredeudaalimentario">
                <?php if (!empty($matricula['libredeudaalimentario'])): ?>
                    <p>Archivo actual: <?= $this->e($matricula['libredeudaalimentario']) ?></p>
                <?php endif; ?>
                <br><br>
                <!-- Campo para adjuntar archivo: Constancia de CUIL -->
                <label>Constancia de CUIL:</label>
                <input type="file" name="constanciacuil">
                <?php if (!empty($matricula['constanciacuil'])): ?>
                    <p>Archivo actual: <?= $this->e($matricula['constanciacuil']) ?></p>
                <?php endif; ?>
                <br><br>
                <!-- Campo para adjuntar archivo: apostillado -->   
                <label>Apostillado:</label>
                <input type="file" name="apostillado">
                <?php if (!empty($matricula['apostillado'])): ?>
                    <p>Archivo actual: <?= $this->e($matricula['apostillado']) ?></p>
                <?php endif; ?>
                <br><br>
                <!-- Campo para adjuntar archivo: Matricula Previa -->
                <label>Matrícula Previa:</label>
                <input type="file" name="matriculaprevia">      
                <?php if (!empty($matricula['matriculaprevia'])): ?>
                    <p>Archivo actual: <?= $this->e($matricula['matriculaprevia']) ?></p>
                <?php endif; ?>
                <br><br>
                <!-- Campo para adjuntar archivo: Certificado de Ética -->      
                <label>Certificado de Ética:</label>
                <input type="file" name="certificadoetica">
                <?php if (!empty($matricula['certificadoetica'])): ?>
                    <p>Archivo actual: <?= $this->e($matricula['certificadoetica']) ?></p>
                <?php endif; ?>
                <br><br>
            </div>
        </div>
        
        <!-- Repite campos similares para cada archivo (dnidorso, titulooriginalfrente, etc.) -->
        <button type="submit">Guardar Cambios</button>
        <button type="button" onclick="window.history.back();">Cancelar</button>
        

    </form>
</div>