<?php
if (!function_exists('carnet_png')) {
    function carnet_png(string $pdfFile, string $pngFile): void {
            $dpi = 300;
            $imagick = new Imagick();
            $imagick->setResolution($dpi, $dpi);
            $imagick->readImage($pdfFile.'[0]'); // primera página
            $imagick->setImageFormat('png');

            // Conversión mm->px
            $pxPerMm = $dpi / 25.4;
            $x = (int) round(36 * $pxPerMm);
            $y = (int) round(20 * $pxPerMm);
            $w = (int) round(75 * $pxPerMm);
            $h = (int) round(50 * $pxPerMm);

            $imagick->cropImage($w, $h, $x, $y);
            $imagick->setImagePage(0,0,0,0); // limpiar canvas virtual
            //$pngFile = $_SESSION['directoriobase'] . '/' .$uploadFolder .'credencial_' . $matricula['matriculaasignada'] . '.png';
            $imagick->writeImage($pngFile);
    }
}