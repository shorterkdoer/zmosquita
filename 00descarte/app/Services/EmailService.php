<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * EmailService - Handles all email sending functionality
 *
 * Extracted from AuthController to centralize email logic
 */
class EmailService
{
    protected array $config;

    public function __construct()
    {
        $configPath = $_SESSION['directoriobase'] ?? '/var/www/zmosquita';
        $this->config = require $configPath . '/config/settings.php';
    }

    /**
     * Send activation email to newly registered user
     */
    public function sendActivation(string $email, string $token): bool
    {
        $link = $this->config['base_url'] . "/activate/{$token}";

        $body = "
            <p>Estimado usuario,</p>
            <p>Gracias por registrarse en el sistema de matriculaciones de CoProBiLP.</p>
            <p>Para activar su cuenta, haga clic en el siguiente enlace:</p>
            <p><a href='{$link}'>Activar Cuenta</a></p>
            <p>Si no registró una cuenta en nuestro sistema, puede ignorar este mensaje.</p>
        ";

        return $this->send($email, 'Activa tu cuenta', $body);
    }

    /**
     * Send password reset email
     */
    public function sendPasswordReset(string $email, string $token): bool
    {
        $link = $this->config['base_url'] . "/password/reset/{$token}";

        $body = "
            <p>Has solicitado restablecer tu contraseña.</p>
            <p>Haz clic en el siguiente enlace (válido por 1 hora):</p>
            <p><a href='{$link}'>Restablecer contraseña</a></p>
            <p>Si no lo solicitaste, ignora este correo.</p>
        ";

        return $this->send($email, 'Recuperar contraseña', $body);
    }

    /**
     * Send notification when user requests revision
     */
    public function sendRevisionNotification(string $email, string $nombre): bool
    {
        $body = "
            <p>Estimado/a {$nombre},</p>
            <p>Se procederá a la revisión de la documentación enviada.</p>
            <p>Posteriormente se le comunicará el turno para la presentación física.</p>
            <p>Este es un mensaje automático. Por favor, no responda este correo.</p>
        ";

        return $this->send($email, 'Solicitó revisión de documentación', $body);
    }

    /**
     * Send revision notification to admin
     */
    public function notifyAdminRevision(string $email, string $nombre, string $userEmail): bool
    {
        $body = "
            <p>Estimado administrador,</p>
            <p>El usuario {$nombre} ({$userEmail}) ha solicitado la revisión de su documentación.</p>
            <p>Por favor, revise la documentación en el panel de administración.</p>
        ";

        return $this->send($email, 'Nueva solicitud de revisión', $body);
    }

    /**
     * Send generic email with custom subject and body
     */
    public function sendGeneric(string $to, string $subject, string $body): bool
    {
        return $this->send($to, $subject, $body);
    }

    /**
     * Send generic email
     */
    protected function send(string $to, string $subject, string $body): bool
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $this->config['MAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['MAIL_USERNAME'];
            $mail->Password = $this->config['MAIL_PASSWORD'];
            $mail->SMTPSecure = $this->config['MAIL_ENCRYPTION'];
            $mail->Port = $this->config['MAIL_PORT'];

            $mail->setFrom(
                $this->config['MAIL_FROM_ADDRESS'],
                $this->config['MAIL_FROM_NAME']
            );
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $this->config['MAIL_SUBJ_PREFIX'] . ' - ' . $subject;
            $mail->Body = $this->wrapBody($body);

            return $mail->send();
        } catch (Exception $e) {
            error_log("Email error: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Wrap email body in standard template
     */
    protected function wrapBody(string $content): string
    {
        return "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <p style='color: #666; font-size: 12px;'>Este es un mail automático del sistema de matriculaciones de CoProBiLP.</p>
                <p style='color: #666; font-size: 12px;'>Por favor, no responda a este correo.</p>
                <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                {$content}
                <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                <p style='color: #999; font-size: 11px;'>CoProBiLP - Sistema de Matriculaciones</p>
            </div>
        ";
    }
}
