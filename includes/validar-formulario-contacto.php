<?php
	
	if(isset($_POST['g-recaptcha-response'])){$captcha=$_POST['g-recaptcha-response'];}
	$secretKey = RECAPTCHA_SECRET_KEY;
	$ip = $_SERVER['REMOTE_ADDR'];
	$response=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secretKey."&response=".$captcha."&remoteip=".$ip);
	$responseKeys = json_decode($response,true);

	if ($responseKeys['success']) {

	  // Verificamos si hay errores en el formulario
	  if (campoVacio($_POST['name'])){

	  	if ($_SESSION['lang'] === 'es') {
	    	$errors['name']='Ingresa tu nombre';
	  	} else {
	  		$errors['name']='Enter your name';
	  	}
	  } else {
	    $name = $_POST['name'];
	  }

	  if (!comprobar_email($_POST['email'])){

	  	if ($_SESSION['lang'] === 'es') {
	    	$errors['email']='Ingresa el mail :(';
	  	} else {
	  		$errors['email']='Enter email :(';
	  	}
	  } else {
	    $email = $_POST['email'];
	  }

	  if (campoVacio($_POST['comments'])){

	  	if ($_SESSION['lang'] === 'es') {
	    	$errors['comments']='Ingresa tus comentarios';
	  	} else {
	  		$errors['comments']='Enter your comments';
	  	}
	  } else {
	    $comments = $_POST['comments'];
	  }

	} else {
		if ($_SESSION['lang'] === 'es') {
	  	$errors['recaptcha'] = 'Error al validar el recaptcha';
	  } else {
	  	$errors['recaptcha'] = 'Error validating recaptcha';
	  }
	}

?>