<?php
// TODO: revisar este archivo para ver si se puede eliminar y usar directamente Database
/**
 * DEPRECATED: Este archivo se mantiene por compatibilidad
 * Use la clase Database en su lugar
 */

// Detectar la ruta correcta basada en cómo se está ejecutando
if (isset($_SERVER['SCRIPT_FILENAME']) && strpos($_SERVER['SCRIPT_FILENAME'], '/backend/php/') !== false) {
  // Se está ejecutando directamente desde backend/php/ (ej: getUser.php)
  require_once('../../includes/config.inc.php');
  require_once('../../clases/Database.php');
} else {
  // Se está ejecutando desde backend/ (ej: index.php -> login.php -> con.php)
  require_once('../includes/config.inc.php');
  require_once('../clases/Database.php');
}

// Para compatibilidad con código existente, crear la conexión PDO directa
$db = Database::getInstance()->getConnection();

// Mantener variables de compatibilidad
$dsn = DSN;
$usuario = DB_USER;
$password = DB_PASS;
