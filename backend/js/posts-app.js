// === CONFIGURACIÓN GLOBAL ===
const APP_CONFIG = {
  // Esta URL será reemplazada dinámicamente por PHP
  API_BASE_URL: window.API_BASE_URL || "http://unique.test/backend/",
};

// === UTILIDADES ===
const Utils = {
  // Mostrar mensaje de éxito
  showSuccess(message) {
    if (typeof msgSuccess === "function") {
      msgSuccess(message);
    } else {
      alert(message);
    }
  },

  // Mostrar mensaje de error
  showError(message) {
    alert(message);
  },

  // Mostrar/ocultar loader
  toggleLoader(show = true) {
    if (show && typeof loader === "function") {
      loader();
    } else {
      $("#loader").fadeOut(500);
    }
  },

  // Hacer petición AJAX con manejo de errores estandarizado
  async makeRequest(url, options = {}) {
    const defaultOptions = {
      method: "GET",
      headers: {
        Accept: "application/json",
      },
    };

    const config = { ...defaultOptions, ...options };

    try {
      const response = await axios(url, config);
      return response.data;
    } catch (error) {
      console.error("Error en petición:", error);

      if (error.response && error.response.data) {
        const errorData = error.response.data;
        throw new Error(errorData.message || "Error en el servidor");
      } else {
        throw new Error("Error de conexión");
      }
    }
  },
};

// === APLICACIÓN VUE PARA GESTIÓN DE POSTS ===
let postsApp = new Vue({
  el: "#app",
  data: {
    posts: [],
    searchQuery: "",
    languageFilter: "all", // Filtro de idioma por defecto
    messages: [],

    // NUEVAS PROPIEDADES PARA PAGINACIÓN
    pagination: {
      current_page: 1,
      per_page: 10,
      total: 0,
      total_pages: 0,
      has_prev: false,
      has_next: false,
      showing_from: 0,
      showing_to: 0,
    },
    isLoading: false,

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

  mounted() {
    this.loadPosts();
    this.getUser();
    this.initializeQuillEditor();
    this.searchTimeout = null;
  },

  watch: {
    searchQuery: "searchPosts",
  },

  methods: {
    // === FILTROS DE IDIOMA ===
    setLanguageFilter(language) {
      this.languageFilter = language;
      this.loadPosts(1, true); // Cargar desde página 1 con nuevo filtro
    },

    // AGREGAR método para búsqueda con debounce
    searchPosts() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.loadPosts(1, true);
      }, 300);
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
      this.loadPosts(1, true);
      this.showSuccess("Filtros reseteados - Mostrando todos los posts");
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

    // === GESTIÓN DE USUARIO ===
    async getUser() {
      try {
        Utils.toggleLoader(true);
        const userData = await Utils.makeRequest(
          APP_CONFIG.API_BASE_URL + "php/getUser.php"
        );
        this.user = userData;
      } catch (error) {
        console.error("Error al cargar usuario:", error);
      } finally {
        Utils.toggleLoader(false);
      }
    },

    checkFormUser() {
      this.errorsUser = [];

      if (!this.user.user) {
        this.errorsUser.push("El nombre de usuario es obligatorio.");
      }
      if (!this.user.email) {
        this.errorsUser.push("El email de contacto obligatorio.");
      }

      if (this.changePass) {
        let pass = $("#pass").val();
        let cpass = $("#cpass").val();

        if (!pass || !cpass) {
          this.errorsUser.push("Ingresa la contraseña.");
        }

        if (pass != cpass) {
          this.errorsUser.push("Las contraseñas no coinciden.");
        }

        if (pass.length < 6 || cpass.length < 6) {
          this.errorsUser.push(
            "Las contraseñas deben tener al menos 6 caracteres."
          );
        }
      }

      return this.errorsUser.length === 0;
    },

    // Alias para compatibilidad
    checkForm() {
      return this.checkFormUser();
    },

    submitFormUser(e) {
      e.preventDefault();

      if (!this.checkFormUser()) {
        return;
      }

      const data = $("#formUser").serialize();

      Utils.toggleLoader(true);
      $.ajax({
        type: "POST",
        url: APP_CONFIG.API_BASE_URL + "php/editUser.php",
        data: data,
        success: (response) => {
          if (response) {
            Utils.toggleLoader(false);
            $("#modalFormUser").modal("hide");
            Utils.showSuccess("El usuario se editó exitosamente.");
            this.getUser();
          }
        },
        error: () => {
          Utils.toggleLoader(false);
          Utils.showError("Hubo un error, intente nuevamente.");
        },
      });
    },

    rememberPassword() {
      this.changePass = !this.changePass;
    },

    // === GESTIÓN DE POSTS ===
    async loadPosts(page = 1, resetPage = false) {
      this.isLoading = true;
      this.showLoader();

      if (resetPage) {
        page = 1;
        this.pagination.current_page = 1;
      }

      try {
        const params = new URLSearchParams({
          page: page,
          per_page: this.pagination.per_page,
          include_inactive: true,
        });

        // Agregar filtros si están activos
        if (this.languageFilter !== "all") {
          params.append("language", this.languageFilter);
        }

        if (this.searchQuery.trim()) {
          params.append("search", this.searchQuery.trim());
        }

        const response = await fetch(`php/getPosts.php?${params}`);
        const result = await response.json();

        if (result) {
          this.posts = result.data || [];
          this.pagination = result.pagination || this.pagination;
        }
      } catch (error) {
        console.error("Error al cargar posts:", error);
        this.showError("Error al cargar los posts");
      } finally {
        this.isLoading = false;
        this.hideLoader();
      }
    },

    // MÉTODO PARA CAMBIAR DE PÁGINA
    changePage(page) {
      if (page >= 1 && page <= this.pagination.total_pages) {
        this.loadPosts(page);
      }
    },

    // MÉTODO PARA CAMBIAR NÚMERO DE ELEMENTOS POR PÁGINA
    changePerPage(perPage) {
      this.pagination.per_page = perPage;
      this.loadPosts(1, true); // Reiniciar a página 1
    },

    // MÉTODO AUXILIAR PARA PAGINACIÓN INTELIGENTE
    getPaginationRange() {
      const current = this.pagination.current_page;
      const total = this.pagination.total_pages;
      const range = [];

      // Mostrar páginas alrededor de la actual
      let start = Math.max(2, current - 1);
      let end = Math.min(total - 1, current + 1);

      // Ajustar rango si estamos cerca del inicio o final
      if (current <= 3) {
        end = Math.min(total - 1, 4);
      }
      if (current >= total - 2) {
        start = Math.max(2, total - 3);
      }

      for (let i = start; i <= end; i++) {
        if (i !== 1 && i !== total) {
          range.push(i);
        }
      }

      return range;
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
        title: "", // Asegurar que sea string vacío
        content: "",
        youtube_url: "",
        language: "es",
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

      // VALIDACIÓN ROBUSTA: Asegurar que todos los campos sean strings válidos
      this.currentPost = {
        id: postData.id,
        title: String(postData.title || ""), // Convertir a string y manejar null/undefined
        content: String(postData.content || ""),
        youtube_url: String(postData.youtube_url || ""),
        language: postData.language || "es", // Fallback a español
        active: postData.status == 1,
      };

      this.formErrors = [];

      // Cargar contenido en el editor
      if (this.quillEditor) {
        this.quillEditor.root.innerHTML = this.currentPost.content;
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

      // VALIDACIÓN MEJORADA: Verificar que currentPost.title sea string válido
      if (
        !this.currentPost.title ||
        typeof this.currentPost.title !== "string" ||
        !this.currentPost.title.trim()
      ) {
        this.formErrors = ["El título es obligatorio"];
        return;
      }

      if (!this.currentPost.language) {
        this.formErrors = ["El idioma es obligatorio"];
        return;
      }

      // Preparar datos del formulario con strings seguros
      const formData = new FormData();
      formData.append("title", String(this.currentPost.title).trim());
      formData.append("content", String(content));
      formData.append(
        "youtube_url",
        String(this.currentPost.youtube_url || "")
      );
      formData.append("language", String(this.currentPost.language));
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

        // 🔧 CORRECCIÓN: Mostrar errores específicos si existen
        let errorMessage = "Error al subir imagen";

        if (error.response?.data) {
          const errorData = error.response.data;

          // Si hay errores específicos en el array, mostrarlos
          if (
            errorData.errors &&
            Array.isArray(errorData.errors) &&
            errorData.errors.length > 0
          ) {
            errorMessage = errorData.errors.join(". ");
          }
          // Si no, usar el mensaje general
          else if (errorData.message) {
            errorMessage = errorData.message;
          }
        }
        // Si no hay respuesta del servidor, usar el mensaje de error general
        else if (error.message) {
          errorMessage = error.message;
        }

        this.showError(errorMessage);
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
        // 🔧 CORRECCIÓN: Mismo manejo de errores mejorado
        let errorMessage = "Error al eliminar imagen";

        if (error.response?.data) {
          const errorData = error.response.data;

          if (
            errorData.errors &&
            Array.isArray(errorData.errors) &&
            errorData.errors.length > 0
          ) {
            errorMessage = errorData.errors.join(". ");
          } else if (errorData.message) {
            errorMessage = errorData.message;
          }
        } else if (error.message) {
          errorMessage = error.message;
        }

        this.showError(errorMessage);
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
