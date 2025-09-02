<?php

/**
 * Clase base para operaciones CRUD genéricas
 */

require_once 'Database.php';

class BaseCRUD
{
  protected $db;
  protected $table;
  protected $primaryKey = 'id';
  protected $fillable = []; // Campos permitidos para inserción/actualización
  protected $timestamps = false; // Si maneja created_at/updated_at

  public function __construct($table)
  {
    $this->db = Database::getInstance();
    $this->table = $table;
  }

  /**
   * Obtener todos los registros
   */
  public function getAll($orderBy = null, $orderDirection = 'ASC')
  {
    $sql = "SELECT * FROM {$this->table}";

    if ($orderBy) {
      $sql .= " ORDER BY {$orderBy} {$orderDirection}";
    }

    return $this->db->fetchAll($sql);
  }

  /**
   * Obtener registro por ID
   */
  public function getById($id)
  {
    $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
    return $this->db->fetch($sql, ['id' => $id]);
  }

  /**
   * Obtener registros con filtros
   */
  public function getWhere($conditions, $params = [], $orderBy = null, $orderDirection = 'ASC')
  {
    $sql = "SELECT * FROM {$this->table} WHERE {$conditions}";

    if ($orderBy) {
      $sql .= " ORDER BY {$orderBy} {$orderDirection}";
    }

    return $this->db->fetchAll($sql, $params);
  }

  /**
   * Crear nuevo registro
   */
  public function create($data)
  {
    $filteredData = $this->filterFillableData($data);

    if ($this->timestamps) {
      $filteredData['created_at'] = date('Y-m-d H:i:s');
      $filteredData['updated_at'] = date('Y-m-d H:i:s');
    }

    return $this->db->insert($this->table, $filteredData);
  }

  /**
   * Actualizar registro
   */
  public function update($id, $data)
  {
    $filteredData = $this->filterFillableData($data);

    if ($this->timestamps) {
      $filteredData['updated_at'] = date('Y-m-d H:i:s');
    }

    return $this->db->update(
      $this->table,
      $filteredData,
      "{$this->primaryKey} = :id",
      ['id' => $id]
    );
  }

  /**
   * Eliminar registro
   */
  public function delete($id)
  {
    return $this->db->delete(
      $this->table,
      "{$this->primaryKey} = :id",
      ['id' => $id]
    );
  }

  /**
   * Contar registros
   */
  public function count($where = '1=1', $params = [])
  {
    return $this->db->count($this->table, $where, $params);
  }

  /**
   * Verificar si existe un registro
   */
  public function exists($id)
  {
    return $this->getById($id) !== null;
  }

  /**
   * Búsqueda con paginación
   */
  public function paginate($page = 1, $perPage = 10, $conditions = '1=1', $params = [], $orderBy = null)
  {
    $offset = ($page - 1) * $perPage;

    $sql = "SELECT * FROM {$this->table} WHERE {$conditions}";

    if ($orderBy) {
      $sql .= " ORDER BY {$orderBy}";
    }

    $sql .= " LIMIT {$perPage} OFFSET {$offset}";

    $data = $this->db->fetchAll($sql, $params);
    $total = $this->count($conditions, $params);

    return [
      'data' => $data,
      'total' => $total,
      'page' => $page,
      'per_page' => $perPage,
      'total_pages' => ceil($total / $perPage)
    ];
  }

  /**
   * Buscar registros por texto en múltiples campos
   */
  public function search($query, $fields = [])
  {
    if (empty($fields)) {
      throw new InvalidArgumentException("Se debe especificar al menos un campo para buscar");
    }

    $conditions = [];
    $params = [];

    foreach ($fields as $field) {
      $conditions[] = "{$field} LIKE :query";
    }

    $where = implode(' OR ', $conditions);
    $params['query'] = "%{$query}%";

    return $this->getWhere($where, $params);
  }

  /**
   * Cambiar estado de un registro (activo/inactivo)
   */
  public function toggleStatus($id, $statusField = 'status')
  {
    $current = $this->getById($id);
    if (!$current) {
      return false;
    }

    $newStatus = $current[$statusField] ? 0 : 1;
    return $this->update($id, [$statusField => $newStatus]);
  }

  /**
   * Filtrar datos según campos permitidos
   */
  protected function filterFillableData($data)
  {
    if (empty($this->fillable)) {
      return $data; // Si no hay restricciones, permitir todo
    }

    $filtered = [];
    foreach ($this->fillable as $field) {
      if (isset($data[$field])) {
        $filtered[$field] = $data[$field];
      }
    }

    return $filtered;
  }

  /**
   * Validar datos antes de insertar/actualizar
   * Los hijos pueden sobrescribir este método
   */
  protected function validate($data, $id = null)
  {
    return []; // Retorna array vacío si no hay errores
  }

  /**
   * Crear o actualizar registro (upsert)
   */
  public function createOrUpdate($data, $id = null)
  {
    if ($id && $this->exists($id)) {
      return $this->update($id, $data);
    } else {
      return $this->create($data);
    }
  }

  /**
   * Obtener registros activos (con status = 1)
   */
  public function getActive($orderBy = null, $orderDirection = 'ASC')
  {
    return $this->getWhere('status = :status', ['status' => 1], $orderBy, $orderDirection);
  }

  /**
   * Ejecutar consulta personalizada
   */
  protected function query($sql, $params = [])
  {
    return $this->db->fetchAll($sql, $params);
  }

  /**
   * Ejecutar consulta que retorna un solo resultado
   */
  protected function queryOne($sql, $params = [])
  {
    return $this->db->fetch($sql, $params);
  }
}
