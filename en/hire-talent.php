<?php
// PRIMERO: Iniciar sesión
session_start();

// SEGUNDO: Definir idioma y página actual
$_SESSION['lang'] = 'en';
$current = 'talento';

include_once('../includes/config.inc.php');
include_once('../includes/funciones_validar.php');
require_once("../clases/app.php");
require_once("../clases/repositorioSQL.php");

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Nos especializamos en reclutar talentos para la industria del Turismo y Hoteleria. Busca talentos">
	<meta name="author" content="Librecomunicacion">
	<!-- Favicons -->
	<?php include('../includes/favicon.inc.php'); ?>
	<title>Unique Talent Solutions - El talento impulsa el éxito</title>

	<link rel="stylesheet" href="../node_modules/normalize.css/normalize.css">
	<link rel="stylesheet" href="../node_modules/@fortawesome/fontawesome-free/css/all.min.css">
	<link rel="stylesheet" href="../node_modules/bootstrap/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="../node_modules/wowjs/css/libs/animate.css">
	<link rel="stylesheet" href="../css/app.css">
	<?php include('../includes/tag_manager_head.php'); ?>
</head>

<body>
	<?php include('../includes/tag_manager_body.php'); ?>

	<!-- Mensajes -->
	<div id="message" class="alert alert-success alert-dismissible fade show" role="alert">
		<strong>Successful Send! </strong> We will contact you shortly.
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	<!-- Mensajes end -->

	<!-- Mensajes Error -->
	<div id="message-error" class="alert alert-danger alert-dismissible fade show" role="alert">
		<strong>Sending failed! </strong> Please try again later.
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	<!-- Mensajes end -->

	<!-- Header -->
	<header class="container transition">

		<div class="menu_bar">
			<a href="./">
				<img class="transition" src="../img/logo-unique.png" alt="logo unique">
			</a>
			<button class="transition" id="btn-menu-mobile" type="button"><i class="fas fa-bars"></i></button>
		</div>
		<div class="content_navegacion">
			<nav>
				<ul>
					<li><a class="btn_nav transition" href="./#equipo">ABOUT</a></li>
					<li><a class="btn_nav transition" href="./#servicios">SERVICES</a></li>
					<li><a class="transition" href="https://unique.hiringroom.com/jobs" target="_blank" rel="noopener noreferrer">JOB OFFERS</a></li>
					<li><a class="transition" href="hire-talent.php">HIRE A TALENT</a></li>
					<li><a class="btn_nav transition" href="./#contacto">CONTACT</a></li>
				</ul>
			</nav>
			<div class="languages_rrss">
				<div>
					<?php $activeES = $_SESSION['lang'] == 'es' ? 'active' : ''; ?>
					<?php $activeEN = $_SESSION['lang'] == 'en' ? 'active' : ''; ?>
					<a class="transition <?= $activeEN ?>" href="#">ENG</a>
					<a class="transition <?= $activeES ?>" href="../busca-talento.php">SPA</a>
				</div>
				<div>
					<a class="transition" href="https://www.instagram.com/unqtalent/" target="_blank"><i class="fab fa-instagram-square"></i></a>
					<a class="transition" href="https://www.linkedin.com/company/unqtalent/about/" target="_blank"><i class="fab fa-linkedin"></i></a>
					<a class="transition" href="https://api.whatsapp.com/send?phone=+5491157550306&text=Hola!%20Necesito%20hacer%20una%20consulta!" target="_blank"><i class="fab fa-whatsapp-square"></i></a>
				</div>
			</div>
		</div>
	</header>
	<!-- Header end -->

	<!-- Slide -->
	<div class="img_principal container-fluid p-0">
		<img class="img-fluid" src="../img/slide-busca-talento.jpg" alt="slide busca trabajo">
		<h2 class="wow bounceInLeft">TALENT DRIVES SUCCESS</h2>
	</div>
	<!-- Slide end -->

	<!-- Busca Talento -->
	<section class="wow bounceInUp talento container">
		<div class="row">

			<div class="col-md-6">
				<h2 class=" wow fadeInLeft">HIRE A TALENT</h2>
			</div>

			<div class="col-md-6">
				<h1 class=" wow fadeInRight">
					As Partners, we believe in a human-centered approach to the recruitment process. We work with a Unique combination of experiences, personalities and backgrounds to connect the right person with the job they love.
				</h1>
			</div>

			<div class="col-md-12">
				<h3 class=" wow fadeInUp">OUR PROCESS</h3>

				<div class="proceso">

					<div class="content paso activo">
						<img src="./../img/contactese.svg" alt="icono contactese">
						<div class="content_data">
							<p>
								<span>CONNECT HIRE! WITH UNIQUE: </span>
								Understanding the role and its organizational context.
							</p>
						</div>
					</div>

					<div class="content paso">
						<img src="./../img/identificacion.svg" alt="icono identificacion">
						<div class="content_data">
							<p>
								<span>IDENTIFYING AND ATTRACTING TOP TALENT: </span>
								Identifying top talent for the position.
							</p>
						</div>
					</div>

					<div class="content paso">
						<img src="./../img/presenta.svg" alt="icono presenta">
						<div class="content_data">
							<p>
								<span>UNIQUE PRESENTS CANDIDATES: </span>
								Presenting qualified candidates to the client.
							</p>
						</div>
					</div>

					<div class="content paso">
						<img src="./../img/entrevista.svg" alt="icono entrevista">
						<div class="content_data">
							<p>
								<span>CLIENT INTERVIEWS CANDIDATES: </span>
								Coordinating client interviews with selected candidates.
							</p>
						</div>
					</div>

					<div class="content paso">
						<img src="./../img/contratar.svg" alt="icono contratar">
						<div class="content_data">
							<p>
								<span>HIRE! : </span>
								Supporting negotiation and final hiring process!.
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
						<h2>Connect with Unique</h2>

						<?php include_once('../php/send-talento.php');  ?>

						<form id="send" method="post" class="needs-validation" novalidate>

							<!-- Errores Formulario -->
							<?php if ($errors): ?>
								<div id="error" class="alert alert-danger alert-dismissible fade show fadeInLeft" role="alert">
									<strong>¡Please verify the data!</strong>
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
										<input required type="text" class="form-control transition" name="name" value="<?= $name ?>" placeholder="Name">
										<div class="invalid-feedback">
											Enter your name.
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<input required type="email" class="form-control transition" name="email" value="<?= $email ?>" placeholder="E-mail">
										<div class="invalid-feedback">
											Enter your email
										</div>
									</div>
								</div>
							</div>

							<div class="form-row">
								<div class="col-md-6">
									<div class="form-group">
										<input required type="text" class="form-control transition" name="company" value="<?= $company ?>" placeholder="Company">
										<div class="invalid-feedback">
											Enter your company.
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<input required type="text" class="form-control transition" name="phone" value="<?= $phone ?>" placeholder="Phone">
										<div class="invalid-feedback">
											Enter your phone
										</div>
									</div>
								</div>
							</div>

							<div class="form-row">
								<div class="col-md-6">
									<div class="form-group">
										<input required type="text" class="form-control transition" name="job" value="<?= $job ?>" placeholder="I'm looking for...">
										<div class="invalid-feedback">
											Enter a search position.
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<input required type="text" class="form-control transition" name="location" value="<?= $location ?>" placeholder="Location...">
										<div class="invalid-feedback">
											Enter the location.
										</div>
									</div>
								</div>
							</div>

							<div class="form-group">
								<textarea required class="form-control transition" name="comments" rows="3" placeholder="Leave us a message"><?= $comments ?></textarea>
								<div class="invalid-feedback">
									Leave us a message
								</div>
							</div>

							<div class="form-group">
								<div id="recaptcha" class="g-recaptcha" data-sitekey="<?= RECAPTCHA_PUBLIC_KEY ?>"></div>
							</div>

							<button type="submit" name="send" class="btn btn-primary transition">SEND</button>

						</form>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- Contacto end -->

	<!-- Footer EN -->
	<?php include('../includes/footer-en.php') ?>

	<script src="../node_modules/jquery/dist/jquery.min.js"></script>
	<script src="../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
	<script src="../node_modules/jquery.easing/jquery.easing.min.js"></script>
	<script src="../node_modules/wowjs/dist/wow.min.js"></script>
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	<script src="../js/app.js"></script>
	<script src="./../js/busca-talento.js"></script>
</body>

</html>