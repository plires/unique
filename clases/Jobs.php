<?php

/**
 * Modelo para la gestión de Jobs
 */

require_once 'BaseCRUD.php';

class Jobs extends BaseCRUD
{

  protected $fillable = [
    'position',
    'location',
    'job_function',
    'employment_type',
    'description',
    'link',
    'status'
  ];

  public function __construct()
  {
    parent::__construct('jobs');
  }

  /**
   * Obtener jobs activos ordenados por ID descendente
   */
  public function getActiveJobs()
  {
    return $this->getWhere('status = :status', ['status' => 1], 'id', 'DESC');
  }

  /**
   * Buscar jobs por criterios específicos
   */
  public function searchJobs($filters = [])
  {
    $conditions = ['status = 1']; // Solo activos
    $params = [];

    if (!empty($filters['position'])) {
      $conditions[] = 'position LIKE :position';
      $params['position'] = '%' . $filters['position'] . '%';
    }

    if (!empty($filters['location'])) {
      $conditions[] = 'location LIKE :location';
      $params['location'] = '%' . $filters['location'] . '%';
    }

    if (!empty($filters['job_function'])) {
      $conditions[] = 'job_function LIKE :job_function';
      $params['job_function'] = '%' . $filters['job_function'] . '%';
    }

    if (!empty($filters['employment_type'])) {
      $conditions[] = 'employment_type LIKE :employment_type';
      $params['employment_type'] = '%' . $filters['employment_type'] . '%';
    }

    $where = implode(' AND ', $conditions);
    return $this->getWhere($where, $params, 'id', 'DESC');
  }

  /**
   * Obtener valores únicos para filtros de selección
   */
  public function getUniqueValues($field)
  {
    $sql = "SELECT DISTINCT {$field} FROM {$this->table} 
                WHERE {$field} IS NOT NULL AND {$field} != '' AND status = 1
                ORDER BY {$field}";

    $results = $this->db->fetchAll($sql);
    return array_column($results, $field);
  }

  /**
   * Obtener todas las ubicaciones únicas
   */
  public function getUniqueLocations()
  {
    return $this->getUniqueValues('location');
  }

  /**
   * Obtener todas las funciones laborales únicas
   */
  public function getUniqueJobFunctions()
  {
    return $this->getUniqueValues('job_function');
  }

  /**
   * Obtener todos los tipos de empleo únicos
   */
  public function getUniqueEmploymentTypes()
  {
    return $this->getUniqueValues('employment_type');
  }

  /**
   * Validaciones específicas para jobs
   */
  protected function validate($data, $id = null)
  {
    $errors = [];

    if (empty($data['position'])) {
      $errors[] = 'La posición es obligatoria';
    }

    if (empty($data['location'])) {
      $errors[] = 'La ubicación es obligatoria';
    }

    if (empty($data['job_function'])) {
      $errors[] = 'La función laboral es obligatoria';
    }

    if (empty($data['employment_type'])) {
      $errors[] = 'El tipo de empleo es obligatorio';
    }

    // Validar URL si se proporciona link
    if (!empty($data['link']) && !filter_var($data['link'], FILTER_VALIDATE_URL)) {
      $errors[] = 'El link debe ser una URL válida';
    }

    return $errors;
  }

  /**
   * Crear job con validaciones
   */
  public function createJob($data)
  {
    $errors = $this->validate($data);

    if (!empty($errors)) {
      return ['success' => false, 'errors' => $errors];
    }

    // Establecer valores por defecto
    $data['status'] = $data['status'] ?? 1;
    $data['link'] = empty($data['link']) ? null : $data['link'];

    $jobId = $this->create($data);

    if ($jobId) {
      return ['success' => true, 'id' => $jobId];
    } else {
      return ['success' => false, 'errors' => ['Error al crear el job']];
    }
  }

  /**
   * Actualizar job con validaciones
   */
  public function updateJob($id, $data)
  {
    $errors = $this->validate($data, $id);

    if (!empty($errors)) {
      return ['success' => false, 'errors' => $errors];
    }

    $data['link'] = empty($data['link']) ? null : $data['link'];

    $success = $this->update($id, $data);

    if ($success) {
      return ['success' => true];
    } else {
      return ['success' => false, 'errors' => ['Error al actualizar el job']];
    }
  }

  /**
   * Cambiar estado de job (activo/inactivo)
   */
  public function toggleJobStatus($id)
  {
    return $this->toggleStatus($id, 'status');
  }

  /**
   * Obtener estadísticas de jobs
   */
  public function getStats()
  {
    $total = $this->count();
    $active = $this->count('status = 1');
    $inactive = $this->count('status = 0');

    return [
      'total' => $total,
      'active' => $active,
      'inactive' => $inactive
    ];
  }
}
