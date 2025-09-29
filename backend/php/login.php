<?php

include('con.php');

$sql = "SELECT * FROM users WHERE user = :user AND pass = :password;";
$stmt = $db->prepare($sql);

$stmt->execute([
	':user' => $_POST['user'],
	':password' => md5($_POST['password'])
]);

$userBdd = $stmt->fetch(PDO::FETCH_ASSOC);

if ($userBdd) {
	session_start();
	$_SESSION['id'] = (int)$userBdd['id'];
	$_SESSION['user'] = $userBdd['user'];
	header('Location: posts.php');
} else {
	$errors['match'] = 'usuario o Contraseña incorrecta.';
}
