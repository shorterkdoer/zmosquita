<?php
session_start();
require_once 'vendor/autoload.php'; // Asegurate de incluir la clase si usás Composer
use Gregwar\Captcha\CaptchaBuilder;

$builder = new CaptchaBuilder(null, new \Gregwar\Captcha\PhraseBuilder(7));
$builder->setMaxFrontLines(1);
$builder->setMaxBehindLines(0);
$builder->setMaxAngle(15);
$builder->setDistortion(true);
$builder->build();

$_SESSION['phrase'] = $builder->getPhrase();

header('Content-type: image/jpeg');
$builder->output();
