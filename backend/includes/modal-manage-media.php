<div class="modal fade" id="mediaModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Gestionar Medios - {{ currentMediaPost.title }}</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Tabs -->
        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" id="images-tab" data-toggle="tab" href="#images" role="tab">
              Imágenes ({{ currentMediaPost.images ? currentMediaPost.images.length : 0 }})
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="videos-tab" data-toggle="tab" href="#videos" role="tab">
              Videos ({{ currentMediaPost.videos ? currentMediaPost.videos.length : 0 }})
            </a>
          </li>
        </ul>

        <div class="tab-content mt-3">
          <!-- Tab Imágenes -->
          <div class="tab-pane fade show active" id="images" role="tabpanel">
            <div class="row mb-3">
              <div class="col-md-12">
                <h6>Subir nueva imagen:</h6>
                <form @submit="uploadImage">
                  <div class="form-row">
                    <div class="col-md-6">
                      <input type="file" class="form-control-file" ref="imageFile" accept="image/*" required>
                    </div>
                    <div class="col-md-3">
                      <input type="text" class="form-control" placeholder="Texto alternativo" v-model="newImage.alt_text">
                    </div>
                    <div class="col-md-3">
                      <button type="submit" class="btn btn-sm btn-primary">Subir</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>

            <div class="row">
              <div v-for="image in currentMediaPost.images" :key="'img-' + image.id" class="col-md-3 mb-3">
                <div class="card">
                  <img :src="getImageUrl(image.file_path)" class="card-img-top" style="height: 150px; object-fit: cover;">
                  <div class="card-body p-2">
                    <small class="text-muted">{{ image.original_name }}</small>
                    <div class="btn-group btn-group-sm w-100 mt-1">
                      <button @click="deleteImage(image.id)" class="btn btn-outline-danger">
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="!currentMediaPost.images || currentMediaPost.images.length === 0" class="text-center text-muted">
              <p>No hay imágenes para este post</p>
            </div>
          </div>

          <!-- Tab Videos -->
          <div class="tab-pane fade" id="videos" role="tabpanel">
            <div class="row mb-3">
              <div class="col-md-12">
                <h6>Agregar nuevo video:</h6>
                <!-- Aquí iría el formulario para agregar videos -->
                <div class="text-muted">
                  <small>Funcionalidad de videos en desarrollo...</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>