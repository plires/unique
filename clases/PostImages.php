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
    'status',
    'image_size',  // AGREGAR
    'width',       // AGREGAR
    'height'       // AGREGAR
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
    error_log("=== INICIO UPLOAD IMAGEN ===");

    // Validar archivo
    $validation = $this->validateImageFile($file);
    if (!$validation['valid']) {
      return ['success' => false, 'errors' => $validation['errors']];
    }

    try {
      // Crear directorio si no existe
      $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/' . UPLOAD_PATH_IMAGES;
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
      }

      // Generar nombre único
      $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
      $filename = uniqid('img_') . '_' . time() . '.' . $extension;
      $tempPath = $uploadDir . 'temp_' . $filename;

      error_log("Subiendo archivo temporal a: " . $tempPath);

      // Mover archivo temporalmente
      if (!move_uploaded_file($file['tmp_name'], $tempPath)) {
        return ['success' => false, 'errors' => ['Error al subir el archivo']];
      }

      $savedImages = [];

      // SI tienes ImageManager instalado, usar optimización
      if (class_exists('ImageManager') && defined('IMAGE_SIZES')) {
        error_log("Usando ImageManager para procesamiento múltiple");
        require_once 'ImageManager.php';

        $imageManager = new ImageManager();
        $result = $imageManager->processImage($tempPath, $filename, $imageData);

        // Limpiar archivo temporal
        @unlink($tempPath);

        if (!$result['success']) {
          error_log("Error en ImageManager: " . $result['error']);
          return ['success' => false, 'errors' => [$result['error']]];
        }

        error_log("ImageManager procesó " . count($result['images']) . " tamaños");

        // GUARDAR TODAS LAS IMÁGENES GENERADAS EN LA BD
        foreach ($result['images'] as $sizeName => $imageInfo) {
          error_log("Guardando en BD: " . $sizeName . " - " . $imageInfo['filename']);

          $nextOrder = $this->getNextSortOrder($postId);

          $sizeImageData = array_merge([
            'post_id' => $postId,
            'filename' => $imageInfo['filename'],
            'original_name' => $file['name'],
            'file_path' => $imageInfo['path'],
            'file_size' => $imageInfo['size'],
            'mime_type' => $file['type'],
            'image_size' => $sizeName,  // CRÍTICO: Guardar el tamaño
            'width' => $imageInfo['width'],
            'height' => $imageInfo['height'],
            'sort_order' => $nextOrder,
            'status' => 1
          ], $imageData);

          $imageId = $this->create($sizeImageData);

          if ($imageId) {
            error_log("Imagen guardada en BD con ID: " . $imageId);
            $savedImages[$sizeName] = [
              'id' => $imageId,
              'filename' => $imageInfo['filename'],
              'path' => $imageInfo['path'],
              'size' => $sizeName
            ];
          } else {
            error_log("ERROR: No se pudo guardar imagen en BD para tamaño: " . $sizeName);
          }
        }
      } else {
        error_log("Fallback: No se encontró ImageManager o IMAGE_SIZES no definido");

        // Fallback: usar imagen original sin optimizar
        $finalPath = $uploadDir . $filename;
        rename($tempPath, $finalPath);

        $nextOrder = $this->getNextSortOrder($postId);
        $relativePath = UPLOAD_PATH_IMAGES . $filename;

        $originalImageData = array_merge([
          'post_id' => $postId,
          'filename' => $filename,
          'original_name' => $file['name'],
          'file_path' => $relativePath,
          'file_size' => filesize($finalPath),
          'mime_type' => $file['type'],
          'image_size' => 'original',
          'width' => 0,
          'height' => 0,
          'sort_order' => $nextOrder,
          'status' => 1
        ], $imageData);

        $imageId = $this->create($originalImageData);

        if ($imageId) {
          $savedImages['original'] = [
            'id' => $imageId,
            'filename' => $filename,
            'path' => $relativePath,
            'size' => 'original'
          ];
        }
      }

      error_log("=== RESULTADO FINAL ===");
      error_log("Imágenes guardadas en BD: " . count($savedImages));

      if (!empty($savedImages)) {
        return [
          'success' => true,
          'images_created' => count($savedImages),
          'images' => $savedImages,
          'main_image' => reset($savedImages) // Primera imagen como principal
        ];
      } else {
        return ['success' => false, 'errors' => ['Error al guardar las imágenes en base de datos']];
      }
    } catch (Exception $e) {
      error_log("ERROR CRÍTICO en uploadImage: " . $e->getMessage());
      error_log("Stack trace: " . $e->getTraceAsString());
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

    // CAMBIO: Construir ruta absoluta para eliminar archivo físico
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

  /**
   * Obtener imagen específica por tamaño
   */
  public function getImageBySize($postId, $size = 'large')
  {
    return $this->db->fetch(
      "SELECT * FROM images_id WHERE post_id = :post_id AND image_size = :size AND status = 1",
      ['post_id' => $postId, 'size' => $size]
    );
  }

  /**
   * Obtener todas las imágenes de un post agrupadas por tamaño
   */
  public function getImagesBySizes($postId)
  {
    $images = $this->db->fetchAll(
      "SELECT * FROM images_id WHERE post_id = :post_id AND status = 1 ORDER BY sort_order, image_size",
      ['post_id' => $postId]
    );

    $grouped = [];
    foreach ($images as $image) {
      $grouped[$image['image_size']][] = $image;
    }

    return $grouped;
  }
}
