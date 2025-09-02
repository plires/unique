<?php

include('con.php');

$sql = "SELECT * FROM users;";
$stmt = $db->prepare($sql);
$stmt->execute();
echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));

?>