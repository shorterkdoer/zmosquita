<?php
// src/Controllers/AuthWhatsappController.php

namespace App\Controllers;
use App\Models\User;
use App\Core\Controller;
use App\Core\Auth;


use GuzzleHttp\Client;

class AuthWhatsappController {
    protected $agentUrl = 'http://localhost:3001';
/*
    public function showQr() {
        // Llama al servicio Node para obtener QR
        $client = new Client();
        $resp = $client->get($this->agentUrl . '/auth/qr');
        $body = json_decode($resp->getBody(), true);
        echo $this->view('wa_login', ['qrData' => $body['qr']]);
    }

    public function statusAjax() {
        $client = new Client();
        $resp = $client->get($this->agentUrl . '/auth/status');
        header('Content-Type: application/json');
        echo $resp->getBody();
    }

    public function callback() {
        $client = new Client();
        $resp = $client->get($this->agentUrl . '/auth/user');
        $data = json_decode($resp->getBody(), true);
        $jid  = $data['jid']; // ej. "5491122233344@s.whatsapp.net"
        $phone = explode('@', $jid)[0];

        // Aquí buscas/creas usuario en tu BD
        $user = User::where('whatsapp', $phone)->first();
        if (!$user) {
            $user = User::create([
                'name'     => 'WA User '.$phone,
                'whatsapp' => $phone,
                // otros campos...
            ]);
        }
        // Iniciar sesión convencional
        Auth::login($user);
        header('Location: /dashboard');
    }
*/
}
