<?php

/**
 * API para crear y editar posts
 */

require_once('../../includes/config.inc.php');
require_once('../../clases/Posts.php');
require_once('../../clases/ResponseHelper.php');

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  ResponseHelper::error('Método no permitido', null, 405);
}

try {
  $postsModel = new Posts();

  // Obtener datos del POST
  $data = [
    'title' => $_POST['title'] ?? '',
    'content' => $_POST['content'] ?? '',
    'youtube_url' => $_POST['youtube_url'] ?? '',  // NUEVO CAMPO
    'status' => isset($_POST['status']) ? (int)$_POST['status'] : 1
  ];

  // Limpiar youtube_url si está vacío
  if (empty(trim($data['youtube_url']))) {
    $data['youtube_url'] = null;
  }

  if (isset($_POST['edit']) && !empty($_POST['id'])) {
    // Actualizar post existente
    $id = (int)$_POST['id'];

    if (!$postsModel->exists($id)) {
      ResponseHelper::notFound('El post no existe');
    }

    $result = $postsModel->updatePost($id, $data);

    if ($result['success']) {
      ResponseHelper::success(['id' => $id], 'Post actualizado exitosamente');
    } else {
      ResponseHelper::validationError($result['errors']);
    }
  } else {
    // Crear nuevo post
    $result = $postsModel->createPost($data);

    if ($result['success']) {
      ResponseHelper::success(
        ['id' => $result['id']],
        'Post creado exitosamente',
        201
      );
    } else {
      ResponseHelper::validationError($result['errors']);
    }
  }
} catch (Exception $e) {
  error_log("Error en add_edit_post.php: " . $e->getMessage());
  ResponseHelper::serverError('Error al procesar la solicitud');
}
