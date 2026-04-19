<?php
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd; // o puedes usar GdImageBackEnd si no tenés Imagick

//use BaconQrCode\Renderer\Image\GdImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QRHelper
{
    /**
     * Genera un QR, opcionalmente le superpone un ícono, y lo embebe en el PDF.
     */
    public static function embedToPdf(
        $pdf,
        string $contenido,
        float $x,
        float $y,
        float $w,
        string $iconoPath = null,
        bool $eliminarTemporal = true
    ): void {
        $tempQrPath = sys_get_temp_dir() . '/qr_' . uniqid() . '.png';

        // 1. Generar QR básico
        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new ImagickImageBackEnd()
            //new GdImageBackEnd()
        );

        $writer = new Writer($renderer);
        file_put_contents($tempQrPath, $writer->writeString($contenido));

        // 2. Si se indica un ícono, lo superpone
        if ($iconoPath && file_exists($iconoPath)) {
            $qr = imagecreatefrompng($tempQrPath);
            $icon = self::loadImage($iconoPath);
            if ($qr && $icon) {
                $qrSize = imagesx($qr);
                $iconSize = $qrSize * 0.25;

                $iconResized = imagecreatetruecolor($iconSize, $iconSize);
                imagealphablending($iconResized, false);
                imagesavealpha($iconResized, true);

                imagecopyresampled(
                    $iconResized, $icon,
                    0, 0, 0, 0,
                    $iconSize, $iconSize,
                    imagesx($icon), imagesy($icon)
                );

                $iconX = ($qrSize - $iconSize) / 2;
                $iconY = ($qrSize - $iconSize) / 2;

                imagecopy(
                    $qr, $iconResized,
                    $iconX, $iconY,
                    0, 0, $iconSize, $iconSize
                );

                imagepng($qr, $tempQrPath);

                imagedestroy($icon);
                imagedestroy($iconResized);
            }
        }

        // 3. Insertar en PDF
        
        $pdf->Image($tempQrPath, $x, $y, $w, $w); // cuadrado

        // 4. Borrar temporal si se indica
        if ($eliminarTemporal && file_exists($tempQrPath)) {
            unlink($tempQrPath);
        }
    }

    /**
     * Carga imágenes PNG o ICO como GD
     */
    private static function loadImage(string $path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === 'png') {
            return imagecreatefrompng($path);
        } elseif ($ext === 'ico') {
            // Si usás ico, primero convertílo externamente o cambiá esta parte
            return null;
        } else {
            return null;
        }
    }
}
