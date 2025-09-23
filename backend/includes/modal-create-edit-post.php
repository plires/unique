<div class="modal fade" id="postModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ modalTitle }}</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form @submit="submitPost">
          <div class="form-group">
            <label for="postTitle">Título *</label>
            <input
              type="text"
              class="form-control"
              id="postTitle"
              v-model="currentPost.title"
              required>
          </div>

          <div class="form-group">
            <label>Contenido *</label>
            <div id="editor" style="height: 300px;"></div>
          </div>

          <!-- CAMPO: YouTube URL -->
          <div class="form-group">
            <label for="youtubeUrl">URL de YouTube (opcional)</label>
            <input
              type="url"
              class="form-control"
              id="youtubeUrl"
              v-model="currentPost.youtube_url"
              placeholder="https://www.youtube.com/watch?v=..."
              @blur="validateYouTubeUrl">
            <small class="form-text text-muted">
              Ingrese la URL completa del video de YouTube. Ejemplo: https://www.youtube.com/watch?v=dQw4w9WgXcQ
            </small>
            <div v-if="youtubePreview" class="mt-2">
              <small class="text-success">
                <i class="fas fa-check-circle"></i> URL válida detectada
              </small>
            </div>
          </div>

          <div class="form-group">
            <label for="postLanguage">Idioma *</label>
            <select
              class="form-control"
              id="postLanguage"
              v-model="currentPost.language"
              required>
              <option value="">Seleccione un idioma</option>
              <option value="es">🇪🇸 Español</option>
              <option value="en">🇺🇸 English</option>
            </select>
            <small class="form-text text-muted">
              Seleccione el idioma principal del contenido del post
            </small>
          </div>

          <div class="form-group">
            <div class="custom-control custom-checkbox">
              <input
                type="checkbox"
                class="custom-control-input"
                id="postActive"
                v-model="currentPost.active">
              <label class="custom-control-label" for="postActive">Post activo</label>
            </div>
          </div>

          <div v-if="formErrors.length" class="alert alert-danger">
            <ul class="mb-0">
              <li v-for="error in formErrors" :key="error">{{ error }}</li>
            </ul>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" @click="submitPost" class="btn btn-primary">
          {{ isEditing ? 'Actualizar' : 'Crear' }}
        </button>
      </div>
    </div>
  </div>
</div>