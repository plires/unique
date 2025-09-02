
<?php
/**
 * API para obtener todos los posts con información de medios
 */

require_once('../../includes/config.inc.php');
require_once('../clases/Posts.php');
require_once('../clases/ResponseHelper.php');

try {
  $postsModel = new Posts();

  // Parámetros opcionales
  $includeInactive = isset($_GET['include_inactive']) ? (bool)$_GET['include_inactive'] : false;
  $search = isset($_GET['search']) ? trim($_GET['search']) : '';

  if (!empty($search)) {
    // Búsqueda
    $posts = $postsModel->searchPosts($search, $includeInactive);
  } else {
    // Obtener todos
    $posts = $postsModel->getPostsWithMedia($includeInactive);
  }

  ResponseHelper::json($posts);
} catch (Exception $e) {
  error_log("Error en getPosts.php: " . $e->getMessage());
  ResponseHelper::serverError('Error al obtener los posts');
}
?>