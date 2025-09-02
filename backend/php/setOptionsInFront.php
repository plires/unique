<?php

	require_once('con.php');

	if (isset($_POST['images_products']) == 'on') {
		$setImage = "1";
	} else {
		$setImage = "0";
	}

	if (isset($_POST['active_mercado_pago']) == 'on') {
		$setMP = "1";
	} else {
		$setMP = "0";
	}

	$id = (int)$_POST['id'];

	$sql = "UPDATE store SET images_products = :images_products, active_mercado_pago = :active_mercado_pago WHERE id = '$id' ";

  $stmt = $db->prepare($sql);
  $stmt->bindValue(":images_products", $setImage, PDO::PARAM_STR);
  $stmt->bindValue(":active_mercado_pago", $setMP, PDO::PARAM_STR);
  $result = $stmt->execute();

  echo json_encode($result);

?>