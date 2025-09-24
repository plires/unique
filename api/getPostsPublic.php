<?php

/**
 * API para obtener posts activos para el blog público con paginación
 */

require_once('../includes/config.inc.php');
require_once('../clases/PostsPublic.php');
require_once('../clases/ResponseHelper.php');

// Permitir CORS para el front público
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

try {
  $postsPublic = new PostsPublic();

  // Parámetros de paginación
  $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
  $perPage = isset($_GET['per_page']) ? max(1, min(50, (int)$_GET['per_page'])) : 9;
  $language = isset($_GET['language']) ? $_GET['language'] : 'es';

  // Obtener posts paginados
  $result = $postsPublic->getPostsPaginated($page, $perPage, $language);

  // Procesar cada post para agregar datos adicionales
  foreach ($result['data'] as $index => $post) {
    // Generar URL de la imagen
    $result['data'][$index]['image_url'] = $postsPublic->getImageUrl($post['listing_image_path']);

    // Formatear fecha según el idioma
    $result['data'][$index]['formatted_date'] = $postsPublic->formatDate($post['created_at'], $language);

    // Generar resumen del contenido
    $result['data'][$index]['excerpt'] = $postsPublic->getContentExcerpt($post['content'], 120);

    // Generar URL para ver el post completo (placeholder por ahora)
    $result['data'][$index]['view_url'] = '#';

    // Texto del botón según idioma
    if ($language === 'es') {
      $result['data'][$index]['button_text'] = 'Leer más';
      $result['data'][$index]['read_more_aria'] = 'Leer más sobre ' . htmlspecialchars($post['title']);
    } else {
      $result['data'][$index]['button_text'] = 'Read more';
      $result['data'][$index]['read_more_aria'] = 'Read more about ' . htmlspecialchars($post['title']);
    }
  }

  // Respuesta exitosa
  echo json_encode([
    'success' => true,
    'data' => $result['data'],
    'pagination' => $result['pagination'],
    'message' => 'Posts cargados correctamente'
  ]);
} catch (Exception $e) {
  error_log("Error en getPostsPublic.php: " . $e->getMessage());

  echo json_encode([
    'success' => false,
    'data' => [],
    'pagination' => [
      'current_page' => 1,
      'total_pages' => 0,
      'total' => 0,
      'per_page' => 9,
      'has_prev' => false,
      'has_next' => false,
      'showing_from' => 0,
      'showing_to' => 0
    ],
    'message' => 'Error al obtener los posts: ' . $e->getMessage()
  ]);
}
