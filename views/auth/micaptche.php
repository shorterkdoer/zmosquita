<?php

session_start(); # iniciamos la sesion
header("Content-Type: image/png");
$numero = rand(100,999); # generamos un numero aleatorio
//$numero2 = rand(10,99);

# declaramos im con la creación de una imagen
$im = imagecreate(200, 40);
$op = rand(0,5);
$coltext = rand(0,1);
$color1 = rand(0,255);
$color2 = rand(0,255);
$color3 = rand(0,255);
$rotar = rand(1,60) -30;

//$fondo = imagecolorallocate($im, $color1, $color2, $color3); 
$nlineas = rand(9,22);

for ($i = 0; $i < $nlineas; $i++) {
            $c = mt_rand(70, 250);
            $clinea = imagecolorallocate($im, $c, $c, $c); 
            imageline($im, mt_rand(0, 200), mt_rand(0, 30), mt_rand(0, 200), mt_rand(0, 30), $clinea);
        }

$xxx= 200;
$yyy= 40;
$angle = 22 +rand(7,25);

$ttffile = "LiberationSans-Regular.ttf";
$_SESSION['captchikey'] = $numero ; 
$texto = $numero ;
		

# indicamos el color del fondo (RGB)
# 
//imagestring($im, 12, 20, 5, $numero, $texto);
//imagestring($im, 12, 70, 5, $numero2, $texto);
//imagestring($im, 12, 110, 5, $opestr, $texto);

# se crea la imagen, la imagen será formato PNG

//imagettftext($im, 20, 10, $xxx, $yyy, $fondo, $ttffile, $texto);


if($coltext==0) {
//	$texto = imagecolorallocate($im, 255, 255, 255); 
//	$fondo = imagecolorallocate($im, 0, 0, 0); 
	imagestring($im, 14, 20, 5, $texto, 255);

}
else 
{
//	$texto = imagecolorallocate($im, 0, 0, 0); 
	imagestring($im, 14, 20, 5, $texto,255);

	}

$rotate = imagerotate($im, $rotar, 0);
imagepng($rotate);


?>
