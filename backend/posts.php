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

    .language-filter .btn-group {
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      border-radius: 6px;
    }

    .language-filter .btn {
      border-radius: 0;
      font-weight: 500;
      transition: all 0.2s ease;
    }

    .language-filter .btn:first-child {
      border-top-left-radius: 6px;
      border-bottom-left-radius: 6px;
    }

    .language-filter .btn:last-child {
      border-top-right-radius: 6px;
      border-bottom-right-radius: 6px;
    }

    .language-filter .btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }

    .alert-sm {
      font-size: 0.875rem;
    }

    .post-title {
      font-weight: 500;
      color: #333;
    }

    .post-content {
      color: #666;
      font-size: 0.85rem;
    }

    /* Mejorar apariencia de las banderas de idioma en la tabla */
    .table td .badge {
      font-size: 0.8rem;
      padding: 0.4rem 0.6rem;
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
        <div class="col-md-6">
          <small class="text-muted d-block mb-1">Buscar Posts:</small>
          <input v-model="searchQuery" class="form-control" type="search" placeholder="Buscar posts..." aria-label="Search">
        </div>

        <!-- NUEVO: Filtro de idiomas -->
        <div class="col-md-3">
          <div class="language-filter">
            <small class="text-muted d-block mb-1">Filtrar por idioma:</small>
            <div class="btn-group btn-group-sm w-100" role="group" aria-label="Filtros de idioma">
              <button
                type="button"
                @click="setLanguageFilter('all')"
                :class="['btn', languageFilter === 'all' ? 'btn-dark' : 'btn-outline-dark']"
                title="Mostrar todos los idiomas">
                <i class="fas fa-globe"></i> Todos
              </button>
              <button
                type="button"
                @click="setLanguageFilter('es')"
                :class="['btn', languageFilter === 'es' ? 'btn-primary' : 'btn-outline-primary']"
                title="Solo posts en español">
                🇪🇸 ES
              </button>
              <button
                type="button"
                @click="setLanguageFilter('en')"
                :class="['btn', languageFilter === 'en' ? 'btn-success' : 'btn-outline-success']"
                title="Solo posts en inglés">
                🇺🇸 EN
              </button>
            </div>
          </div>
        </div>

        <div class="col-md-3 text-right">
          <button @click="openCreateModal" class="btn btn-primary">
            <i class="fas fa-plus"></i> Crear Post
          </button>
        </div>
      </div>

      <!-- NUEVO: Mostrar contador de resultados filtrados -->
      <div class="row mb-2" v-if="languageFilter !== 'all' || searchQuery.trim()">
        <div class="col-md-12">
          <div class="alert alert-info alert-sm py-2">
            <i class="fas fa-filter"></i>
            Mostrando {{ filteredPosts.length }} de {{ posts.length }} posts
            <span v-if="languageFilter !== 'all'"> - Idioma: <strong>{{ getLanguageName(languageFilter) }}</strong></span>
            <span v-if="searchQuery.trim()"> - Búsqueda: "<strong>{{ searchQuery }}</strong>"</span>
            <button @click="clearFilters" class="btn btn-sm btn-outline-secondary ml-2" v-if="languageFilter !== 'all' || searchQuery.trim()">
              <i class="fas fa-times"></i> Limpiar filtros
            </button>
          </div>
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
                  <th>Idioma</th>
                  <th>Imágenes</th>
                  <th>Videos</th>
                  <th>Estado</th>
                  <th>Fecha</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="post in filteredPosts" :key="post.id">
                  <td>{{ post.id }}</td>
                  <td>
                    <div class="post-title" :title="post.title">
                      {{ post.title.length > 50 ? post.title.substring(0, 50) + '...' : post.title }}
                    </div>
                    <small class="text-muted post-content" :title="stripHtml(post.content)">
                      {{ stripHtml(post.content).length > 80 ? stripHtml(post.content).substring(0, 80) + '...' : stripHtml(post.content) }}
                    </small>
                  </td>
                  <td>
                    <!-- Mostrar idioma con bandera -->
                    <span v-if="post.language === 'es'" class="badge badge-primary" title="Español">
                      🇪🇸 ES
                    </span>
                    <span v-else-if="post.language === 'en'" class="badge badge-success" title="English">
                      🇺🇸 EN
                    </span>
                    <span v-else class="badge badge-secondary" title="Sin idioma definido">
                      ❓ --
                    </span>
                  </td>
                  <td>
                    <span class="badge badge-info">
                      <i class="fas fa-images"></i> {{ post.total_images || 0 }}
                    </span>
                  </td>
                  <td>
                    <span class="badge" :class="post.total_videos > 0 ? 'badge-warning' : 'badge-light'">
                      <i class="fas fa-video"></i> {{ post.total_videos || 0 }}
                    </span>
                  </td>
                  <td>
                    <button
                      @click="toggleStatus(post.id, post.status)"
                      :class="['btn', 'btn-sm', post.status ? 'btn-success' : 'btn-outline-secondary']"
                      :title="post.status ? 'Activo - Click para desactivar' : 'Inactivo - Click para activar'">
                      <i :class="['fas', post.status ? 'fa-check-circle' : 'fa-times-circle']"></i>
                      {{ post.status ? 'Activo' : 'Inactivo' }}
                    </button>
                  </td>
                  <td>
                    <small>{{ formatDate(post.created_at) }}</small>
                  </td>
                  <td>
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