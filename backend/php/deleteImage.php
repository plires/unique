<?php

/**
 * API para eliminar imágenes de posts
 */

require_once('../includes/config.inc.php');
require_once('../clases/PostImages.php');
require_once('../clases/ResponseHelper.php');

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  ResponseHelper::error('Método no permitido', null, 405);
}

try {
  if (empty($_POST['id'])) {
    ResponseHelper::error('ID de imagen es requerido');
  }

  $id = (int)$_POST['id'];
  $imagesModel = new PostImages();

  $result = $imagesModel->deleteImage($id);

  if ($result['success']) {
    ResponseHelper::success(null, 'Imagen eliminada exitosamente');
  } else {
    ResponseHelper::error('Error al eliminar imagen', $result['errors']);
  }
} catch (Exception $e) {
  error_log("Error en deleteImage.php: " . $e->getMessage());
  ResponseHelper::serverError('Error al procesar la solicitud');
}
