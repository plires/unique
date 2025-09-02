<?php

	require_once('con.php');
  require_once('functions.php');

  // Id del producto a editar
  $id = (int)$_POST['id'];

  $sql = "
    UPDATE products SET name = :name, category = :category, description = :description, price = :price WHERE id = '$id' 
  ";

  $stmt = $db->prepare($sql);
  $stmt->bindValue(":name", $_POST['name'], PDO::PARAM_STR);
  $stmt->bindValue(":category", $_POST['category'], PDO::PARAM_STR);
  $stmt->bindValue(":description", $_POST['description'], PDO::PARAM_STR);
  $stmt->bindValue(":price", $_POST['price'], PDO::PARAM_STR);
  $result = $stmt->execute();

	echo json_encode($result);

?>