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
      active: true,
    },
    formErrors: [],

    // Modal de Medios
    currentMediaPost: {
      id: null,
      title: "",
      images: [],
      videos: [],
    },
    newImage: {
      alt_text: "",
      caption: "",
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
      // Implementar lógica de actualización de usuario
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
        active: true,
      };
      this.formErrors = [];

      // Limpiar editor
      if (this.quillEditor) {
        this.quillEditor.setContents([]);
      }

      $("#postModal").modal("show");
    },

    async editPost(postId) {
      const post = this.posts.find((p) => p.id === postId);
      if (!post) {
        this.showError("Post no encontrado");
        return;
      }

      this.isEditing = true;
      this.modalTitle = "Editar Post";
      this.currentPost = {
        id: post.id,
        title: post.title,
        content: post.content,
        active: post.status == 1,
      };
      this.formErrors = [];

      // Cargar contenido en el editor
      if (this.quillEditor) {
        this.quillEditor.root.innerHTML = post.content;
      }

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

      if (this.formErrors.length > 0) {
        return;
      }

      // Preparar datos
      const formData = new FormData();
      formData.append("title", this.currentPost.title);
      formData.append("content", content);
      formData.append("status", this.currentPost.active ? 1 : 0);

      if (this.isEditing) {
        formData.append("edit", "true");
        formData.append("id", this.currentPost.id);
      }

      try {
        this.showLoader();

        const response = await axios.post(
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

    // === GESTIÓN DE MEDIOS ===

    async manageMedia(postId) {
      const postData = await this.loadPostComplete(postId);
      if (!postData) return;

      this.currentMediaPost = {
        id: postData.id,
        title: postData.title,
        images: postData.images || [],
        videos: postData.videos || [],
      };

      this.newImage = {
        alt_text: "",
        caption: "",
      };

      $("#mediaModal").modal("show");
    },

    async uploadImage(e) {
      e.preventDefault();

      const fileInput = this.$refs.imageFile;
      if (!fileInput.files.length) {
        this.showError("Seleccione un archivo");
        return;
      }

      const formData = new FormData();
      formData.append("post_id", this.currentMediaPost.id);
      formData.append("image", fileInput.files[0]);
      if (this.newImage.alt_text) {
        formData.append("alt_text", this.newImage.alt_text);
      }
      if (this.newImage.caption) {
        formData.append("caption", this.newImage.caption);
      }

      try {
        this.showLoader();

        await axios.post(
          window.APP_CONFIG.API_BASE_URL + "php/uploadImage.php",
          formData,
          {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          }
        );

        this.showSuccess("Imagen subida exitosamente");

        // Recargar datos del post
        const updatedPost = await this.loadPostComplete(
          this.currentMediaPost.id
        );
        if (updatedPost) {
          this.currentMediaPost.images = updatedPost.images || [];
        }

        // Limpiar formulario
        fileInput.value = "";
        this.newImage = { alt_text: "", caption: "" };
      } catch (error) {
        this.showError(
          "Error al subir imagen: " +
            (error.response?.data?.message || error.message)
        );
      } finally {
        this.hideLoader();
      }
    },

    async deleteImage(imageId) {
      if (!confirm("¿Está seguro de eliminar esta imagen?")) {
        return;
      }

      try {
        this.showLoader();

        const formData = new FormData();
        formData.append("id", imageId);

        await axios.post(
          window.APP_CONFIG.API_BASE_URL + "php/deleteImage.php",
          formData
        );

        this.showSuccess("Imagen eliminada exitosamente");

        // Recargar datos del post
        const updatedPost = await this.loadPostComplete(
          this.currentMediaPost.id
        );
        if (updatedPost) {
          this.currentMediaPost.images = updatedPost.images || [];
        }
      } catch (error) {
        this.showError(
          "Error al eliminar imagen: " +
            (error.response?.data?.message || error.message)
        );
      } finally {
        this.hideLoader();
      }
    },

    // === UTILIDADES ===

    initializeQuillEditor() {
      this.quillEditor = new Quill("#editor", {
        theme: "snow",
        modules: {
          toolbar: [
            [{ header: [1, 2, 3, false] }],
            ["bold", "italic", "underline", "strike"],
            [{ list: "ordered" }, { list: "bullet" }],
            [{ color: [] }, { background: [] }],
            [{ align: [] }],
            ["link", "image"],
            ["clean"],
          ],
        },
        placeholder: "Escriba el contenido del post aquí...",
      });
    },

    getImageUrl(filePath) {
      // Convertir ruta del servidor a URL accesible
      if (filePath.startsWith("uploads/")) {
        return window.APP_CONFIG.FRONTEND_URL + "/" + filePath;
      }
      return filePath;
    },

    formatDate(dateString) {
      const date = new Date(dateString);
      return (
        date.toLocaleDateString("es-ES") +
        " " +
        date.toLocaleTimeString("es-ES", {
          hour: "2-digit",
          minute: "2-digit",
        })
      );
    },

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
      alert(message);
      console.error(message);
    },
  },
});
