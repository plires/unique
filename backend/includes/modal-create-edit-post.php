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