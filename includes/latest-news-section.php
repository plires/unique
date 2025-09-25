<?php

/**
 * Sección de Últimas Novedades
 * Este archivo debe ser incluido en index.php después de incluir latest-news-data.php
 */

// Obtener textos según idioma actual
$currentLang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'es';
$texts = getLatestNewsTexts($currentLang);
?>

<!-- Últimas Novedades -->
<section class="latest-news" id="latest-news">
  <div class="container">
    <!-- Título de la sección -->
    <div class="latest-news-title">
      <h2><?php echo htmlspecialchars($texts['title']); ?></h2>
      <p><?php echo htmlspecialchars($texts['subtitle']); ?></p>
    </div>

    <?php if ($hasLatestPosts): ?>
      <!-- Grid de posts -->
      <div class="row">
        <?php foreach ($latestPosts as $index => $post): ?>
          <div class="col-lg-4 col-md-6 mb-4">
            <article class="news-card" role="article">
              <!-- Imagen del post -->
              <div class="news-card-image">
                <?php echo renderPostImage($post, $currentLang); ?>

                <!-- Fecha -->
                <div class="news-card-date">
                  <time datetime="<?php echo date('Y-m-d', strtotime($post['created_at'])); ?>">
                    <?php echo htmlspecialchars($post['formatted_date']); ?>
                  </time>
                </div>
              </div>

              <!-- Contenido del post -->
              <div class="news-card-content">
                <h3 class="news-card-title">
                  <?php echo htmlspecialchars($post['title']); ?>
                </h3>

                <?php if (!empty($post['excerpt'])): ?>
                  <p class="news-card-excerpt">
                    <?php echo $post['excerpt']; ?>
                  </p>
                <?php endif; ?>

                <!-- Footer con botón -->
                <div class="news-card-footer">
                  <a href="post.php?id=<?= $post['id'] ?>"
                    class="news-card-btn"
                    aria-label="<?php echo htmlspecialchars($post['read_more_aria']); ?>"
                    role="button">
                    <?php echo htmlspecialchars($post['button_text']); ?>
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                  </a>
                </div>
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      </div>

    <?php else: ?>
      <!-- Estado vacío -->
      <div class="row">
        <div class="col-12">
          <div class="news-empty-state">
            <i class="fas fa-newspaper" aria-hidden="true"></i>
            <h3><?php echo htmlspecialchars($texts['empty_title']); ?></h3>
            <p><?php echo htmlspecialchars($texts['empty_description']); ?></p>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>