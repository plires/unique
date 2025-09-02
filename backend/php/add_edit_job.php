<?php

/**
 * API para crear y editar jobs
 */

require_once('../../includes/config.inc.php');
require_once('../../clases/Jobs.php');
require_once('../../clases/ResponseHelper.php');

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  ResponseHelper::error('Método no permitido', null, 405);
}

try {
  $jobsModel = new Jobs();

  // Obtener datos del POST
  $data = [
    'position' => $_POST['position'] ?? '',
    'location' => $_POST['location'] ?? '',
    'job_function' => $_POST['job_function'] ?? '',
    'employment_type' => $_POST['employment_type'] ?? '',
    'description' => $_POST['description'] ?? '',
    'link' => !empty($_POST['link']) ? $_POST['link'] : null
  ];

  if (isset($_POST['edit']) && !empty($_POST['id'])) {
    // Actualizar job existente
    $id = (int)$_POST['id'];

    if (!$jobsModel->exists($id)) {
      ResponseHelper::notFound('El trabajo no existe');
    }

    $result = $jobsModel->updateJob($id, $data);

    if ($result['success']) {
      ResponseHelper::success(null, 'Trabajo actualizado exitosamente');
    } else {
      ResponseHelper::validationError($result['errors']);
    }
  } else {
    // Crear nuevo job
    $data['status'] = 1; // Activo por defecto
    $result = $jobsModel->createJob($data);

    if ($result['success']) {
      ResponseHelper::success(
        ['id' => $result['id']],
        'Trabajo creado exitosamente',
        201
      );
    } else {
      ResponseHelper::validationError($result['errors']);
    }
  }
} catch (Exception $e) {
  error_log("Error en add_edit_job.php: " . $e->getMessage());
  ResponseHelper::serverError('Error al procesar la solicitud');
}
