<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\DatosPersonales;
use App\Models\Matricula;
use App\Core\Session;
use App\Models\User;
use App\Models\Comision;
use App\Controllers\ComisionController;

// Ensure the User model exists in the App\Models namespace
use App\Core\Controller;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Gregwar\Captcha\PhraseBuilder;
use App\Core\Helpers\messages;
use App\Support\Sanitizer;


class AuthController extends Controller
{
    /**
     * Muestra el formulario de registro
     */
    public function logged(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['phrase']) && PhraseBuilder::comparePhrases($_SESSION['phrase'], $_POST['phrase'])) {
            $errors = ''; 
        } else {
            $errors =  "Captcha no válido!";
        }

        if ($errors <> '') {
            session_start();
            $_SESSION['errors'] = $errors;
            //$_SESSION['old']    = ['email' => $email];
            $this->redirect('/login');

            exit;
        }

        $email = Sanitizer::email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $phase2 = $_POST['phrase2'] ?? '';

        // Validar usuario 

        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            Session::flash('error', 'Credenciales inválidas.');
            Response::redirect('/login');
        }

        // Iniciar sesión y almacenar los datos del usuario
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Guardar en sesión un array con los datos del usuario
        $_SESSION['user'] = [
            'id'    => $user['id'],
            'email' => $email,
            'role'  => $user['role']
        ];

        // cargar los datos de la comisión activa.
        $phrase2 = trim($_POST['phrase2']) ?? '';
        if ($phrase2 <> '') {
            // Guardar los errores en la sesión (o pasarlos a la vista) y redirigir.
            Session::flash('error', 'Recargue el formulario y pruebe nuevamente.');
            $this->redirect('/login');
        }

        //Session::flash('', '');
        //resetFlashMessage();
        if (!empty($errors)) {
            session_start();
            $_SESSION['errors'] = $errors;
            //$_SESSION['old']    = ['email' => $email];
            Response::redirect('/login');
            //$this->redirect('/register');

            //header('Location: /auth/register');
            exit;
        }


        $errors = ''; 
        $_SESSION['errors'] = $errors;

        // Redireccionar según el rol del usuario
        if ($user['role'] === 'admin') {
            Response::redirect('/admin-dashboard');
        } else {
            Response::redirect('/user-dashboard');
        }

        

    }



    // procedurevision
    
    public function procedurevision(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['phrase']) && PhraseBuilder::comparePhrases($_SESSION['phrase'], $_POST['phrase'])) {
            $errors = ''; 
        } else {
            $errors =  "Captcha no válido!";
        }

        if ($errors <> '') {
            session_start();
            $_SESSION['errors'] = $errors;
            //$_SESSION['old']    = ['email' => $email];
            $this->redirect('/arevision');

            exit;
        }

        $phase2 = $_POST['phrase2'] ?? '';

        // Validar usuario 


        // Iniciar sesión y almacenar los datos del usuario
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Guardar en sesión un array con los datos del usuario


        $email = $_SESSION['user']['email'] ?? '';

        $phrase2 = trim($_POST['phrase2']) ?? '';

        if ($phrase2 <> '') {
            // Guardar los errores en la sesión (o pasarlos a la vista) y redirigir.
            Session::flash('error', 'Recargue el formulario y pruebe nuevamente.');
            $this->redirect('/arevision');
        }

// {{}} mandar el mail
        $locuser = $_SESSION['user']['id'] ?? '';

        if (Matricula::statusmatricula($locuser) != '') {
            Session::flash('error', 'No puede volver a solicitar la revisión en este momento.');
            $this->redirect('/user-dashboard');
        }else {
            
            $datos = Matricula::freezedata($locuser);

            $this->sendRevisionEmail($email);
            $this->NotifyRevisionEmail($email);
            Session::flash('Success', 'Revise su mail. Se recibió su solicitud.');
            Response::redirect('/user-dashboard');
        }
        

    }
    
    
    public function loginForm(): void
    {
        $this->view('auth/login', [
            'error' => $_GET['error'] ?? null
        ]);
    }

    public function piderevision(): void
    {
        $this->view('auth/pedirrevision', [
            'error' => $_GET['error'] ?? null
        ]);
    }
    

    public function registerForm(): void
    {
        $this->view('auth/register', [
            'error' => Session::flash('error', 'No se pudo completar la operación.') // En caso de mensajes flash de error
        ]);
    }

    public function requisitos(): void
    {
        $this->view('sitio/requisitos', [
            'error' => Session::flash('error', 'No se pudo completar la operación.') // En caso de mensajes flash de error
        ]);
    }

    public function institucional(): void
    {
        $this->view('sitio/comisiones', [
            'error' => Session::flash('error', 'No se pudo completar la operación.') // En caso de mensajes flash de error
        ]);
    }

    /**
     * Procesa el registro.
     */
    public function register(): void
    {
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
      
        if (isset($_SESSION['phrase']) && PhraseBuilder::comparePhrases($_SESSION['phrase'], $_POST['phrase'])) {
            $errors = ''; 
        } else {
            $errors =  "Captcha no válido!";
        }
        // The phrase can't be used twice
        unset($_SESSION['phrase']); 

        if(trim($_POST['verdura']) <> trim($_POST['frutita'])) {
            Session::flash('error', 'Las contraseñas no coinciden.');
            $this->redirect('/register');
        }
        $data = [
                'email'    => Sanitizer::email(trim($_POST['emilio'])),
                'password' => trim($_POST['frutita'])
                
        ];

        // Validar el CAPTCHA casero
        $variable01 = $_SESSION['jamm01'] ;
        //$variable02 = $_SESSION['jamm02'] ;
        $quest01 = (int)$_POST["vari_l"]; // debe ser $_SESSION['preverif']
        $answ00 = $_SESSION['preverif'];
        //$quest02 = $_POST[$$variable02] ?? ''; // debe ser $_SESSION['verif']

//        echo "quest01: $quest01<br>";
//        echo "preverif: ".$_SESSION['preverif']."<br>";
        //echo $quest01-$_SESSION['preverif']."<br>";
        
        if(!isset($_SESSION['preverif']))
            {
            Session::flash('error', 'Reinicie la operación!');
            $this->redirect('/register');

            }
        $zmierda = $quest01-$_SESSION['preverif'];
        if ( $zmierda != 0 ) {
        
            Session::flash('error', 'Respuesta incorrecta!');
            $this->redirect('/register');
        }

/*
        if ($quest01 != $_SESSION['preverif'] || $quest02 != $_SESSION['verif']) {
            Session::flash('error', 'Operación incorrecta!');
            $this->redirect('/register');
        }
*/
        // Definir las reglas de validación.
        $rules = [
            'email'    => 'required|email',
            'password' => 'required|min:6'
        ];
        $phrase2 = trim($_POST['phrase2']) ?? '';
        if ($phrase2 <> '') {
            // Guardar los errores en la sesión (o pasarlos a la vista) y redirigir.
            Session::flash('error', 'Recargue el formulario y pruebe nuevamente.');
            $this->redirect('/register');
        }
    
        $validator = new Validator($data, $rules);
        if ($validator->fails()) {
            // Guardar los errores en la sesión (o pasarlos a la vista) y redirigir.
            Session::flash('error', implode("<br>", array_merge(...array_values($validator->errors()))));
            $this->redirect('/register');
        }else{
            // Validar si el email ya está registrado
            $existingUser = User::findByEmail($data['email']);
            if ($existingUser) {
                Session::flash('error', 'El email ya está registrado.');
                $this->redirect('/register');
            }
        }


        if (!empty($errors)) {
            session_start();
            $_SESSION['errors'] = $errors;
            //$_SESSION['old']    = ['email' => $email];
            $this->redirect('/register');

            //header('Location: /auth/register');
            exit;
        }
    // Si la validación pasa, se continua con la lógica de registro:
    $activationToken = bin2hex(random_bytes(16));
    $passwordHash    = password_hash($data['password'], PASSWORD_DEFAULT);
    //$emilio = $data['email'];

     if (!$this->sendActivationEmail(trim(Sanitizer::email($_POST['emilio'])), $activationToken)) {
        Session::flash('error', 'No se pudo enviar el correo de activación.');
        $this->redirect('/register');
    }
   // ya no se envía el mail en forma automática, se deja para el admin
    if (!User::createUser( $data['email'], $passwordHash, $activationToken)) {
        Session::flash('error', 'No se pudo registrar el usuario. Intente nuevamente.');
        $this->redirect('/register');
    }


    Session::flash('error', 'Registro sujeto a revisión. Recibirá un mail de activación y luego podrá iniciar sesión.');
    $this->redirect('/login');


    }

    public function aceptarsolicitud(string $email): void
    {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if($_SESSION['user']['role'] != 'admin') {
            Session::flash('error', 'No tiene permisos para realizar esta acción.');
            $this->redirect('/login');
        }

        $aspirante = User::findByEmail($email);

        if (!$aspirante) {
            Session::flash('error', 'ID de usuario no proporcionado.');
            $this->redirect('/login');
            return;
        }
//enviar el correo de activacion
        $activationToken = $aspirante['activation_token'] ; 
        
        //$passwordHash    = $aspirante['password']; 

    if (!$this->sendActivationEmail($email, $activationToken)) {
        Session::flash('error', 'No se pudo enviar el correo de activación.');
        $this->redirect('/register');
    }


    }

    public function logout(): void
    {
        // Iniciar la sesión si no está iniciada.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Limpiar y destruir la sesión.
        session_unset();
        session_destroy();
        
        // O, si usas un helper de Session, podrías hacer:
        // Session::clear();
        
        // Redirige al formulario de login.
        $this->redirect('/login');
    }

    /**
     * Envía el correo de activación usando PHPMailer.
     */
    protected function sendActivationEmail(string $email, string $token): bool
    {
        // Creación del enlace de activación. Cambiá 'tu-dominio.com' por tu dominio real.
        $activationLink = "/activate/{$token}";

        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor
            $config = require $_SESSION['directoriobase'].'/config/settings.php';

            $mail->isSMTP();
            $mail->Host       = $config['MAIL_HOST']; // Cambiá por tu servidor SMTP
            $mail->SMTPAuth   = true;
            $mail->Username   = 'admisiones@coprobilp.org.ar';            //$config['MAIL_USERNAME'];
            $mail->Password   = 'S4lt4r4L@B4nc4'; // $config['MAIL_PASSWORD'];
            $mail->SMTPSecure = $config['MAIL_ENCRYPTION']; // o PHPMailer::ENCRYPTION_SMTPS
            $mail->Port       = $config['MAIL_PORT']; // Cambiá según tu configuración

            // Remitente y destinatario
            $mail->setFrom($mail->Username, $config['MAIL_NOREPLAY_LABEL']);
            $mail->addAddress($email);

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = $config['MAIL_SUBJ_PREFIX']. 'Activa tu cuenta';
            //Agrego el prefijo a la url de activacion
            $activationLink = $config['base_url'] . $activationLink;

            $mail->Body    = "
                <p>Hola!</p>
                <p>Este es un mail automático del sistema de matriculaciones de CoProBiLP.</p>
                <p>Por favor, no responda a este correo.</p>
                <p>--- Si usted quiere mayor información por favor escriba a secretaria@ ---</p>
                <p> </p>
                <p> </p>      

                <p>Gracias por registrarte. Por favor, haz clic en el siguiente enlace para activar tu cuenta:</p>
                <p><a href='{$activationLink}'>Activar Cuenta</a></p>
                <p>Si no registraste una cuenta, ignora este mensaje.</p>
            ";

            return $mail->send();

        } catch (Exception $e) {
            error_log("Error enviando correo de activación: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Activa la cuenta dado el token.
     */
    public function activateAccount(Request $request, array $params = []): void
    {
        $token = $params[0] ?? null;
        if (!$token) {
            Session::flash('error', 'Token no proporcionado.');
            $this->redirect('/login');
            return;
        }

        // Buscar el usuario por token
        $user = User::findByActivationToken($token);
        if (!$user) {
            Session::flash('error', 'Token inválido o expirado.');
            $this->redirect('/login');
        }

        // Activar la cuenta (actualiza el campo active a 1 y limpia el token)
        $activated = User::activate($user['id']);

        if ($activated) {
            $xxsql = 'INSERT INTO datospersonales (user_id) VALUES (' . $user['id'] .')';
            $xxsql2 = 'INSERT INTO matriculas (user_id) VALUES (' . $user['id'] .')';
            
            $uploadDir = $this->getUserUploadFolder($user['id']);
                // Get upload directory
                //$uploadDir = $_SESSION['directoriobase'] . '/storage/uploads/' . md5($_SESSION['user_id'] . require($_SESSION['directoriobase'].'/config/settings.php')['basellave']) . '/';
                
                // Create directory if it doesn't exist
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                    chmod($uploadDir, 0777);
                }
            DatosPersonales::CustomQry( $xxsql  );

            Matricula::CustomQry( $xxsql2);

            Session::flash('info', 'Cuenta activada correctamente. Ahora puede iniciar sesión.');
            //Session::flash('success', 'Cuenta activada correctamente. Ahora puede iniciar sesión.');
            $this->redirect('/user-dashboard');
        } else {
            Session::flash('error','Error de activación. Reintente.');

            //Session::flash('error', 'Error al activar la cuenta. Intente nuevamente.');
            $this->redirect('/user-dashboard');
        }
    }

 /** 1) Muestra el formulario para pedir email */
 public function forgotForm(): void
 {
     $this->view('auth/forgot-password', [
         'error'   => Session::flash('error', "Error!"),
         'message' => Session::flash('success', "Ok!"),
     ]);
 }

 /** 2) Procesa el envío de email con token */
 public function sendForgotPassword()
 {
     $email = trim($_POST['email'] ?? '');
     if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
         Session::flash('error', 'Email inválido');
         return Response::redirect('/password/forgot');
     }

     $user = User::findByEmail($email);
     if (!$user) {
         // para no revelar usuarios, igual enviamos “ok”
         Session::flash('success', 'Si el email existe, recibirás un enlace para resetear tu contraseña.');
         return Response::redirect('/login');
     }

     // Generar token + expiración (1 hora)
     $token   = bin2hex(random_bytes(16));
     $expires = date('Y-m-d H:i:s', time() + 3600);

     if (!User::setPasswordResetToken($email, $token, $expires)) {
         Session::flash('error', 'No se pudo generar el enlace; intenta más tarde.');
         return Response::redirect('/login');
     }

     // Enviar correo
     if (!$this->sendPasswordResetEmail($email, $token)) {
         Session::flash('error', 'Error al enviar el correo; intenta más tarde.');
         return Response::redirect('/login');
     }
     Session::flash('success', 'Revisa tu correo; allí encontrarás el enlace para cambiar tu contraseña.');
     //$this->GeneralEmail('soportesistema@coprobilp.org.ar', 'Se solicitó cambio de contraseña', "El usuario con email ". $email . " solicitó el cambio de su contraseña. ");

     Response::redirect('/login');
 }

 /** 3) Muestra el formulario para establecer la nueva contraseña */
 public function resetPasswordForm(Request $request, array $params = [])
 {
     $token = $params[0] ?? null;
     if (!$token) {
         Session::flash('error', 'Token no proporcionado.');
         return Response::redirect('/password/forgot');
     }
     
     $user = User::findByResetToken($token);
     if (!$user) {
         Session::flash('error', 'Enlace inválido o expirado.');
         return Response::redirect('/password/forgot');
     }

     $this->view('auth/reset-password', [
         'token'   => $token,
         'error'   => Session::flash('error', 'No se pudo completar.'),
         'message' => Session::flash('success', 'Realizado!'),
     ]);
 }

 /** 4) Procesa el cambio de contraseña */
 public function resetPassword()
 {
     $token     = $_POST['token']       ?? '';
     $password  = trim($_POST['password'] ?? '');
     $repPass   = trim($_POST['rep_password'] ?? '');

     if ($password === '' || $password !== $repPass) {
         Session::flash('error', 'Las contraseñas no coinciden o están vacías.');
         return Response::redirect("/password/reset/{$token}");
     }

     $user = User::findByResetToken($token);
     if (!$user) {
         Session::flash('error', 'Enlace inválido o expirado.');
         return Response::redirect('/password/forgot');
     }

     // Actualizar contraseña
     $hash = password_hash($password, PASSWORD_DEFAULT);
     if (!User::updatePassword($user['id'], $hash) ||
         !User::clearPasswordResetToken($user['id']))
     {
         Session::flash('error', 'No se pudo actualizar la contraseña. Intenta más tarde.');
         return Response::redirect("/password/reset/{$token}");
     }

     Session::flash('success', 'Contraseña actualizada. Ahora puedes iniciar sesión.');
     Response::redirect('/login');
 }

 /** Helper: enviar correo de reset */
 protected function sendPasswordResetEmail(string $email, string $token): bool
 {
     $config = require $_SESSION['directoriobase'].'/config/settings.php';
     $link   = $config['base_url'] . "/password/reset/{$token}";

     $mail = new PHPMailer(true);
     try {
         $mail->isSMTP();
         $mail->Host       = $config['MAIL_HOST'];
         $mail->SMTPAuth   = true;
            $mail->Username   = 'admisiones@coprobilp.org.ar';            //$config['MAIL_USERNAME'];
            $mail->Password   = 'S4lt4r4L@B4nc4'; // $config['MAIL_PASSWORD'];
         $mail->SMTPSecure = $config['MAIL_ENCRYPTION'];
         $mail->Port       = $config['MAIL_PORT'];

         $mail->setFrom($mail->Username, $config['MAIL_NOREPLAY_LABEL']);
         $mail->addAddress($email);

         $mail->isHTML(true);
         $mail->Subject = 'Recuperar contraseña';
         $mail->Body    = "
                <p>Hola!</p>
                <p>Este es un mail automático del sistema de matriculaciones de CoProBiLP.</p>
                <p>Por favor, no responda a este correo.</p>
                <p>--- Si usted quiere mayor información por favor escriba a secretaria@  ---</p>
                <p> </p>
                <p> </p>      
             <p>Has solicitado restablecer tu contraseña.</p>
             <p>Haz clic en este enlace (válido 1 hora):</p>
             <p><a href=\"{$link}\">Restablecer contraseña</a></p>
             <p>Si no lo solicitaste, ignora este correo.</p>
         ";

         return $mail->send();
     } catch (Exception $e) {
         error_log("Error enviando reset-password: {$mail->ErrorInfo}");
         return false;
     }
 }

    protected function sendRevisionEmail(string $email): bool
    {
        // Creación del enlace de activación. Cambiá 'tu-dominio.com' por tu dominio real.

        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor
            $config = require $_SESSION['directoriobase'].'/config/settings.php';

            $mail->isSMTP();
            $mail->Host       = $config['MAIL_HOST']; // Cambiá por tu servidor SMTP
            $mail->SMTPAuth   = true;
            $mail->Username   = 'admisiones@coprobilp.org.ar';            //$config['MAIL_USERNAME'];
            $mail->Password   = 'S4lt4r4L@B4nc4'; // $config['MAIL_PASSWORD'];
            $mail->SMTPSecure = $config['MAIL_ENCRYPTION']; // o PHPMailer::ENCRYPTION_SMTPS
            $mail->Port       = $config['MAIL_PORT']; // Cambiá según tu configuración

            // Remitente y destinatario
            $mail->setFrom($mail->Username, $config['MAIL_NOREPLAY_LABEL']);
            $mail->addAddress('secretaria@coprobilp.org.ar');

            // Contenido del correo
            $mail->isHTML(true);

            $solicitante = User::findByEmail($email);
            $locuser = $solicitante['id'] ;
            $datos = DatosPersonales::findByUserId($locuser);
            $quien = ucwords($datos['apellido'] . ', ' . $datos['nombre']) ;
            $mail->Subject = $quien. ' - Solicita revisión de documentación';

            //Agrego el prefijo a la url de activacion

            $mail->Body    = "
                <p>Hola!</p>
                <p>Este es un mail automático del sistema de matriculaciones de CoProBiLP.</p>
                <p>Por favor, no responda a este correo.</p>
                <p>--- Si usted quiere mayor información por favor escriba a secretaria@ ---</p>
                <p> </p>
                <p> </p>      
                <p>El profesional acaba de solicitar la revisión de su documentación.</p>
                <p>Se solicita que se le informe el resultado de dicho proceso</p>
                <p>La cuenta de correo que informó es {$email} .</p>
                <p>Por favor, luego del procedimiento solicitado, realice la devolución correspondiente.</p>";

            return $mail->send();

        } catch (Exception $e) {
            error_log("Error enviando correo: {$mail->ErrorInfo}");
            return false;
        }

    }
    protected function NotifyRevisionEmail(string $email): bool
    {
        // Creación del enlace de activación. Cambiá 'tu-dominio.com' por tu dominio real.

        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor
            $config = require $_SESSION['directoriobase'].'/config/settings.php';

            $mail->isSMTP();
            $mail->Host       = $config['MAIL_HOST']; // Cambiá por tu servidor SMTP
            $mail->SMTPAuth   = true;
            $mail->Username   = 'admisiones@coprobilp.org.ar';            //$config['MAIL_USERNAME'];
            $mail->Password   = 'S4lt4r4L@B4nc4'; // $config['MAIL_PASSWORD'];
            $mail->SMTPSecure = $config['MAIL_ENCRYPTION']; // o PHPMailer::ENCRYPTION_SMTPS
            $mail->Port       = $config['MAIL_PORT']; // Cambiá según tu configuración

            // Remitente y destinatario
            $mail->setFrom($mail->Username, $config['MAIL_NOREPLAY_LABEL']);
            $mail->addAddress($email);

            // Contenido del correo
            $mail->isHTML(true);

            $solicitante = User::findByEmail($email);
            $locuser = $solicitante['id'] ;
            $datos = DatosPersonales::findByUserId($locuser);
            $quien = ucwords($datos['apellido'] . ', ' . $datos['nombre']) ;
            $mail->Subject = 'Solicitó revisión de documentación';

            //Agrego el prefijo a la url de activacion

            $mail->Body    = "
                <p>Hola!</p>
                <p>Este es un mail automático del sistema de matriculaciones de CoProBiLP.</p>
                <p>Por favor, no responda a este correo.</p>
                <p>--- Si usted quiere mayor información por favor escriba a secretaria@coprobilp.org.ar ---</p>
                <p> </p>
                <p> </p>      
                <p>Estimado $quien </p>

                <p>Se procederá a la revisión de la documentación enviada. </p>
		        <p>Posteriormente se le comunicara el turno para la presentación de la documentación física.</p>
                <p>Cordial saludo.</p>";
                

            return $mail->send();

        } catch (Exception $e) {
            error_log("Error enviando correo: {$mail->ErrorInfo}");
            return false;
        }

    }

    static public function GeneralEmail(string $email, string $subject, string $body): bool
    {
        // Creación del enlace de activación. Cambiá 'tu-dominio.com' por tu dominio real.

        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor
            $config = require $_SESSION['directoriobase'].'/config/settings.php';

            $mail->isSMTP();
            $mail->Host       = $config['MAIL_HOST']; // Cambiá por tu servidor SMTP
            $mail->SMTPAuth   = true;
            $mail->Username   = 'admisiones@coprobilp.org.ar';            //$config['MAIL_USERNAME'];
            $mail->Password   = 'S4lt4r4L@B4nc4'; // $config['MAIL_PASSWORD'];
            $mail->SMTPSecure = $config['MAIL_ENCRYPTION']; // o PHPMailer::ENCRYPTION_SMTPS
            $mail->Port       = $config['MAIL_PORT']; // Cambiá según tu configuración

            // Remitente y destinatario
            $mail->setFrom($mail->Username, $config['MAIL_NOREPLAY_LABEL']);
            $mail->addAddress($email);

            // Contenido del correo
            $mail->isHTML(true);

            $solicitante = User::findByEmail($email);
            $locuser = $solicitante['id'] ;
            $datos = DatosPersonales::findByUserId($locuser);
            $quien = ucwords($datos['apellido'] . ', ' . $datos['nombre']) ;
            $mail->Subject = $subject;

            //Agrego el prefijo a la url de activacion

            $mail->Body    = 
                "<p>Hola!</p>
                <p>Este es un mail automático del sistema de matriculaciones de CoProBiLP.</p>
                <p>Por favor, no responda a este correo.</p>
                <p>--- Si usted quiere mayor información por favor escriba a secretaria@ ---</p>
                <p> </p>
                <p> </p>";
            $mail->Body    .= $body;
           /* "
                <p>Estimado $quien </p>
                <p>Se procederá a la revisión de la documentación enviada. </p>
		        <p>Posteriormente se le comunicara el turno para la presentación de la documentación física.</p>
                <p>Cordial saludo.</p>";
            */    

            return $mail->send();

        } catch (Exception $e) {
            error_log("Error enviando correo: {$mail->ErrorInfo}");
            return false;
        }

    }



    //Se procederá a la revisión de la documentación enviada. Posteriormente se le comunicara el turno para la presentación de la documentación física. 


 public function showLanding(): void
    {
        $this->view('dashboard/showlanding', [
            'user' => $_SESSION['user'] ?? null,
            'error' => Session::flash('error'),
            'success' => Session::flash('success'),
            'info' => Session::flash('info'),
            'warning' => Session::flash('warning')
        ]);
    }

    public function showAdminPanel(): void
    {
        $this->view('dashboard/admin-dashboard', [
            'user' => $_SESSION['user'] ?? null,
            'error' => Session::flash('error'),
            'success' => Session::flash('success'),
            'info' => Session::flash('info'),
            'warning' => Session::flash('warning')
        ]);
    }

    public function showAdminDashboard(): void
    {
     
        // [[]] terminar - revisar
        
        // 1) Verificar si el usuario está logueado y obtener su ID
        $user = Session::get('user');
        if (!$user || !isset($user['id'])) {
            Session::flash('error', 'Debe iniciar sesión para acceder al panel de control');
            Response::redirect('/login');
            return;
        }
        if($user['role'] != 'admin') {
            Session::flash('error', 'No tiene permisos para acceder a esta sección.');
            Response::redirect('/login');
            return;
        }

        $userId = $user['id'];

        // 2) Cargar configuración de landing
        //  /var/www/copro3/Matricu22k/views/dashboard/userlandingpage.php
        $cfgdash     = require $_SESSION['directoriobase'] . '/views/dashboard/adminlandingpage.php';

        
        $landingCfg  = $cfgdash['landing']    ?? [];
        $cfgHeader   = $landingCfg['header']  ?? [];
        $buttons     = $landingCfg['botones'] ?? [];

        // /var/www/copro3/Matricu22k/config/cruds/defaults/landingstyle.php
        $cfgstyle   = require $_SESSION['directoriobase'] . '/config/cruds/defaults/landingstyle.php';

        $landinCSS  = $cfgstyle['styles']    ?? [];
        // 3) Renderizar con Plates
        $this->view('dashboard/mylandingpage', [
            'cfgHeader'  => $cfgHeader,
            'estilos'    => $landinCSS,
            'buttons'    => $buttons,
            'userId'     => $userId,
        ]);
    }

    public function InscripcionesDashboard(): void
    {
     
        // [[]] terminar - revisar
        
        // 1) Verificar si el usuario está logueado y obtener su ID
        $user = Session::get('user');
        if (!$user || !isset($user['id'])) {
            Session::flash('error', 'Debe iniciar sesión para acceder al panel de control');
            Response::redirect('/login');
            return;
        }
        if($user['role'] != 'admin') {
            Session::flash('error', 'No tiene permisos para acceder a esta sección.');
            Response::redirect('/login');
            return;
        }

        $userId = $user['id'];

        // 2) Cargar configuración de landing
        //  /var/www/copro3/Matricu22k/views/dashboard/userlandingpage.php
        $cfgdash     = require $_SESSION['directoriobase'] . '/views/dashboard/ctrlinscripciones.php';

        
        $landingCfg  = $cfgdash['landing']    ?? [];
        $cfgHeader   = $landingCfg['header']  ?? [];
        $buttons     = $landingCfg['botones'] ?? [];

        // /var/www/copro3/Matricu22k/config/cruds/defaults/landingstyle.php
        $cfgstyle   = require $_SESSION['directoriobase'] . '/config/cruds/defaults/landingstyle.php';

        $landinCSS  = $cfgstyle['styles']    ?? [];
        // 3) Renderizar con Plates
        $this->view('dashboard/mylandingpage', [
            'cfgHeader'  => $cfgHeader,
            'estilos'    => $landinCSS,
            'buttons'    => $buttons,
            'userId'     => $userId,
        ]);
    }


    // src/Controllers/AuthController.php

    public function showUserDashboard(): void
    {
        // 1) Verificar si el usuario está logueado y obtener su ID
        $user = Session::get('user');
        if (!$user || !isset($user['id'])) {
            Session::flash('error', 'Debe iniciar sesión para acceder al panel de control');
            Response::redirect('/login');
            return;
        }


        $userId = $user['id'];

        // 2) Cargar configuración de landing
        //  /var/www/copro3/Matricu22k/views/dashboard/userlandingpage.php
        $statusmat = Matricula::getMatriculaByUserId($userId);
        $tienematricula = ($statusmat['matriculaasignada'] <>  null) and ($statusmat['comisionotorgante'] <> null) 
            and ($statusmat['baja'] == null);
        if($tienematricula) {
            $cfgdash     = require $_SESSION['directoriobase'] . '/views/dashboard/userlandingpage.php';
        } else {
            $cfgdash     = require $_SESSION['directoriobase'] . '/views/dashboard/userlandingpage2.php';
        }

        
        $landingCfg  = $cfgdash['landing']    ?? [];
        $cfgHeader   = $landingCfg['header']  ?? [];

        
        $buttons = $landingCfg['botones'] ?? [];
        // /var/www/copro3/Matricu22k/config/cruds/defaults/landingstyle.php
        $cfgstyle   = require $_SESSION['directoriobase'] . '/config/cruds/defaults/landingstyle.php';

        $landinCSS  = $cfgstyle['styles']    ?? [];
        // 3) Renderizar con Plates
        $this->view('dashboard/mylandingpage', [
            'cfgHeader'  => $cfgHeader,
            'estilos'    => $landinCSS,
            'buttons'    => $buttons,
            'userId'     => $userId,
        ]);
    }

        public function showMenuMatriculas(): void
    {
        // 1) Verificar si el usuario está logueado y obtener su ID
        $user = Session::get('user');
        if (!$user || !isset($user['id'])) {
            Session::flash('error', 'Debe iniciar sesión para acceder al panel de control');
            Response::redirect('/login');
            return;
        }

        $userId = $user['id'];

        // 2) Cargar configuración de landing
        if ($user['role'] == 'admin' ) {
            $cfgdash     = require $_SESSION['directoriobase'] . '/views/dashboard/rematriculalandingpage.php';
        } else {
            $cfgdash     = require $_SESSION['directoriobase'] . '/views/dashboard/matriculalandingpage.php';
        }
        //$cfgdash     = require $_SESSION['directoriobase'] . '/views/dashboard/matriculalandingpage.php';

        $landingCfg  = $cfgdash['landing']    ?? [];
        $cfgHeader   = $landingCfg['header']  ?? [];
        $buttons     = $landingCfg['botones'] ?? [];

        $cfgstyle   = require $_SESSION['directoriobase'] . '/config/cruds/defaults/landingstyle.php';
        $landinCSS  = $cfgstyle['styles']    ?? [];
        // 3) Renderizar con Plates
        $this->view('dashboard/mylandingpage', [
            'cfgHeader'  => $cfgHeader,
            'estilos'    => $landinCSS,
            'buttons'    => $buttons,
            'userId'     => $userId,
        ]);
    }


    //showMenuCtrlMatric
        public function showMenuCtrlMatric(): void
    {
        // 1) Verificar si el usuario está logueado y obtener su ID
        $user = Session::get('user');
        if (!$user || !isset($user['id'])) {
            Session::flash('error', 'Debe iniciar sesión para acceder al panel de control');
            Response::redirect('/login');
            return;
        }

        $userId = $user['id'];

        // 2) Cargar configuración de landing
        if(Comision::espresidente($userId) || Comision::esvicepresidente($userId) ) {
            $cfgdash     = require $_SESSION['directoriobase'] . '/views/dashboard/ctrlinscrip2presi.php';
        } else {
            $cfgdash     = require $_SESSION['directoriobase'] . '/views/dashboard/ctrlinscripciones.php';
        }
        //$cfgdash     = require $_SESSION['directoriobase'] . '/views/dashboard/ctrlinscripciones.php';

        $landingCfg  = $cfgdash['landing']    ?? [];
        $cfgHeader   = $landingCfg['header']  ?? [];
        $buttons     = $landingCfg['botones'] ?? [];

        $cfgstyle   = require $_SESSION['directoriobase'] . '/config/cruds/defaults/landingstyle.php';
        $landinCSS  = $cfgstyle['styles']    ?? [];
        // 3) Renderizar con Plates
        $this->view('dashboard/mylandingpage', [
            'cfgHeader'  => $cfgHeader,
            'estilos'    => $landinCSS,
            'buttons'    => $buttons,
            'userId'     => $userId,
        ]);
    }

        public function showMenuCtrlDocu(): void
    {
        // 1) Verificar si el usuario está logueado y obtener su ID
        $user = Session::get('user');
        if (!$user || !isset($user['id'])) {
            Session::flash('error', 'Debe iniciar sesión para acceder al panel de control');
            Response::redirect('/login');
            return;
        }

        $userId = $user['id'];

        // 2) Cargar configuración de landing
        $cfgdash     = require $_SESSION['directoriobase'] . '/views/dashboard/ctrldocumentos.php';

        $landingCfg  = $cfgdash['landing']    ?? [];
        $cfgHeader   = $landingCfg['header']  ?? [];
        $buttons     = $landingCfg['botones'] ?? [];

        $cfgstyle   = require $_SESSION['directoriobase'] . '/config/cruds/defaults/landingstyle.php';
        $landinCSS  = $cfgstyle['styles']    ?? [];
        // 3) Renderizar con Plates
        $this->view('dashboard/mylandingpage', [
            'cfgHeader'  => $cfgHeader,
            'estilos'    => $landinCSS,
            'buttons'    => $buttons,
            'userId'     => $userId,
        ]);
    }



    public function showDescargas(): void
    {
        // 1) Verificar si el usuario está logueado y obtener su ID
        $user = Session::get('user');
        if (!$user || !isset($user['id'])) {
            Session::flash('error', 'Debe iniciar sesión para acceder al panel de control');
            Response::redirect('/login');
            return;
        }

        $userId = $user['id'];

        // 2) Cargar configuración de landing
        $cfgdash     = require $_SESSION['directoriobase'] . '/views/dashboard/otrosrecursos.php';

        $landingCfg  = $cfgdash['landing']    ?? [];
        $cfgHeader   = $landingCfg['header']  ?? [];
        $buttons     = $landingCfg['botones'] ?? [];

        $cfgstyle   = require $_SESSION['directoriobase'] . '/config/cruds/defaults/landingstyle.php';
        $landinCSS  = $cfgstyle['styles']    ?? [];
        // 3) Renderizar con Plates
        $this->view('dashboard/mylandingpage', [
            'cfgHeader'  => $cfgHeader,
            'estilos'    => $landinCSS,
            'buttons'    => $buttons,
            'userId'     => $userId,
        ]);
    }


}
