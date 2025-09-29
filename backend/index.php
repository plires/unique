<?php

$user = '';
$password = '';
$errors = [];

if (isset($_POST['send'])) {

  if ($_POST['user'] == '') {
    $errors['user'] = 'Ingresá un usuario';
  } else {
    $user = $_POST['user'];
  }

  if ($_POST['password'] == '') {
    $errors['password'] = 'Ingresá la contraseña';
  } else {
    $password = $_POST['password'];
  }

  if (count($errors) === 0) {
    include('php/login.php');
  }
}
?>

<!doctype html>
<html lang="es">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Normalize CSS -->
  <link rel="stylesheet" href="css/normalize.css">

  <!-- Fontawesome CSS -->
  <link rel="stylesheet" href="css/all.min.css">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="css/bootstrap.min.css">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/app.css">

  <!-- Favicons -->
  <?php include('includes/favicon.inc.php'); ?>

  <title>Login - Tienda Online</title>
</head>

<body>
  <div id="app" class="h-100">

    <div class="login">

      <div class="container">
        <div class="row">
          <div class="col-md-6 offset-md-3">

            <?php if ($errors): ?>
              <div id="error" class="alert alert-danger alert-dismissible fade show fadeInLeft" role="alert">
                <strong>¡Por favor verificá los datos!</strong>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>

                <ul style="padding: 0;">
                  <?php foreach ($errors as $error) { ?>
                    <li> <?php echo $error; ?></li>
                  <?php } ?>
                </ul>

              </div>
            <?php endif ?>

            <form id="login" method="post" class="needs-validation" novalidate>
              <div class="form-group">
                <label for="user">Usuario</label>
                <input type="text" class="form-control" id="user" name="user" value="<?= $user ?>">
                <div class="invalid-feedback">
                  Ingrese el usuario.
                </div>
              </div>
              <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password">
                <div class="invalid-feedback">
                  Ingrese su pass.
                </div>
              </div>

              <button type="submit" name="send" class="btn btn-primary float-right">Enviar</button>
            </form>
            <a class="reset_pass transition" href="reset_pass.php">Olvide mi contraseña</a>
          </div>
        </div>
      </div>

    </div>

  </div>

  <!-- Optional JavaScript -->
  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="js/jquery-3.5.1.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
</body>

</html>