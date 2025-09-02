<?php

/**
 * Modelo para la gestión de Videos de Posts
 */

require_once 'BaseCRUD.php';

class PostVideos extends BaseCRUD
{

  protected $fillable = [
    'post_id',
    'video_type',
    'filename',
    'original_name',
    'file_path',
    'file_size',
    'mime_type',
    'video_url',
    'video_id',
    'thumbnail_path',
    'duration',
    'title',
    'description',
    'sort_order',
    'is_featured',
    'status'
  ];

  protected $timestamps = true;

  public function __construct()
  {
    parent::__construct('videos_id');
  }

  /**
   * Subir y guardar video archivo
   */
  public function uploadVideoFile($postId, $file, $videoData = [])
  {
    // Validar archivo
    $validation = $this->validateVideoFile($file);
    if (!$validation['valid']) {
      return ['success' => false, 'errors' => $validation['errors']];
    }

    try {
      // Crear directorio si no existe
      $uploadDir = UPLOAD_PATH_VIDEOS;
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
      }

      // Generar nombre único
      $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
      $filename = uniqid('vid_') . '_' . time() . '.' . $extension;
      $filePath = $uploadDir . $filename;

      // Mover archivo
      if (move_uploaded_file($file['tmp_name'], $filePath)) {

        // Obtener siguiente orden
        $nextOrder = $this->getNextSortOrder($postId);

        // Preparar datos del video
        $videoData = array_merge([
          'post_id' => $postId,
          'video_type' => 'file',
          'filename' => $filename,
          'original_name' => $file['name'],
          'file_path' => $filePath,
          'file_size' => $file['size'],
          'mime_type' => $file['type'],
          'sort_order' => $nextOrder,
          'status' => 1
        ], $videoData);

        $videoId = $this->create($videoData);

        if ($videoId) {
          return [
            'success' => true,
            'id' => $videoId,
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
      error_log("Error subiendo video: " . $e->getMessage());
      return ['success' => false, 'errors' => ['Error interno al subir video']];
    }
  }

  /**
   * Agregar video de YouTube
   */
  public function addYouTubeVideo($postId, $url, $videoData = [])
  {
    $videoId = $this->extractYouTubeId($url);
    if (!$videoId) {
      return ['success' => false, 'errors' => ['URL de YouTube no válida']];
    }

    // Obtener información del video desde YouTube API (opcional)
    $videoInfo = $this->getYouTubeInfo($videoId);

    $nextOrder = $this->getNextSortOrder($postId);

    $data = array_merge([
      'post_id' => $postId,
      'video_type' => 'youtube',
      'video_url' => $url,
      'video_id' => $videoId,
      'title' => $videoInfo['title'] ?? 'Video de YouTube',
      'description' => $videoInfo['description'] ?? '',
      'duration' => $videoInfo['duration'] ?? null,
      'thumbnail_path' => $videoInfo['thumbnail'] ?? null,
      'sort_order' => $nextOrder,
      'status' => 1
    ], $videoData);

    $id = $this->create($data);

    if ($id) {
      return ['success' => true, 'id' => $id];
    } else {
      return ['success' => false, 'errors' => ['Error al guardar video de YouTube']];
    }
  }

  /**
   * Agregar video de Vimeo
   */
  public function addVimeoVideo($postId, $url, $videoData = [])
  {
    $videoId = $this->extractVimeoId($url);
    if (!$videoId) {
      return ['success' => false, 'errors' => ['URL de Vimeo no válida']];
    }

    $videoInfo = $this->getVimeoInfo($videoId);

    $nextOrder = $this->getNextSortOrder($postId);

    $data = array_merge([
      'post_id' => $postId,
      'video_type' => 'vimeo',
      'video_url' => $url,
      'video_id' => $videoId,
      'title' => $videoInfo['title'] ?? 'Video de Vimeo',
      'description' => $videoInfo['description'] ?? '',
      'duration' => $videoInfo['duration'] ?? null,
      'thumbnail_path' => $videoInfo['thumbnail'] ?? null,
      'sort_order' => $nextOrder,
      'status' => 1
    ], $videoData);

    $id = $this->create($data);

    if ($id) {
      return ['success' => true, 'id' => $id];
    } else {
      return ['success' => false, 'errors' => ['Error al guardar video de Vimeo']];
    }
  }

  /**
   * Agregar video por URL personalizada
   */
  public function addCustomVideo($postId, $url, $videoData = [])
  {
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
      return ['success' => false, 'errors' => ['URL no válida']];
    }

    $nextOrder = $this->getNextSortOrder($postId);

    $data = array_merge([
      'post_id' => $postId,
      'video_type' => 'url',
      'video_url' => $url,
      'title' => $videoData['title'] ?? 'Video personalizado',
      'description' => $videoData['description'] ?? '',
      'sort_order' => $nextOrder,
      'status' => 1
    ], $videoData);

    $id = $this->create($data);

    if ($id) {
      return ['success' => true, 'id' => $id];
    } else {
      return ['success' => false, 'errors' => ['Error al guardar video personalizado']];
    }
  }

  /**
   * Validar archivo de video
   */
  private function validateVideoFile($file)
  {
    $errors = [];

    // Verificar errores de subida
    if ($file['error'] !== UPLOAD_ERR_OK) {
      $errors[] = 'Error al subir el archivo';
      return ['valid' => false, 'errors' => $errors];
    }

    // Verificar tamaño
    if ($file['size'] > MAX_VIDEO_SIZE) {
      $errors[] = 'El archivo es demasiado grande. Máximo: ' . (MAX_VIDEO_SIZE / 1024 / 1024) . 'MB';
    }

    // Verificar tipo de archivo
    $allowedTypes = explode(',', ALLOWED_VIDEO_TYPES);
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedTypes)) {
      $errors[] = 'Tipo de archivo no permitido. Permitidos: ' . ALLOWED_VIDEO_TYPES;
    }

    return ['valid' => empty($errors), 'errors' => $errors];
  }

  /**
   * Extraer ID de video de YouTube
   */
  private function extractYouTubeId($url)
  {
    $patterns = [
      '/youtube\.com\/watch\?v=([^&\n]+)/',
      '/youtube\.com\/embed\/([^&\n]+)/',
      '/youtu\.be\/([^&\n]+)/'
    ];

    foreach ($patterns as $pattern) {
      if (preg_match($pattern, $url, $matches)) {
        return $matches[1];
      }
    }

    return null;
  }

  /**
   * Extraer ID de video de Vimeo
   */
  private function extractVimeoId($url)
  {
    if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
      return $matches[1];
    }
    return null;
  }

  /**
   * Obtener información de YouTube (requiere API key)
   */
  private function getYouTubeInfo($videoId)
  {
    // Implementación básica sin API key
    return [
      'title' => 'Video de YouTube',
      'description' => '',
      'duration' => null,
      'thumbnail' => "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg"
    ];

    // Con API key sería algo así:
    /*
        $apiKey = env('YOUTUBE_API_KEY');
        if (!$apiKey) return [];
        
        $url = "https://www.googleapis.com/youtube/v3/videos?id={$videoId}&key={$apiKey}&part=snippet,contentDetails";
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        
        if (isset($data['items'][0])) {
            $item = $data['items'][0];
            return [
                'title' => $item['snippet']['title'],
                'description' => $item['snippet']['description'],
                'duration' => $this->parseYouTubeDuration($item['contentDetails']['duration']),
                'thumbnail' => $item['snippet']['thumbnails']['high']['url']
            ];
        }
        */
  }

  /**
   * Obtener información de Vimeo
   */
  private function getVimeoInfo($videoId)
  {
    try {
      $url = "https://vimeo.com/api/v2/video/{$videoId}.json";
      $response = @file_get_contents($url);

      if ($response) {
        $data = json_decode($response, true);
        if (isset($data[0])) {
          $video = $data[0];
          return [
            'title' => $video['title'],
            'description' => $video['description'],
            'duration' => $video['duration'],
            'thumbnail' => $video['thumbnail_large']
          ];
        }
      }
    } catch (Exception $e) {
      error_log("Error obteniendo info de Vimeo: " . $e->getMessage());
    }

    return [
      'title' => 'Video de Vimeo',
      'description' => '',
      'duration' => null,
      'thumbnail' => null
    ];
  }

  /**
   * Obtener videos de un post
   */
  public function getPostVideos($postId, $includeInactive = false)
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
   * Obtener video destacado de un post
   */
  public function getFeaturedVideo($postId)
  {
    return $this->db->fetch(
      "SELECT * FROM videos_id WHERE post_id = :post_id AND is_featured = 1 AND status = 1",
      ['post_id' => $postId]
    );
  }

  /**
   * Establecer video destacado
   */
  public function setFeaturedVideo($postId, $videoId)
  {
    try {
      $this->db->beginTransaction();

      // Quitar featured de todos los videos del post
      $this->db->execute(
        "UPDATE videos_id SET is_featured = 0 WHERE post_id = :post_id",
        ['post_id' => $postId]
      );

      // Establecer el nuevo video destacado
      $success = $this->update($videoId, ['is_featured' => 1]);

      if ($success) {
        $this->db->commit();
        return ['success' => true];
      } else {
        $this->db->rollback();
        return ['success' => false, 'errors' => ['Error al establecer video destacado']];
      }
    } catch (Exception $e) {
      $this->db->rollback();
      error_log("Error estableciendo video destacado: " . $e->getMessage());
      return ['success' => false, 'errors' => ['Error interno']];
    }
  }

  /**
   * Actualizar orden de los videos
   */
  public function updateSortOrder($videoIds)
  {
    try {
      $this->db->beginTransaction();

      foreach ($videoIds as $order => $videoId) {
        $this->update($videoId, ['sort_order' => $order + 1]);
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
   * Eliminar video
   */
  public function deleteVideo($id)
  {
    $video = $this->getById($id);
    if (!$video) {
      return ['success' => false, 'errors' => ['Video no encontrado']];
    }

    // Eliminar archivos físicos si es video de archivo
    if ($video['video_type'] === 'file') {
      if ($video['file_path'] && file_exists($video['file_path'])) {
        @unlink($video['file_path']);
      }
      if ($video['thumbnail_path'] && file_exists($video['thumbnail_path'])) {
        @unlink($video['thumbnail_path']);
      }
    }

    // Eliminar de base de datos
    $success = $this->delete($id);

    if ($success) {
      return ['success' => true];
    } else {
      return ['success' => false, 'errors' => ['Error al eliminar video']];
    }
  }

  /**
   * Actualizar información de video
   */
  public function updateVideoInfo($id, $data)
  {
    $allowedFields = ['title', 'description', 'sort_order', 'is_featured', 'status'];
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
      return ['success' => false, 'errors' => ['Error al actualizar video']];
    }
  }

  /**
   * Obtener siguiente número de orden
   */
  private function getNextSortOrder($postId)
  {
    $result = $this->db->fetch(
      "SELECT COALESCE(MAX(sort_order), 0) + 1 as next_order FROM videos_id WHERE post_id = :post_id",
      ['post_id' => $postId]
    );

    return $result['next_order'];
  }

  /**
   * Obtener estadísticas de videos
   */
  public function getStats()
  {
    $total = $this->count();
    $active = $this->count('status = 1');
    $featured = $this->count('is_featured = 1 AND status = 1');

    $typeStats = $this->db->fetchAll(
      "SELECT video_type, COUNT(*) as count FROM videos_id WHERE status = 1 GROUP BY video_type"
    );

    $sizeStats = $this->db->fetch(
      "SELECT 
                COUNT(*) as total_files,
                SUM(COALESCE(file_size, 0)) as total_size,
                AVG(COALESCE(file_size, 0)) as avg_size,
                MAX(COALESCE(file_size, 0)) as max_size
            FROM videos_id 
            WHERE status = 1 AND video_type = 'file'"
    );

    return [
      'total' => $total,
      'active' => $active,
      'featured' => $featured,
      'by_type' => $typeStats,
      'size_stats' => $sizeStats
    ];
  }
}
