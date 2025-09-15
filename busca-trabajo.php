<?php
$_SESSION['lang'] = 'es';
$current = 'trabajo';

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
	<meta name="description" content="Nos especializamos en reclutar talentos para la industria del Turismo y Hoteleria. Busca trabajo">
	<meta name="author" content="Librecomunicacion">
	<!-- Favicons -->
	<?php include('includes/favicon.inc.php'); ?>
	<title>Unique Talent Solutions - Te conectamos con tu empleador perfecto</title>

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
	<header class="container transition">

		<div class="menu_bar">
			<a href="./">
				<img class="transition" src="img/logo-unique.png" alt="logo unique">
			</a>
			<button class="transition" id="btn-menu-mobile" type="button"><i class="fas fa-bars"></i></button>
		</div>

		<div class="content_navegacion">
			<nav>
				<ul>
					<li><a class="btn_nav transition" href="./#equipo">EQUIPO</a></li>
					<li><a class="btn_nav transition" href="./#servicios">SERVICIOS</a></li>
					<li><a class="transition <?= ($current === 'trabajo') ? 'active' : ''; ?>" href="https://unique.hiringroom.com/jobs" target="_blank" rel="noopener noreferrer">EMPLEOS</a></li>
					<li><a class="transition <?= ($current === 'talento') ? 'active' : ''; ?>" href="busca-talento.php">TALENTOS</a></li>
					<li><a class="btn_nav transition" href="./#contacto">CONTACTO</a></li>
				</ul>
			</nav>
			<div class="languages_rrss">
				<div>
					<?php $activeES = $_SESSION['lang'] == 'es' ? 'active' : ''; ?>
					<?php $activeEN = $_SESSION['lang'] == 'en' ? 'active' : ''; ?>
					<a class="transition <?= $activeEN ?>" href="https://unique.hiringroom.com/jobs" target="_blank" rel="noopener noreferrer">ENG</a>
					<a class="transition <?= $activeES ?>" href="#">SPA</a>
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
		<img class="img-fluid" src="img/slide-busca-trabajo.jpg" alt="slide busca trabajo">
		<h2 class="wow bounceInLeft">¿CUÁL ES EL PRÓXIMO?</h2>
	</div>
	<!-- Slide end -->

	<div id="app">

		<!-- Busca Trabajo -->
		<section class="talento container">
			<div class="row">

				<div class="col-md-6">
					<h2 class="wow fadeInLeft">BUSCA TRABAJO</h2>
				</div>

				<div class="col-md-6">
					<h1 class="wow fadeInRight">
						Unique Talent Solutions te conectara con el empleador que sea el match perfecto. Te apoyaremos durante todo el proceso
						de búsqueda para que puedas identificar mejor tus fortalezas y competencias.
					</h1>
				</div>

			</div>
		</section>
		<!-- Busca Trabajo end -->

		<!-- Trabajo -->
		<section id="trabajo" class="contacto trabajo wow fadeInUp">
			<div class="container-fluid">
				<div class="container">
					<div class="row">
						<div class="col-md-10 offset-md-1">
							<form id="send" method="post" class="needs-validation" novalidate>
								<div class="content_search">
									<p>Ubicación</p>

									<select v-on:change="filterSelect(location, 'location')" v-model="location" id="location" required name="location" class="form-control">
										<option value="" selected>Todas</option>
										<option v-for="(location, index) in selectLocation" :key="index" :value="location">{{ location }}</option>
									</select>

								</div>
								<div class="content_search">
									<p>Función laboral</p>

									<select v-on:change="filterSelect(job_function, 'job_function')" v-model="job_function" id="jobFunction" required name="jobFunction" class="form-control">
										<option value="" selected>Todas</option>
										<option v-for="(jobFunction, index) in selectJobFunction" :key="index" :value="jobFunction">{{ jobFunction }}</option>
									</select>

								</div>
								<div class="content_search">
									<p>Tipo de empleo</p>

									<select v-on:change="filterSelect(employment_type, 'employment_type')" v-model="employment_type" id="employmentType" required name="employmentType" class="form-control">
										<option value="" selected>Todas</option>
										<option v-for="(employmentType, index) in selectEmploymentType" :key="index" :value="employmentType">{{ employmentType }}</option>
									</select>

								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- Trabajo end -->

		<?php include_once('includes/modal-esp.php'); ?>

		<!-- Accordion -->
		<section class="accordion container" id="accordion">

			<div class="row">
				<div class="col-md-12">

					<div v-for="(job, index) in arrayJobFiltered" :key="job.id" class="card">

						<div class="card-header" :id="'heading'+job.id">

							<div class="row">

								<div class="col-lg-3">
									{{ job.position }}
								</div>

								<div class="col-lg-2">
									{{ job.location }}
								</div>

								<div class="col-lg-2">
									{{ job.job_function }}
								</div>

								<div class="col-lg-2">
									{{ job.employment_type }}
								</div>

								<div class="col-lg-3 acciones">
									<a v-if="job.link" title="Aplicar a este puesto desde Linkedin" target="_blank" class="linkedin transition" :href="job.link"><i class="fab fa-linkedin"></i></a>

									<button title="Aplicar a este puesto desde Formulario" class="btn btn-sm btn-primary btn-apply" @click="prepareForm(job.id)" data-toggle="modal" data-target="#consultaEsp">Aplicar</button>
									<button title="Ver descripción del puesto" @click="changeTitleButton($event, 'es')" class="btn btn-sm btn-primary btn-apply btn_ver_mas" type="button" data-toggle="collapse" :data-target="'#desplegable'+job.id" aria-expanded="false" :aria-controls="job.id">ver más</button>
								</div>

							</div>

						</div>

						<div :id="'desplegable'+job.id" class="collapse" :aria-labelledby="'heading'+job.id" data-parent="#accordion">
							<div class="card-body">

								<div class="row">
									<div class="col-md-12">
										<p v-if="job.description">
											{{ job.description }}
										</p>
										<p v-else>Contactanos para mas información</p>
									</div>
								</div>

							</div>
						</div>
					</div>

				</div>
			</div>

		</section>
		<!-- Accordion end -->

	</div>

	<!-- Footer -->
	<?php include('includes/footer-esp.php'); ?>

	<script src="node_modules/jquery/dist/jquery.min.js"></script>
	<script src="node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
	<script src="node_modules/jquery.easing/jquery.easing.min.js"></script>
	<script src="node_modules/wowjs/dist/wow.min.js"></script>
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	<script src="js/app.js"></script>
	<!-- axios -->
	<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

	<!-- versión de desarrollo, incluye advertencias de ayuda en la consola -->
	<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
	<!-- <script src="https://cdn.jsdelivr.net/npm/vue"></script> -->
	<script src="js/search-job.js"></script>

	<?php if ($errors): ?>

		<script>
			app.formConsultPosition = '<?php echo $_POST['position']; ?>';
			app.formConsultLocation = '<?php echo $_POST['location']; ?>';
			app.formConsultJobFunction = '<?php echo $_POST['jobFunction']; ?>';
			app.formConsultEmploymentType = '<?php echo $_POST['employmentType']; ?>';
			$('.modales').modal('show');
		</script>

	<?php endif ?>

</body>

</html>