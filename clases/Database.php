<?php

/**
 * Clase Database - Manejo centralizado de conexiones PDO
 */

class Database
{
  private static $instance = null;
  private $connection = null;

  private function __construct()
  {
    $this->connect();
  }

  /**
   * Singleton pattern para una sola instancia de conexión
   */
  public static function getInstance()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
   * Establecer conexión PDO
   */
  private function connect()
  {
    try {
      $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
      ];

      $this->connection = new PDO(DSN, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
      $this->handleError("Error de conexión a la base de datos: " . $e->getMessage());
    }
  }

  /**
   * Obtener la conexión PDO
   */
  public function getConnection()
  {
    return $this->connection;
  }

  /**
   * Ejecutar consulta preparada
   */
  public function execute($sql, $params = [])
  {
    try {
      $stmt = $this->connection->prepare($sql);
      $result = $stmt->execute($params);
      return $stmt;
    } catch (PDOException $e) {
      $this->handleError("Error ejecutando consulta: " . $e->getMessage() . " | SQL: " . $sql);
      return false;
    }
  }

  /**
   * Obtener todos los registros
   */
  public function fetchAll($sql, $params = [])
  {
    $stmt = $this->execute($sql, $params);
    return $stmt ? $stmt->fetchAll() : [];
  }

  /**
   * Obtener un solo registro
   */
  public function fetch($sql, $params = [])
  {
    $stmt = $this->execute($sql, $params);
    return $stmt ? $stmt->fetch() : null;
  }

  /**
   * Insertar registro y retornar ID
   */
  public function insert($table, $data)
  {
    $fields = implode(',', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));

    $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";

    if ($this->execute($sql, $data)) {
      return $this->connection->lastInsertId();
    }
    return false;
  }

  /**
   * Actualizar registros
   */
  public function update($table, $data, $where, $whereParams = [])
  {
    $fields = [];
    foreach ($data as $key => $value) {
      $fields[] = "{$key} = :{$key}";
    }
    $fieldsString = implode(', ', $fields);

    $sql = "UPDATE {$table} SET {$fieldsString} WHERE {$where}";

    $params = array_merge($data, $whereParams);
    return $this->execute($sql, $params) !== false;
  }

  /**
   * Eliminar registros
   */
  public function delete($table, $where, $params = [])
  {
    $sql = "DELETE FROM {$table} WHERE {$where}";
    return $this->execute($sql, $params) !== false;
  }

  /**
   * Contar registros
   */
  public function count($table, $where = '1=1', $params = [])
  {
    $sql = "SELECT COUNT(*) as total FROM {$table} WHERE {$where}";
    $result = $this->fetch($sql, $params);
    return $result ? $result['total'] : 0;
  }

  /**
   * Iniciar transacción
   */
  public function beginTransaction()
  {
    return $this->connection->beginTransaction();
  }

  /**
   * Confirmar transacción
   */
  public function commit()
  {
    return $this->connection->commit();
  }

  /**
   * Revertir transacción
   */
  public function rollback()
  {
    return $this->connection->rollBack();
  }

  /**
   * Manejo centralizado de errores
   */
  private function handleError($message)
  {
    // Log del error
    error_log($message);

    // En desarrollo, mostrar el error
    if (defined('APP_DEBUG') && env('APP_DEBUG', false)) {
      die($message);
    }

    // En producción, mostrar mensaje genérico
    die('Error interno del sistema. Intente nuevamente.');
  }

  /**
   * Prevenir clonación
   */
  private function __clone() {}

  /**
   * Prevenir deserialización
   */
  public function __wakeup()
  {
    throw new Exception("No se puede deserializar singleton");
  }
}
