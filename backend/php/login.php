<?php

include('con.php');

$sql = "SELECT * FROM users;";
$stmt = $db->prepare($sql);
$stmt->execute();
$userBdd = ($stmt->fetch(PDO::FETCH_ASSOC));

$user = $_POST['user'];
$pass = $_POST['password'];

if ($userBdd['user'] === $user && $userBdd['pass'] === md5($pass)) {
	session_start();
	$_SESSION['id'] = (int)$user;
	$_SESSION['user'] = $pass;
	header('Location: posts.php');
} else {
	$errors['match'] = 'usuario o Contraseña incorrecta.';
}
