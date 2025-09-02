<?php

	require_once('con.php');

  // Id del producto a eliminar
  $id = (int)$_POST['id'];
        
  $sql = "DELETE FROM jobs WHERE id='$id'";
  $stmt = $db->prepare($sql);
  $result = $stmt->execute();

  echo json_encode($result);
	
?>