<?php

/**
 * Header reutilizable para todas las páginas
 */
?>

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
        <li><a class="btn_nav transition" href="/#equipo">EQUIPO</a></li>
        <li><a class="btn_nav transition" href="/#servicios">SERVICIOS</a></li>
        <li><a class="transition" href="https://unique.hiringroom.com/jobs" target="_blank" rel="noopener noreferrer">EMPLEOS</a></li>
        <li><a class="transition" href="/busca-talento.php">TALENTOS</a></li>
        <li><a class="transition" href="/blog.php">BLOG</a></li>
        <li><a class="btn_nav transition" href="/#contacto">CONTACTO</a></li>
      </ul>
    </nav>
    <div class="languages_rrss">
      <div>
        <?php $activeES = $_SESSION['lang'] == 'es' ? 'active' : ''; ?>
        <?php $activeEN = $_SESSION['lang'] == 'en' ? 'active' : ''; ?>
        <a class="btn_language transition <?php echo $activeES; ?>" href="/">ES</a>
        <a class="btn_language transition <?php echo $activeEN; ?>" href="/en/">EN</a>
      </div>
      <div>
        <a class="transition" href="https://www.linkedin.com/company/unique-talent-solutions/" target="_blank" rel="noopener noreferrer">
          <i class="fab fa-linkedin-in"></i>
        </a>
        <a class="transition" href="https://www.instagram.com/uniquetalentsolutions/" target="_blank" rel="noopener noreferrer">
          <i class="fab fa-instagram"></i>
        </a>
      </div>
    </div>
  </div>
</header>