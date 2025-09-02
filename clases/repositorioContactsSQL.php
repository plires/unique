<?php

require_once("repositorioContacts.php");

class RepositorioContactsSQL extends repositorioContacts
{
  protected $conexion;

  public function __construct($conexion) 
  {
    $this->conexion = $conexion;
  }

  public function saveInBDD($post, $table)
  {

    switch ($table) {
      case 'contacts':
        $sql = "INSERT INTO contacts values(default, :name, :email, :comments, :origin, :date)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(":name", $post['name'], PDO::PARAM_STR);
        $stmt->bindValue(":email", $post['email'], PDO::PARAM_STR);
        $stmt->bindValue(":comments", $post['comments'], PDO::PARAM_STR);
        $stmt->bindValue(":origin", $post['origin'], PDO::PARAM_STR);
        $stmt->bindValue(":date", date("F j, Y, g:i a"), PDO::PARAM_STR);
        break;
      
      case 'talents':
        $sql = "INSERT INTO talents values(default, :name, :email, :company, :phone, :job, :location, :comments, :origin, :date)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(":name", $post['name'], PDO::PARAM_STR);
        $stmt->bindValue(":email", $post['email'], PDO::PARAM_STR);
        $stmt->bindValue(":company", $post['company'], PDO::PARAM_STR);
        $stmt->bindValue(":phone", $post['phone'], PDO::PARAM_STR);
        $stmt->bindValue(":job", $post['job'], PDO::PARAM_STR);
        $stmt->bindValue(":location", $post['location'], PDO::PARAM_STR);
        $stmt->bindValue(":comments", $post['comments'], PDO::PARAM_STR);
        $stmt->bindValue(":origin", $post['origin'], PDO::PARAM_STR);
        $stmt->bindValue(":date", date("F j, Y, g:i a"), PDO::PARAM_STR);
        break;

      case 'consults':
        $sql = "INSERT INTO consults values(default, :name, :email, :comments, :position, :location, :job_function, :employment_type, :origin, :date)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(":name", $post['name'], PDO::PARAM_STR);
        $stmt->bindValue(":email", $post['email'], PDO::PARAM_STR);
        $stmt->bindValue(":comments", $post['comments'], PDO::PARAM_STR);
        $stmt->bindValue(":position", $post['position'], PDO::PARAM_STR);
        $stmt->bindValue(":location", $post['location'], PDO::PARAM_STR);
        $stmt->bindValue(":job_function", $post['jobFunction'], PDO::PARAM_STR);
        $stmt->bindValue(":employment_type", $post['employmentType'], PDO::PARAM_STR);
        $stmt->bindValue(":origin", $post['origin'], PDO::PARAM_STR);
        $stmt->bindValue(":date", date("F j, Y, g:i a"), PDO::PARAM_STR);
        break;
    }
        
    $save = $stmt->execute();

    return $save;

  }

}

?>
