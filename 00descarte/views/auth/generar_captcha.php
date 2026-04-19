<?php

require $_SESSION['directoriobase']. '/app/Core/CaptchaGenerator.php';

$captcha = new CaptchaGenerator();
$captcha->length = 10;
$captcha->width = 250;
$captcha->height = 80;
$captcha->angle = 15;
$captcha->linesFront = 4;
$captcha->linesBack = 2;

$captcha->generate();
$_SESSION['captcha'] = $captcha->getPhrase();

?>