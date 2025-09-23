<?php

/**
 * Modelo para la gestión de Posts
 * ACTUALIZADO: Incluye soporte completo para campo 'language'
 */

require_once 'BaseCRUD.php';

class Posts extends BaseCRUD
{

  protected $fillable = [
    'title',
    'content',
    'youtube_url',
    'language', // NUEVO: Campo idioma
    'status'
  ];

  protected $timestamps = true;

  public function __construct()
  {
    parent::__construct('posts');
  }

  /**
   * Obtener posts con información de medios asociados (ACTUALIZADO para incluir language)
   */
  public function getPostsWithMedia($includeInactive = false)
  {
    $statusCondition = $includeInactive ? '' : 'WHERE p.status = 1';

    $sql = "
        SELECT 
            p.id,
            p.title,
            p.content,
            p.youtube_url,
            p.language,
            p.status,
            p.created_at,
            p.updated_at,
            COUNT(DISTINCT i.id) as total_images,
            CASE 
                WHEN p.youtube_url IS NOT NULL AND p.youtube_url != '' THEN 1 
                ELSE 0 
            END as total_videos,
            GROUP_CONCAT(DISTINCT CASE WHEN i.type = 'listing' THEN i.filename END) as listing_image,
            GROUP_CONCAT(DISTINCT CASE WHEN i.type = 'header' THEN i.filename END) as header_image,
            COUNT(CASE WHEN i.type = 'content' THEN 1 END) as content_images_count
        FROM posts p
        LEFT JOIN post_images i ON p.id = i.post_id AND i.status = 1
        {$statusCondition}
        GROUP BY p.id, p.youtube_url, p.language
        ORDER BY p.created_at DESC
    ";

    return $this->query($sql);
  }

  /**
   * Obtener posts filtrados por idioma para el front público
   */
  public function getPostsForPublic($limit = null, $page = 1, $language = null)
  {
    $conditions = ['p.status = 1'];
    $params = [];

    // Filtrar por idioma si se especifica
    if ($language) {
      $conditions[] = 'p.language = :language';
      $params['language'] = $language;
    }

    $whereClause = 'WHERE ' . implode(' AND ', $conditions);

    $sql = "
          SELECT 
              p.id,
              p.title,
              p.content,
              p.youtube_url,
              p.language,
              p.created_at,
              p.updated_at,
              COUNT(DISTINCT i.id) as total_images,
              GROUP_CONCAT(DISTINCT CASE WHEN i.type = 'listing' THEN i.filename END) as listing_image,
              GROUP_CONCAT(DISTINCT CASE WHEN i.type = 'header' THEN i.filename END) as header_image,
              COUNT(CASE WHEN i.type = 'content' THEN 1 END) as content_images_count
          FROM posts p
          LEFT JOIN post_images i ON p.id = i.post_id AND i.status = 1
          {$whereClause}
          GROUP BY p.id
          ORDER BY p.created_at DESC
      ";

    // Agregar paginación si se especifica límite
    if ($limit) {
      $offset = ($page - 1) * $limit;
      $sql .= " LIMIT {$limit} OFFSET {$offset}";
    }

    $posts = $this->db->fetchAll($sql, $params);

    // Si hay paginación, obtener total para metadatos
    if ($limit) {
      $countConditions = ['status = 1'];
      $countParams = [];

      if ($language) {
        $countConditions[] = 'language = :language';
        $countParams['language'] = $language;
      }

      $countWhere = implode(' AND ', $countConditions);
      $totalPosts = $this->count($countWhere, $countParams);
      $totalPages = ceil($totalPosts / $limit);

      return [
        'data' => $posts,
        'pagination' => [
          'current_page' => $page,
          'total_pages' => $totalPages,
          'total_posts' => $totalPosts,
          'per_page' => $limit,
          'has_next' => $page < $totalPages,
          'has_prev' => $page > 1
        ]
      ];
    }

    return $posts;
  }

  /**
   * Obtener post completo con todas sus imágenes agrupadas por tipo 
   */
  public function getPostComplete($id)
  {
    $post = $this->getById($id);
    if (!$post) {
      return null;
    }

    // Obtener imágenes agrupadas por tipo
    require_once 'PostImages.php';
    $postImages = new PostImages();
    $post['images'] = $postImages->getPostImagesByType($id);

    return $post;
  }

  /**
   * Obtener post con imagen de listado para vista pública
   */
  public function getPostForListing($id)
  {
    $post = $this->getById($id);
    if (!$post || $post['status'] != 1) {
      return null;
    }

    // Obtener solo imagen de listado
    require_once 'PostImages.php';
    $postImages = new PostImages();
    $listingImage = $postImages->getImageByType($id, 'listing');

    $post['listing_image'] = $listingImage;

    return $post;
  }

  /**
   * Obtener post completo para vista pública
   */
  public function getPostForPublic($id)
  {
    $post = $this->getById($id);
    if (!$post || $post['status'] != 1) {
      return null;
    }

    // Obtener todas las imágenes por tipo
    require_once 'PostImages.php';
    $postImages = new PostImages();
    $post['images'] = $postImages->getPostImagesByType($id, false); // Solo activas

    return $post;
  }

  /**
   * Crear post con validaciones (ACTUALIZADO)
   */
  public function createPost($data)
  {
    $errors = $this->validatePostData($data);

    if (!empty($errors)) {
      return ['success' => false, 'errors' => $errors];
    }

    // Establecer valores por defecto
    $data['status'] = $data['status'] ?? 1;
    $data['language'] = $data['language'] ?? 'es'; // NUEVO: Valor por defecto

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
   * Actualizar post con validaciones (ACTUALIZADO)
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
   * Validar datos del post (ACTUALIZADO para incluir language)
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

    // NUEVO: Validar idioma
    if (empty($data['language'])) {
      $errors[] = 'El idioma es obligatorio';
    } elseif (!in_array($data['language'], ['es', 'en'])) {
      $errors[] = 'El idioma debe ser "es" (Español) o "en" (English)';
    }

    // Validar que el título sea único POR IDIOMA (excluyendo el post actual si es edición)
    $sql = "SELECT id FROM posts WHERE title = :title AND language = :language" . ($id ? " AND id != :id" : "");
    $params = [
      'title' => $data['title'],
      'language' => $data['language']
    ];
    if ($id) {
      $params['id'] = $id;
    }

    $existing = $this->db->fetch($sql, $params);
    if ($existing) {
      $languageName = $data['language'] === 'es' ? 'español' : 'inglés';
      $errors[] = "Ya existe un post con este título en {$languageName}";
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
   * Eliminar post con todas sus imágenes asociadas
   */
  public function deletePost($id)
  {
    try {
      $this->db->beginTransaction();

      // Obtener y eliminar todas las imágenes asociadas
      require_once 'PostImages.php';
      $postImages = new PostImages();
      $images = $postImages->getWhere('post_id = :post_id', ['post_id' => $id]);

      foreach ($images as $image) {
        $postImages->deleteImage($image['id']);
      }

      // Eliminar el post
      $success = $this->delete($id);

      if ($success) {
        $this->db->commit();
        return ['success' => true];
      } else {
        $this->db->rollback();
        return ['success' => false, 'errors' => ['Error al eliminar el post']];
      }
    } catch (Exception $e) {
      $this->db->rollback();
      error_log("Error al eliminar post: " . $e->getMessage());
      return ['success' => false, 'errors' => ['Error interno al eliminar el post']];
    }
  }

  /**
   * Cambiar estado del post (activo/inactivo)
   */
  public function togglePostStatus($id)
  {
    return $this->toggleStatus($id, 'status');
  }

  /**
   * NUEVO: Obtener estadísticas de posts por idioma
   */
  public function getLanguageStats()
  {
    $sql = "
        SELECT 
            language,
            COUNT(*) as total,
            COUNT(CASE WHEN status = 1 THEN 1 END) as active,
            COUNT(CASE WHEN status = 0 THEN 1 END) as inactive
        FROM posts
        GROUP BY language
        ORDER BY language
    ";

    return $this->query($sql);
  }

  /**
   * NUEVO: Buscar posts por idioma y texto
   */
  public function searchPosts($query, $language = null)
  {
    $conditions = ['(title LIKE :query OR content LIKE :query)'];
    $params = ['query' => "%{$query}%"];

    if ($language) {
      $conditions[] = 'language = :language';
      $params['language'] = $language;
    }

    $where = implode(' AND ', $conditions);

    return $this->getWhere($where, $params, 'created_at', 'DESC');
  }
}
