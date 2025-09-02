<?php

	$db = new RepositorioSQL();
	$errors = [];
	$name = '';
	$email = '';
	$company = '';
	$job = '';
	$location = '';
	$phone = '';
	$comments = '';
	$origin = 'Consulta desde Formulario Busqueda de Talento';

	// Envio del formulario de contacto
	if (isset($_POST["send"])) {

		if ($_SESSION['lang'] === 'es') {
      // Validaciones del formulario
			include('includes/validar-formulario-talento.php');
    } else {
    	// Validaciones del formulario
			include('../includes/validar-formulario-talento.php');
    }

		if (!$errors) {

	  	//grabamos en la base de datos
	    $save = $db->getRepositorioContacts()->saveInBDD($_POST, 'talents');

	    //Enviamos los mails al cliente y usuario
	    $app = new App;

	    $sendClient = $app->sendEmail('Cliente', 'Talento Cliente', $_POST);

	    $sendUser = $app->sendEmail('Usuario', 'Talento Usuario', $_POST);

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