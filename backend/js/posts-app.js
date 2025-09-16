// === APLICACIÓN VUE PARA GESTIÓN DE POSTS ===

let postsApp = new Vue({
  el: "#app",
  data: {
    posts: [],
    searchQuery: "",
    messages: [],

    // Agregar propiedades para compatibilidad con modalUser
    user: {},
    errorsUser: [],
    changePass: false,

    // Modal de Post
    isEditing: false,
    modalTitle: "Crear Post",
    currentPost: {
      id: null,
      title: "",
      content: "",
      youtube_url: "",
      active: true,
    },
    formErrors: [],
    youtubePreview: false,

    // Modal de Medios
    currentMediaPost: {
      id: null,
      title: "",
      images: {
        listing: [],
        header: [],
        content: [],
      },
    },
    newImage: {
      type: "content",
      alt_text: "",
    },

    // Eliminación
    postToDelete: null,

    // Editor
    quillEditor: null,
  },

  computed: {
    filteredPosts() {
      if (!this.searchQuery.trim()) {
        return this.posts;
      }

      const query = this.searchQuery.toLowerCase();
      return this.posts.filter(
        (post) =>
          post.title.toLowerCase().includes(query) ||
          post.content.toLowerCase().includes(query)
      );
    },
  },

  mounted() {
    this.loadPosts();
    this.getUser();
    this.initializeQuillEditor();
  },

  methods: {
    // === VALIDACIÓN DE YOUTUBE ===
    validateYouTubeUrl() {
      if (!this.currentPost.youtube_url) {
        this.youtubePreview = false;
        return;
      }

      const youtubeRegex =
        /^(https?:\/\/)?(www\.)?(youtube\.com\/(watch\?v=|embed\/)|youtu\.be\/)[\w-]+(&.*)?$/;
      this.youtubePreview = youtubeRegex.test(this.currentPost.youtube_url);
    },

    extractYouTubeId(url) {
      if (!url) return null;

      const patterns = [
        /youtube\.com\/watch\?v=([^&\n]+)/,
        /youtube\.com\/embed\/([^&\n]+)/,
        /youtu\.be\/([^&\n]+)/,
      ];

      for (let pattern of patterns) {
        const match = url.match(pattern);
        if (match) return match[1];
      }
      return null;
    },

    // === USUARIO ===
    async getUser() {
      try {
        const response = await axios.get(
          window.APP_CONFIG.API_BASE_URL + "php/getUser.php"
        );
        this.user = response.data;
      } catch (error) {
        console.error("Error al cargar usuario:", error);
      }
    },

    submitFormUser(e) {
      e.preventDefault();
      console.log("Actualizar usuario:", this.user);
    },

    checkFormUser() {
      this.errorsUser = [];
      if (!this.user.user) {
        this.errorsUser.push("El nombre de usuario es obligatorio.");
      }
      if (!this.user.email) {
        this.errorsUser.push("El email es obligatorio.");
      }
      return this.errorsUser.length === 0;
    },

    rememberPassword() {
      this.changePass = !this.changePass;
    },

    // === CARGA DE DATOS ===
    async loadPosts() {
      try {
        this.showLoader();
        const response = await axios.get(
          window.APP_CONFIG.API_BASE_URL + "php/getPosts.php?include_inactive=1"
        );
        this.posts = response.data;
      } catch (error) {
        this.showError(
          "Error al cargar posts: " +
            (error.response?.data?.message || error.message)
        );
      } finally {
        this.hideLoader();
      }
    },

    async loadPostComplete(postId) {
      try {
        this.showLoader();
        const response = await axios.get(
          window.APP_CONFIG.API_BASE_URL + "php/getPost.php?id=" + postId
        );
        return response.data;
      } catch (error) {
        this.showError(
          "Error al cargar post completo: " +
            (error.response?.data?.message || error.message)
        );
        return null;
      } finally {
        this.hideLoader();
      }
    },

    // === GESTIÓN DE POSTS ===
    openCreateModal() {
      this.isEditing = false;
      this.modalTitle = "Crear Post";
      this.currentPost = {
        id: null,
        title: "",
        content: "",
        youtube_url: "", // NUEVO CAMPO
        active: true,
      };
      this.formErrors = [];
      this.youtubePreview = false; // RESET VALIDACIÓN

      // Limpiar editor
      if (this.quillEditor) {
        this.quillEditor.setContents([]);
      }

      $("#postModal").modal("show");
    },

    async editPost(postId) {
      // ✅ CORRECTO: Cargar datos completos del post
      const postData = await this.loadPostComplete(postId);
      if (!postData) return;

      this.isEditing = true;
      this.modalTitle = "Editar Post";
      this.currentPost = {
        id: postData.id,
        title: postData.title,
        content: postData.content,
        youtube_url: postData.youtube_url || "", // NUEVO CAMPO
        active: postData.status == 1,
      };
      this.formErrors = [];

      // Cargar contenido en el editor
      if (this.quillEditor) {
        this.quillEditor.root.innerHTML = postData.content;
      }

      // Validar URL de YouTube si existe
      this.validateYouTubeUrl();

      $("#postModal").modal("show");
    },

    async submitPost(e) {
      e.preventDefault();

      // Obtener contenido del editor
      const content = this.quillEditor ? this.quillEditor.root.innerHTML : "";

      // Validación básica
      this.formErrors = [];
      if (!this.currentPost.title.trim()) {
        this.formErrors.push("El título es obligatorio");
      }
      if (!content.trim() || content === "<p><br></p>") {
        this.formErrors.push("El contenido es obligatorio");
      }

      // Validar URL de YouTube si se proporcionó
      if (this.currentPost.youtube_url && !this.youtubePreview) {
        this.formErrors.push("La URL de YouTube no es válida");
      }

      if (this.formErrors.length > 0) {
        return;
      }

      // Preparar datos
      const formData = new FormData();
      formData.append("title", this.currentPost.title);
      formData.append("content", content);
      formData.append("youtube_url", this.currentPost.youtube_url || ""); // NUEVO CAMPO
      formData.append("status", this.currentPost.active ? 1 : 0);

      if (this.isEditing) {
        formData.append("edit", "true");
        formData.append("id", this.currentPost.id);
      }

      try {
        this.showLoader();

        await axios.post(
          window.APP_CONFIG.API_BASE_URL + "php/add_edit_post.php",
          formData
        );

        $("#postModal").modal("hide");

        const message = this.isEditing
          ? "Post actualizado exitosamente"
          : "Post creado exitosamente";
        this.showSuccess(message);

        await this.loadPosts();
      } catch (error) {
        if (error.response?.data?.errors) {
          this.formErrors = error.response.data.errors;
        } else {
          this.showError(
            "Error al guardar post: " +
              (error.response?.data?.message || error.message)
          );
        }
      } finally {
        this.hideLoader();
      }
    },

    async toggleStatus(postId, currentStatus) {
      const formData = new FormData();
      formData.append("id", postId);

      try {
        this.showLoader();

        await axios.post(
          window.APP_CONFIG.API_BASE_URL + "php/changeStatusPost.php",
          formData
        );

        this.showSuccess("Estado cambiado exitosamente");
        await this.loadPosts();
      } catch (error) {
        this.showError(
          "Error al cambiar estado: " +
            (error.response?.data?.message || error.message)
        );
      } finally {
        this.hideLoader();
      }
    },

    // === GESTIÓN DE MEDIOS ===
    async manageMedia(postId) {
      console.log("=== INICIO manageMedia ===");
      console.log("Post ID recibido:", postId);

      // Validar que el ID sea válido
      if (!postId || postId === null || postId === undefined) {
        console.error("ID de post inválido:", postId);
        this.showError("Error: ID de post inválido");
        return;
      }

      const postData = await this.loadPostComplete(postId);
      console.log("Post data cargado:", postData);

      if (!postData) {
        console.error("No se pudo cargar el post con ID:", postId);
        return;
      }

      // Asegurar estructura correcta de imágenes
      const images = postData.images || {
        listing: [],
        header: [],
        content: [],
      };
      console.log("Imágenes estructuradas:", images);

      this.currentMediaPost = {
        id: postData.id,
        title: postData.title,
        images: images,
      };

      console.log("currentMediaPost configurado:", this.currentMediaPost);

      // Resetear formulario de nueva imagen
      this.newImage = {
        type: "content",
        alt_text: "",
      };

      console.log("=== manageMedia completado ===");
      $("#mediaModal").modal("show");
    },

    async uploadImage(e) {
      e.preventDefault();

      console.log("=== INICIO uploadImage ===");
      console.log("currentMediaPost:", this.currentMediaPost);
      console.log("newImage:", this.newImage);

      const fileInput = this.$refs.imageFile;
      if (!fileInput.files.length) {
        this.showError("Seleccione un archivo");
        return;
      }

      // VALIDACIÓN CRÍTICA: Verificar que currentMediaPost.id existe y es válido
      if (!this.currentMediaPost || !this.currentMediaPost.id) {
        console.error("ERROR CRÍTICO: currentMediaPost.id no está definido");
        console.error("currentMediaPost:", this.currentMediaPost);
        this.showError(
          "Error: No se puede identificar el post. Cierre y vuelva a abrir el modal."
        );
        return;
      }

      console.log("ID del post validado:", this.currentMediaPost.id);

      // Validar que las imágenes tengan la estructura correcta
      if (
        !this.currentMediaPost.images ||
        typeof this.currentMediaPost.images !== "object"
      ) {
        console.error("ERROR: Estructura de imágenes incorrecta");
        this.currentMediaPost.images = { listing: [], header: [], content: [] };
      }

      // Validar límites por tipo
      const type = this.newImage.type;
      const currentImages = this.currentMediaPost.images[type] || [];

      console.log(
        `Validando tipo ${type}, imágenes actuales:`,
        currentImages.length
      );

      if (
        (type === "listing" || type === "header") &&
        currentImages.length >= 1
      ) {
        this.showError(
          `Solo se permite una imagen de tipo ${
            type === "listing" ? "listado" : "header"
          } por post`
        );
        return;
      }

      const formData = new FormData();
      formData.append("post_id", this.currentMediaPost.id);
      formData.append("image", fileInput.files[0]);
      formData.append("type", this.newImage.type);
      if (this.newImage.alt_text) {
        formData.append("alt_text", this.newImage.alt_text);
      }

      console.log("FormData preparado para post_id:", this.currentMediaPost.id);

      try {
        this.showLoader();

        const uploadResponse = await axios.post(
          window.APP_CONFIG.API_BASE_URL + "php/uploadImage.php",
          formData,
          {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          }
        );

        console.log("Upload response:", uploadResponse.data);

        this.showSuccess("Imagen subida exitosamente");

        // VALIDACIÓN CRÍTICA: Asegurar que el ID sigue siendo válido antes de recargar
        const postIdToReload = this.currentMediaPost.id;
        console.log("Recargando post con ID:", postIdToReload);

        if (!postIdToReload) {
          console.error("ERROR: ID de post perdido durante el upload");
          this.showError(
            "Error: Se perdió la referencia del post. Cierre y vuelva a abrir el modal."
          );
          return;
        }

        // Recargar datos del post
        const updatedPost = await this.loadPostComplete(postIdToReload);
        if (updatedPost) {
          console.log("Post actualizado recibido:", updatedPost);

          this.currentMediaPost.images = updatedPost.images || {
            listing: [],
            header: [],
            content: [],
          };

          console.log("Imágenes actualizadas:", this.currentMediaPost.images);
        } else {
          console.error("No se pudo recargar el post actualizado");
          // No mostrar error al usuario, ya que la imagen se subió correctamente
        }

        // Limpiar formulario
        fileInput.value = "";
        this.newImage = { type: "content", alt_text: "" };

        console.log("=== uploadImage completado ===");
      } catch (error) {
        console.error("Error en upload:", error);
        this.showError(
          "Error al subir imagen: " +
            (error.response?.data?.message || error.message)
        );
      } finally {
        this.hideLoader();
      }
    },

    async deleteImage(imageId) {
      if (!confirm("¿Está seguro de eliminar esta imagen?")) return;

      console.log("=== INICIO deleteImage ===");
      console.log("Image ID:", imageId);
      console.log("currentMediaPost:", this.currentMediaPost);

      // Validar que tenemos un ID de post válido
      if (!this.currentMediaPost || !this.currentMediaPost.id) {
        console.error(
          "ERROR: currentMediaPost.id no está definido en deleteImage"
        );
        this.showError(
          "Error: No se puede identificar el post. Cierre y vuelva a abrir el modal."
        );
        return;
      }

      const formData = new FormData();
      formData.append("id", imageId);

      try {
        this.showLoader();

        await axios.post(
          window.APP_CONFIG.API_BASE_URL + "php/deleteImage.php",
          formData
        );

        this.showSuccess("Imagen eliminada exitosamente");

        // Recargar datos del post con ID validado
        const postIdToReload = this.currentMediaPost.id;
        console.log("Recargando post después de eliminar, ID:", postIdToReload);

        const updatedPost = await this.loadPostComplete(postIdToReload);
        if (updatedPost) {
          this.currentMediaPost.images = updatedPost.images || {
            listing: [],
            header: [],
            content: [],
          };
          console.log("Post recargado después de eliminar");
        }

        // Recargar lista de posts
        await this.loadPosts();

        console.log("=== deleteImage completado ===");
      } catch (error) {
        console.error("Error en deleteImage:", error);
        this.showError(
          "Error al eliminar imagen: " +
            (error.response?.data?.message || error.message)
        );
      } finally {
        this.hideLoader();
      }
    },

    // === ELIMINACIÓN ===
    setPostToDelete(postId) {
      this.postToDelete = postId;
    },

    async confirmDelete() {
      if (!this.postToDelete) return;

      const formData = new FormData();
      formData.append("id", this.postToDelete);

      try {
        this.showLoader();

        await axios.post(
          window.APP_CONFIG.API_BASE_URL + "php/deletePost.php",
          formData
        );

        $("#deleteModal").modal("hide");
        this.postToDelete = null;

        this.showSuccess("Post eliminado exitosamente");
        await this.loadPosts();
      } catch (error) {
        this.showError(
          "Error al eliminar post: " +
            (error.response?.data?.message || error.message)
        );
      } finally {
        this.hideLoader();
      }
    },

    // === UTILIDADES ===
    getImageUrl(imagePath) {
      return window.APP_CONFIG.FRONTEND_URL + "/" + imagePath;
    },

    truncateContent(content, maxLength = 100) {
      const text = content.replace(/<[^>]*>/g, "");
      return text.length > maxLength
        ? text.substring(0, maxLength) + "..."
        : text;
    },

    formatDate(dateString) {
      if (!dateString) return "-";
      const date = new Date(dateString);
      return date.toLocaleDateString("es-ES", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      });
    },

    initializeQuillEditor() {
      this.$nextTick(() => {
        if (document.getElementById("editor")) {
          this.quillEditor = new Quill("#editor", {
            theme: "snow",
            modules: {
              toolbar: [
                [{ header: [1, 2, 3, false] }],
                ["bold", "italic", "underline", "strike"],
                [{ color: [] }, { background: [] }],
                [{ list: "ordered" }, { list: "bullet" }],
                ["blockquote", "code-block"],
                ["link", "image"],
                ["clean"],
              ],
            },
          });
        }
      });
    },

    // === MENSAJES Y LOADER ===
    showLoader() {
      if (typeof loader === "function") {
        loader();
      } else {
        $("#loader").fadeIn(300);
      }
    },

    hideLoader() {
      $("#loader").fadeOut(500);
    },

    showSuccess(message) {
      this.messages = [message];
      setTimeout(() => {
        this.messages = [];
      }, 5000);

      if (typeof msgSuccess === "function") {
        msgSuccess(message);
      }
    },

    showError(message) {
      console.error("Error:", message);
      alert("Error: " + message); // Temporal - puedes mejorar esto
    },
  },
});
