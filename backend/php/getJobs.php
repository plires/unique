<?php

include('con.php');

$sql = "SELECT * FROM jobs";
$stmt = $db->prepare($sql);
$stmt->execute();
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($jobs, JSON_NUMERIC_CHECK);

?>