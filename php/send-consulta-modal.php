<?php
	
	if ($_SESSION['lang'] === 'es') {
		include_once('includes/config.inc.php');
		require_once("clases/app.php");
	} else {
		include_once('../includes/config.inc.php');
		require_once("../clases/app.php");
	}

	$db = new RepositorioSQL();
	$errors = [];
	$name = '';
	$email = '';
	$phone = '';
	$comments = '';
	$position = '';
	$location = '';
	$jobFunction = '';
	$type = '';
	$origin = 'Consulta desde Formulario Aplicación a Puesto';

	// Envio del formulario de contacto
	if (isset($_POST["send"])) {

		if ($_SESSION['lang'] === 'es') {
      // Validaciones del formulario
			include('includes/validar-formulario-trabajo.php');
    } else {
    	// Validaciones del formulario
			include('../includes/validar-formulario-trabajo.php');
    }

		if (!$errors) {

			//grabamos en la base de datos
	    $save = $db->getRepositorioContacts()->saveInBDD($_POST, 'consults');

	    //Enviamos los mails al cliente y usuario
	    $app = new App;

	    $sendClient = $app->sendEmail('Cliente', 'Consulta Cliente', $_POST);

	    $sendUser = $app->sendEmail('Usuario', 'Consulta Usuario', $_POST);

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