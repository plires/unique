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
                p.status,
                p.created_at,
                p.updated_at,
                COUNT(DISTINCT i.id) as total_images,
                COUNT(DISTINCT v.id) as total_videos,
                GROUP_CONCAT(DISTINCT i.filename ORDER BY i.sort_order) as image_files,
                GROUP_CONCAT(DISTINCT v.filename ORDER BY v.sort_order) as video_files
            FROM posts p
            LEFT JOIN images_id i ON p.id = i.post_id AND i.status = 1
            LEFT JOIN videos_id v ON p.id = v.post_id AND v.status = 1
            {$statusCondition}
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ";

    return $this->query($sql);
  }

  /**
   * Obtener post completo con todas sus imágenes y videos
   */
  public function getPostComplete($id)
  {
    $post = $this->getById($id);
    if (!$post) {
      return null;
    }

    // Obtener imágenes
    $images = $this->db->fetchAll(
      "SELECT * FROM images_id WHERE post_id = :post_id AND status = 1 ORDER BY sort_order",
      ['post_id' => $id]
    );

    // Obtener videos
    $videos = $this->db->fetchAll(
      "SELECT * FROM videos_id WHERE post_id = :post_id AND status = 1 ORDER BY sort_order",
      ['post_id' => $id]
    );

    $post['images'] = $images;
    $post['videos'] = $videos;

    return $post;
  }

  /**
   * Crear post con validaciones
   */
  public function createPost($data)
  {
    $errors = $this->validate($data);

    if (!empty($errors)) {
      return ['success' => false, 'errors' => $errors];
    }

    // Establecer valores por defecto
    $data['status'] = $data['status'] ?? 1;

    $postId = $this->create($data);

    if ($postId) {
      return ['success' => true, 'id' => $postId];
    } else {
      return ['success' => false, 'errors' => ['Error al crear el post']];
    }
  }

  /**
   * Actualizar post con validaciones
   */
  public function updatePost($id, $data)
  {
    if (!$this->exists($id)) {
      return ['success' => false, 'errors' => ['Post no encontrado']];
    }

    $errors = $this->validate($data, $id);

    if (!empty($errors)) {
      return ['success' => false, 'errors' => $errors];
    }

    $success = $this->update($id, $data);

    if ($success) {
      return ['success' => true];
    } else {
      return ['success' => false, 'errors' => ['Error al actualizar el post']];
    }
  }

  /**
   * Validaciones específicas para posts
   */
  protected function validate($data, $id = null)
  {
    $errors = [];

    if (empty(trim($data['title'] ?? ''))) {
      $errors[] = 'El título es obligatorio';
    } elseif (strlen($data['title']) > 255) {
      $errors[] = 'El título no puede tener más de 255 caracteres';
    }

    if (empty(trim($data['content'] ?? ''))) {
      $errors[] = 'El contenido es obligatorio';
    }

    // Verificar título único (excluyendo el post actual si es edición)
    if (!empty($data['title'])) {
      $existingPost = $this->db->fetch(
        "SELECT id FROM posts WHERE title = :title AND id != :id",
        ['title' => $data['title'], 'id' => $id ?? 0]
      );

      if ($existingPost) {
        $errors[] = 'Ya existe un post con este título';
      }
    }

    return $errors;
  }

  /**
   * Eliminar post y todos sus medios asociados
   */
  public function deletePost($id)
  {
    if (!$this->exists($id)) {
      return ['success' => false, 'errors' => ['Post no encontrado']];
    }

    try {
      $this->db->beginTransaction();

      // Obtener archivos para eliminar físicamente
      $images = $this->db->fetchAll(
        "SELECT file_path FROM images_id WHERE post_id = :post_id",
        ['post_id' => $id]
      );

      $videos = $this->db->fetchAll(
        "SELECT file_path, thumbnail_path FROM videos_id WHERE post_id = :post_id",
        ['post_id' => $id]
      );

      // Eliminar registros de base de datos (las FK en CASCADE se encargan de images_id y videos_id)
      $success = $this->delete($id);

      if ($success) {
        $this->db->commit();

        // Eliminar archivos físicos en background
        $this->deletePhysicalFiles($images, $videos);

        return ['success' => true];
      } else {
        $this->db->rollback();
        return ['success' => false, 'errors' => ['Error al eliminar el post']];
      }
    } catch (Exception $e) {
      $this->db->rollback();
      error_log("Error eliminando post: " . $e->getMessage());
      return ['success' => false, 'errors' => ['Error interno al eliminar el post']];
    }
  }

  /**
   * Eliminar archivos físicos
   */
  private function deletePhysicalFiles($images, $videos)
  {
    // Eliminar imágenes
    foreach ($images as $image) {
      if (file_exists($image['file_path'])) {
        @unlink($image['file_path']);
      }
    }

    // Eliminar videos y thumbnails
    foreach ($videos as $video) {
      if ($video['file_path'] && file_exists($video['file_path'])) {
        @unlink($video['file_path']);
      }
      if ($video['thumbnail_path'] && file_exists($video['thumbnail_path'])) {
        @unlink($video['thumbnail_path']);
      }
    }
  }

  /**
   * Buscar posts por título o contenido
   */
  public function searchPosts($query, $includeInactive = false)
  {
    $conditions = ['(p.title LIKE :query OR p.content LIKE :query)'];
    $params = ['query' => "%{$query}%"];

    if (!$includeInactive) {
      $conditions[] = 'p.status = 1';
    }

    $where = implode(' AND ', $conditions);

    $sql = "
            SELECT 
                p.id,
                p.title,
                p.content,
                p.status,
                p.created_at,
                p.updated_at,
                COUNT(DISTINCT i.id) as total_images,
                COUNT(DISTINCT v.id) as total_videos
            FROM posts p
            LEFT JOIN images_id i ON p.id = i.post_id AND i.status = 1
            LEFT JOIN videos_id v ON p.id = v.post_id AND v.status = 1
            WHERE {$where}
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ";

    return $this->db->fetchAll($sql, $params);
  }

  /**
   * Obtener posts activos para el frontend
   */
  public function getActivePosts($limit = null)
  {
    $sql = "
            SELECT 
                p.id,
                p.title,
                p.content,
                p.created_at,
                p.updated_at,
                COUNT(DISTINCT i.id) as total_images,
                COUNT(DISTINCT v.id) as total_videos
            FROM posts p
            LEFT JOIN images_id i ON p.id = i.post_id AND i.status = 1
            LEFT JOIN videos_id v ON p.id = v.post_id AND v.status = 1
            WHERE p.status = 1
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ";

    if ($limit) {
      $sql .= " LIMIT " . (int)$limit;
    }

    return $this->query($sql);
  }

  /**
   * Cambiar estado del post
   */
  public function togglePostStatus($id)
  {
    return $this->toggleStatus($id, 'status');
  }

  /**
   * Obtener estadísticas de posts
   */
  public function getStats()
  {
    $total = $this->count();
    $active = $this->count('status = 1');
    $inactive = $this->count('status = 0');

    $totalImages = $this->db->fetch("SELECT COUNT(*) as total FROM images_id WHERE status = 1");
    $totalVideos = $this->db->fetch("SELECT COUNT(*) as total FROM videos_id WHERE status = 1");

    return [
      'posts' => [
        'total' => $total,
        'active' => $active,
        'inactive' => $inactive
      ],
      'media' => [
        'images' => $totalImages['total'],
        'videos' => $totalVideos['total']
      ]
    ];
  }
}
