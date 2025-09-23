<?php

/**
 * Clase PostsPublic - Manejo de posts para el frontend público
 */

require_once 'Database.php';

class PostsPublic
{
  private $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  /**
   * Obtener los últimos N posts activos con su imagen de listado
   */
  public function getLatestPosts($limit = 3, $language = 'es')
  {
    $sql = "
        SELECT 
            p.id,
            p.title,
            p.content,
            p.youtube_url,
            p.created_at,
            p.updated_at,
            i.filename as listing_image,
            i.file_path as listing_image_path,
            i.alt_text as listing_image_alt
        FROM posts p
        LEFT JOIN post_images i ON (
            p.id = i.post_id 
            AND i.type = 'listing' 
            AND i.status = 1
        )
        WHERE p.status = 1
        AND p.language = '" . $language . "'
        ORDER BY p.created_at DESC
        LIMIT :limit
    ";

    return $this->db->fetchAll($sql, [
      'limit' => $limit
    ]);

    // return $this->db->fetchAll($sql, ['limit' => $limit]);
  }

  /**
   * Obtener un post específico por ID con todas sus imágenes
   */
  public function getPostById($id)
  {
    // Obtener el post principal
    $postSql = "
        SELECT 
            p.id,
            p.title,
            p.content,
            p.youtube_url,
            p.created_at,
            p.updated_at
        FROM posts p
        WHERE p.id = :id AND p.status = 1
    ";

    $post = $this->db->fetch($postSql, ['id' => $id]);

    if (!$post) {
      return null;
    }

    // Obtener todas las imágenes del post agrupadas por tipo
    $imagesSql = "
        SELECT 
            id,
            filename,
            file_path,
            alt_text,
            type,
            sort_order
        FROM post_images 
        WHERE post_id = :post_id AND status = 1
        ORDER BY type, sort_order
    ";

    $images = $this->db->fetchAll($imagesSql, ['post_id' => $id]);

    // Agrupar imágenes por tipo
    $post['images'] = [
      'listing' => [],
      'header' => [],
      'content' => []
    ];

    foreach ($images as $image) {
      $post['images'][$image['type']][] = $image;
    }

    return $post;
  }

  /**
   * Obtener la URL completa de una imagen
   */
  public function getImageUrl($imagePath)
  {
    if (empty($imagePath)) {
      return null;
    }

    // Si ya es una URL completa, devolverla tal como está
    if (strpos($imagePath, 'http') === 0) {
      return $imagePath;
    }

    // Construir URL relativa desde la raíz del sitio
    return '/' . ltrim($imagePath, '/');
  }

  /**
   * Formatear fecha para mostrar en el frontend
   */
  public function formatDate($date, $lang = 'es')
  {
    $timestamp = strtotime($date);

    if ($lang === 'es') {
      $months = [
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre'
      ];

      $day = date('j', $timestamp);
      $month = $months[date('n', $timestamp)];
      $year = date('Y', $timestamp);

      return "{$day} de {$month} de {$year}";
    } else {
      return date('F j, Y', $timestamp);
    }
  }

  /**
   * Extraer resumen del contenido HTML
   */
  public function getContentExcerpt($content, $maxLength = 150)
  {
    // Primero decodificar entidades HTML
    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // Remover tags HTML pero preservar espacios y saltos de línea
    $plainText = strip_tags($content);

    // Limpiar espacios múltiples y normalizar
    $plainText = preg_replace('/\s+/', ' ', $plainText);
    $plainText = trim($plainText);

    // Truncar texto
    if (strlen($plainText) > $maxLength) {
      $plainText = substr($plainText, 0, $maxLength);

      // Buscar el último espacio para no cortar palabras
      $lastSpace = strrpos($plainText, ' ');
      if ($lastSpace !== false) {
        $plainText = substr($plainText, 0, $lastSpace);
      }

      $plainText .= '...';
    }

    return $plainText;
  }

  /**
   * Extraer resumen del contenido HTML preservando formato básico
   */
  public function getContentExcerptWithFormat($content, $maxLength = 150)
  {
    // Primero decodificar entidades HTML
    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // Permitir solo tags básicos de formato
    $allowedTags = '<strong><b><em><i><u>';
    $formattedText = strip_tags($content, $allowedTags);

    // Limpiar espacios múltiples pero preservar tags HTML
    $formattedText = preg_replace('/\s+/', ' ', $formattedText);
    $formattedText = trim($formattedText);

    // Para truncar con HTML, necesitamos ser más cuidadosos
    if (strlen(strip_tags($formattedText)) > $maxLength) {
      // Convertir a texto plano para medir longitud
      $plainText = strip_tags($formattedText);

      if (strlen($plainText) > $maxLength) {
        $plainText = substr($plainText, 0, $maxLength);

        // Buscar el último espacio
        $lastSpace = strrpos($plainText, ' ');
        if ($lastSpace !== false) {
          $plainText = substr($plainText, 0, $lastSpace);
        }

        // Reconstruir con el HTML original hasta esta posición
        $tempContent = '';
        $currentLength = 0;
        $inTag = false;

        for ($i = 0; $i < strlen($formattedText) && $currentLength < strlen($plainText); $i++) {
          $char = $formattedText[$i];
          $tempContent .= $char;

          if ($char === '<') {
            $inTag = true;
          } elseif ($char === '>') {
            $inTag = false;
          } elseif (!$inTag) {
            $currentLength++;
          }
        }

        $formattedText = $tempContent . '...';
      }
    }

    return $formattedText;
  }

  /**
   * Limpiar y preparar contenido HTML para mostrar
   */
  public function cleanHtmlContent($content)
  {
    // Decodificar entidades HTML
    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // Limpiar espacios múltiples
    $content = preg_replace('/\s+/', ' ', $content);

    return trim($content);
  }
}
