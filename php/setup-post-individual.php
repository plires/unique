<?php
// Obtener ID del post desde URL
$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$postId) {
  header('HTTP/1.1 404 Not Found');
  header('Location: blog.php');
  exit;
}

// Instanciar modelo y obtener post completo
$postsModel = new PostsPublic();
$post = $postsModel->getPostById($postId);

// Verificar si el post existe
if (!$post) {
  header('HTTP/1.1 404 Not Found');
  header('Location: blog.php');
  exit;
}

$nivel = $_SESSION['lang'] === 'es' ? '' : '../';

// Formatear fecha para mostrar
$formattedDate = $postsModel->formatDate($post['created_at'], $_SESSION['lang']);

// Verificar si tiene video de YouTube
$hasYouTubeVideo = !empty($post['youtube_url']);

// Obtener ID del video de YouTube para el embed
$youtubeVideoId = null;
if ($hasYouTubeVideo) {
  preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/', $post['youtube_url'], $matches);
  $youtubeVideoId = $matches[1] ?? null;
}

// Función para obtener URL completa de imagen
function getImageUrl($imagePath)
{
  if (empty($imagePath)) {
    return null;
  }
  if (strpos($imagePath, 'http') === 0) {
    return $imagePath;
  }
  return '/' . ltrim($imagePath, '/');
}

// Meta tags dinámicos para SEO
$metaTitle = htmlspecialchars($post['title']) . ' | Unique Talent Solutions';
$metaDescription = strip_tags(substr($post['content'], 0, 155));
$canonicalUrl = APP_URL_FRONTEND . '/post.php?id=' . $postId;
