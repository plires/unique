// === APLICACIÓN VUE PARA GESTIÓN DE POSTS ===

let postsApp = new Vue({
  el: "#app",
  data: {
    posts: [],
    searchQuery: "",
    languageFilter: "all", // Filtro de idioma por defecto
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
      language: "es", // NUEVO: Campo idioma con valor por defecto
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
      let filtered = this.posts;

      // Filtrar por idioma si no es "all"
      if (this.languageFilter !== "all") {
        filtered = filtered.filter((post) => {
          // Manejar posts sin idioma definido
          const postLanguage = post.language || "undefined";
          return postLanguage === this.languageFilter;
        });
      }

      // Filtrar por búsqueda de texto
      if (this.searchQuery.trim()) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(
          (post) =>
            post.title.toLowerCase().includes(query) ||
            post.content.toLowerCase().includes(query) ||
            (post.language && post.language.toLowerCase().includes(query))
        );
      }

      return filtered;
    },
  },

  mounted() {
    this.loadPosts();
    this.getUser();
    this.initializeQuillEditor();
  },

  methods: {
    // === FILTROS DE IDIOMA ===
    setLanguageFilter(language) {
      this.languageFilter = language;

      // Opcional: Scroll suave al top de la tabla
      this.$nextTick(() => {
        const table = document.querySelector(".table-responsive");
        if (table) {
          table.scrollIntoView({ behavior: "smooth", block: "start" });
        }
      });
    },

    getLanguageName(langCode) {
      const languages = {
        es: "Español",
        en: "English",
        all: "Todos",
      };
      return languages[langCode] || langCode;
    },

    clearFilters() {
      this.searchQuery = "";
      this.languageFilter = "all";

      // Mostrar mensaje de filtros limpiados
      this.showSuccess("Filtros limpiados - Mostrando todos los posts");
    },

    // Método auxiliar para obtener estadísticas de idiomas
    getLanguageStats() {
      const stats = {
        total: this.posts.length,
        es: this.posts.filter((p) => p.language === "es").length,
        en: this.posts.filter((p) => p.language === "en").length,
        undefined: this.posts.filter((p) => !p.language).length,
      };

      return stats;
    },

    // === UTILIDADES ===
    stripHtml(html) {
      const tmp = document.createElement("div");
      tmp.innerHTML = html;
      return tmp.textContent || tmp.innerText || "";
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      });
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

    // === GESTIÓN DE POSTS ===
    async loadPosts() {
      try {
        this.showLoader();
        const response = await axios.get(
          window.APP_CONFIG.API_BASE_URL + "php/getPosts.php"
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

    openCreateModal() {
      this.isEditing = false;
      this.modalTitle = "Crear Post";
      this.currentPost = {
        id: null,
        title: "",
        content: "",
        youtube_url: "",
        language: "es", // NUEVO: Valor por defecto español
        active: true,
      };
      this.formErrors = [];
      this.youtubePreview = false;

      // Limpiar editor
      if (this.quillEditor) {
        this.quillEditor.setContents([]);
      }

      $("#postModal").modal("show");
    },

    async editPost(postId) {
      const postData = await this.loadPostComplete(postId);
      if (!postData) return;

      this.isEditing = true;
      this.modalTitle = "Editar Post";
      this.currentPost = {
        id: postData.id,
        title: postData.title,
        content: postData.content,
        youtube_url: postData.youtube_url || "",
        language: postData.language || "es", // NUEVO: Campo idioma con fallback
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
      const content = this.quillEditor
        ? this.quillEditor.root.innerHTML
        : this.currentPost.content;

      // NUEVO: Validar campos requeridos incluyendo idioma
      if (!this.currentPost.title.trim()) {
        this.formErrors = ["El título es obligatorio"];
        return;
      }

      if (!this.currentPost.language) {
        this.formErrors = ["El idioma es obligatorio"];
        return;
      }

      // Preparar datos del formulario
      const formData = new FormData();
      formData.append("title", this.currentPost.title);
      formData.append("content", content);
      formData.append("youtube_url", this.currentPost.youtube_url || "");
      formData.append("language", this.currentPost.language); // NUEVO: Campo idioma
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

        // Si estamos editando, actualizar contadores específicos
        if (this.isEditing && this.currentPost.id) {
          setTimeout(async () => {
            await this.refreshPostCounts(this.currentPost.id);
          }, 500);
        }
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

    // === GESTIÓN DE ELIMINACIÓN ===
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
        this.showSuccess("Post eliminado exitosamente");
        await this.loadPosts();
      } catch (error) {
        this.showError(
          "Error al eliminar post: " +
            (error.response?.data?.message || error.message)
        );
      } finally {
        this.hideLoader();
        this.postToDelete = null;
      }
    },

    // === GESTIÓN DE MEDIOS ===
    async manageMedia(postId) {
      const postData = await this.loadPostComplete(postId);
      if (!postData) {
        console.error("No se pudo cargar post data");
        return;
      }

      const newCurrentMediaPost = {
        id: parseInt(postData.id),
        title: String(postData.title || ""),
        images: postData.images || {
          listing: [],
          header: [],
          content: [],
        },
      };

      this.currentMediaPost = newCurrentMediaPost;
      this.newImage = {
        type: "content",
        alt_text: "",
      };

      $("#mediaModal").modal("show");
    },

    async refreshPostCounts(postId) {
      try {
        const postData = await this.loadPostComplete(postId);
        if (postData && postData.images) {
          const totalImages = Object.values(postData.images).reduce(
            (sum, typeImages) => sum + (typeImages ? typeImages.length : 0),
            0
          );

          const totalVideos =
            postData.youtube_url && postData.youtube_url.trim() !== "" ? 1 : 0;

          this.updatePostInList(postId, {
            total_images: totalImages,
            total_videos: totalVideos,
          });
        }
      } catch (error) {
        console.error("Error al actualizar contadores:", error);
      }
    },

    updatePostInList(postId, updateData) {
      const postIndex = this.posts.findIndex((p) => p.id === parseInt(postId));
      if (postIndex !== -1) {
        this.$set(this.posts, postIndex, {
          ...this.posts[postIndex],
          ...updateData,
        });
      }
    },

    async uploadImage(e) {
      e.preventDefault();

      const fileInput = this.$refs.imageFile;
      if (!fileInput.files.length) {
        this.showError("Seleccione un archivo");
        return;
      }

      if (!this.currentMediaPost || !this.currentMediaPost.id) {
        console.error("ERROR CRÍTICO: currentMediaPost.id no está definido");
        this.showError(
          "Error: No se puede identificar el post. Cierre y vuelva a abrir el modal."
        );
        return;
      }

      const formData = new FormData();
      formData.append("image", fileInput.files[0]);
      formData.append("post_id", this.currentMediaPost.id);
      formData.append("type", this.newImage.type);
      formData.append("alt_text", this.newImage.alt_text);

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

        const postIdToReload = this.currentMediaPost.id;
        const updatedPost = await this.loadPostComplete(postIdToReload);
        if (updatedPost) {
          this.currentMediaPost.images = updatedPost.images || {
            listing: [],
            header: [],
            content: [],
          };
        }

        await this.refreshPostCounts(this.currentMediaPost.id);

        fileInput.value = "";
        this.newImage.alt_text = "";
      } catch (error) {
        console.error("Error en uploadImage:", error);
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

      try {
        this.showLoader();

        const formData = new FormData();
        formData.append("id", imageId);

        await axios.post(
          window.APP_CONFIG.API_BASE_URL + "php/deleteImage.php",
          formData
        );

        this.showSuccess("Imagen eliminada exitosamente");

        const postIdToReload = this.currentMediaPost.id;
        const updatedPost = await this.loadPostComplete(postIdToReload);
        if (updatedPost) {
          this.currentMediaPost.images = updatedPost.images || {
            listing: [],
            header: [],
            content: [],
          };
        }

        await this.refreshPostCounts(this.currentMediaPost.id);
      } catch (error) {
        this.showError(
          "Error al eliminar imagen: " +
            (error.response?.data?.message || error.message)
        );
      } finally {
        this.hideLoader();
      }
    },

    getImageUrl(imagePath) {
      if (!imagePath) return "";

      // Si ya es una URL completa, devolverla
      if (imagePath.startsWith("http")) return imagePath;

      // Construir URL relativa
      return window.APP_CONFIG.API_BASE_URL + "../" + imagePath;
    },

    // === INICIALIZACIÓN DEL EDITOR ===
    initializeQuillEditor() {
      const toolbarOptions = [
        ["bold", "italic", "underline", "strike"],
        ["blockquote", "code-block"],
        [{ header: 1 }, { header: 2 }],
        [{ list: "ordered" }, { list: "bullet" }],
        [{ script: "sub" }, { script: "super" }],
        [{ indent: "-1" }, { indent: "+1" }],
        [{ size: ["small", false, "large", "huge"] }],
        [{ header: [1, 2, 3, 4, 5, 6, false] }],
        [{ color: [] }, { background: [] }],
        [{ font: [] }],
        [{ align: [] }],
        ["clean"],
        ["link"],
      ];

      this.quillEditor = new Quill("#editor", {
        modules: {
          toolbar: toolbarOptions,
        },
        placeholder: "Escriba el contenido del post aquí...",
        theme: "snow",
      });
    },
  },
});
