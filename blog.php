<?php

session_start();

$_SESSION['lang'] = 'es';
$current = 'blog';

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
  <meta name="description" content="Enterate de las últimas novedades.">
  <meta name="author" content="Librecomunicacion">
  <!-- Favicons -->
  <?php include('includes/favicon.inc.php'); ?>
  <title>Unique Talent Solutions - Últimas novedades</title>

  <link rel="stylesheet" href="node_modules/normalize.css/normalize.css">
  <link rel="stylesheet" href="node_modules/@fortawesome/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="node_modules/wowjs/css/libs/animate.css">
  <link rel="stylesheet" href="css/app.css">
  <?php include('includes/tag_manager_head.php'); ?>
</head>

<body>
  <?php include('includes/tag_manager_body.php'); ?>


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


  <main id="app">

    <div class="container">
      <div class="row">
        <div class="col-md-12">
          // COLOCAR AQUI LOS POSTS SEGUN EL IDIOMA ACTUAL
        </div>
      </div>
    </div>

  </main>

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
  <script src="js/blog.js"></script>

</body>

</html>