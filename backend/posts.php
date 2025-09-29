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

    /* Paginacion */
    .pagination-info {
      padding: 8px 0;
    }

    .per-page-selector select {
      width: auto;
      display: inline-block;
    }

    .pagination-sm .page-link {
      padding: 0.375rem 0.75rem;
    }

    .pagination .page-item.disabled .page-link {
      cursor: not-allowed;
    }

    .spinner-border {
      width: 2rem;
      height: 2rem;
    }

    /* Estilo para mensaje de no hay posts */
    .text-muted .fa-inbox {
      color: #dee2e6;
    }

    /* Hacer la tabla responsive para paginación */
    @media (max-width: 768px) {
      .pagination {
        flex-wrap: wrap;
        justify-content: center !important;
      }

      .pagination-info {
        text-align: center;
        margin-bottom: 1rem;
      }

      .per-page-selector {
        text-align: center;
        margin-bottom: 1rem;
      }
    }
  </style>
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

        <!-- Filtro de idiomas -->
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

      <!-- Información de paginación -->
      <div class="row mb-2" v-if="pagination.total > 0">
        <div class="col-md-12">
          <div class="alert alert-info alert-sm py-2">
            <i class="fas fa-info-circle"></i>
            <span v-if="pagination.total > pagination.per_page">
              Mostrando {{ pagination.showing_from }} a {{ pagination.showing_to }} de {{ pagination.total }} posts
            </span>
            <span v-else>
              {{ pagination.total }} post{{ pagination.total !== 1 ? 's' : '' }} en total
            </span>
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
                <tr v-for="post in posts" :key="post.id">
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

                <!-- Mensaje cuando no hay posts -->
                <tr v-if="!isLoading && posts.length === 0">
                  <td colspan="8" class="text-center py-4">
                    <div class="text-muted">
                      <i class="fas fa-inbox fa-2x mb-2"></i>
                      <div v-if="languageFilter !== 'all' || searchQuery.trim()">
                        No se encontraron posts con los filtros aplicados
                      </div>
                      <div v-else>
                        No hay posts disponibles
                      </div>
                    </div>
                  </td>
                </tr>

                <!-- Loading spinner cuando está cargando -->
                <tr v-if="isLoading">
                  <td colspan="8" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                      <span class="sr-only">Cargando posts...</span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Componente de paginación completo -->
      <div class="row mt-3" v-if="!isLoading && pagination.total > 0">
        <!-- Información de resultados -->
        <div class="col-md-6">
          <div class="pagination-info">
            <small class="text-muted">
              <span v-if="pagination.total > 0">
                Mostrando {{ pagination.showing_from }} a {{ pagination.showing_to }} de {{ pagination.total }} posts
                <span v-if="languageFilter !== 'all' || searchQuery.trim()">filtrados</span>
              </span>
            </small>
          </div>
        </div>

        <!-- Selector de elementos por página -->
        <div class="col-md-3">
          <div class="per-page-selector" v-if="pagination.total > 10">
            <small class="text-muted d-block">Posts por página:</small>
            <select
              @change="changePerPage($event.target.value)"
              :value="pagination.per_page"
              class="form-control form-control-sm">
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
            </select>
          </div>
        </div>

        <!-- Controles de paginación -->
        <div class="col-md-3">
          <nav v-if="pagination.total_pages > 1" aria-label="Paginación de posts">
            <ul class="pagination pagination-sm justify-content-end mb-0">
              <!-- Botón Anterior -->
              <li class="page-item" :class="{ disabled: !pagination.has_prev }">
                <button
                  @click="changePage(pagination.current_page - 1)"
                  :disabled="!pagination.has_prev"
                  class="page-link"
                  aria-label="Página anterior">
                  <i class="fas fa-chevron-left"></i>
                </button>
              </li>

              <!-- Páginas -->
              <template v-if="pagination.total_pages <= 7">
                <!-- Mostrar todas las páginas si son pocas -->
                <li
                  v-for="page in pagination.total_pages"
                  :key="page"
                  class="page-item"
                  :class="{ active: page === pagination.current_page }">
                  <button
                    @click="changePage(page)"
                    class="page-link">
                    {{ page }}
                  </button>
                </li>
              </template>

              <template v-else>
                <!-- Paginación inteligente para muchas páginas -->
                <!-- Primera página -->
                <li class="page-item" :class="{ active: pagination.current_page === 1 }">
                  <button @click="changePage(1)" class="page-link">1</button>
                </li>

                <!-- Puntos suspensivos iniciales -->
                <li v-if="pagination.current_page > 3" class="page-item disabled">
                  <span class="page-link">...</span>
                </li>

                <!-- Páginas alrededor de la actual -->
                <li
                  v-for="page in getPaginationRange()"
                  :key="page"
                  class="page-item"
                  :class="{ active: page === pagination.current_page }">
                  <button @click="changePage(page)" class="page-link">{{ page }}</button>
                </li>

                <!-- Puntos suspensivos finales -->
                <li v-if="pagination.current_page < pagination.total_pages - 2" class="page-item disabled">
                  <span class="page-link">...</span>
                </li>

                <!-- Última página -->
                <li
                  v-if="pagination.total_pages > 1"
                  class="page-item"
                  :class="{ active: pagination.current_page === pagination.total_pages }">
                  <button @click="changePage(pagination.total_pages)" class="page-link">
                    {{ pagination.total_pages }}
                  </button>
                </li>
              </template>

              <!-- Botón Siguiente -->
              <li class="page-item" :class="{ disabled: !pagination.has_next }">
                <button
                  @click="changePage(pagination.current_page + 1)"
                  :disabled="!pagination.has_next"
                  class="page-link"
                  aria-label="Página siguiente">
                  <i class="fas fa-chevron-right"></i>
                </button>
              </li>
            </ul>
          </nav>
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