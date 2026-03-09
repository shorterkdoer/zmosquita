<?php $this->layout('layout', ['title' => 'Solicitar revisión de la documentación']) ?>

<h2>Sólo si se adjuntó toda la documentación requerida</h2>
sesssion_start();
<?php 

use Gregwar\Captcha\CaptchaBuilder;
$builder = new CaptchaBuilder(null, new \Gregwar\Captcha\PhraseBuilder(7));
$builder->setMaxFrontLines(0);
$builder->setMaxBehindLines(0);
$builder->setMaxAngle(15);
$builder->setDistortion(true);
$builder->build();


 
  
 ?>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('togglePassword');
    const input  = document.getElementById('password');

    toggle.addEventListener('click', () => {
      // Cambia el tipo
      const nuevoTipo = input.type === 'password' ? 'text' : 'password';
      input.type = nuevoTipo;

      // Cambia el icono (Font Awesome)
      const icon = toggle.querySelector('i');
      icon.classList.toggle('bi-eye');
      icon.classList.toggle('bi-eye-slash');
    });
  });
</script>

<div class=".form-group">
    <p class="text-danger"><?= $this->e($error) ?></p>
<form action="/vaarevision" method="POST" class="form-group">
    <div class="input-group mb-3">
    <label>Usted está por solicitar la revisión de la documentación presentada para su matrícula. 
        <br>Si no ha adjuntado toda la documentación requerida, por favor, vuelva a la sección de <a href="/matriculas">Documentación</a> y complete los campos requeridos.
        <br>Si ya ha adjuntado toda la documentación requerida, complete el captcha y luego
        <br>haga clic en "Enviar" para solicitar la revisión.

    </label><br>
    </div>


 


				<div class="row form-group">
					<script> 
					$(document).ready(function() {  
						$('#freshcaptcha').click(function(){
							document.location.reload();
							return false;
						});
					});
					</script>


						<div>
							<button id="freshcaptcha">Refrescar imagen</button>
						</div>

				</div>





    <div class="input-group mb-3">
            <label>Repita la frase de la imagen:
            <?php
            echo '<img src="'.$builder->inline().'" alt="CAPTCHA" style="width: 350px; height: 50px;">';
            $_SESSION['phrase'] = $builder->getPhrase();
            
            ?>
            
            <input type="text" name="phrase" required>
            </label>
        </div>
        <input type="hidden" name="phrase2">

  <button type="submit" class="btn btn-primary">Enviar</button>
    

</form>
</div>


