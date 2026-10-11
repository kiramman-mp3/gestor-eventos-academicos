<?php
require_once 'sql/conexion.php';
require_once __DIR__ . '/libs/PHPMailer/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function generarCodigoVerificacion($longitud = 4)
{
  return str_pad(random_int(0, 9999), $longitud, '0', STR_PAD_LEFT);
}

function enviarCodigoCorreo($correo, $codigo)
{
  $mail = new PHPMailer(true);
  try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = '907johan@gmail.com';
    $mail->Password = 'mrwn xjrx gcfp ethz'; // Cambiar por variable de entorno en producción
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('907johan@gmail.com', 'Sistema FISEI');
    $mail->addAddress($correo);
    $mail->isHTML(true);
    $mail->Subject = 'Código de verificación de cuenta';

    $mail->Body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; background: #f4f4f4; padding: 20px; border-radius: 10px; border: 1px solid #ddd;'>
      <div style='background-color: #B02A37; padding: 10px 20px; border-radius: 8px 8px 0 0; color: #fff; text-align: center;'>
        <h2 style='margin: 0;'>Verificación de Cuenta</h2>
      </div>
      <div style='padding: 20px; background-color: #fff; border-radius: 0 0 8px 8px;'>
        <p>Hola,</p>
        <p>Gracias por registrarte. Tu código de verificación es:</p>
        <div style='font-size: 32px; font-weight: bold; color: #B02A37; text-align: center; margin: 20px 0;'>$codigo</div>
        <p>Este código expira pronto. Si no solicitaste esta cuenta, ignora este mensaje.</p>
        <p style='margin-top: 30px;'>Atentamente,<br><strong>Equipo FISEI</strong></p>
      </div>
    </div>";

    $mail->send();
    return true;
  } catch (Exception $e) {
    return false;
  }
}
