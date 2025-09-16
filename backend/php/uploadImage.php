<?php

/**
 * API para subir imágenes de posts
 */

require_once('../../includes/config.inc.php');
require_once('../../clases/PostImages.php');
require_once('../../clases/ResponseHelper.php');

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  ResponseHelper::error('Método no permitido', null, 405);
}

try {
  // Validar campos requeridos
  if (empty($_POST['post_id'])) {
    ResponseHelper::error('ID de post es requerido');
  }

  if (!isset($_FILES['image'])) {
    ResponseHelper::error('Debe seleccionar una imagen');
  }

  $postId = (int)$_POST['post_id'];
  $imageType = $_POST['type'] ?? 'content';
  $altText = $_POST['alt_text'] ?? '';

  // Validar tipo de imagen
  if (!in_array($imageType, ['listing', 'header', 'content'])) {
    ResponseHelper::error('Tipo de imagen inválido');
  }

  $postImages = new PostImages();

  // Datos adicionales de la imagen
  $imageData = [
    'type' => $imageType,
    'alt_text' => $altText
  ];

  $result = $postImages->uploadImage($postId, $_FILES['image'], $imageData);

  if ($result['success']) {
    ResponseHelper::success($result, 'Imagen subida exitosamente');
  } else {
    ResponseHelper::error('Error al subir imagen', $result['errors']);
  }
} catch (Exception $e) {
  error_log("Error en uploadImage.php: " . $e->getMessage());
  ResponseHelper::serverError('Error al procesar la solicitud');
}
