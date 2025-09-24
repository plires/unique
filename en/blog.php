<?php
// PRIMERO: Iniciar sesión
session_start();

// SEGUNDO: Definir idioma
$_SESSION['lang'] = 'en';
$current = 'blog';

include_once('../includes/config.inc.php');
include_once('../includes/funciones_validar.php');
require_once("../clases/app.php");
require_once("../clases/repositorioSQL.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Unique Talent Solutions Blog - Latest trends and news from the Tourism and Hospitality industry.">
  <meta name="author" content="Librecomunicacion">
  <!-- Favicons -->
  <?php include('../includes/favicon.inc.php'); ?>
  <title>Blog - Unique Talent Solutions</title>

  <link rel="stylesheet" href="../node_modules/normalize.css/normalize.css">
  <link rel="stylesheet" href="../node_modules/@fortawesome/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../node_modules/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../node_modules/wowjs/css/libs/animate.css">
  <link rel="stylesheet" href="../css/latest-news.css">
  <link rel="stylesheet" href="../css/blog-pagination.css">
  <link rel="stylesheet" href="../css/app.css">
  <?php include('../includes/tag_manager_head.php'); ?>
</head>

<body>
  <?php include('../includes/tag_manager_body.php'); ?>

  <!-- Header -->
  <?php include('../includes/header.inc.php'); ?>

  <div id="app">

    <!-- Main Content -->
    <main>
      <!-- Sección del Blog -->
      <section class="latest-news" id="blog">
        <div class="container">

          <!-- Título de la sección -->
          <div class="latest-news-title">
            <h1>Our Blog</h1>
            <p>Stay up to date with the latest trends and news from the tourism and hospitality sector</p>
          </div>

          <!-- Loading State -->
          <div v-if="isLoading" class="blog-loading">
            <div class="spinner-border text-primary" role="status">
              <span class="sr-only">Loading posts...</span>
            </div>
            <p class="mt-3 text-muted">Loading content...</p>
          </div>

          <!-- Posts Grid -->
          <div v-if="!isLoading && posts.length > 0" class="blog-posts-container" :class="{ loading: isLoading }">
            <!-- Grid de posts -->
            <div class="row">
              <div v-for="post in posts" :key="post.id" class="col-lg-4 col-md-6 mb-4">
                <article class="news-card" role="article">
                  <!-- Imagen del post -->
                  <div class="news-card-image">
                    <img v-if="post.image_url"
                      :src="post.image_url"
                      :alt="post.listing_image_alt || post.title"
                      loading="lazy">
                    <div v-else class="news-card-image-placeholder">
                      <i class="fas fa-image" aria-hidden="true"></i>
                      <span class="sr-only">No image</span>
                    </div>

                    <!-- Fecha -->
                    <div class="news-card-date">
                      <time :datetime="post.created_at">
                        {{ post.formatted_date }}
                      </time>
                    </div>
                  </div>

                  <!-- Contenido del post -->
                  <div class="news-card-content">
                    <h3 class="news-card-title">
                      {{ post.title }}
                    </h3>

                    <p v-if="post.excerpt" class="news-card-excerpt" v-html="post.excerpt"></p>

                    <!-- Footer con botón -->
                    <div class="news-card-footer">
                      <a href="#"
                        class="news-card-btn"
                        :aria-label="`Read more about ${post.title}`"
                        role="button">
                        Read more
                        <i class="fas fa-arrow-right" aria-hidden="true"></i>
                      </a>
                    </div>
                  </div>
                </article>
              </div>
            </div>

            <!-- Paginación -->
            <div class="blog-pagination" v-if="pagination.total_pages > 1">
              <div class="row">
                <div class="col-12">
                  <nav aria-label="Blog pagination">
                    <ul class="pagination justify-content-center">
                      <!-- Botón Anterior -->
                      <li class="page-item" :class="{ disabled: !pagination.has_prev }">
                        <button
                          @click="changePage(pagination.current_page - 1)"
                          :disabled="!pagination.has_prev"
                          class="page-link"
                          aria-label="Previous page">
                          <i class="fas fa-chevron-left"></i>
                          <span class="d-none d-md-inline ml-2">Previous</span>
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
                          aria-label="Next page">
                          <span class="d-none d-md-inline mr-2">Next</span>
                          <i class="fas fa-chevron-right"></i>
                        </button>
                      </li>
                    </ul>
                  </nav>
                </div>
              </div>
            </div>

            <!-- Info de paginación -->
            <div class="pagination-info" v-if="pagination.total > 0">
              <small class="text-muted">
                Showing {{ pagination.showing_from }} to {{ pagination.showing_to }} of {{ pagination.total }} articles
              </small>
            </div>
          </div>

          <!-- Empty State -->
          <div v-if="!isLoading && posts.length === 0" class="row">
            <div class="col-12">
              <div class="news-empty-state">
                <i class="fas fa-newspaper" aria-hidden="true"></i>
                <h3>No articles available</h3>
                <p>No articles have been published on our blog yet. Come back soon to see the latest news!</p>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- Footer -->
    <?php include('../includes/footer.inc.php'); ?>
  </div>

  <!-- JavaScript -->
  <script src="../node_modules/jquery/dist/jquery.min.js"></script>
  <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="node_modules/wowjs/dist/wow.min.js"></script>
  <script src="../js/app.js"></script>
  <script src="../js/blog.js"></script>

  <?php include('../includes/tag_manager_body.php'); ?>
</body>

</html>