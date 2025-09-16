<?php

/**
 * Modelo para la gestión de Imágenes de Posts - CON OPTIMIZACIÓN AUTOMÁTICA
 */

require_once 'BaseCRUD.php';
require_once 'ImageManager.php';

class PostImages extends BaseCRUD
{
  protected $fillable = [
    'post_id',
    'filename',
    'file_path',
    'alt_text',
    'type',
    'sort_order',
    'is_featured',
    'status'
  ];

  protected $timestamps = true;
  private $imageManager;

  public function __construct()
  {
    parent::__construct('post_images');
    $this->imageManager = new ImageManager();
  }

  /**
   * Subir y guardar imagen con optimización automática
   */
  public function uploadImage($postId, $file, $imageData = [])
  {
    error_log("=== INICIO UPLOAD IMAGEN CON OPTIMIZACIÓN ===");

    // Validar archivo
    $validation = $this->validateImageFile($file);
    if (!$validation['valid']) {
      return ['success' => false, 'errors' => $validation['errors']];
    }

    // Validar tipo de imagen
    $type = $imageData['type'] ?? 'content';
    if (!$this->imageManager->isValidType($type)) {
      return ['success' => false, 'errors' => ['Tipo de imagen inválido: ' . $type]];
    }

    // Validar límites por tipo
    $validationResult = $this->validateImageTypeLimit($postId, $type);
    if (!$validationResult['valid']) {
      return ['success' => false, 'errors' => $validationResult['errors']];
    }

    try {
      // Crear directorio temporal para procesamiento
      $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/' . UPLOAD_PATH_IMAGES;
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
      }

      // Generar nombre temporal para el archivo original
      $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
      $tempFilename = 'temp_' . uniqid() . '.' . $extension;
      $tempFilepath = $uploadDir . $tempFilename;

      error_log("Subiendo archivo temporal a: " . $tempFilepath);

      // Mover archivo temporal
      if (!move_uploaded_file($file['tmp_name'], $tempFilepath)) {
        return ['success' => false, 'errors' => ['Error al subir el archivo']];
      }

      // Procesar imagen con ImageManager
      error_log("Procesando imagen con tipo: " . $type);
      $processingResult = $this->imageManager->processImageByType(
        $tempFilepath,
        $file['name'],
        $type
      );

      // Eliminar archivo temporal
      @unlink($tempFilepath);

      if (!$processingResult['success']) {
        return [
          'success' => false,
          'errors' => ['Error al optimizar imagen: ' . ($processingResult['error'] ?? 'Error desconocido')]
        ];
      }

      error_log("Imagen procesada exitosamente: " . print_r($processingResult, true));

      // Guardar registro en base de datos
      $imageRecord = [
        'post_id' => $postId,
        'filename' => $processingResult['filename'],
        'file_path' => $processingResult['path'],
        'alt_text' => $imageData['alt_text'] ?? '',
        'type' => $type,
        'sort_order' => $this->getNextSortOrder($postId, $type),
        'is_featured' => ($type === 'listing') ? 1 : 0,
        'status' => 1
      ];

      $imageId = $this->create($imageRecord);

      if ($imageId) {
        error_log("Imagen guardada con ID: " . $imageId);

        // Añadir información de procesamiento al resultado
        $finalResult = [
          'success' => true,
          'image_id' => $imageId,
          'filename' => $processingResult['filename'],
          'file_path' => $processingResult['path'],
          'width' => $processingResult['width'],
          'height' => $processingResult['height'],
          'size' => $processingResult['size'],
          'format' => $processingResult['format'],
          'type' => $type,
          'optimized' => true
        ];

        error_log("=== UPLOAD COMPLETADO EXITOSAMENTE ===");
        return $finalResult;
      } else {
        // Si falla la BD, eliminar archivo procesado
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $processingResult['path'];
        @unlink($fullPath);
        return ['success' => false, 'errors' => ['Error al guardar en base de datos']];
      }
    } catch (Exception $e) {
      error_log("Error en uploadImage: " . $e->getMessage());
      error_log("Stack trace: " . $e->getTraceAsString());

      // Limpiar archivos temporales en caso de error
      if (isset($tempFilepath)) {
        @unlink($tempFilepath);
      }

      return ['success' => false, 'errors' => ['Error interno: ' . $e->getMessage()]];
    }
  }

  /**
   * Validar límites por tipo de imagen
   */
  private function validateImageTypeLimit($postId, $type)
  {
    $errors = [];

    if ($type === 'listing') {
      $existing = $this->count("post_id = :post_id AND type = 'listing' AND status = 1", ['post_id' => $postId]);
      if ($existing >= 1) {
        $errors[] = 'Solo se permite una imagen para listado por post';
      }
    } elseif ($type === 'header') {
      $existing = $this->count("post_id = :post_id AND type = 'header' AND status = 1", ['post_id' => $postId]);
      if ($existing >= 1) {
        $errors[] = 'Solo se permite una imagen de header por post';
      }
    }
    // 'content' permite múltiples imágenes

    return ['valid' => empty($errors), 'errors' => $errors];
  }

  /**
   * Validar archivo de imagen
   */
  private function validateImageFile($file)
  {
    $errors = [];

    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
      $errors[] = 'No se ha subido ningún archivo válido';
      return ['valid' => false, 'errors' => $errors];
    }

    // Verificar tamaño
    if ($file['size'] > MAX_IMAGE_SIZE) {
      $errors[] = 'El archivo es demasiado grande. Máximo: ' . (MAX_IMAGE_SIZE / 1024 / 1024) . 'MB';
    }

    // Verificar tipo de archivo
    $allowedTypes = explode(',', ALLOWED_IMAGE_TYPES);
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedTypes)) {
      $errors[] = 'Tipo de archivo no permitido. Permitidos: ' . ALLOWED_IMAGE_TYPES;
    }

    // Verificar que sea realmente una imagen
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
      $errors[] = 'El archivo no es una imagen válida';
    }

    return ['valid' => empty($errors), 'errors' => $errors];
  }

  /**
   * Obtener siguiente orden para un tipo específico
   */
  private function getNextSortOrder($postId, $type)
  {
    $maxOrder = $this->queryOne(
      "SELECT MAX(sort_order) as max_order FROM post_images WHERE post_id = :post_id AND type = :type",
      ['post_id' => $postId, 'type' => $type]
    );

    return ($maxOrder['max_order'] ?? 0) + 1;
  }

  /**
   * Obtener imágenes de un post agrupadas por tipo
   */
  public function getPostImagesByType($postId, $includeInactive = false)
  {
    $conditions = ['post_id = :post_id'];
    $params = ['post_id' => $postId];

    if (!$includeInactive) {
      $conditions[] = 'status = 1';
    }

    $where = implode(' AND ', $conditions);
    $images = $this->getWhere($where, $params, 'type, sort_order', 'ASC');

    // Agrupar por tipo
    $grouped = [
      'listing' => [],
      'header' => [],
      'content' => []
    ];

    foreach ($images as $image) {
      $grouped[$image['type']][] = $image;
    }

    return $grouped;
  }

  /**
   * Obtener imagen específica por tipo
   */
  public function getImageByType($postId, $type)
  {
    return $this->queryOne(
      "SELECT * FROM post_images WHERE post_id = :post_id AND type = :type AND status = 1 ORDER BY sort_order LIMIT 1",
      ['post_id' => $postId, 'type' => $type]
    );
  }

  /**
   * Eliminar imagen con archivo físico
   */
  public function deleteImage($id)
  {
    $image = $this->getById($id);
    if (!$image) {
      return ['success' => false, 'errors' => ['Imagen no encontrada']];
    }

    // Eliminar archivo físico
    $absolutePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $image['file_path'];

    if (file_exists($absolutePath)) {
      if (@unlink($absolutePath)) {
        error_log("Imagen eliminada: " . $absolutePath);
      } else {
        error_log("No se pudo eliminar la imagen: " . $absolutePath);
      }
    } else {
      error_log("Archivo no existe: " . $absolutePath);
    }

    // Eliminar registro de BD
    if ($this->delete($id)) {
      return ['success' => true, 'message' => 'Imagen eliminada exitosamente'];
    } else {
      return ['success' => false, 'errors' => ['Error al eliminar registro de base de datos']];
    }
  }
}
