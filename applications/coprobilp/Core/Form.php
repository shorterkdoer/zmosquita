<?php

namespace App\Core;

use League\Plates\Engine;

class Form
{
    protected Engine $viewEngine;

    public function __construct()
    {
        $this->viewEngine = new Engine($_SESSION['directoriobase']. '/views');
        //$this->viewEngine = new Engine($_SESSION['directoriobase'].'/templates');
    }

    protected function view(string $template, array $data = []): void
    {
        echo $this->viewEngine->render($template, $data);
        exit;
    }

    protected function redirect(string $url): void
    {
        //echo "Location: " . $_SESSION['directoriobase'] . $url;
        //$config = require $_SESSION['base_url'].'/config/settings.php';
        //header("Location: " . $_SESSION['base_url'] . $url);
        header("Location: " . $url);
        exit;
    }

}
