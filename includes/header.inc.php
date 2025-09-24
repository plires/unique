<?php

/**
 * Header reutilizable para todas las páginas
 * Maneja automáticamente idiomas y rutas según la ubicación
 */

// Detectar idioma actual
$currentLang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'es';

// Detectar si estamos en subdirectorio /en/
$isEnglishPath = strpos($_SERVER['REQUEST_URI'], '/en/') !== false;

// Determinar rutas base
$baseUrl = $isEnglishPath ? '../' : './';
$homeUrl = $isEnglishPath ? '../en/' : './';
$logoPath = $isEnglishPath ? '../img/logo-unique.png' : 'img/logo-unique.png';

// Configurar textos del menú
$menuTexts = [
  'es' => [
    'team' => 'EQUIPO',
    'services' => 'SERVICIOS',
    'jobs' => 'EMPLEOS',
    'talents' => 'TALENTOS',
    'blog' => 'BLOG',
    'contact' => 'CONTACTO'
  ],
  'en' => [
    'team' => 'TEAM',
    'services' => 'SERVICES',
    'jobs' => 'JOBS',
    'talents' => 'TALENTS',
    'blog' => 'BLOG',
    'contact' => 'CONTACT'
  ]
];

$texts = $menuTexts[$currentLang];

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

// Estados activos para idiomas
$activeES = $currentLang == 'es' ? 'active' : '';
$activeEN = $currentLang == 'en' ? 'active' : '';

$nivel = $current !== 'home' ? './../' : '';

?>

<!-- Header -->
<header class="container transition">
  <div class="menu_bar">
    <a href="<?php echo $urls['home']; ?>">
      <img class="transition" src="<?php echo $logoPath; ?>" alt="logo unique">
    </a>
    <button class="transition" id="btn-menu-mobile" type="button"><i class="fas fa-bars"></i></button>
  </div>
  <div class="content_navegacion">
    <nav>
      <ul>
        <li><a data-section="#equipo" class="btn_nav transition" href="<?= $nivel ?><?php echo $urls['team']; ?>"><?php echo $texts['team']; ?></a></li>
        <li><a data-section="#servicios" class="btn_nav transition" href="<?= $nivel ?><?php echo $urls['services']; ?>"><?php echo $texts['services']; ?></a></li>
        <li><a class="transition" href="<?php echo $urls['jobs']; ?>" target="_blank" rel="noopener noreferrer"><?php echo $texts['jobs']; ?></a></li>
        <li><a class="transition <?php echo $current === 'talento' ? 'active' : ''; ?>" href="<?php echo $urls['talents']; ?>"><?php echo $texts['talents']; ?></a></li>
        <li><a class="transition <?php echo $current === 'blog' ? 'active' : ''; ?>" href="<?php echo $urls['blog']; ?>"><?php echo $texts['blog']; ?></a></li>
        <li><a data-section="#contacto" class="btn_nav transition" href="<?= $nivel ?><?php echo $urls['contact']; ?>"><?php echo $texts['contact']; ?></a></li>
      </ul>
    </nav>
    <div class="languages_rrss">
      <div>
        <a class="btn_language transition <?php echo $activeES; ?>" href="<?php echo $urls['lang_es']; ?>">ES</a>
        <a class="btn_language transition <?php echo $activeEN; ?>" href="<?php echo $urls['lang_en']; ?>">EN</a>
      </div>
      <?php include($baseUrl . 'includes/rrss.php'); ?>
    </div>
  </div>
</header>