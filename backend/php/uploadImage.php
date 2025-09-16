<?php

/**
 * API para subir imágenes de posts - CON OPTIMIZACIÓN AUTOMÁTICA
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
    ResponseHelper::error('Tipo de imagen inválido. Tipos permitidos: listing, header, content');
  }

  error_log("=== INICIO UPLOAD API ===");
  error_log("Post ID: " . $postId);
  error_log("Tipo: " . $imageType);
  error_log("Archivo: " . $_FILES['image']['name']);

  $postImages = new PostImages();

  // Datos adicionales de la imagen
  $imageData = [
    'type' => $imageType,
    'alt_text' => $altText
  ];

  $result = $postImages->uploadImage($postId, $_FILES['image'], $imageData);

  if ($result['success']) {
    error_log("Upload exitoso: " . print_r($result, true));

    // Preparar respuesta con información de optimización
    $responseData = [
      'image_id' => $result['image_id'],
      'filename' => $result['filename'],
      'file_path' => $result['file_path'],
      'width' => $result['width'] ?? null,
      'height' => $result['height'] ?? null,
      'size' => $result['size'] ?? null,
      'format' => $result['format'] ?? 'webp',
      'type' => $result['type'],
      'optimized' => $result['optimized'] ?? false
    ];

    ResponseHelper::success($responseData, 'Imagen subida y optimizada exitosamente');
  } else {
    error_log("Error en upload: " . print_r($result['errors'], true));
    ResponseHelper::error('Error al subir imagen', $result['errors']);
  }
} catch (Exception $e) {
  error_log("Error en uploadImage.php: " . $e->getMessage());
  error_log("Stack trace: " . $e->getTraceAsString());
  ResponseHelper::serverError('Error al procesar la solicitud');
}
