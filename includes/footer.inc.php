<?php

/**
 * Footer reutilizable para todas las páginas
 * Maneja automáticamente idiomas y rutas según la ubicación
 */

// Detectar idioma actual
$currentLang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'es';

// Detectar si estamos en subdirectorio /en/
$isEnglishPath = strpos($_SERVER['REQUEST_URI'], '/en/') !== false;

// Determinar rutas
$homeUrl = $isEnglishPath ? '../en/' : './';
$logoPath = $isEnglishPath ? '../img/logo-unique-footer.png' : 'img/logo-unique-footer.png';
$nivel = $current !== 'home' ? './../' : '';

// Configurar textos del footer
$footerTexts = [
  'es' => [
    'description' => 'Reclutamos talentos para la industria del Turismo y Hotelería',
    'links' => 'Enlaces',
    'home' => 'Inicio',
    'team' => 'Equipo',
    'services' => 'Servicios',
    'blog' => 'Blog',
    'servicesTitle' => 'Servicios',
    'jobs' => 'Empleos',
    'talents' => 'Talentos',
    'followUs' => 'Síguenos',
    'rights' => 'Todos los derechos reservados.',
    'contact' => 'Contacto'
  ],
  'en' => [
    'description' => 'We recruit talents for the Tourism and Hospitality industry',
    'links' => 'Links',
    'home' => 'Home',
    'team' => 'Team',
    'services' => 'Services',
    'blog' => 'Blog',
    'servicesTitle' => 'Services',
    'jobs' => 'Jobs',
    'talents' => 'Talents',
    'followUs' => 'Follow Us',
    'rights' => 'All rights reserved.',
    'contact' => 'Contact'
  ]
];

$texts = $footerTexts[$currentLang];

// Configurar URLs según el contexto
$urls = [
  'home' => $homeUrl,
  'team' => $isEnglishPath ? '../en/#equipo' : '#equipo',
  'services' => $isEnglishPath ? '../en/#servicios' : '#servicios',
  'jobs' => 'https://unique.hiringroom.com/jobs',
  'talents' => $isEnglishPath ? 'hire-talent.php' : '../busca-talento.php',
  'blog' => $isEnglishPath ? '../en/blog.php' : '../blog.php',
  'contact' => $isEnglishPath ? '../en/#contacto' : '#contacto',
  'lang_es' => '/',
  'lang_en' => '/en/'
];
?>

<!-- Footer -->
<footer class="footer_content">
  <div class="container">
    <div class="row">
      <div class="col-lg-3 col-md-6 mb-4 content_column logo">
        <div class="footer-logo mb-3">
          <img src="<?php echo $logoPath; ?>" alt="Unique Talent Solutions">
        </div>
        <p class="footer-description">
          <?php echo $texts['description']; ?>
        </p>
      </div>

      <div class="col-lg-3 col-md-6 mb-4 content_column links">
        <h5 class="footer-title"><?php echo $texts['links']; ?></h5>
        <ul class="footer-links">
          <li><a href="<?php echo $urls['home']; ?>"><?php echo $texts['home']; ?></a></li>
          <li>
            <a data-section="#equipo" class="btn_nav transition" href="<?= $nivel ?><?php echo $urls['team']; ?>">
              <?php echo $texts['team']; ?>
            </a>
          </li>
          <li>
            <a data-section="#servicios" class="btn_nav transition" href="<?= $nivel ?><?php echo $urls['services']; ?>">
              <?php echo $texts['services']; ?>
            </a>
          </li>
          <li><a href="<?php echo $urls['blog']; ?>"><?php echo $texts['blog']; ?></a></li>
          <li>
            <a data-section="#contacto" class="btn_nav transition" href="<?= $nivel ?><?php echo $urls['contact']; ?>">
              <?php echo $texts['contact']; ?>
            </a>
          </li>
        </ul>
      </div>

      <div class="col-lg-3 col-md-6 mb-4 content_column services">
        <h5 class="footer-title"><?php echo $texts['servicesTitle']; ?></h5>
        <ul class="footer-links">
          <li><a href="<?php echo $urls['jobs']; ?>" target="_blank" rel="noopener noreferrer"><?php echo $texts['jobs']; ?></a></li>
          <li><a href="<?php echo $urls['talents']; ?>"><?php echo $texts['talents']; ?></a></li>
        </ul>
      </div>

      <div class="col-lg-3 col-md-6 mb-4 content_column rrss">
        <h5 class="footer-title"><?php echo $texts['followUs']; ?></h5>
        <ul>
          <li class="mt-2"><a class="transition btn_nav" target="_blank" href="https://api.whatsapp.com/send?phone=+5491157550306&text=Hola!%20Necesito%20hacer%20una%20consulta!">+54 9 115 755 0306 <i class="fab fa-whatsapp-square ml-2"></i></a>
          </li>
          <li>
            <a class="transition btn_nav" target="_blank" href="https://www.instagram.com/unqtalent/">Instagram <i class="ml-2 fab fa-instagram-square"></i></a>
          </li>
          <li>
            <a class="transition btn_nav" target="_blank" href="https://www.linkedin.com/company/unqtalent/about/">Linkedin <i class="ml-2 fab fa-linkedin"></i></a>
          </li>
          <li>
            <a class="transition btn_nav" href="https://www.youtube.com/channel/UCy4f2I06PEwGFlZKqvDLRFA" target="_blank" rel="noopener noreferrer">
              Youtube <i class="fab fa-youtube-square"></i>
            </a>
          </li>
        </ul>
      </div>
    </div>

    <hr class="footer-divider">

    <div class="row">
      <div class="col-12 text-center">
        <p class="footer-copyright">
          &copy; <?php echo date('Y'); ?> Unique Talent Solutions.
          <?php echo $texts['rights']; ?>
        </p>
      </div>
    </div>
  </div>
</footer>