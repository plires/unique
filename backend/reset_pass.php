<?php 

  $email = '';
  $errors = [];
  $message = [];

  if (isset($_POST['send'])) {
  
    if ($_POST['email'] == '') {
      $errors['email'] = 'Ingresá el email con el que te registraste';
    } else {
      $email = $_POST['email'];
    }
    
    if (count($errors) === 0) {
      include('php/reset.php');
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

    <title>Reset Pass - Tienda Online</title>
  </head>
  <body>
    <div id="app" class="h-100">

      <div class="reset">

        <div class="reset">
          <div class="row">
            <div class="col-md-6 offset-md-3">

              <div class="jumbotron">

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

                <?php if ($message): ?>

                  <?php $email = ''; ?>
                  
                  <div class="alert alert-success alert-dismissible fade show fadeInLeft" role="alert">
                    <strong>¡Contraseña reseteada!</strong>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>

                    <p><?php echo $message; ?></p>

                  </div>
                <?php endif ?>

                <form id="login" method="post">

                  <div class="form-group">
                    <label for="email">Email del usuario</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= $email ?>">
                  </div>

                  <button type="submit" name="send" class="btn btn-primary float-right">Resetear Contraseña</button>
                  <a href="./" type="button" class="btn btn-secondary float-right  mr-3">Volver</a>
                </form>
                
              </div>

            </div>
          </div>
        </div>

      </div>
      
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="js/jquery-3.5.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

    <!-- axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- versión de desarrollo, incluye advertencias de ayuda en la consola -->
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <script src="js/app.js"></script>
  </body>
</html>