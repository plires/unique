<?php

/**
 * API para obtener posts activos para el front público
 */

require_once('../../includes/config.inc.php');
require_once('../../clases/Posts.php');
require_once('../../clases/ResponseHelper.php');

// Permitir CORS para el front público
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
  $postsModel = new Posts();

  // Parámetros opcionales para el front
  $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
  $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

  // Solo posts activos para el público
  $posts = $postsModel->getPostsForPublic($limit, $page);

  ResponseHelper::json($posts);
} catch (Exception $e) {
  error_log("Error en getPostsPublic.php: " . $e->getMessage());
  ResponseHelper::serverError('Error al obtener las noticias');
}
