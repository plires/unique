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

        if (isset($_POST['pass'])) {
          $emailDestino = $post['email'];
          $emailBCC = null;
          $nameShow = NAME_SENDER_SHOW;
          $emailAddReplyTo = EMAIL_SENDER_SHOW;
        }
        break;

      case 'Usuario':
        $emailDestino = $post['email'];
        $nameShow = NAME_SENDER_SHOW;
        $emailShow = EMAIL_RECIPIENT;  // Mi cuenta de correo
        $emailAddReplyTo = EMAIL_SENDER_SHOW;
        $emailBCC = '';
        break;
    }

    $path = '';

    if (isset($_SESSION['lang'])) {

      $lang = $_SESSION['lang'];
      if ($lang !== 'es') {
        $path = '../';
      }
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

      case 'Reset Pass Cliente':
        include($path . './../includes/emails/reset/template-reset-pass.inc.php');
        $subject = 'Reseteo de contraseña - Sitio Unique.';
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

      if (ENVIRONMENT === 'dev') {
        $mail->isSendmail();
      } else {
        $mail->isSMTP();
      }
      // Send using SMTP
      $mail->Host       = SMTP;
      $mail->SMTPAuth   = true;
      $mail->Username   = EMAIL_CLIENT;
      $mail->Password   = PASSWORD;
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port       = EMAIL_PORT;

      if (ENVIRONMENT === 'dev') {
        $mail->SMTPOptions = array(
          'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
          )
        );
      }

      // Recipientes
      $mail->setFrom($emailShow, $nameShow);
      $mail->AddAddress($emailDestino);
      $mail->AddReplyTo($emailAddReplyTo);

      // Content
      $mail->isHTML(true);
      $mail->Subject = $subject;
      $mail->Body = $body;

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
