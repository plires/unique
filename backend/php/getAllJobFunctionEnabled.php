<?php

include('con.php');

$sql = "SELECT job_function FROM jobs WHERE status = 1 GROUP BY job_function;";
$stmt = $db->prepare($sql);
$stmt->execute();
$jobFunction = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($jobFunction);

?>