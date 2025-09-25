<?php if (!empty($post['images']['content'])): ?>
  <section class="post-images">
    <div class="container">
      <h2 class="post-images-title">Galería</h2>

      <?php
      $contentImages = $post['images']['content'];
      $imageCount = count($contentImages);
      ?>

      <?php if ($imageCount == 1): ?>
        <!-- Una sola imagen: 12 columnas -->
        <div class="row">
          <div class="col-12 mb-4">
            <img src="<?php echo getImageUrl($contentImages[0]['file_path']); ?>"
              alt="<?php echo htmlspecialchars($contentImages[0]['alt_text'] ?: $post['title']); ?>"
              class="img-fluid post-content-image"
              data-toggle="modal"
              data-target="#imageModal"
              data-image-src="<?php echo getImageUrl($contentImages[0]['file_path']); ?>"
              data-image-alt="<?php echo htmlspecialchars($contentImages[0]['alt_text'] ?: $post['title']); ?>"
              role="button"
              tabindex="0">
          </div>
        </div>

      <?php elseif ($imageCount == 2): ?>
        <!-- Dos imágenes: 6 columnas cada una -->
        <div class="row">
          <?php foreach ($contentImages as $image): ?>
            <div class="col-md-6 mb-4">
              <img src="<?php echo getImageUrl($image['file_path']); ?>"
                alt="<?php echo htmlspecialchars($image['alt_text'] ?: $post['title']); ?>"
                class="img-fluid post-content-image"
                data-toggle="modal"
                data-target="#imageModal"
                data-image-src="<?php echo getImageUrl($image['file_path']); ?>"
                data-image-alt="<?php echo htmlspecialchars($image['alt_text'] ?: $post['title']); ?>"
                role="button"
                tabindex="0">
            </div>
          <?php endforeach; ?>
        </div>

      <?php elseif ($imageCount == 3): ?>
        <!-- Tres imágenes: 4 columnas cada una -->
        <div class="row">
          <?php foreach ($contentImages as $image): ?>
            <div class="col-md-4 mb-4">
              <img src="<?php echo getImageUrl($image['file_path']); ?>"
                alt="<?php echo htmlspecialchars($image['alt_text'] ?: $post['title']); ?>"
                class="img-fluid post-content-image"
                data-toggle="modal"
                data-target="#imageModal"
                data-image-src="<?php echo getImageUrl($image['file_path']); ?>"
                data-image-alt="<?php echo htmlspecialchars($image['alt_text'] ?: $post['title']); ?>"
                role="button"
                tabindex="0">
            </div>
          <?php endforeach; ?>
        </div>

      <?php else: ?>
        <!-- Más de tres imágenes: filas de 6 columnas cada imagen -->
        <div class="row">
          <?php foreach ($contentImages as $image): ?>
            <div class="col-md-6 mb-4">
              <img src="<?php echo getImageUrl($image['file_path']); ?>"
                alt="<?php echo htmlspecialchars($image['alt_text'] ?: $post['title']); ?>"
                class="img-fluid post-content-image"
                data-toggle="modal"
                data-target="#imageModal"
                data-image-src="<?php echo getImageUrl($image['file_path']); ?>"
                data-image-alt="<?php echo htmlspecialchars($image['alt_text'] ?: $post['title']); ?>"
                role="button"
                tabindex="0">
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
<?php endif; ?>