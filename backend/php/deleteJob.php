<?php

/**
 * API para eliminar jobs
 */

require_once('../includes/config.inc.php');
require_once('../clases/Jobs.php');
require_once('../clases/ResponseHelper.php');

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  ResponseHelper::error('Método no permitido', null, 405);
}

try {
  if (empty($_POST['id'])) {
    ResponseHelper::error('ID de trabajo es requerido');
  }

  $id = (int)$_POST['id'];
  $jobsModel = new Jobs();

  // Verificar que el job existe
  if (!$jobsModel->exists($id)) {
    ResponseHelper::notFound('El trabajo no existe');
  }

  // Eliminar el job
  $success = $jobsModel->delete($id);

  if ($success) {
    ResponseHelper::success(null, 'Trabajo eliminado exitosamente');
  } else {
    ResponseHelper::serverError('Error al eliminar el trabajo');
  }
} catch (Exception $e) {
  error_log("Error en deleteJob.php: " . $e->getMessage());
  ResponseHelper::serverError('Error al procesar la solicitud');
}
