<?php
session_start();

// Definir idioma
$_SESSION['lang'] = 'es';
$current = 'blog';

include_once('includes/config.inc.php');
include_once('includes/funciones_validar.php');
require_once("clases/app.php");
require_once("clases/repositorioSQL.php");
require_once("clases/PostsPublic.php");
require_once("php/setup-post-individual.php");

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
  <meta name="author" content="Librecomunicacion">

  <!-- SEO Meta Tags -->
  <title><?php echo $metaTitle; ?></title>
  <link rel="canonical" href="<?php echo $canonicalUrl; ?>">

  <!-- Open Graph Meta Tags -->
  <meta property="og:title" content="<?php echo htmlspecialchars($post['title']); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars($metaDescription); ?>">
  <meta property="og:url" content="<?php echo $canonicalUrl; ?>">
  <meta property="og:type" content="article">
  <?php if (!empty($post['images']['header'])): ?>
    <meta property="og:image" content="<?php echo APP_URL_FRONTEND . getImageUrl($post['images']['header'][0]['file_path']); ?>">
  <?php elseif (!empty($post['images']['listing'])): ?>
    <meta property="og:image" content="<?php echo APP_URL_FRONTEND . getImageUrl($post['images']['listing'][0]['file_path']); ?>">
  <?php endif; ?>

  <!-- Twitter Card Meta Tags -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?php echo htmlspecialchars($post['title']); ?>">
  <meta name="twitter:description" content="<?php echo htmlspecialchars($metaDescription); ?>">

  <!-- Favicons -->
  <?php include('includes/favicon.inc.php'); ?>

  <!-- Stylesheets -->
  <link rel="stylesheet" href="node_modules/normalize.css/normalize.css">
  <link rel="stylesheet" href="node_modules/@fortawesome/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="node_modules/wowjs/css/libs/animate.css">
  <link rel="stylesheet" href="css/app.css">
  <link rel="stylesheet" href="css/latest-news.css">
  <link rel="stylesheet" href="css/post-individual.css">

  <?php include('includes/tag_manager_head.php'); ?>
</head>

<body>
  <?php include('includes/tag_manager_body.php'); ?>

  <!-- Modal para ver imagenes -->
  <?php require_once("includes/modal-blog-images-front.php");
  ?>

  <!-- Header -->
  <?php include('includes/header.inc.php'); ?>

  <!-- Main Content -->
  <main>

    <!-- Section Hero -->
    <?php include('includes/post-section-hero.php'); ?>

    <!-- Section Content -->
    <?php include('includes/post-section-content.php'); ?>

    <!-- Section Video (solo si existe) -->
    <?php include('includes/post-section-video.php'); ?>

    <!-- Section Images (solo si existen) -->
    <?php include('includes/post-section-images.php'); ?>

    <!-- Navegación -->
    <section class="post-navigation">
      <div class="container">
        <a href="blog.php" class="btn-back-to-blog">
          <i class="fas fa-arrow-left"></i>
          Volver al Blog
        </a>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <?php include('includes/footer.inc.php'); ?>

  <!-- Scripts -->
  <script src="node_modules/jquery/dist/jquery.min.js"></script>
  <script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="node_modules/wowjs/dist/wow.min.js"></script>
  <script src="js/app.js"></script>

  <!-- Script para ocultar mensajes automáticamente y modal de imágenes -->
  <script src="js/post-individual.js"></script>
</body>

</html>