<?php
session_start();

if (!$_SESSION['user']) {
  header('location:index.php');
}

$messages = '';
$_SESSION['lang'] = 'es';

include_once('../includes/config.inc.php');
?>

<!doctype html>
<html lang="es">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- CSS -->
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/all.min.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/app.css">

  <!-- Favicons -->
  <?php include('../includes/favicon.inc.php'); ?>

  <title>Gestión de Trabajos - Unique Talent Solutions</title>
</head>

<body>
  <div id="app">

    <!-- Loader -->
    <?php require('includes/loader.inc.php'); ?>

    <!-- Loader -->
    <?php require('includes/header-backend.php'); ?>

    <!-- Mensajes de éxito -->
    <div style="z-index: 9999;" v-if="messages.length" id="messages" class="alert alert-success alert-dismissible fade show fadeInLeft" role="alert">
      <strong>Operación realizada!</strong>
      <button @click="messages = []" type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
      </button>
      <ul>
        <li v-for="(message, index) in messages" :key="index">{{ message }}</li>
      </ul>
    </div>

    <div class="container">
      <div class="row mb-5">
        <div class="col-md-12 text-center">
          <h1 class="mb-5">Listado de Trabajos</h1>
        </div>
      </div>

      <div class="row mb-0 sticky-top">
        <div class="col-md-12 text-center searchProduct">
          <input v-model="q" class="form-control" type="search" placeholder="Buscar..." aria-label="Search">
          <button @click="resetForm" data-toggle="modal" data-target="#modalAddJob" class="btn btn-primary">Agregar Trabajo</button>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <!-- Tabla -->
          <div class="tabla wow fadeInUp">
            <table class="table table-hover table-responsive-md">
              <thead>
                <tr>
                  <th>Posición</th>
                  <th>Ubicación</th>
                  <th>Función laboral</th>
                  <th>Tipo</th>
                  <th class="text-center">Desc.</th>
                  <th class="text-center">Li.</th>
                  <th class="text-center">Acción</th>
                  <th class="text-center">Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(job, index) in searchJob" :key="job.id">
                  <td>{{ job.position }}</td>
                  <td>{{ job.location }}</td>
                  <td>{{ job.job_function }}</td>
                  <td>{{ job.employment_type }}</td>

                  <td class="text-center">
                    <a @click="showDescription(job.description)" class="mas_info transition" href="#"><i class="fas fa-info-circle"></i></a>
                  </td>

                  <td v-if="job.link == 0 || !job.link" class="text-center">
                    <i class="unlink fas fa-unlink"></i>
                  </td>
                  <td v-else class="text-center">
                    <a target="_blank" class="mas_info transition" :href="job.link"><i class="fab fa-linkedin"></i></a>
                  </td>

                  <td>
                    <div class="btn-group w-100" role="group">
                      <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                        Acciones
                      </button>
                      <div class="dropdown-menu">
                        <button @click="editJob(job.id)" class="dropdown-item"><i class="fas fa-edit mr-2"></i>Editar</button>
                        <button @click="setIdJobToDelete(job.id)" class="dropdown-item" data-toggle="modal" data-target="#formDelete"><i class="fas fa-trash-alt mr-2"></i>Eliminar</button>
                      </div>
                    </div>
                  </td>

                  <td>
                    <div class="custom-control custom-switch text-center">
                      <input @click="changeStatus(job.id, job.status)" :checked="job.status == 1" type="checkbox" class="custom-control-input" :id="'switch' + job.id">
                      <label title="habilitar / deshabilitar" class="custom-control-label" :for="'switch' + job.id"></label>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <?php include_once('includes/modalAddEdit.inc.php') ?>
    <?php include_once('includes/modalDelete.inc.php') ?>
    <?php include_once('includes/modalUser.inc.php') ?>
    <?php include_once('includes/modalDescription.php') ?>
  </div>

  <!-- JavaScript -->
  <script src="js/jquery-3.5.1.min.js"></script>
  <script src="js/popper.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>

  <!-- Configuración dinámica desde PHP -->
  <script src="includes/js_config.php"></script>

  <!-- App principal -->
  <script src="js/app.js"></script>

</body>

</html>