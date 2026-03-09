<?php


namespace App\Core\Helpers;
use Exception;
use Imagick;
use Fpdf\FPDF;

//require_once 'fpdf/fpdf.php';

class ExpedienteBuilder
{
    protected array $archivos = [];

    public function addArchivo(string $ruta): void
    {
        if (file_exists($ruta)) {
            $this->archivos[] = $ruta;
        }
    }

    public function emitirPDF(string $nombreArchivo = 'expediente_final.pdf'): void
    {
        $pdf = new FPDF();

        foreach ($this->archivos as $archivo) {
            $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));

            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $imagenes = [$archivo];
            } elseif ($ext === 'pdf') {
                $imagenes = $this->convertirPDFaImagenes($archivo);
            } else {
                continue; // ignorar otros formatos
            }

            foreach ($imagenes as $img) {
                list($ancho, $alto) = getimagesize($img);
                $ancho_mm = $ancho * 0.264583; // px a mm
                $alto_mm = $alto * 0.264583;

                $pdf->AddPage();
                $pdf->Image($img, 0, 0, $ancho_mm, $alto_mm);
            }
        }

        $pdf->Output('I', $nombreArchivo);
    }

    protected function convertirPDFaImagenes(string $pdfPath): array
    {
        $imagenes = [];
        $im = new Imagick();
        $im->setResolution(150, 150);

        try {
            $im->readImage($pdfPath);
            $im->setImageFormat('png');

            foreach ($im as $i => $page) {
                $page->setImageFormat('png');
                $tmpPath = sys_get_temp_dir() . '/doc_' . uniqid() . "_$i.png";
                $page->writeImage($tmpPath);
                $imagenes[] = $tmpPath;
            }

            $im->clear();
            $im->destroy();

            return $imagenes;
        } catch (Exception $e) {
            error_log("Error al convertir PDF: " . $e->getMessage());
            return [];
        }
    }
}
