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
   * Establecer conexión PDO - CORREGIDO para soporte de emojis
   */
  private function connect()
  {
    try {
      // Verificar que las constantes están definidas
      if (!defined('DSN') || !defined('DB_USER') || !defined('DB_PASS')) {
        throw new Exception("Las constantes de base de datos no están definidas. Verifique config.inc.php");
      }

      $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        // CORREGIDO: Mejor configuración para utf8mb4 y emojis
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
      ];

      $this->connection = new PDO(DSN, DB_USER, DB_PASS, $options);

      // NUEVO: Asegurar charset utf8mb4 después de la conexión
      $this->connection->exec("SET CHARACTER SET utf8mb4");
      $this->connection->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    } catch (PDOException $e) {
      $this->handleError("Error de conexión a la base de datos: " . $e->getMessage());
    } catch (Exception $e) {
      $this->handleError("Error de configuración: " . $e->getMessage());
    }
  }

  /**
   * Obtener la conexión PDO
   */
  public function getConnection()
  {
    if ($this->connection === null) {
      $this->connect();
    }
    return $this->connection;
  }

  /**
   * Ejecutar consulta preparada
   */
  public function execute($sql, $params = [])
  {
    try {
      if ($this->connection === null) {
        throw new Exception("No hay conexión a la base de datos");
      }

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
   * Ejecutar consulta SQL personalizada (para métodos específicos)
   */
  public function query($sql, $params = [])
  {
    return $this->fetchAll($sql, $params);
  }

  /**
   * Iniciar transacción
   */
  public function beginTransaction()
  {
    return $this->connection ? $this->connection->beginTransaction() : false;
  }

  /**
   * Confirmar transacción
   */
  public function commit()
  {
    return $this->connection ? $this->connection->commit() : false;
  }

  /**
   * Revertir transacción
   */
  public function rollback()
  {
    return $this->connection ? $this->connection->rollBack() : false;
  }

  /**
   * Verificar si hay conexión activa
   */
  public function isConnected()
  {
    try {
      return $this->connection && $this->connection->query('SELECT 1');
    } catch (PDOException $e) {
      return false;
    }
  }

  /**
   * Manejo centralizado de errores - CORREGIDO
   */
  private function handleError($message)
  {
    // Log del error
    error_log($message);

    // En desarrollo, mostrar el error detallado
    if (defined('APP_DEBUG') && APP_DEBUG === true) {
      die('<div style="background:#f8d7da;color:#721c24;padding:20px;border:1px solid #f5c6cb;border-radius:5px;margin:20px;font-family:monospace;"><strong>Database Error:</strong><br>' . htmlspecialchars($message) . '</div>');
    }

    // En producción, mostrar mensaje genérico
    die('<div style="background:#d1ecf1;color:#0c5460;padding:20px;border:1px solid #bee5eb;border-radius:5px;margin:20px;"><strong>Error interno del sistema.</strong><br>Intente nuevamente más tarde.</div>');
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

  /**
   * Destructor - cerrar conexión
   */
  public function __destruct()
  {
    $this->connection = null;
  }
}
