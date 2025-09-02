<?php

/**
 * API para obtener un post específico con todas sus imágenes y videos
 */

require_once('../includes/config.inc.php');
require_once('../clases/Posts.php');
require_once('../clases/ResponseHelper.php');

// Verificar que sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  ResponseHelper::error('Método no permitido', null, 405);
}

try {
  if (empty($_GET['id'])) {
    ResponseHelper::error('ID de post es requerido');
  }

  $id = (int)$_GET['id'];
  $postsModel = new Posts();

  // Obtener post completo con imágenes y videos
  $post = $postsModel->getPostComplete($id);

  if (!$post) {
    ResponseHelper::notFound('Post no encontrado');
  }

  ResponseHelper::json($post);
} catch (Exception $e) {
  error_log("Error en getPost.php: " . $e->getMessage());
  ResponseHelper::serverError('Error al obtener el post');
}
