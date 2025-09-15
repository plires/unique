<?php
session_start();

if (!$_SESSION['user']) {
  header('location:index.php');
}

$_SESSION['lang'] = 'es';
include_once('../includes/config.inc.php');
?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- CSS -->
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/all.min.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/app.css">

  <!-- Editor de texto enriquecido -->
  <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

  <!-- Favicons -->
  <?php include('../includes/favicon.inc.php'); ?>

  <title>Gestión de Posts - Unique Talent Solutions</title>

  <style>
    .ql-editor {
      min-height: 200px;
    }

    .media-preview {
      max-width: 100px;
      max-height: 100px;
      object-fit: cover;
      margin: 5px;
      border-radius: 5px;
    }

    .media-item {
      position: relative;
      display: inline-block;
      margin: 5px;
    }

    .media-item .btn-remove {
      position: absolute;
      top: -5px;
      right: -5px;
      width: 20px;
      height: 20px;
      padding: 0;
      border-radius: 50%;
      font-size: 12px;
      line-height: 1;
    }

    .post-content {
      max-height: 100px;
      overflow: hidden;
      text-overflow: ellipsis;
    }
  </style>
</head>

<body>
  <div id="app">

    <!-- Loader -->
    <?php require('includes/loader.inc.php'); ?>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-5">
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo01">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
        <a class="navbar-brand" href="#">
          <img src="../img/logo-unique-footer.png" alt="logo">
        </a>
        <ul class="navbar-nav mr-auto mt-2 mt-lg-0">
          <li class="nav-item">
            <a class="nav-link" href="jobs.php">Trabajos</a>
          </li>
          <li class="nav-item active">
            <a class="nav-link" href="posts.php">Posts</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#" data-toggle="modal" data-target="#modalFormUser">Mi Usuario</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="php/logout.php">Logout</a>
          </li>
        </ul>
      </div>
    </nav>

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

    <div class="container-fluid">
      <div class="row mb-5">
        <div class="col-md-12 text-center">
          <h1 class="mb-5">Gestión de Posts</h1>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-8">
          <input v-model="searchQuery" class="form-control" type="search" placeholder="Buscar posts..." aria-label="Search">
        </div>
        <div class="col-md-4 text-right">
          <button @click="openCreateModal" class="btn btn-primary">
            <i class="fas fa-plus"></i> Crear Post
          </button>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <!-- Tabla de Posts -->
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Título</th>
                  <th>Contenido</th>
                  <th class="text-center">Imágenes</th>
                  <th class="text-center">Videos</th>
                  <th class="text-center">Fecha</th>
                  <th class="text-center">Estado</th>
                  <th class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="post in filteredPosts" :key="post.id">
                  <td>{{ post.id }}</td>
                  <td>
                    <strong>{{ post.title }}</strong>
                  </td>
                  <td>
                    <div class="post-content" v-html="post.content"></div>
                  </td>
                  <td class="text-center">
                    <span class="badge badge-info">{{ post.total_images || 0 }}</span>
                  </td>
                  <td class="text-center">
                    <span class="badge badge-info">{{ post.total_videos || 0 }}</span>
                  </td>
                  <td class="text-center">
                    <small>{{ formatDate(post.created_at) }}</small>
                  </td>
                  <td class="text-center">
                    <div class="custom-control custom-switch">
                      <input
                        @click="toggleStatus(post.id, post.status)"
                        :checked="post.status == 1"
                        type="checkbox"
                        class="custom-control-input"
                        :id="'switch' + post.id">
                      <label class="custom-control-label" :for="'switch' + post.id"></label>
                    </div>
                  </td>
                  <td class="text-center">
                    <div class="btn-group" role="group">
                      <button @click="editPost(post.id)" class="btn btn-sm btn-outline-primary" title="Editar">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button @click="manageMedia(post.id)" class="btn btn-sm btn-outline-info" title="Gestionar Medios">
                        <i class="fas fa-images"></i>
                      </button>
                      <button @click="setPostToDelete(post.id)" class="btn btn-sm btn-outline-danger" title="Eliminar" data-toggle="modal" data-target="#deleteModal">
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>

                <tr v-if="posts.length === 0">
                  <td colspan="8" class="text-center">
                    <em>No hay posts disponibles</em>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Crear/Editar Post -->
    <?php include_once('includes/modal-create-edit-post.php') ?>

    <!-- Modal Gestionar Medios -->
    <?php include_once('includes/modal-manage-media.php') ?>

    <!-- Modal Eliminar Post -->
    <?php include_once('includes/modal-delete-post.php') ?>

    <!-- Incluir modales de usuario existentes -->
    <?php include_once('includes/modalUser.inc.php') ?>
  </div>

  <!-- JavaScript -->
  <script src="js/jquery-3.5.1.min.js"></script>
  <script src="js/popper.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>

  <!-- Editor de texto -->
  <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

  <!-- Configuración dinámica -->
  <script src="includes/js_config.php"></script>

  <!-- App de Posts -->
  <script src="js/posts-app.js"></script>

</body>

</html>