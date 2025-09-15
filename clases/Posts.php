<?php

/**
 * Modelo para la gestión de Posts
 */

require_once 'BaseCRUD.php';

class Posts extends BaseCRUD
{

  protected $fillable = [
    'title',
    'content',
    'youtube_url',  // NUEVO CAMPO
    'status'
  ];

  protected $timestamps = true;

  public function __construct()
  {
    parent::__construct('posts');
  }

  /**
   * Obtener posts con información de medios asociados
   */
  public function getPostsWithMedia($includeInactive = false)
  {
    $statusCondition = $includeInactive ? '' : 'WHERE p.status = 1';

    $sql = "
            SELECT 
                p.id,
                p.title,
                p.content,
                p.youtube_url,  -- NUEVO CAMPO
                p.status,
                p.created_at,
                p.updated_at,
                COUNT(DISTINCT i.id) as total_images,
                GROUP_CONCAT(DISTINCT i.filename ORDER BY i.sort_order) as image_files
            FROM posts p
            LEFT JOIN images_id i ON p.id = i.post_id AND i.status = 1
            {$statusCondition}
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ";

    return $this->query($sql);
  }

  /**
   * Obtener post completo con todas sus imágenes
   */
  public function getPostComplete($id)
  {
    $post = $this->getById($id);
    if (!$post) {
      return null;
    }

    // Obtener imágenes (NO VIDEOS)
    $images = $this->db->fetchAll(
      "SELECT * FROM images_id WHERE post_id = :post_id AND status = 1 ORDER BY sort_order",
      ['post_id' => $id]
    );

    $post['images'] = $images;

    return $post;
  }

  /**
   * Crear post con validaciones
   */
  public function createPost($data)
  {
    $errors = $this->validatePostData($data);

    if (!empty($errors)) {
      return ['success' => false, 'errors' => $errors];
    }

    // Establecer valores por defecto
    $data['status'] = $data['status'] ?? 1;

    // Validar y limpiar URL de YouTube si se proporciona
    if (!empty($data['youtube_url'])) {
      $data['youtube_url'] = $this->validateAndCleanYouTubeUrl($data['youtube_url']);
      if (!$data['youtube_url']) {
        return ['success' => false, 'errors' => ['URL de YouTube no válida']];
      }
    }

    $id = $this->create($data);

    if ($id) {
      return ['success' => true, 'id' => $id];
    } else {
      return ['success' => false, 'errors' => ['Error al crear el post']];
    }
  }

  /**
   * Actualizar post con validaciones
   */
  public function updatePost($id, $data)
  {
    $errors = $this->validatePostData($data, $id);

    if (!empty($errors)) {
      return ['success' => false, 'errors' => $errors];
    }

    // Validar y limpiar URL de YouTube si se proporciona
    if (isset($data['youtube_url'])) {
      if (!empty($data['youtube_url'])) {
        $data['youtube_url'] = $this->validateAndCleanYouTubeUrl($data['youtube_url']);
        if (!$data['youtube_url']) {
          return ['success' => false, 'errors' => ['URL de YouTube no válida']];
        }
      } else {
        $data['youtube_url'] = null; // Permitir eliminar la URL
      }
    }

    $success = $this->update($id, $data);

    if ($success) {
      return ['success' => true];
    } else {
      return ['success' => false, 'errors' => ['Error al actualizar el post']];
    }
  }

  /**
   * Validar datos del post
   */
  private function validatePostData($data, $id = null)
  {
    $errors = [];

    // Validar título
    if (empty($data['title']) || trim($data['title']) === '') {
      $errors[] = 'El título es obligatorio';
    } elseif (strlen($data['title']) > 255) {
      $errors[] = 'El título no puede exceder 255 caracteres';
    }

    // Validar contenido
    if (empty($data['content']) || trim($data['content']) === '' || trim($data['content']) === '<p><br></p>') {
      $errors[] = 'El contenido es obligatorio';
    }

    // Validar que el título sea único (excluyendo el post actual si es edición)
    $sql = "SELECT id FROM posts WHERE title = :title" . ($id ? " AND id != :id" : "");
    $params = ['title' => $data['title']];
    if ($id) {
      $params['id'] = $id;
    }

    $existing = $this->db->fetch($sql, $params);
    if ($existing) {
      $errors[] = 'Ya existe un post con este título';
    }

    return $errors;
  }

  /**
   * Validar y limpiar URL de YouTube
   */
  private function validateAndCleanYouTubeUrl($url)
  {
    if (empty($url)) {
      return null;
    }

    // Patrones para diferentes formatos de URL de YouTube
    $patterns = [
      '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/',
      '/youtube\.com\/.*[?&]v=([^&\n]+)/'
    ];

    foreach ($patterns as $pattern) {
      if (preg_match($pattern, $url, $matches)) {
        $videoId = $matches[1];
        // Retornar URL normalizada
        return "https://www.youtube.com/watch?v=" . $videoId;
      }
    }

    return false; // URL no válida
  }

  /**
   * Extraer ID de video de YouTube
   */
  public function getYouTubeVideoId($url)
  {
    if (empty($url)) {
      return null;
    }

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
   * Cambiar estado del post (activo/inactivo)
   */
  public function togglePostStatus($id)
  {
    return $this->toggleStatus($id, 'status');
  }

  /**
   * Obtener URL de miniatura de YouTube
   */
  public function getYouTubeThumbnail($url)
  {
    $videoId = $this->getYouTubeVideoId($url);
    if ($videoId) {
      return "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
    }
    return null;
  }

  /**
   * Eliminar post y todos sus medios asociados
   */
  public function deletePost($id)
  {
    try {
      $this->db->getConnection()->beginTransaction();

      // Eliminar imágenes asociadas
      $images = $this->db->fetchAll(
        "SELECT * FROM images_id WHERE post_id = :post_id",
        ['post_id' => $id]
      );

      foreach ($images as $image) {
        // Eliminar archivo físico
        $imagePath = $image['file_path'];
        if (file_exists($imagePath)) {
          unlink($imagePath);
        }
      }

      // Eliminar registros de imágenes
      $this->db->execute(
        "DELETE FROM images_id WHERE post_id = :post_id",
        ['post_id' => $id]
      );

      // Eliminar el post
      $success = $this->delete($id);

      if ($success) {
        $this->db->getConnection()->commit();
        return ['success' => true];
      } else {
        $this->db->getConnection()->rollBack();
        return ['success' => false, 'errors' => ['Error al eliminar el post']];
      }
    } catch (Exception $e) {
      $this->db->getConnection()->rollBack();
      error_log("Error eliminando post: " . $e->getMessage());
      return ['success' => false, 'errors' => ['Error interno al eliminar el post']];
    }
  }
}
