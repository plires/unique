<?php

include('con.php');

$sql = "SELECT location FROM jobs WHERE status = 1 GROUP BY location;";
$stmt = $db->prepare($sql);
$stmt->execute();
$locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($locations);

?>