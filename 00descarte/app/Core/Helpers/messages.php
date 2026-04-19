<?php

function setFlashMessage(string $text, string $type = 'info'): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash_message'] = [
        'text' => $text,
        'type' => $type
    ];
}

function renderFlashMessage(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash_message'])) 
        if ($_SESSION['flash_message'] !=='') {
        // Render the flash message) {
            $msg = $_SESSION['flash_message'];
            echo "<div class='alert alert-{$msg['type']}' role='alert'>{$msg['text']}</div>";
            $_SESSION['flash_message'] = [
                'text' => '',
                'type' => ''
            ];
        }
        unset($_SESSION['flash_message']);
    }
/*
    if (!empty($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        echo "<script>alert('" . addslashes($msg['text']) . "');</script>";
        unset($_SESSION['flash_message']);
    }*/

function resetFlashMessage(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash_message'] = [
        'text' =>  '',
        'type' => ''
    ];
}    

