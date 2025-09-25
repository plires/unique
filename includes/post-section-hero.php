<section class="post-hero">
  <?php if (!empty($post['images']['header'])): ?>
    <!-- Imagen de header como fondo -->
    <img class="post-hero-bg"
      src="<?php echo getImageUrl($post['images']['header'][0]['file_path']); ?>"
      alt="<?php echo htmlspecialchars($post['images']['header'][0]['alt_text'] ?: $post['title']); ?>">
  <?php else: ?>
    <!-- Video genérico como fondo -->
    <video class="post-hero-video" autoplay muted loop>
      <source src="<?= $nivel ?>videos/generic-hero-video.mp4" type="video/mp4">
      <source src="<?= $nivel ?>videos/generic-hero-video.webm" type="video/webm">
    </video>
  <?php endif; ?>

  <!-- Overlay oscuro -->
  <div class="post-hero-overlay"></div>

  <!-- Contenido centrado -->
  <div class="post-hero-content">
    <h1 class="post-hero-title"><?php echo htmlspecialchars($post['title']); ?></h1>
    <p class="post-hero-date"><?php echo $formattedDate; ?></p>
  </div>
</section>