<?php

/**
 * Modelo para la gestión de Imágenes de Posts
 */

require_once 'BaseCRUD.php';

class PostImages extends BaseCRUD
{

  protected $fillable = [
    'post_id',
    'filename',
    'original_name',
    'file_path',
    'file_size',
    'mime_type',
    'alt_text',
    'caption',
    'sort_order',
    'is_featured',
    'status'
  ];

  protected $timestamps = true;

  public function __construct()
  {
    parent::__construct('images_id');
  }

  /**
   * Subir y guardar imagen
   */
  public function uploadImage($postId, $file, $imageData = [])
  {
    // Validar archivo
    $validation = $this->validateImageFile($file);
    if (!$validation['valid']) {
      return ['success' => false, 'errors' => $validation['errors']];
    }

    try {
      // Crear directorio si no existe
      $uploadDir = UPLOAD_PATH_IMAGES;
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
      }

      // Generar nombre único
      $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
      $filename = uniqid('img_') . '_' . time() . '.' . $extension;
      $filePath = $uploadDir . $filename;

      // Mover archivo
      if (move_uploaded_file($file['tmp_name'], $filePath)) {

        // Obtener siguiente orden
        $nextOrder = $this->getNextSortOrder($postId);

        // Guardar en base de datos
        $imageData = array_merge([
          'post_id' => $postId,
          'filename' => $filename,
          'original_name' => $file['name'],
          'file_path' => $filePath,
          'file_size' => $file['size'],
          'mime_type' => $file['type'],
          'sort_order' => $nextOrder,
          'status' => 1
        ], $imageData);

        $imageId = $this->create($imageData);

        if ($imageId) {
          return [
            'success' => true,
            'id' => $imageId,
            'filename' => $filename,
            'file_path' => $filePath
          ];
        } else {
          // Eliminar archivo si falló la BD
          @unlink($filePath);
          return ['success' => false, 'errors' => ['Error al guardar en base de datos']];
        }
      } else {
        return ['success' => false, 'errors' => ['Error al subir el archivo']];
      }
    } catch (Exception $e) {
      error_log("Error subiendo imagen: " . $e->getMessage());
      return ['success' => false, 'errors' => ['Error interno al subir imagen']];
    }
  }

  /**
   * Validar archivo de imagen
   */
  private function validateImageFile($file)
  {
    $errors = [];

    // Verificar errores de subida
    if ($file['error'] !== UPLOAD_ERR_OK) {
      $errors[] = 'Error al subir el archivo';
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
   * Obtener imágenes de un post
   */
  public function getPostImages($postId, $includeInactive = false)
  {
    $conditions = ['post_id = :post_id'];
    $params = ['post_id' => $postId];

    if (!$includeInactive) {
      $conditions[] = 'status = 1';
    }

    $where = implode(' AND ', $conditions);
    return $this->getWhere($where, $params, 'sort_order', 'ASC');
  }

  /**
   * Obtener imagen destacada de un post
   */
  public function getFeaturedImage($postId)
  {
    return $this->db->fetch(
      "SELECT * FROM images_id WHERE post_id = :post_id AND is_featured = 1 AND status = 1",
      ['post_id' => $postId]
    );
  }

  /**
   * Establecer imagen destacada
   */
  public function setFeaturedImage($postId, $imageId)
  {
    try {
      $this->db->beginTransaction();

      // Quitar featured de todas las imágenes del post
      $this->db->execute(
        "UPDATE images_id SET is_featured = 0 WHERE post_id = :post_id",
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
   * Actualizar orden de las imágenes
   */
  public function updateSortOrder($imageIds)
  {
    try {
      $this->db->beginTransaction();

      foreach ($imageIds as $order => $imageId) {
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
   * Eliminar imagen
   */
  public function deleteImage($id)
  {
    $image = $this->getById($id);
    if (!$image) {
      return ['success' => false, 'errors' => ['Imagen no encontrada']];
    }

    // Eliminar archivo físico
    if (file_exists($image['file_path'])) {
      @unlink($image['file_path']);
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
    $allowedFields = ['alt_text', 'caption', 'sort_order', 'is_featured', 'status'];
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
   * Obtener siguiente número de orden
   */
  private function getNextSortOrder($postId)
  {
    $result = $this->db->fetch(
      "SELECT COALESCE(MAX(sort_order), 0) + 1 as next_order FROM images_id WHERE post_id = :post_id",
      ['post_id' => $postId]
    );

    return $result['next_order'];
  }

  /**
   * Obtener estadísticas de imágenes
   */
  public function getStats()
  {
    $total = $this->count();
    $active = $this->count('status = 1');
    $featured = $this->count('is_featured = 1 AND status = 1');

    $sizeStats = $this->db->fetch(
      "SELECT 
                COUNT(*) as total_files,
                SUM(file_size) as total_size,
                AVG(file_size) as avg_size,
                MAX(file_size) as max_size
            FROM images_id 
            WHERE status = 1"
    );

    return [
      'total' => $total,
      'active' => $active,
      'featured' => $featured,
      'size_stats' => $sizeStats
    ];
  }
}
