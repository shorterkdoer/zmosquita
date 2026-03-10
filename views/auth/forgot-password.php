<?php $this->layout('layout', ['title'=>'Recuperar contraseña']); 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



use Gregwar\Captcha\CaptchaBuilder;
$builder = new CaptchaBuilder(null, new \Gregwar\Captcha\PhraseBuilder(7));
$builder->setMaxFrontLines(0);
$builder->setMaxBehindLines(0);
$builder->setMaxAngle(15);
$builder->setDistortion(true);
//$builder->;

$builder->build();



if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


$variable01 = cadenaAleatoria(10, true);
//$variable02 = cadenaAleatoria(10, true);
$_SESSION['jamm01'] = $variable01;
//$_SESSION['jamm02'] = $variable02;


?>
<h2>Recuperar contraseña</h2>
<div class="alert alert-info" role="alert">
<?php
require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/messages.php';
renderFlashMessage(); 
resetFlashMessage();
?>
</div>

 <div class="form-group">
<form action="/password/forgot" method="POST" class="mx-auto" style="max-width: 400px;">

    <?php
    use App\Core\CSRF;
    echo CSRF::tokenField();
    ?>

    <!-- Email -->
    <div class="mb-3">
        <label for="email" class="form-label">Ingrese su correo:</label>
        <input type="email" name="email" id="email" class="form-control" required>
    </div>
 

    <!-- CAPTCHA -->
    <div class="mb-3">
        <label for="phrase" class="form-label">Repita la frase de la imagen:</label><br>
        <?php
        echo '<img src="' . $builder->inline() . '" alt="CAPTCHA" class="my-2" style="width: 400px; height: 110px;"  >';
        $_SESSION['phrase'] = $builder->getPhrase();
        ?>
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
        <input type="text" name="phrase" id="phrase" class="form-control" required>
  <button type="submit" class="btn btn-primary w-100">Enviar enlace de recuperación</button>

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
