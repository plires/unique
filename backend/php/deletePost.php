<?php

/**
 * API para eliminar posts
 */

require_once('../includes/config.inc.php');
require_once('../clases/Posts.php');
require_once('../clases/ResponseHelper.php');

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  ResponseHelper::error('Método no permitido', null, 405);
}

try {
  if (empty($_POST['id'])) {
    ResponseHelper::error('ID de post es requerido');
  }

  $id = (int)$_POST['id'];
  $postsModel = new Posts();

  $result = $postsModel->deletePost($id);

  if ($result['success']) {
    ResponseHelper::success(null, 'Post eliminado exitosamente');
  } else {
    ResponseHelper::error('Error al eliminar post', $result['errors']);
  }
} catch (Exception $e) {
  error_log("Error en deletePost.php: " . $e->getMessage());
  ResponseHelper::serverError('Error al procesar la solicitud');
}
