<?php

/**
 * API para obtener todos los jobs
 */

require_once('../../includes/config.inc.php');
require_once('../../clases/Jobs.php');
require_once('../../clases/ResponseHelper.php');

try {
  $jobsModel = new Jobs();
  $jobs = $jobsModel->getAll('id', 'DESC');

  ResponseHelper::success($jobs, 'Trabajos obtenidos exitosamente');
} catch (Exception $e) {
  error_log("Error en getJobs.php: " . $e->getMessage());
  ResponseHelper::serverError('Error al obtener los trabajos');
}
