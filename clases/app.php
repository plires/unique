<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// NOTA: El autoloader ya se carga desde config.inc.php
// No es necesario cargarlo aquí porque todos los archivos que usan esta clase
// cargan primero config.inc.php

class App
{

  public function sendEmail($destinatario, $template, $post)
  {

    switch ($destinatario) {
      case 'Cliente':
        $emailDestino = EMAIL_RECIPIENT;
        $emailBCC = EMAIL_BCC;

        if (isset($post['name'])) {
          $nameShow = $post['name'];
          $emailAddReplyTo = $post['email'];
        } else {
          $nameShow = $post['email'];
          $emailAddReplyTo = $post['email'];
        }
        $emailShow = EMAIL_SENDER;  // Mi cuenta de correo
        break;

      case 'Usuario':
        $emailDestino = $post['email'];
        $nameShow = NAME_SENDER_SHOW;
        $emailShow = EMAIL_RECIPIENT;  // Mi cuenta de correo
        $emailAddReplyTo = EMAIL_SENDER_SHOW;
        $emailBCC = '';
        break;
    }

    if ($_SESSION['lang'] === 'es') {
      $path = '';
    } else {
      $path = '../';
    }

    switch ($template) {

      case 'Contacto Cliente':

        include($path . 'includes/emails/contacts/template-envio-cliente.inc.php'); // Cargo el contenido del email a enviar al cliente.
        $subject = 'Nueva consulta web.';
        break;

      case 'Contacto Usuario':
        include($path . 'includes/emails/contacts/template-envio-usuario.inc.php'); // Cargo el contenido del email a enviar al usuario.
        $subject = 'Gracias por tu contacto.';
        break;

      case 'Talento Cliente':
        include($path . 'includes/emails/talent/template-envio-cliente.inc.php'); // Cargo el contenido del email a enviar al cliente.
        $subject = 'Nueva Consulta Formulario Busqueda de Talento.';
        break;

      case 'Talento Usuario':
        include($path . 'includes/emails/talent/template-envio-usuario.inc.php'); // Cargo el contenido del email a enviar al usuario.
        $subject = 'Gracias por tu contacto.';
        break;

      case 'Consulta Cliente':

        if ($_FILES['cv']['size'] != 0) {
          $this->uploadCV($_FILES['cv']);
          include($path . 'includes/emails/consults/template-envio-cliente-cv.inc.php'); // Cargo el contenido del email a enviar al cliente con la opcion de descarga del CV
        } else {
          include($path . 'includes/emails/consults/template-envio-cliente.inc.php'); // Cargo el contenido del email a enviar al cliente SIN la opcion de descarga del CV
        }

        $subject = 'Nueva Consulta Formulario Busqueda de Trabajo.';
        break;

      case 'Consulta Usuario':
        include($path . 'includes/emails/consults/template-envio-usuario.inc.php'); // Cargo el contenido del email a enviar al usuario.
        $subject = 'Gracias por tu contacto.';
        break;
    }

    // Instantiation and passing `true` enables exceptions
    $mail = new PHPMailer(true);
    $send = false;

    try {
      //Server settings
      // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
      // $mail->SMTPDebug = 2; //Alternative to above constant

      $mail->isSMTP();                                    // Send using SMTP
      $mail->Host       = 'localhost';                    // Esto tiene que estar asi por GoDaddy
      $mail->SMTPAuth   = false;                          // Esto tiene que estar asi por GoDaddy
      $mail->SMTPAutoTLS = false;                         // Esto tiene que estar asi por GoDaddy
      // $mail->Username   = EMAIL_SENDER;                  // SMTP username
      // $mail->Password = EMAIL_PASS;                               // SMTP password
      // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
      $mail->Port       = EMAIL_PORT;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
      $mail->SMTPOptions = array(
        'ssl' => array(
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true
        )
      );

      // Recipientes
      $mail->setFrom($emailShow, $nameShow);
      $mail->AddAddress($emailDestino); // Esta es la dirección a donde enviamos los datos del formulario
      $mail->AddReplyTo($emailAddReplyTo); // Esto es para que al recibir el correo y poner Responder, lo haga a la cuenta del visitante. 

      // Content
      $mail->isHTML(true);                                  // Set email format to HTML
      $mail->Subject = $subject; // Este es el asunto del email.
      // $mensajeHtml = nl2br($body);
      $mail->Body = $body; // Texto del email en formato HTML
      // FIN - VALORES A MODIFICAR //

      if ($emailBCC != '') { // si no esta vacio el campo BCC
        $mail->addBCC($emailBCC, $subject); // Copia del email
      }
      $mail->AltBody = 'Consulta recibida';
      $mail->CharSet = EMAIL_CHARSET;

      $send = $mail->send();
      // echo 'Mensaje enviado';

    } catch (Exception $e) {
      $send = false;
    }

    return $send;
  }

  public function randomString()
  {
    return md5(rand(100, 200));
  }

  public function uploadCV($cv)
  {

    $name = $this->randomString();

    $info = new SplFileInfo($cv['name']);
    $ext = $info->getExtension();

    $filename = $name . '.' . $ext;

    // Cargamos la en variable de session el la ruta y nombre del archivo subido
    $_SESSION['cv'] = $filename;

    // $destination = $_SERVER['DOCUMENT_ROOT'] . '/unique/sitio/uploads/' .$filename; // Para Local
    $destination = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $filename; // Para Produccion
    $location =  $cv['tmp_name'];

    return move_uploaded_file($location, $destination);
  }

  public function checkUploadCV($cv)
  {

    $errors = [];

    if ($cv['size'] > 2000000) {

      if ($_SESSION['lang'] === 'es') {
        $errors['size'] = "Tamaño máximo de archivo: 2mb";
      } else {
        $errors['size'] = "Maximum file size: 2mb";
      }
    }

    if (
      $cv['type'] != "image/jpeg" &&
      $cv['type'] != "png" &&
      $cv['type'] != "gif" &&
      $cv['type'] != "application/pdf" &&
      $cv['type'] != "application/msword" &&
      $cv['type'] != "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
    ) {

      if ($_SESSION['lang'] === 'es') {
        $errors['type'] = "Tipos de archivos permitidos: PDF, DOC, DOCX, JPG, PNG & GIF.";
      } else {
        $errors['type'] = "Allowed file types: PDF, DOC, DOCX, JPG, PNG & GIF.";
      }
    }

    return $errors;
  }
}
