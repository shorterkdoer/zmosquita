<?php

function pdf2png(string $pdfFile, string $imageFile): void {
    if (!file_exists($pdfFile)) {
        throw new Exception("El archivo PDF no existe: " . $pdfFile);
    }

    if (!extension_loaded('imagick')) {
        throw new Exception("La extensión Imagick no está habilitada.");
    }

    try {
        dothemagick($pdfFile, $imageFile);
        echo "Imagen guardada como: " . $imageFile;
    } catch (Exception $e) {
        echo "Error al convertir PDF a PNG: " . $e->getMessage();
    }
}

function dothemagick( $pdfFile, string $imageFile): void {

    $imagick = new Imagick();
    $imagick->setResolution(150, 150); // Ajusta la resolución según necesites
    $imagick->readImage($pdfFile."[0]"); // Lee la primera página del PDF (índice 0)
    $imagick->setImageFormat('png');
    $imagick->writeImage($imageFile);
    $imagick->clear();
    $imagick->destroy();
}
// Usage example



