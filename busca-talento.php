<?php
// PRIMERO: Iniciar sesión
session_start();

// SEGUNDO: Definir idioma y página actual
$_SESSION['lang'] = 'es';
$current = 'talento';

include_once('includes/config.inc.php');
include_once('includes/funciones_validar.php');
require_once("clases/app.php");
require_once("clases/repositorioSQL.php");

?>

<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Nos especializamos en reclutar talentos para la industria del Turismo y Hoteleria. Busca talentos">
	<meta name="author" content="Librecomunicacion">
	<!-- Favicons -->
	<?php include('includes/favicon.inc.php'); ?>
	<title>Unique Talent Solutions - El talento impulsa el éxito</title>

	<link rel="stylesheet" href="node_modules/normalize.css/normalize.css">
	<link rel="stylesheet" href="node_modules/@fortawesome/fontawesome-free/css/all.min.css">
	<link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="node_modules/wowjs/css/libs/animate.css">
	<link rel="stylesheet" href="css/app.css">
	<?php include('includes/tag_manager_head.php'); ?>
</head>

<body>
	<?php include('includes/tag_manager_body.php'); ?>

	<!-- Mensajes -->
	<div id="message" class="alert alert-success alert-dismissible fade show" role="alert">
		<strong>Envio Exitoso!</strong> Nos comunicaremos con usted a la brevedad.
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	<!-- Mensajes end -->

	<!-- Mensajes Error -->
	<div id="message-error" class="alert alert-danger alert-dismissible fade show" role="alert">
		<strong>Error al enviar!</strong> Por favor intente mas tarde.
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	<!-- Mensajes end -->

	<!-- Header -->
	<?php include('includes/header.inc.php'); ?>

	<!-- Slide -->
	<div class="img_principal container-fluid p-0">
		<img class="img-fluid" src="img/slide-busca-talento.jpg" alt="slide busca trabajo">
		<h2 class="wow bounceInLeft">EL TALENTO IMPULSA EL ÉXITO</h2>
	</div>
	<!-- Slide end -->

	<!-- Busca Talento -->
	<section class="wow bounceInUp talento container">
		<div class="row">

			<div class="col-md-6">
				<h2 class=" wow fadeInLeft">BUSCA TALENTO</h2>
			</div>

			<div class="col-md-6">
				<h1 class=" wow fadeInRight">
					Como socios, creemos en un enfoque humano para el proceso de selección. Trabajamos con una combinación única de experiencias, personalidades y antecedentes para conectar a la persona adecuada con el trabajo que ama.
				</h1>
			</div>

			<div class="col-md-12">
				<h3 class="wow fadeInUp">NUESTRO PROCESO</h3>

				<div class="proceso">

					<div class="content paso activo">
						<img src="./img/contactese.svg" alt="icono contactese">
						<div class="content_data">
							<p>
								<span>CONTÁCTESE CON UNIQUE:</span>
								Relevamiento de la posición a cubrir y su entorno organizacional.
							</p>
						</div>
					</div>

					<div class="content paso">
						<img src="./img/identificacion.svg" alt="icono identificacion">
						<div class="content_data">
							<p>
								<span>IDENTIFICACIÓN Y ATRACCIÓN DE TALENTO:</span>
								Búsqueda de los mejores talentos para la posición.
							</p>
						</div>
					</div>

					<div class="content paso">
						<img src="./img/presenta.svg" alt="icono presenta">
						<div class="content_data">
							<p>
								<span>UNIQUE PRESENTA CANDIDATOS:</span>
								Presentación de candidatos calificados al cliente.
							</p>
						</div>
					</div>

					<div class="content paso">
						<img src="./img/entrevista.svg" alt="icono entrevista">
						<div class="content_data">
							<p>
								<span>CLIENTE ENTREVISTA A CANDIDATOS:</span>
								Coordinación de entrevistas entre el cliente y los candidatos seleccionados.
							</p>
						</div>
					</div>

					<div class="content paso">
						<img src="./img/contratar.svg" alt="icono contratar">
						<div class="content_data">
							<p>
								<span>CONTRATAR!:</span>
								Acompañamiento en la negociación y el proceso de contratación final!.
							</p>
						</div>
					</div>

				</div>

			</div>

		</div>
	</section>
	<!-- Busca Talento end -->

	<!-- Contacto -->
	<section id="contacto" class="contacto wow fadeInUp">
		<div class="container-fluid">
			<div class="container">
				<div class="row">
					<div class="col-md-8 offset-md-2">
						<h2>Contáctese con Unique</h2>

						<?php include_once('php/send-talento.php');  ?>

						<form id="send" method="post" class="needs-validation" novalidate>

							<!-- Errores Formulario -->
							<?php if ($errors): ?>
								<div id="error" class="alert alert-danger alert-dismissible fade show fadeInLeft" role="alert">
									<strong>¡Por favor verificá los datos!</strong>
									<button type="button" class="close" data-dismiss="alert" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
									<ul style="padding: 0;">
										<?php foreach ($errors as $error) { ?>
											<li>- <?php echo $error; ?></li>
										<?php } ?>
									</ul>
								</div>
							<?php endif ?>
							<!-- Errores Formulario end -->

							<input type="hidden" name="origin" value="<?= $origin ?>">

							<div class="form-row">
								<div class="col-md-6">
									<div class="form-group">
										<input required type="text" class="form-control transition" name="name" value="<?= $name ?>" placeholder="Nombre Completo">
										<div class="invalid-feedback">
											Ingrese su nombre.
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<input required type="email" class="form-control transition" name="email" value="<?= $email ?>" placeholder="E-mail">
										<div class="invalid-feedback">
											Ingrese un email válido.
										</div>
									</div>
								</div>
							</div>

							<div class="form-row">
								<div class="col-md-6">
									<div class="form-group">
										<input required type="text" class="form-control transition" name="company" value="<?= $company ?>" placeholder="Empresa">
										<div class="invalid-feedback">
											Ingrese su empresa.
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<input required type="text" class="form-control transition" name="phone" value="<?= $phone ?>" placeholder="Teléfono">
										<div class="invalid-feedback">
											Ingrese un teléfono.
										</div>
									</div>
								</div>
							</div>

							<div class="form-row">
								<div class="col-md-6">
									<div class="form-group">
										<input required type="text" class="form-control transition" name="job" value="<?= $job ?>" placeholder="Estoy buscando...">
										<div class="invalid-feedback">
											Ingrese una posición de búsqueda.
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<input required type="text" class="form-control transition" name="location" value="<?= $location ?>" placeholder="Ubicación...">
										<div class="invalid-feedback">
											Ingrese la ubicación.
										</div>
									</div>
								</div>
							</div>

							<div class="form-group">
								<textarea required class="form-control transition" name="comments" rows="3" placeholder="Déjanos un mensaje"><?= $comments ?></textarea>
								<div class="invalid-feedback">
									Déjanos un mensaje
								</div>
							</div>

							<div class="form-group">
								<div id="recaptcha" class="g-recaptcha" data-sitekey="<?= RECAPTCHA_PUBLIC_KEY ?>"></div>
							</div>

							<button type="submit" name="send" class="btn btn-primary transition">ENVIAR</button>

						</form>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- Contacto end -->

	<!-- Footer -->
	<?php include('includes/footer-esp.php'); ?>

	<script src="node_modules/jquery/dist/jquery.min.js"></script>
	<script src="node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
	<script src="node_modules/jquery.easing/jquery.easing.min.js"></script>
	<script src="node_modules/wowjs/dist/wow.min.js"></script>
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	<script src="js/app.js"></script>
	<script src="js/busca-talento.js"></script>
</body>

</html>