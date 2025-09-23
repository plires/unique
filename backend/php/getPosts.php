<?php

/**
 * API para obtener todos los posts para el backend administrativo
 * VERIFICAR que esté llamando a getPostsWithMedia()
 */

require_once('../../includes/config.inc.php');
require_once('../../clases/Posts.php');
require_once('../../clases/ResponseHelper.php');

try {
  $postsModel = new Posts();

  // Parámetros de paginación
  $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
  $perPage = isset($_GET['per_page']) ? max(1, min(50, (int)$_GET['per_page'])) : 10;

  // Parámetros de filtrado existentes
  $includeInactive = isset($_GET['include_inactive']) ? (bool)$_GET['include_inactive'] : true;
  $languageFilter = isset($_GET['language']) ? $_GET['language'] : null;
  $searchQuery = isset($_GET['search']) ? $_GET['search'] : null;

  // Obtener posts con paginación
  $result = $postsModel->getPostsWithMediaPaginated(
    $page,
    $perPage,
    $includeInactive,
    $languageFilter,
    $searchQuery
  );

  ResponseHelper::json($result);
} catch (Exception $e) {
  error_log("Error en getPosts.php: " . $e->getMessage());
  ResponseHelper::serverError('Error al obtener los posts');
}
