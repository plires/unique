<?php

/**
 * API para subir/agregar videos a posts
 */

require_once('../../includes/config.inc.php');
require_once('../../clases/PostVideos.php');
require_once('../../clases/Posts.php');
require_once('../../clases/ResponseHelper.php');

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  ResponseHelper::error('Método no permitido', null, 405);
}

try {
  // Validar parámetros
  if (empty($_POST['post_id'])) {
    ResponseHelper::error('ID de post es requerido');
  }

  if (empty($_POST['video_type'])) {
    ResponseHelper::error('Tipo de video es requerido');
  }

  $postId = (int)$_POST['post_id'];
  $videoType = $_POST['video_type'];

  // Verificar que el post existe
  $postsModel = new Posts();
  if (!$postsModel->exists($postId)) {
    ResponseHelper::notFound('El post no existe');
  }

  $videosModel = new PostVideos();

  // Datos adicionales opcionales
  $videoData = [];
  if (!empty($_POST['title'])) {
    $videoData['title'] = $_POST['title'];
  }
  if (!empty($_POST['description'])) {
    $videoData['description'] = $_POST['description'];
  }
  if (isset($_POST['is_featured'])) {
    $videoData['is_featured'] = (int)$_POST['is_featured'];
  }

  switch ($videoType) {
    case 'file':
      // Subir archivo de video
      if (empty($_FILES['video'])) {
        ResponseHelper::error('Archivo de video es requerido');
      }

      $file = $_FILES['video'];
      $result = $videosModel->uploadVideoFile($postId, $file, $videoData);
      break;

    case 'youtube':
      // Agregar video de YouTube
      if (empty($_POST['video_url'])) {
        ResponseHelper::error('URL de YouTube es requerida');
      }

      $result = $videosModel->addYouTubeVideo($postId, $_POST['video_url'], $videoData);
      break;

    case 'vimeo':
      // Agregar video de Vimeo
      if (empty($_POST['video_url'])) {
        ResponseHelper::error('URL de Vimeo es requerida');
      }

      $result = $videosModel->addVimeoVideo($postId, $_POST['video_url'], $videoData);
      break;

    case 'url':
      // Agregar video por URL personalizada
      if (empty($_POST['video_url'])) {
        ResponseHelper::error('URL del video es requerida');
      }

      $result = $videosModel->addCustomVideo($postId, $_POST['video_url'], $videoData);
      break;

    default:
      ResponseHelper::error('Tipo de video no válido');
  }

  if ($result['success']) {
    ResponseHelper::success(
      ['id' => $result['id']],
      'Video agregado exitosamente',
      201
    );
  } else {
    ResponseHelper::validationError($result['errors']);
  }
} catch (Exception $e) {
  error_log("Error en uploadVideo.php: " . $e->getMessage());
  ResponseHelper::serverError('Error al procesar la solicitud');
}
