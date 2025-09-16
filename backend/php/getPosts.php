<?php

/**
 * API para obtener todos los posts para el backend administrativo
 */

require_once('../../includes/config.inc.php');
require_once('../../clases/Posts.php');
require_once('../../clases/ResponseHelper.php');

try {
  $postsModel = new Posts();

  // Parámetro opcional para incluir inactivos (por defecto: incluir todos)
  $includeInactive = isset($_GET['include_inactive']) ? (bool)$_GET['include_inactive'] : true;

  // Obtener todos los posts
  $posts = $postsModel->getPostsWithMedia($includeInactive);

  ResponseHelper::json($posts);
} catch (Exception $e) {
  error_log("Error en getPosts.php: " . $e->getMessage());
  ResponseHelper::serverError('Error al obtener los posts');
}
