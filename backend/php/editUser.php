<?php

	require_once('con.php');

	$username = $_POST['user_user'];
	$email = $_POST['user_email'];
	$id = (int)$_POST['user_id'];

	if (isset($_POST['pass'])) {
		$pass = md5($_POST['pass']);

		$sql = "UPDATE users SET user = :user, pass = :pass, email = :email WHERE id = '$id' ";
		
		$stmt = $db->prepare($sql);

		$stmt->bindValue(":pass", $pass, PDO::PARAM_STR);
		
	} else {
		$sql = "UPDATE users SET user = :user, email = :email WHERE id = '$id' ";
		
		$stmt = $db->prepare($sql);
	}

	$stmt->bindValue(":user", $username, PDO::PARAM_STR);
	$stmt->bindValue(":email", $email, PDO::PARAM_STR);
	
	$result = $stmt->execute();

	echo json_encode($result);

?>