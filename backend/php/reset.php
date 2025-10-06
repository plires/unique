<?php

include('con.php');
include('functions.php');
require_once("./../clases/app.php");

$sql = "SELECT * FROM users;";
$stmt = $db->prepare($sql);
$stmt->execute();
$userBdd = ($stmt->fetch(PDO::FETCH_ASSOC));

$id = (int)$userBdd['id'];

$email = $_POST['email'];

if ($userBdd['email'] === $email) {

	$passNew = generateStrongPassword();

	$app = new App;
	$_POST['pass'] = $passNew;
	$sendClient = $app->sendEmail('Cliente', 'Reset Pass Cliente', $_POST);

	$sql = "UPDATE users SET pass = :pass WHERE id = '$id' ";

	$stmt = $db->prepare($sql);
	$stmt->bindValue(":pass", md5($passNew), PDO::PARAM_STR);
	$result = $stmt->execute();

	if ($result != false) {
		$message = 'La nueva contraseña se envio al email registrado. Verifica tu casilla. No olvides revisar en SPAM ;)';
	}
} else {
	$errors['match'] = 'Ingresá el email con el que te registraste.';
}
