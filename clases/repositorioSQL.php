<?php

require_once("repositorio.php");
require_once("repositorioContactsSQL.php");

class RepositorioSQL extends Repositorio {

  protected $conexion;

  /**
   * [__construct Establece la conexion con la base]
   */
  public function __construct() {

    try {
      $this->conexion = new PDO(DSN, DB_USER, DB_PASS);
    } catch (Exception $e) {
      echo 'No se pudo conectar a la base de datos. Intente en un momento por favor...';
    }

    $this->repositorioContacts = new RepositorioContactsSQL($this->conexion);

  }
}

?>
