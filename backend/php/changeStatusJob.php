<?php

	require_once('con.php');

	if ( isset($_POST['id']) && isset($_POST['status']) ) {
		
		$id = $_POST['id'];
		$status = (int)$_POST['status'];

		if ($status) {
			$status = 0;
		} else {
			$status = 1;
		}

	} else {
		return false;exit;
	}

	$sql = "UPDATE jobs SET status = :status WHERE id = '$id' ";

  $stmt = $db->prepare($sql);
  $stmt->bindValue(":status", $status, PDO::PARAM_STR);
  $result = $stmt->execute();

  echo json_encode($result);
  
?>