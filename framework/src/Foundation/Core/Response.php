<?php

namespace Foundation\Core;

class Response
{
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    public static function text(string $content, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: text/plain');
        echo $content;
        exit;
    }
}
