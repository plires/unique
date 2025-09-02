<?php

include('con.php');

$sql = "SELECT employment_type FROM jobs WHERE status = 1 GROUP BY employment_type;";
$stmt = $db->prepare($sql);
$stmt->execute();
$employmentType = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($employmentType);

?>