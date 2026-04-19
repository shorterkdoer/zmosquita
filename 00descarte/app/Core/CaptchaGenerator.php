<?php
class CaptchaGenerator
{
    public int $length = 8;
    public int $width = 200;
    public int $height = 70;
    public int $angle = 0;
    public int $linesFront = 5;
    public int $linesBack = 3;

    private string $phrase = '';
    private string $font;

    public function __construct()
    {
        // Fuente predeterminada o pasada por parámetro
        $this->font = $font ?? __DIR__ . '/arial.ttf';
        if (!file_exists($this->font)) {
            throw new Exception("No se encontró el archivo de fuente: " . $this->font);
        }
    }

    public function getPhrase(): string
    {
        return $this->phrase;
    }

    public function generate(): void
    {
        session_start();
        $this->phrase = $this->generatePseudoWord($this->length);
        $_SESSION['captcha'] = $this->phrase;

        $image = imagecreatetruecolor($this->width, $this->height);
        $bgColor = imagecolorallocate($image, 240, 240, 240);
        $textColor = imagecolorallocate($image, 20, 20, 20);
        $lineColor = imagecolorallocate($image, 100, 100, 100);

        imagefilledrectangle($image, 0, 0, $this->width, $this->height, $bgColor);

        // Líneas detrás
        for ($i = 0; $i < $this->linesBack; $i++) {
            imageline($image, rand(0, $this->width), rand(0, $this->height), rand(0, $this->width), rand(0, $this->height), $lineColor);
        }

        // Texto
        $fontSize = (int)($this->height * 0.6);
        $posX = rand(10, 20);
        $posY = (int)($this->height * 0.7);
        $angle = $this->angle ?: rand(-15, 15);
        imagettftext($image, $fontSize, $angle, $posX, $posY, $textColor, $this->font, $this->phrase);

        // Líneas delante
        for ($i = 0; $i < $this->linesFront; $i++) {
            imageline($image, rand(0, $this->width), rand(0, $this->height), rand(0, $this->width), rand(0, $this->height), $lineColor);
        }

        // Salida
        header('Content-Type: image/png');
        imagepng($image);
        imagedestroy($image);
    }

    private function generatePseudoWord(int $length): string
    {
        $consonants = 'bcdfghjklmnpqrstvwxyz';
        $vowels = 'aeiou';
        $word = '';
        for ($i = 0; $i < $length / 2; $i++) {
            $word .= $consonants[random_int(0, strlen($consonants)-1)];
            $word .= $vowels[random_int(0, strlen($vowels)-1)];
        }
        return substr($word, 0, $length);
    }
}
