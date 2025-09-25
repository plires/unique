<?php if ($hasYouTubeVideo && $youtubeVideoId): ?>
  <section class="post-video">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <h2 class="post-video-title">Video</h2>
          <div class="video-container">
            <iframe src="https://www.youtube.com/embed/<?php echo htmlspecialchars($youtubeVideoId); ?>?rel=0&showinfo=0&modestbranding=1"
              title="<?php echo htmlspecialchars($post['title']); ?>"
              frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen>
            </iframe>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>