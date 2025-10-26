<?php
include_once '/config/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class SendValidationEmail
{

    private static $url = "http://localhost/validator/validate";

    public static function sendValidationEmail($userEmail,  $userName, $token)
    {
        $mail = new PHPMailer(true);
        $config = parse_ini_file("config/config.ini", true);

        $validationLink = self::$url . "?usuario=" . urlencode($userName) . "&token=" . urlencode($token);

        try {

            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['phpmailer']['email'];
            $mail->Password   = $config['phpmailer']['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;


            $mail->setFrom($config['phpmailer']['email'], $config['phpmailer']['username']);
            $mail->addAddress($userEmail, $userName);

            $mail->isHTML(true);
            $mail->Subject =    'Activa tu cuenta en Preguntados';

            $mail->Body    =    "<h2>¡Hola, $userName!</h2>
                                <p>Gracias por registrarte. Para completar el proceso, por favor haz clic en el siguiente enlace para activar tu cuenta:</p>
                                <p><a href='$validationLink' style='padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Activar mi cuenta</a></p>
                                <p>Si el botón no funciona, copia y pega la siguiente URL en tu navegador:</p>
                                <p>$validationLink</p>";
            $mail->AltBody = "Hola, $userName. Para activar tu cuenta, copia y pega este enlace en tu navegador: $validationLink";

            $mail->send();
        } catch (Exception $e) {
            error_log("El mensaje no pudo ser enviado. Mailer Error: {$mail->ErrorInfo}");
        }
    }
}
