<?php

/**
 * API para cambiar estado de posts (activo/inactivo)
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

  // Verificar que el post existe
  if (!$postsModel->exists($id)) {
    ResponseHelper::notFound('El post no existe');
  }

  // Cambiar estado
  $success = $postsModel->togglePostStatus($id);

  if ($success) {
    ResponseHelper::success(null, 'Estado del post cambiado exitosamente');
  } else {
    ResponseHelper::serverError('Error al cambiar el estado del post');
  }
} catch (Exception $e) {
  error_log("Error en changeStatusPost.php: " . $e->getMessage());
  ResponseHelper::serverError('Error al procesar la solicitud');
}
