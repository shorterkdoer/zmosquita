<?php $this->layout('layout', ['title' => 'Login']) ?>

<h2>Iniciar sesión</h2>

<?php 

use Gregwar\Captcha\CaptchaBuilder;
$builder = new CaptchaBuilder(null, new \Gregwar\Captcha\PhraseBuilder(7));
$builder->setMaxFrontLines(0);
$builder->setMaxBehindLines(0);
$builder->setMaxAngle(15);
$builder->setDistortion(true);
//$builder->;

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
<form action="/login" method="POST" class="form-group">
    <?php
    use Foundation\Core\CSRF;
    echo CSRF::tokenField();
    ?>
    <div class="input-group mb-3">
    <label>Email:
        <input type="email" name="email" required>
    </label><br>
    </div>


    <div class="input-group mb-3">
        <label>Contraseña:
        <input type="password" id="password" name="password" class="form-control"> 
        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
        <i class="bi bi-eye-slash"></i>
        </button>
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
            echo '<img src="'.$builder->inline().'" alt="CAPTCHA" style="width: 400px; height: 110px;">';
            $_SESSION['phrase'] = $builder->getPhrase();
            
            ?>
            
            <input type="text" name="phrase" required>
            </label>
        </div>
        <input type="hidden" name="phrase2">

  <button type="submit" class="btn btn-primary">Enviar</button>
     

</form>
</div>
<p class="mt-2">
  <a href="/password/forgot">¿Olvidaste tu contraseña?</a>
</p>


