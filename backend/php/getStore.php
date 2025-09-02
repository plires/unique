<?php

require_once('con.php');

$sql = "SELECT * FROM store";
$stmt = $db->prepare($sql);
$stmt->execute();
echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));

?>