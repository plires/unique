<?php

/**
 * API para subir imágenes a posts
 */

require_once('../includes/config.inc.php');
require_once('../clases/PostImages.php');
require_once('../clases/ResponseHelper.php');

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  ResponseHelper::error('Método no permitido', null, 405);
}

try {
  // Validar parámetros
  if (empty($_POST['post_id'])) {
    ResponseHelper::error('ID de post es requerido');
  }

  if (empty($_FILES['image'])) {
    ResponseHelper::error('Archivo de imagen es requerido');
  }

  $postId = (int)$_POST['post_id'];
  $file = $_FILES['image'];

  // Datos adicionales opcionales
  $imageData = [];
  if (!empty($_POST['alt_text'])) {
    $imageData['alt_text'] = $_POST['alt_text'];
  }
  if (!empty($_POST['caption'])) {
    $imageData['caption'] = $_POST['caption'];
  }
  if (isset($_POST['is_featured'])) {
    $imageData['is_featured'] = (int)$_POST['is_featured'];
  }

  $imagesModel = new PostImages();

  // Verificar que el post existe
  $postsModel = new Posts();
  if (!$postsModel->exists($postId)) {
    ResponseHelper::notFound('El post no existe');
  }

  $result = $imagesModel->uploadImage($postId, $file, $imageData);

  if ($result['success']) {
    ResponseHelper::success(
      [
        'id' => $result['id'],
        'filename' => $result['filename'],
        'file_path' => $result['file_path']
      ],
      'Imagen subida exitosamente',
      201
    );
  } else {
    ResponseHelper::validationError($result['errors']);
  }
} catch (Exception $e) {
  error_log("Error en uploadImage.php: " . $e->getMessage());
  ResponseHelper::serverError('Error al procesar la solicitud');
}
