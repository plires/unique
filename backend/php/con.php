<?php

include('config.inc.php');

$dsn = DSN;
$usuario = DB_USER;
$password = DB_PASS;

try {
  $db = new PDO($dsn, $usuario, $password);
} catch (Exception $e) {
  echo 'No se pudo conectar a la base de datos, intente mas tarde...';
}

?>