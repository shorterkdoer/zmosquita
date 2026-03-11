<?php $this->layout('layout', ['title' => 'Registro de usuario']) ?>

<h2>Registro de Usuario</h2>

<?php 

use Gregwar\Captcha\CaptchaBuilder;
$builder = new CaptchaBuilder(null, new \Gregwar\Captcha\PhraseBuilder(7));
$builder->setMaxFrontLines(1);
$builder->setMaxBehindLines(0);
$builder->setMaxAngle(15);
$builder->setDistortion(true);
//$builder->;

$builder->build();


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$numer01 = random_int(0, 99);
$numer02 = random_int(0, 99);
$numer03 = random_int(0, 99);


$numeroAzar01 = numeroALetras($numer01);
$numeroAzar02 = numeroALetras($numer02);
$numeroAzar03 = numeroALetras($numer03);

$compa = random_int(1, 6);
$leyenda1 = "Elija el número ";
if($compa <= 4 ) {
    $leyenda1 .= " <strong>menor</strong> entre (<strong>$numeroAzar01</strong>), (<strong>$numeroAzar02</strong>) y (<strong>$numeroAzar03</strong>) :";
    if($numer01 <= $numer02 && $numer01 <= $numer03) {
        $_SESSION['preverif'] = $numer01;
    }
    if($numer02 <= $numer01 && $numer02 <= $numer03) {
        $_SESSION['preverif'] = $numer02;
    }
    if($numer03 <= $numer01 && $numer03 <= $numer02) {
        $_SESSION['preverif'] = $numer03;
    }

}else 
if($compa > 4 ) {
    $leyenda1 .= " <strong>mayor</strong> entre (<strong>$numeroAzar01</strong>), (<strong>$numeroAzar02</strong>) y (<strong>$numeroAzar03</strong>) :";
    if($numer01 >= $numer02 && $numer01 >= $numer03) {
        $_SESSION['preverif'] = $numer01;
    }
    if($numer02 >= $numer01 && $numer02 >= $numer03) {
        $_SESSION['preverif'] = $numer02;
    }
    if($numer03 >= $numer01 && $numer03 >= $numer02) {
        $_SESSION['preverif'] = $numer03;
    }
    
}

$leyenda1 .= " e ingréselo sus cifras en el campo de abajo.";


$variable01 = cadenaAleatoria(10, true);
//$variable02 = cadenaAleatoria(10, true);
$_SESSION['jamm01'] = $variable01;
//$_SESSION['jamm02'] = $variable02;

$operacion = random_int(0, 8); // 0,4,3 = suma, 1,5,7 = resta, 2,6,8 = multiplicación
$operador1 = random_int(20, 49);
$opstr1 = numeroALetras($operador1);
$operador2 = random_int(1, 5);
$opstr2 = numeroALetras($operador2);
if($operacion == 0 || $operacion == 4 || $operacion == 3) {
    $leyenda2 = "El resultado de la suma entre ($opstr1) y ($opstr2) :";
    $_SESSION['verif'] = $operador1 + $operador2;
}
if($operacion == 1 || $operacion == 5 || $operacion == 7) {
    $leyenda2 = "El resultado de la resta entre ($opstr1) y ($opstr2) :";
    $_SESSION['verif'] = $operador1 - $operador2;
}
if($operacion == 2 || $operacion == 6 || $operacion == 8) {
    $leyenda2 = "El resultado de multiplicar ($opstr1) y ($opstr2) :";
    $_SESSION['verif'] = $operador1 * $operador2;
}




// Generar el CAPTCHA
$builder = new CaptchaBuilder(null, new \Gregwar\Captcha\PhraseBuilder(7));
$builder->setMaxFrontLines(0);
$builder->setMaxBehindLines(0);
$builder->setMaxAngle(15);
$builder->setDistortion(true);
$builder->build();
$errors = $_SESSION['errors'] ?? [];
$old    = $_SESSION['old']    ?? [];


// Limpia flash data
unset($_SESSION['errors'], $_SESSION['old']);


if (!empty($error)): ?>
    <div style="color:red"><?= $this->e($error) ?></div>
<?php endif; ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
 <div class="form-group">
<form action="/register" method="POST" class="mx-auto" style="max-width: 400px;">
    <?php
    use Foundation\Core\CSRF;
    echo CSRF::tokenField();
    ?>

    <!-- Email -->
    <div class="mb-3">
        <label for="email" class="form-label">Email:</label>
        <input type="email" name="emilio" id="email" class="form-control" required>
    </div>

    <!-- Contraseña -->
    <div class="mb-3">
        <label for="password" class="form-label">Contraseña:</label>
        <input type="password" name="verdura" id="password" class="form-control" required>
    </div>

    <!-- Repetir contraseña -->
    <div class="mb-3">
        <label for="repeat-password" class="form-label">Repetir Contraseña:</label>
        <input type="password" name="frutita" id="repeat-password" class="form-control" required>
    </div>

    <!-- CAPTCHA -->
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

    <!-- Campo oculto de frase -->
    <input type="hidden" name="phrase2">

    <!-- Operaciones matemáticas o lógicas -->
    
    <div class="mb-3">
        <label for="var1" class="form-label"><?php echo $leyenda1; ?></label>
        <input type="number" name="vari_l" id="vari_l" class="form-control" step="1" required>
    </div>



<div class="g-recaptcha" data-sitekey="<?= $this->e(getenv('RECAPTCHA_SITE_KEY')) ?>"></div>
    <?php if (!empty($errors['recaptcha'])): ?>
      <small class="text-danger"><?= $this->e($errors['recaptcha']) ?></small>
    <?php endif; ?>
  </div>
    <!-- Botón -->
    <button type="submit" class="btn btn-primary w-100">Registrarse</button>
</form>
 </div>


<?php

function numeroALetras($numero) {
    $unidades = [
        'cero', 'uno', 'dos', 'tres', 'cuatro', 'cinco',
        'seis', 'siete', 'ocho', 'nueve', 'diez',
        'once', 'doce', 'trece', 'catorce', 'quince',
        'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve'
    ];

    $decenas = [
        '', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta',
        'sesenta', 'setenta', 'ochenta', 'noventa'
    ];

    if ($numero < 20) {
        return $unidades[$numero];
    }

    $d = intdiv($numero, 10);
    $u = $numero % 10;

    if ($numero < 30 && $u != 0) {
        // Para los del tipo veintiuno, veintidós, etc.
        return 'veinti' . $unidades[$u];
    } elseif ($u == 0) {
        return $decenas[$d];
    } else {
        return $decenas[$d] . ' y ' . $unidades[$u];
    }
}

function cadenaAleatoria($longitud = 10, $soloLetrasYNumeros = true) {
    $caracteres = $soloLetrasYNumeros
        ? '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
        : '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+-=[]{}|;:,.<>?';

    $caracteresLong = strlen($caracteres);
    $cadena = '';

    for ($i = 0; $i < $longitud; $i++) {
        $indice = random_int(0, $caracteresLong - 1);
        $cadena .= $caracteres[$indice];
    }

    return $cadena;
}
?>
