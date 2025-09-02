<?php

	require_once('con.php');
  require_once('functions.php');

  $id = (int)$_POST['store_id'];

  $sql = "UPDATE store SET logo = :logo, name = :name, whatsapp = :whatsapp, phone = :phone, address = :address, city = :city, description = :description, delivery_cost = :delivery_cost, instagram = :instagram, facebook = :facebook WHERE id = '$id' ";

  $stmt = $db->prepare($sql);
  $stmt->bindValue(":logo", $_POST['store_logo'], PDO::PARAM_STR);
  $stmt->bindValue(":name", $_POST['store_name'], PDO::PARAM_STR);
  $stmt->bindValue(":whatsapp", $_POST['store_whatsapp'], PDO::PARAM_STR);
  $stmt->bindValue(":phone", $_POST['store_phone'], PDO::PARAM_STR);
  $stmt->bindValue(":address", $_POST['store_address'], PDO::PARAM_STR);
  $stmt->bindValue(":city", $_POST['store_city'], PDO::PARAM_STR);
  $stmt->bindValue(":description", $_POST['store_description'], PDO::PARAM_STR);
  $stmt->bindValue(":delivery_cost", $_POST['store_delivery_cost'], PDO::PARAM_STR);
  $stmt->bindValue(":instagram", $_POST['store_instagram'], PDO::PARAM_STR);
  $stmt->bindValue(":facebook", $_POST['store_facebook'], PDO::PARAM_STR);
  $result = $stmt->execute();

  echo json_encode($result);

?>