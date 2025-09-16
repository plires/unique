<?php

/**
 * Modelo para la gestión de Imágenes de Posts - VERSIÓN ACTUALIZADA
 */

require_once 'BaseCRUD.php';

class PostImages extends BaseCRUD
{

  protected $fillable = [
    'post_id',
    'filename',
    'file_path',
    'alt_text',
    'type',        // NUEVO CAMPO
    'sort_order',
    'is_featured',
    'status'
  ];

  protected $timestamps = true;

  public function __construct()
  {
    parent::__construct('post_images'); // TABLA RENOMBRADA
  }

  /**
   * Subir y guardar imagen con validación por tipo
   */
  public function uploadImage($postId, $file, $imageData = [])
  {
    error_log("=== INICIO UPLOAD IMAGEN ===");

    // Validar archivo
    $validation = $this->validateImageFile($file);
    if (!$validation['valid']) {
      return ['success' => false, 'errors' => $validation['errors']];
    }

    // Validar tipo de imagen
    $type = $imageData['type'] ?? 'content';
    if (!in_array($type, ['listing', 'header', 'content'])) {
      return ['success' => false, 'errors' => ['Tipo de imagen inválido']];
    }

    // Validar límites por tipo
    $validationResult = $this->validateImageTypeLimit($postId, $type);
    if (!$validationResult['valid']) {
      return ['success' => false, 'errors' => $validationResult['errors']];
    }

    try {
      // Crear directorio si no existe
      $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/' . UPLOAD_PATH_IMAGES;
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
      }

      // Generar nombre único
      $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
      $filename = uniqid($type . '_') . '_' . time() . '.' . $extension;
      $filepath = $uploadDir . $filename;

      error_log("Subiendo archivo a: " . $filepath);

      // Mover archivo
      if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'errors' => ['Error al subir el archivo']];
      }

      // Guardar en base de datos
      $imageRecord = [
        'post_id' => $postId,
        'filename' => $filename,
        'file_path' => UPLOAD_PATH_IMAGES . $filename,
        'alt_text' => $imageData['alt_text'] ?? '',
        'type' => $type,
        'sort_order' => $this->getNextSortOrder($postId, $type),
        'is_featured' => ($type === 'listing') ? 1 : 0, // Solo listing puede ser featured por defecto
        'status' => 1
      ];

      $imageId = $this->create($imageRecord);

      if ($imageId) {
        error_log("Imagen guardada con ID: " . $imageId);
        return [
          'success' => true,
          'image_id' => $imageId,
          'filename' => $filename,
          'file_path' => UPLOAD_PATH_IMAGES . $filename
        ];
      } else {
        // Si falla la BD, eliminar archivo
        @unlink($filepath);
        return ['success' => false, 'errors' => ['Error al guardar en base de datos']];
      }
    } catch (Exception $e) {
      error_log("Error subiendo imagen: " . $e->getMessage());
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

    // Eliminar de base de datos
    $success = $this->delete($id);

    if ($success) {
      return ['success' => true];
    } else {
      return ['success' => false, 'errors' => ['Error al eliminar imagen']];
    }
  }

  /**
   * Actualizar información de imagen
   */
  public function updateImageInfo($id, $data)
  {
    $allowedFields = ['alt_text', 'sort_order', 'is_featured', 'status'];
    $updateData = [];

    foreach ($allowedFields as $field) {
      if (isset($data[$field])) {
        $updateData[$field] = $data[$field];
      }
    }

    if (empty($updateData)) {
      return ['success' => false, 'errors' => ['No hay datos para actualizar']];
    }

    $success = $this->update($id, $updateData);

    if ($success) {
      return ['success' => true];
    } else {
      return ['success' => false, 'errors' => ['Error al actualizar imagen']];
    }
  }

  /**
   * Obtener siguiente número de orden por tipo
   */
  private function getNextSortOrder($postId, $type)
  {
    $result = $this->queryOne(
      "SELECT COALESCE(MAX(sort_order), 0) + 1 as next_order FROM post_images WHERE post_id = :post_id AND type = :type",
      ['post_id' => $postId, 'type' => $type]
    );

    return $result['next_order'];
  }

  /**
   * Establecer imagen destacada (solo para tipo 'listing')
   */
  public function setFeaturedImage($postId, $imageId)
  {
    try {
      $this->db->beginTransaction();

      // Verificar que la imagen sea de tipo 'listing'
      $image = $this->getById($imageId);
      if (!$image || $image['type'] !== 'listing') {
        $this->db->rollback();
        return ['success' => false, 'errors' => ['Solo las imágenes de listado pueden ser destacadas']];
      }

      // Quitar featured de todas las imágenes de listing del post
      $this->db->execute(
        "UPDATE post_images SET is_featured = 0 WHERE post_id = :post_id AND type = 'listing'",
        ['post_id' => $postId]
      );

      // Establecer la nueva imagen destacada
      $success = $this->update($imageId, ['is_featured' => 1]);

      if ($success) {
        $this->db->commit();
        return ['success' => true];
      } else {
        $this->db->rollback();
        return ['success' => false, 'errors' => ['Error al establecer imagen destacada']];
      }
    } catch (Exception $e) {
      $this->db->rollback();
      error_log("Error estableciendo imagen destacada: " . $e->getMessage());
      return ['success' => false, 'errors' => ['Error interno']];
    }
  }

  /**
   * Actualizar orden de las imágenes por tipo
   */
  public function updateSortOrder($imageIds, $type = null)
  {
    try {
      $this->db->beginTransaction();

      foreach ($imageIds as $order => $imageId) {
        // Verificar tipo si se especifica
        if ($type) {
          $image = $this->getById($imageId);
          if ($image['type'] !== $type) {
            continue; // Saltar si no es del tipo correcto
          }
        }

        $this->update($imageId, ['sort_order' => $order + 1]);
      }

      $this->db->commit();
      return ['success' => true];
    } catch (Exception $e) {
      $this->db->rollback();
      error_log("Error actualizando orden: " . $e->getMessage());
      return ['success' => false, 'errors' => ['Error al actualizar orden']];
    }
  }

  /**
   * Obtener estadísticas de imágenes
   */
  public function getStats()
  {
    $total = $this->count();
    $active = $this->count('status = 1');
    $byType = [
      'listing' => $this->count("type = 'listing' AND status = 1"),
      'header' => $this->count("type = 'header' AND status = 1"),
      'content' => $this->count("type = 'content' AND status = 1")
    ];

    return [
      'total' => $total,
      'active' => $active,
      'by_type' => $byType
    ];
  }
}
