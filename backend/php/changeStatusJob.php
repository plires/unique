<?php

/**
 * API para cambiar estado de jobs (activo/inactivo)
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

	// Cambiar estado
	$success = $jobsModel->toggleJobStatus($id);

	if ($success) {
		ResponseHelper::success(null, 'Estado del trabajo cambiado exitosamente');
	} else {
		ResponseHelper::serverError('Error al cambiar el estado del trabajo');
	}
} catch (Exception $e) {
	error_log("Error en changeStatusJob.php: " . $e->getMessage());
	ResponseHelper::serverError('Error al procesar la solicitud');
}
