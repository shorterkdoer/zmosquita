<?php
//


    /**
    * Genera el markup para mostrar imágenes con Lightbox2 o PDFs con PDF.js.
    *
    * @param  string $filePath  Ruta en el servidor (p. ej. "uploads/mi.pdf" o "uploads/foto.jpg")
    * @param  string $baseUrl   URL base para construir la ruta pública (p. ej. "/uploads")
    * @param  bool   $useThumbs Si es true, intentará usar miniaturas para imágenes en "uploads/thumbs/"
    */
    function renderFileViewer(string $filePath, string $baseUrl, bool $useThumbs = true): void
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $url = rtrim($baseUrl, '/') . '/' . ltrim($filePath, '/');
    
        if (in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
            // Ruta de la miniatura (si existe)
            $thumbUrl = $useThumbs
                ? rtrim($baseUrl, '/') . '/thumbs/' . basename($filePath)
                : $url;
    
            echo <<<HTML
    <div class="file-viewer image-viewer">
      <a href="{$url}" data-lightbox="gallery">
        <img src="{$thumbUrl}" alt="Imagen" />
      </a>
    </div>
    HTML;
    
        } elseif ($ext === 'pdf') {
            // Contenedor para PDF.js
            echo <<<HTML
    <div class="file-viewer pdf-viewer" data-file-url="{$url}">
      <canvas></canvas>
    </div>
    HTML;
    
        } else {
            // Descarga para otros tipos
            echo <<<HTML
    <div class="file-viewer other-viewer">
      <a href="{$url}" target="_blank" rel="noopener">Descargar archivo</a>
    </div>
    HTML;
        }
    }
    
 