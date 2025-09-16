<div class="modal fade" id="mediaModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Gestionar Imágenes - {{ currentMediaPost.title }}</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">

        <!-- Subir nueva imagen con selector de tipo -->
        <div class="row mb-4">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h6 class="mb-0">Subir nueva imagen</h6>
              </div>
              <div class="card-body">
                <form @submit="uploadImage">
                  <div class="form-row">
                    <div class="col-md-3">
                      <label>Tipo de imagen:</label>
                      <select class="form-control" v-model="newImage.type" required>
                        <option value="listing">Listado (cuadrada)</option>
                        <option value="header">Header (horizontal)</option>
                        <option value="content">Contenido</option>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label>Archivo:</label>
                      <input type="file" class="form-control-file" ref="imageFile" accept="image/*" required>
                    </div>
                    <div class="col-md-3">
                      <label>Texto alternativo:</label>
                      <input type="text" class="form-control" placeholder="Alt text" v-model="newImage.alt_text">
                    </div>
                    <div class="col-md-2">
                      <label>&nbsp;</label>
                      <button type="submit" class="btn btn-primary btn-block">Subir</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <hr>

        <!-- Mostrar imágenes agrupadas por tipo -->
        <div v-if="currentMediaPost.images">

          <!-- Imagen de Listado -->
          <div class="mb-4">
            <h6 class="text-primary">
              <i class="fas fa-th-large"></i> Imagen de Listado (máximo 1)
              <small class="text-muted">- Para mostrar en el listado de noticias</small>
            </h6>
            <div class="row">
              <div v-for="image in currentMediaPost.images.listing" :key="'listing-' + image.id" class="col-md-2 mb-3">
                <div class="card">
                  <img :src="getImageUrl(image.file_path)" class="card-img-top" style="height: 120px; object-fit: cover;">
                  <div class="card-body p-2">
                    <small class="text-muted d-block">{{ image.filename }}</small>
                    <button @click="deleteImage(image.id)" class="btn btn-outline-danger btn-sm btn-block mt-1">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </div>
              </div>
              <div v-if="!currentMediaPost.images.listing || currentMediaPost.images.listing.length === 0" class="col-12">
                <div class="alert alert-light text-center">
                  <i class="fas fa-image fa-2x text-muted"></i>
                  <p class="mb-0 mt-2">No hay imagen de listado</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Imagen de Header -->
          <div class="mb-4">
            <h6 class="text-success">
              <i class="fas fa-window-maximize"></i> Imagen de Header (máximo 1)
              <small class="text-muted">- Imagen principal de la noticia</small>
            </h6>
            <div class="row">
              <div v-for="image in currentMediaPost.images.header" :key="'header-' + image.id" class="col-md-4 mb-3">
                <div class="card">
                  <img :src="getImageUrl(image.file_path)" class="card-img-top" style="height: 150px; object-fit: cover;">
                  <div class="card-body p-2">
                    <small class="text-muted d-block">{{ image.filename }}</small>
                    <button @click="deleteImage(image.id)" class="btn btn-outline-danger btn-sm btn-block mt-1">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </div>
              </div>
              <div v-if="!currentMediaPost.images.header || currentMediaPost.images.header.length === 0" class="col-12">
                <div class="alert alert-light text-center">
                  <i class="fas fa-image fa-2x text-muted"></i>
                  <p class="mb-0 mt-2">No hay imagen de header</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Imágenes de Contenido -->
          <div class="mb-4">
            <h6 class="text-info">
              <i class="fas fa-images"></i> Imágenes de Contenido (múltiples)
              <small class="text-muted">- Para usar dentro del texto de la noticia</small>
            </h6>
            <div class="row">
              <div v-for="image in currentMediaPost.images.content" :key="'content-' + image.id" class="col-md-3 mb-3">
                <div class="card">
                  <img :src="getImageUrl(image.file_path)" class="card-img-top" style="height: 150px; object-fit: cover;">
                  <div class="card-body p-2">
                    <small class="text-muted d-block">{{ image.filename }}</small>
                    <button @click="deleteImage(image.id)" class="btn btn-outline-danger btn-sm btn-block mt-1">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </div>
              </div>
              <div v-if="!currentMediaPost.images.content || currentMediaPost.images.content.length === 0" class="col-12">
                <div class="alert alert-light text-center">
                  <i class="fas fa-images fa-2x text-muted"></i>
                  <p class="mb-0 mt-2">No hay imágenes de contenido</p>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Estado vacío general -->
        <div v-if="!currentMediaPost.images || (!currentMediaPost.images.listing && !currentMediaPost.images.header && !currentMediaPost.images.content)" class="text-center text-muted py-5">
          <i class="fas fa-images fa-4x mb-3"></i>
          <h5>No hay imágenes para este post</h5>
          <p>Sube la primera imagen usando el formulario de arriba</p>
        </div>

      </div>
    </div>
  </div>
</div>