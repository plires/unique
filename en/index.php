<?php
$_SESSION['lang'] = 'en';

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
	<meta name="description" content="Unique Talent Solutions is a company specialized in recruiting talents for the Tourism and Hospitality industry.">
	<meta name="author" content="Librecomunicacion">
	<!-- Favicons -->
	<?php include('../includes/favicon.inc.php'); ?>
	<title>We recruit talents for the Tourism and Hospitality industry</title>

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
					<li><a class="btn_nav transition" href="#equipo">ABOUT</a></li>
					<li><a class="btn_nav transition" href="#servicios">SERVICES</a></li>
					<li><a class="transition" href="job-offers.php">JOB OFFERS</a></li>
					<li><a class="transition" href="hire-talent.php">HIRE A TALENT</a></li>
					<li><a class="btn_nav transition" href="#contacto">CONTACT</a></li>
				</ul>
			</nav>
			<div class="languages_rrss">
				<div>
					<?php $activeES = $_SESSION['lang'] == 'es' ? 'active' : ''; ?>
					<?php $activeEN = $_SESSION['lang'] == 'en' ? 'active' : ''; ?>
					<a class="transition <?= $activeEN ?>" href="./">ENG</a>
					<a class="transition <?= $activeES ?>" href="../">SPA</a>
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

	<!-- Slide Desktop -->
	<div id="slide_desktop" class="slide container-fluid p-0">
		<div id="carouselDesktopFade" class="carousel slide carousel-fade" data-ride="carousel">
			<ol class="carousel-indicators">
				<li data-target="#carouselDesktopFade" data-slide-to="0" class="active"></li>
				<li data-target="#carouselDesktopFade" data-slide-to="1"></li>
				<li data-target="#carouselDesktopFade" data-slide-to="2"></li>
			</ol>
			<div class="carousel-inner">
				<div class="carousel-item active">
					<img class="img-fluid" src="../img/slide-a-eng.jpg" alt="slide a esp">
				</div>
				<div class="carousel-item">
					<img class="img-fluid" src="../img/slide-b-eng.jpg" alt="slide b esp">
				</div>
				<div class="carousel-item">
					<img class="img-fluid" src="../img/slide-c-eng.jpg" alt="slide c esp">
				</div>
			</div>
			<a class="carousel-control-prev" href="#carouselDesktopFade" role="button" data-slide="prev">
				<span class="carousel-control-prev-icon" aria-hidden="true"></span>
				<span class="sr-only">Previous</span>
			</a>
			<a class="carousel-control-next" href="#carouselDesktopFade" role="button" data-slide="next">
				<span class="carousel-control-next-icon" aria-hidden="true"></span>
				<span class="sr-only">Next</span>
			</a>
		</div>
	</div>
	<!-- Slide Desktop end -->

	<!-- Slide Mobile -->
	<div id="slide_mobile" class="slide container-fluid p-0">
		<div id="carouselMobileFade" class="carousel slide carousel-fade" data-ride="carousel">
			<ol class="carousel-indicators">
				<li data-target="#carouselMobileFade" data-slide-to="0" class="active"></li>
				<li data-target="#carouselMobileFade" data-slide-to="1"></li>
				<li data-target="#carouselMobileFade" data-slide-to="2"></li>
			</ol>
			<div class="carousel-inner">
				<div class="carousel-item active">
					<img class="img-fluid" src="../img/mobile-slide-a-eng.jpg" alt="slide a esp">
				</div>
				<div class="carousel-item">
					<img class="img-fluid" src="../img/mobile-slide-b-eng.jpg" alt="slide b esp">
				</div>
				<div class="carousel-item">
					<img class="img-fluid" src="../img/mobile-slide-c-eng.jpg" alt="slide c esp">
				</div>
			</div>
			<a class="carousel-control-prev" href="#carouselMobileFade" role="button" data-slide="prev">
				<span class="carousel-control-prev-icon" aria-hidden="true"></span>
				<span class="sr-only">Previous</span>
			</a>
			<a class="carousel-control-next" href="#carouselMobileFade" role="button" data-slide="next">
				<span class="carousel-control-next-icon" aria-hidden="true"></span>
				<span class="sr-only">Next</span>
			</a>
		</div>
	</div>
	<!-- Slide Mobile end -->

	<!-- Equipo -->
	<section id="equipo" class="equipo container">
		<div class="row">

			<div class="col-md-6 wow bounceInLeft">
				<h2>ABOUT</h2>
				<img class="img-fluid" src="../img/equipo.jpg" alt="equipo de trabajo">
				<img class="img-fluid" src="../img/talent.png" alt="reclutadora de talento">
			</div>

			<div class="col-md-6 wow bounceInRight">
				<h1>
					Unique Talent Solutions is a <br>
					company specialized in <br>
					recruiting candidates for the <br>
					Tourism and Hospitality Industry.
				</h1>
				<p class="roboto primer_parrafo">
					We believe that people are not perfect but they are UNIQUE, each one has their own way of
					doing things, make them different from others and that is what we look for: UNIQUE talents that fit the culture of the organization.
				</p>
				<div class="unq">
					<img class="img-fluid" src="../img/unq.png" alt="logo unq">
				</div>
				<p class="roboto"><span>Why Unique?</span></p>
				<p class="roboto">
					<span>Proffestionality & Expertise:</span> We have more than 15 years of expertise in the Tourism and Hospitality Industry; that helps us understand the needs of your company. We know exactly what you are looking for when hiring a talent.
				<p class="roboto">
					<span>Human Connections:</span> We believe in the power of long – terms relationships. Accurate recruiting by our agency, allows you to build a team of loyal employees, retaining your talent with decreased training costs. We are a HR partner.
				<p class="roboto">
					<span>Passion:</span> We are passionate for the industry we work for and do
					our best every day to help you finding the right job or the right
					talent for your company. Passionate people make the
					difference.
				</p>
			</div>

		</div>
	</section>
	<!-- Equipo end -->

	<!-- Servicios -->
	<section id="servicios" class="servicios container-fluid">
		<div class="container">
			<div class="row">
				<img class="img-fluid servicios_vertical" src="../img/services.png" alt="servicices">

				<div class="col-md-6">
					<h2>SERVICES FOR COMPANIES</h2>
					<p class="que_hacemos wow fadeInUp">HOW WE<br><span>WORK</span></p>
					<p class="roboto">
						<span>Staff Recruitment:</span> Sourcing and selecting the best talent for your organization. We specialise in Tourism and Hospitality Industry – Tour Operators, Hotels & Tourism Products.
					<p class="roboto">
						<span>Success Guarantee:</span> We do not cease working in a position until a perfect match between company and candidate is found.
					</p>
					<div id="empleos" class="busca_trabajo wow fadeInLeft">
						<img class="img-fluid" src="../img/busca-trabajo.jpg" alt="busca trabajo">
						<div>
							<h3>FIND A JOB</h3>
							<p class="roboto">
								Unique Talent Solutions will connect you with the perfect matched employer. We will support you during the process helping you to identify your strengths and main competences.
							</p>
							<a href="job-offers.php" class="transition btn btn-primary">FIND</a>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<img class="img-fluid entrevista wow fadeIn" src="../img/entrevista.jpg" alt="entrevista de trabajo">
					<div data-wow-delay="0.5s" class="busca_talento wow fadeInLeft">
						<img class="img-fluid" src="../img/busca-talento.jpg" alt="busca talento">
						<div>
							<h3>HIRE A TALENT</h3>
							<p class="roboto">
								We work with a unique combination of experiences, personalities, and backgrounds to connect the right person with the job they love.
							</p>
							<a href="hire-talent.php" class="transition btn btn-primary">HIRE</a>
						</div>
					</div>
				</div>

			</div>
		</div>
	</section>
	<!-- Servicios end -->

	<!-- Feed Instagram -->
	<section id="instagram" class="instagram container wow fadeInUp">
		<div class="row">
			<div class="col-md-12 text-center">
				<h2>NEWS & EVENTS</h2>
			</div>
		</div>

		<div class="row">
			<div class="col-md-12 mb-5">
				<!-- Place <div> tag where you want the feed to appear -->
				<div id="curator-feed-default-feed-layout"><a href="https://curator.io" target="_blank" class="crt-logo crt-tag">Powered by Curator.io</a></div>

				<!-- The Javascript can be moved to the end of the html page before the </body> tag -->
				<script type="text/javascript">
					/* curator-feed-default-feed-layout */
					(function() {
						var i, e, d = document,
							s = "script";
						i = d.createElement("script");
						i.async = 1;
						i.charset = "UTF-8";
						i.src = "https://cdn.curator.io/published/6e094e7e-103c-41c9-89ca-af61ac4ae7b1.js";
						e = d.getElementsByTagName(s)[0];
						e.parentNode.insertBefore(i, e);
					})();
				</script>
			</div>
		</div>

	</section>
	<!-- Feed Instagram end -->

	<!-- Contacto -->
	<section id="contacto" class="contacto wow fadeInUp">
		<div class="container-fluid">
			<div class="container">
				<img class="img-fluid contacto_vertical" src="../img/contacto.png" alt="contacto">
				<div class="row">
					<div class="col-md-8 offset-md-2">
						<h2>Contact</h2>

						<?php include_once('../php/send-contact.php');  ?>

						<form id="send" method="post" class="needs-validation" novalidate>

							<!-- Errores Formulario -->
							<?php if ($errors) : ?>
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

							<div class="form-group">
								<input required type="text" class="form-control transition" name="name" value="<?= $name ?>" placeholder="Nombre Completo">
								<div class="invalid-feedback">
									Enter your name.
								</div>
							</div>

							<div class="form-group">
								<input required type="email" class="form-control transition" name="email" value="<?= $email ?>" placeholder="E-mail">
								<div class="invalid-feedback">
									Enter your email.
								</div>
							</div>

							<div class="form-group">
								<textarea required class="form-control transition" name="comments" rows="3" placeholder="Consulta"><?= $comments ?></textarea>
								<div class="invalid-feedback">
									Enter your comments.
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
</body>

</html>