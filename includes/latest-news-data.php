<?php

/**
 * Archivo para obtener los datos de los últimos posts
 * Se incluye en index.php de ambos idiomas
 */

// Incluir la clase PostsPublic si no está ya incluida
if (!class_exists('PostsPublic')) {
  require_once(__DIR__ . '/../clases/PostsPublic.php');
}

// Inicializar variables
$latestPosts = [];
$hasLatestPosts = false;

try {
  // Crear instancia de la clase PostsPublic
  $postsPublic = new PostsPublic();

  // Obtener los últimos 3 posts
  $currentLang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'es';
  $latestPosts = $postsPublic->getLatestPosts(3, $currentLang);

  // Verificar si hay posts disponibles
  $hasLatestPosts = !empty($latestPosts);

  // Procesar cada post para agregar datos adicionales
  foreach ($latestPosts as $index => $post) {
    // Generar URL de la imagen
    $latestPosts[$index]['image_url'] = $postsPublic->getImageUrl($post['listing_image_path']);

    // Formatear fecha según el idioma actual
    $currentLang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'es';
    $latestPosts[$index]['formatted_date'] = $postsPublic->formatDate($post['created_at'], $currentLang);

    // Generar resumen del contenido
    $latestPosts[$index]['excerpt'] = $postsPublic->getContentExcerpt($post['content'], 120);

    // Generar URL para ver el post completo (a implementar más adelante)
    $latestPosts[$index]['view_url'] = '#'; // Por ahora será un placeholder

    // Texto del botón según idioma
    if ($currentLang === 'es') {
      $latestPosts[$index]['button_text'] = 'Leer más';
      $latestPosts[$index]['read_more_aria'] = 'Leer más sobre ' . htmlspecialchars($post['title']);
    } else {
      $latestPosts[$index]['button_text'] = 'Read more';
      $latestPosts[$index]['read_more_aria'] = 'Read more about ' . htmlspecialchars($post['title']);
    }
  }

  // IMPORTANTE: Limpiar cualquier referencia que pueda quedar
  unset($post);
} catch (Exception $e) {
  // En caso de error, registrar el error y continuar sin posts
  error_log("Error al obtener posts para index: " . $e->getMessage());
  $latestPosts = [];
  $hasLatestPosts = false;
}

// Función auxiliar para mostrar imagen o placeholder
function renderPostImage($post, $lang = 'es')
{
  if (!empty($post['image_url'])) {
    $alt = !empty($post['listing_image_alt'])
      ? htmlspecialchars($post['listing_image_alt'])
      : htmlspecialchars($post['title']);

    return '<img src="' . htmlspecialchars($post['image_url']) . '" 
                     alt="' . $alt . '" 
                     loading="lazy">';
  } else {
    // Placeholder cuando no hay imagen
    $placeholderText = $lang === 'es' ? 'Sin imagen' : 'No image';
    return '<div class="news-card-image-placeholder">
                    <i class="fas fa-image" aria-hidden="true"></i>
                    <span class="sr-only">' . $placeholderText . '</span>
                </div>';
  }
}

// Función para obtener textos según idioma
function getLatestNewsTexts($lang = 'es')
{
  if ($lang === 'es') {
    return [
      'title' => 'Últimas Novedades',
      'subtitle' => 'Mantente al día con las últimas noticias y novedades de Unique Talent Solutions',
      'empty_title' => 'No hay novedades disponibles',
      'empty_description' => 'Pronto publicaremos nuevas noticias y actualizaciones.',
      'date_prefix' => 'Publicado el'
    ];
  } else {
    return [
      'title' => 'Latest News',
      'subtitle' => 'Stay up to date with the latest news and updates from Unique Talent Solutions',
      'empty_title' => 'No news available',
      'empty_description' => 'We will publish new news and updates soon.',
      'date_prefix' => 'Published on'
    ];
  }
}
