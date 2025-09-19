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

        // Procesar datos para asegurar contadores numéricos
        this.posts = response.data.map((post) => ({
          ...post,
          // Asegurar que los contadores sean números
          total_images: parseInt(post.total_images) || 0,
          total_videos: parseInt(post.total_videos) || 0,
        }));
      } catch (error) {
        this.showError(
          "Error al cargar posts: " +
            (error.response?.data?.message || error.message)
        );
      } finally {
        this.hideLoader();
      }
    },

    // === NUEVOS MÉTODOS PARA ACTUALIZACIÓN REACTIVA ===

    /**
     * NUEVO: Actualizar contadores específicos de un post sin recargar toda la lista
     */
    async refreshPostCounts(postId) {
      try {
        const response = await axios.get(
          window.APP_CONFIG.API_BASE_URL + `php/getPost.php?id=${postId}`
        );

        if (response.data) {
          const updatedPost = response.data;

          // Contar imágenes del post actualizado
          let totalImages = 0;
          if (updatedPost.images) {
            Object.values(updatedPost.images).forEach((typeImages) => {
              if (Array.isArray(typeImages)) {
                totalImages += typeImages.length;
              }
            });
          }

          // Contar videos (1 si hay URL de YouTube)
          const totalVideos =
            updatedPost.youtube_url && updatedPost.youtube_url.trim() !== ""
              ? 1
              : 0;

          // Actualizar el post en la lista de manera reactiva
          this.updatePostInList(postId, {
            total_images: totalImages,
            total_videos: totalVideos,
          });
        }
      } catch (error) {
        console.error("Error al actualizar contadores:", error);
        // No mostrar error al usuario para esta operación en segundo plano
      }
    },

    /**
     * NUEVO: Actualizar un post específico en la lista de manera reactiva
     */
    updatePostInList(postId, updateData) {
      const postIndex = this.posts.findIndex((p) => p.id === parseInt(postId));
      if (postIndex !== -1) {
        // Usar Vue.set para asegurar reactividad
        this.$set(this.posts, postIndex, {
          ...this.posts[postIndex],
          ...updateData,
        });
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

    /**
     * MODIFICADO: submitPost() - mantener funcionalidad existente + actualizar contadores
     */
    async submitPost(e) {
      e.preventDefault();

      // MANTENER: Obtener contenido del editor
      const content = this.quillEditor
        ? this.quillEditor.root.innerHTML
        : this.currentPost.content;

      // MANTENER: Preparar datos del formulario
      const formData = new FormData();
      formData.append("title", this.currentPost.title);
      formData.append("content", content);
      formData.append("youtube_url", this.currentPost.youtube_url || ""); // MANTENER campo YouTube
      formData.append("active", this.currentPost.active ? 1 : 0);

      if (this.isEditing) {
        formData.append("edit", "true");
        formData.append("id", this.currentPost.id);
      }

      try {
        this.showLoader(); // MANTENER

        await axios.post(
          window.APP_CONFIG.API_BASE_URL + "php/add_edit_post.php",
          formData
        );

        $("#postModal").modal("hide"); // MANTENER

        const message = this.isEditing
          ? "Post actualizado exitosamente"
          : "Post creado exitosamente";
        this.showSuccess(message); // MANTENER

        // MODIFICADO: Recargar posts para obtener contadores actualizados
        await this.loadPosts();

        // NUEVO: Si estamos editando, también actualizar contadores específicos
        // (especialmente importante si se cambió la URL de YouTube)
        if (this.isEditing && this.currentPost.id) {
          // Pequeño delay para asegurar que el servidor procesó los cambios
          setTimeout(async () => {
            await this.refreshPostCounts(this.currentPost.id);
          }, 500);
        }
      } catch (error) {
        // MANTENER: Manejo de errores existente
        if (error.response?.data?.errors) {
          this.formErrors = error.response.data.errors;
        } else {
          this.showError(
            "Error al guardar post: " +
              (error.response?.data?.message || error.message)
          );
        }
      } finally {
        this.hideLoader(); // MANTENER
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
      const postData = await this.loadPostComplete(postId);
      if (!postData) {
        console.error("No se pudo cargar post data");
        return;
      }

      // Método alternativo: crear objeto completamente nuevo
      const newCurrentMediaPost = {
        id: parseInt(postData.id), // Forzar conversión a entero
        title: String(postData.title || ""),
        images: postData.images || {
          listing: [],
          header: [],
          content: [],
        },
      };

      // Asignar el objeto completo
      this.currentMediaPost = newCurrentMediaPost;

      this.newImage = {
        type: "content",
        alt_text: "",
      };

      $("#mediaModal").modal("show");
    },

    // === MÉTODOS DE GESTIÓN DE MEDIOS MODIFICADOS ===

    /**
     * MODIFICADO: uploadImage() - mantener funcionalidad existente + actualizar contadores
     */
    async uploadImage(e) {
      e.preventDefault();

      const fileInput = this.$refs.imageFile;
      if (!fileInput.files.length) {
        this.showError("Seleccione un archivo");
        return;
      }

      // MANTENER: Validación existente
      if (!this.currentMediaPost || !this.currentMediaPost.id) {
        console.error("ERROR CRÍTICO: currentMediaPost.id no está definido");
        console.error("currentMediaPost:", this.currentMediaPost);
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
        this.showLoader(); // MANTENER

        await axios.post(
          window.APP_CONFIG.API_BASE_URL + "php/uploadImage.php",
          formData,
          {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          }
        );

        this.showSuccess("Imagen subida exitosamente"); // MANTENER

        // MANTENER: Recargar medios del modal
        const postIdToReload = this.currentMediaPost.id;
        const updatedPost = await this.loadPostComplete(postIdToReload);
        if (updatedPost) {
          this.currentMediaPost.images = updatedPost.images || {
            listing: [],
            header: [],
            content: [],
          };
        }

        // NUEVO: Actualizar contadores en la tabla principal de manera reactiva
        await this.refreshPostCounts(this.currentMediaPost.id);

        // MANTENER: Limpiar formulario
        fileInput.value = "";
        this.newImage.alt_text = "";
      } catch (error) {
        console.error("Error en uploadImage:", error);
        this.showError(
          "Error al subir imagen: " +
            (error.response?.data?.message || error.message)
        );
      } finally {
        this.hideLoader(); // MANTENER
      }
    },

    /**
     * MODIFICADO: deleteImage() - mantener funcionalidad existente + actualizar contadores
     */
    async deleteImage(imageId) {
      if (!confirm("¿Está seguro de eliminar esta imagen?")) {
        return;
      }

      console.log("=== INICIO deleteImage ===");
      console.log("Image ID:", imageId);
      console.log("currentMediaPost:", this.currentMediaPost);

      // MANTENER: Validación existente
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
        this.showLoader(); // MANTENER

        await axios.post(
          window.APP_CONFIG.API_BASE_URL + "php/deleteImage.php",
          formData
        );

        this.showSuccess("Imagen eliminada exitosamente"); // MANTENER

        // MANTENER: Recargar datos del post con ID validado
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

        // NUEVO: Actualizar contadores en la tabla principal de manera reactiva
        await this.refreshPostCounts(this.currentMediaPost.id);

        console.log("=== deleteImage completado ===");
      } catch (error) {
        console.error("Error en deleteImage:", error);
        this.showError(
          "Error al eliminar imagen: " +
            (error.response?.data?.message || error.message)
        );
      } finally {
        this.hideLoader(); // MANTENER
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
