<?php

	require_once('con.php');
  require_once('functions.php');

	if (isset($_POST['id'])) {
		$id = (int)$_POST['id'];
	} else {
		$messages = ['Error, vuelva a intentar'];
		echo json_encode($messages);
		exit();
	}

	$uploadImage = uploadImage($id);

	if ($uploadImage['status']) {

		$sql = "UPDATE products SET image = :image WHERE id = '$id' ";

		$stmt = $db->prepare($sql);
		$stmt->bindValue(":image", $uploadImage['name_image'], PDO::PARAM_STR);
		$result = $stmt->execute();

		echo json_encode($result);
		
	} else {
		echo json_encode($uploadImage['errors']);
	}

?>