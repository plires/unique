<?php

/**
 * Footer reutilizable para todas las páginas
 */

$currentLang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'es';
?>

<!-- Footer -->
<footer>
  <div class="container">
    <div class="row">
      <div class="col-lg-4 col-md-6 mb-4">
        <div class="footer-logo">
          <img src="img/logo-unique-footer.png" alt="Unique Talent Solutions">
        </div>
        <p class="footer-description">
          <?php if ($currentLang === 'es'): ?>
            Reclutamos talentos para la industria del Turismo y Hotelería
          <?php else: ?>
            We recruit talents for the Tourism and Hospitality industry
          <?php endif; ?>
        </p>
      </div>
      <div class="col-lg-2 col-md-6 mb-4">
        <h5><?php echo $currentLang === 'es' ? 'Enlaces' : 'Links'; ?></h5>
        <ul class="footer-links">
          <li><a href="/"><?php echo $currentLang === 'es' ? 'Inicio' : 'Home'; ?></a></li>
          <li><a href="/#equipo"><?php echo $currentLang === 'es' ? 'Equipo' : 'Team'; ?></a></li>
          <li><a href="/#servicios"><?php echo $currentLang === 'es' ? 'Servicios' : 'Services'; ?></a></li>
          <li><a href="/blog.php">Blog</a></li>
        </ul>
      </div>
      <div class="col-lg-3 col-md-6 mb-4">
        <h5><?php echo $currentLang === 'es' ? 'Servicios' : 'Services'; ?></h5>
        <ul class="footer-links">
          <li><a href="https://unique.hiringroom.com/jobs" target="_blank"><?php echo $currentLang === 'es' ? 'Empleos' : 'Jobs'; ?></a></li>
          <li><a href="/busca-talento.php"><?php echo $currentLang === 'es' ? 'Talentos' : 'Talents'; ?></a></li>
        </ul>
      </div>
      <div class="col-lg-3 col-md-6 mb-4">
        <h5><?php echo $currentLang === 'es' ? 'Síguenos' : 'Follow Us'; ?></h5>
        <div class="footer-social">
          <a href="https://www.linkedin.com/company/unique-talent-solutions/" target="_blank" rel="noopener noreferrer">
            <i class="fab fa-linkedin-in"></i>
          </a>
          <a href="https://www.instagram.com/uniquetalentsolutions/" target="_blank" rel="noopener noreferrer">
            <i class="fab fa-instagram"></i>
          </a>
        </div>
      </div>
    </div>
    <hr>
    <div class="row">
      <div class="col-12 text-center">
        <p>&copy; <?php echo date('Y'); ?> Unique Talent Solutions.
          <?php echo $currentLang === 'es' ? 'Todos los derechos reservados.' : 'All rights reserved.'; ?></p>
      </div>
    </div>
  </div>
</footer>