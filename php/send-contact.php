<?php

	$db = new RepositorioSQL();
	$errors = [];
	$name = '';
	$email = '';
	$comments = '';
	$origin = 'Consulta desde Formulario Contacto Web';

	// Envio del formulario de contacto
	if (isset($_POST["send"])) {

		if ($_SESSION['lang'] === 'es') {
      // Validaciones del formulario
			include('includes/validar-formulario-contacto.php');
    } else {
    	// Validaciones del formulario
			include('../includes/validar-formulario-contacto.php');
    }

		if (!$errors) {

	  	//grabamos en la base de datos
	    $save = $db->getRepositorioContacts()->saveInBDD($_POST, 'contacts');

	    //Enviamos los mails al cliente y usuario
	    $app = new App;

	    $sendClient = $app->sendEmail('Cliente', 'Contacto Cliente', $_POST);

	    $sendUser = $app->sendEmail('Usuario', 'Contacto Usuario', $_POST);

	    if ($sendClient) {
        ?>
        <script type="text/javascript">
        	// Mostrar mensaje de exito
          document.getElementById("message").style.display = "inline-block";
        </script>
        <?php
      } else {
        ?>
        <script type="text/javascript">
          // Mostrar mensaje de error
          document.getElementById("message-error").style.display = "inline-block";
        </script>
        <?php
      }

	  }

	}

?>