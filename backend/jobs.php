<?php

  session_start();

  if (!$_SESSION['user']){
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

    <!-- Normalize CSS -->
    <link rel="stylesheet" href="css/normalize.css">

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="css/all.min.css">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/app.css">

    <!-- Favicons -->
    <?php include('../includes/favicon.inc.php'); ?>

    <title>Operaciones - Tienda Online</title>
  </head>
  <body>
    <div id="app">

      <!-- Loader -->
      <?php require('includes/loader.inc.php'); ?>
      <!-- Loader end -->

      <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-5">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
          <a class="navbar-brand" href="#">
            <img src="../img/logo-unique-footer.png" alt="logo">
          </a>
          <ul class="navbar-nav mr-auto mt-2 mt-lg-0">
            <li class="nav-item active">
              <a class="nav-link" href="#" data-toggle="modal" data-target="#modalFormUser">Mi Usuario</a>
            </li>
            <li class="nav-item active">
              <a class="nav-link" href="php/logout.php">Logout</a>
            </li>
          </ul>
        </div>
      </nav>

      <div style="z-index: 9999;" v-if="messages.length" id="messages" class="alert alert-success alert-dismissible fade show fadeInLeft" role="alert">
        <strong>Operación realizada!</strong>
        <button @click="messages = []" type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <ul>
          <li  v-for="(message, index) in messages" :key="index">{{ message }}</li>
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
            <input v-model="q" class="form-control" type="search" placeholder="Search" aria-label="Search">
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

                    <td v-if="job.link == 0" class="text-center">
                      <i class="unlink fas fa-unlink"></i>
                    </td>
                    <td v-else class="text-center">
                      <a target="_blank" class="mas_info transition" :href="job.link"><i class="fab fa-linkedin"></i></a>
                    </td>

                    <td>
                      <div class="btn-group w-100" role="group">
                        <button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          Acciones
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                          <button @click="editJob(job.id)" class="dropdown-item"><i class="fas fa-edit mr-2"></i>Editar</button>
                          <button @click="setIdJobToDelete(job.id)" class="dropdown-item" data-toggle="modal" data-target="#formDelete"><i class="fas fa-trash-alt mr-2"></i>Eliminar</button>
                        </div>
                      </div>
                    </td>

                    <td>
                      <div class="custom-control custom-switch text-center">
                        <input @click="changeStatus(job.id, job.status)" :checked="job.status" type="checkbox" class="custom-control-input" :id=job.id>
                        <label title="habilitar / deshabilitar" class="custom-control-label" :for=job.id>  </label>
                      </div>
                    </td>

                  </tr>

                </tbody>
              </table>
            </div>
            <!-- Tabla end -->

          </div>
        </div>

      </div>

      <?php include_once('includes/modalAddEdit.inc.php') ?>
      <?php include_once('includes/modalDelete.inc.php') ?>
      <?php include_once('includes/modalUser.inc.php') ?>
      <?php include_once('includes/modalDescription.php') ?>

    </div>
    
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="js/jquery-3.5.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

    <!-- axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- versión de desarrollo, incluye advertencias de ayuda en la consola -->
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/vue"></script> -->
    <script src="js/app.js"></script>

  </body>
</html>