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

// === APLICACIÓN VUE ===
let app = new Vue({
  el: "#app",
  data: {
    jobs: [],
    user: {},
    jobEdit: false,
    idJobToEdit: false,
    idJobToDelete: "",
    q: "",
    titleForm: "Agregar Trabajo",
    position: "",
    location: "",
    job_function: "",
    employment_type: "",
    description: "",
    jobDescription: "",
    link: "",
    errors: [],
    errorsUser: [],
    messages: [],
    changePass: false,
  },

  computed: {
    // Filtrar trabajos según búsqueda
    searchJob() {
      if (!this.q) {
        return this.jobs;
      }

      const query = this.q.toLowerCase();
      return this.jobs.filter(
        (job) =>
          job.position.toLowerCase().includes(query) ||
          job.location.toLowerCase().includes(query) ||
          job.job_function.toLowerCase().includes(query) ||
          job.employment_type.toLowerCase().includes(query)
      );
    },
  },

  mounted() {
    this.getJobs();
    this.getUser();
  },

  methods: {
    // === GESTIÓN DE IMÁGENES POR TIPO ===
    openImageModal(postId, imageType = "content") {
      this.currentPostId = postId;
      this.currentImageType = imageType;

      // Configurar títulos según tipo
      const titles = {
        listing: "Imagen para Listado (Cuadrada)",
        header: "Imagen de Header (Horizontal)",
        content: "Imágenes de Contenido",
      };

      this.imageModalTitle = titles[imageType] || "Subir Imagen";

      // Resetear formulario
      this.imageFormErrors = [];
      document.getElementById("imageFile").value = "";
      document.getElementById("imageAltText").value = "";

      $("#imageModal").modal("show");
    },

    async uploadImage(e) {
      e.preventDefault();

      const formData = new FormData();
      const fileInput = document.getElementById("imageFile");
      const altText = document.getElementById("imageAltText").value;

      if (!fileInput.files[0]) {
        this.showError("Debe seleccionar una imagen");
        return;
      }

      formData.append("post_id", this.currentPostId);
      formData.append("image", fileInput.files[0]);
      formData.append("type", this.currentImageType);
      formData.append("alt_text", altText);

      try {
        this.showLoader();
        const response = await axios.post(
          window.APP_CONFIG.API_BASE_URL + "php/uploadImage.php",
          formData,
          {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          }
        );

        if (response.data.success) {
          this.showSuccess(["Imagen subida exitosamente"]);
          $("#imageModal").modal("hide");
          await this.loadPosts(); // Recargar lista
        } else {
          this.imageFormErrors = response.data.errors || [
            "Error al subir imagen",
          ];
        }
      } catch (error) {
        this.showError(
          "Error al subir imagen: " +
            (error.response?.data?.message || error.message)
        );
      } finally {
        this.hideLoader();
      }
    },

    // Método para mostrar imágenes agrupadas por tipo
    displayImagesByType(images) {
      const container = document.getElementById("imagesContainer");
      if (!container) return;

      let html = "";

      // Imagen de listado
      if (images.listing && images.listing.length > 0) {
        html += "<h6>Imagen de Listado:</h6>";
        images.listing.forEach((img) => {
          html += `<div class="image-item">
        <img src="../${img.file_path}" alt="${img.alt_text}" style="width: 100px; height: 100px; object-fit: cover;">
        <button onclick="app.deleteImage(${img.id})" class="btn btn-sm btn-danger">Eliminar</button>
      </div>`;
        });
      }

      // Imagen de header
      if (images.header && images.header.length > 0) {
        html += "<h6>Imagen de Header:</h6>";
        images.header.forEach((img) => {
          html += `<div class="image-item">
        <img src="../${img.file_path}" alt="${img.alt_text}" style="width: 200px; height: 100px; object-fit: cover;">
        <button onclick="app.deleteImage(${img.id})" class="btn btn-sm btn-danger">Eliminar</button>
      </div>`;
        });
      }

      // Imágenes de contenido
      if (images.content && images.content.length > 0) {
        html += "<h6>Imágenes de Contenido:</h6>";
        images.content.forEach((img) => {
          html += `<div class="image-item">
        <img src="../${img.file_path}" alt="${img.alt_text}" style="width: 150px; height: 100px; object-fit: cover;">
        <button onclick="app.deleteImage(${img.id})" class="btn btn-sm btn-danger">Eliminar</button>
      </div>`;
        });
      }

      container.innerHTML = html;
    },

    async deleteImage(imageId) {
      if (!confirm("¿Está seguro de eliminar esta imagen?")) return;

      try {
        this.showLoader();
        const response = await axios.post(
          window.APP_CONFIG.API_BASE_URL + "php/deleteImage.php",
          { id: imageId }
        );

        if (response.data.success) {
          this.showSuccess(["Imagen eliminada exitosamente"]);
          await this.loadPosts(); // Recargar lista
        } else {
          this.showError("Error al eliminar imagen");
        }
      } catch (error) {
        this.showError("Error al eliminar imagen: " + error.message);
      } finally {
        this.hideLoader();
      }
    },
    // === GESTIÓN DE TRABAJOS ===

    async getJobs() {
      try {
        Utils.toggleLoader(true);
        const response = await Utils.makeRequest(
          APP_CONFIG.API_BASE_URL + "php/getJobs.php"
        );

        // Ordenar por ID descendente - usar response.data en lugar de response
        this.jobs = response.data.sort((a, b) => b.id - a.id);
      } catch (error) {
        Utils.showError("Error al cargar trabajos: " + error.message);
      } finally {
        Utils.toggleLoader(false);
      }
    },

    async submitFormJob(e) {
      e.preventDefault();

      if (!this.validateJobForm()) {
        return;
      }

      const formData = new FormData();
      formData.append("position", this.position);
      formData.append("location", this.location);
      formData.append("job_function", this.job_function);
      formData.append("employment_type", this.employment_type);
      formData.append("description", this.description);
      formData.append("link", this.link);

      if (this.jobEdit) {
        formData.append("edit", "true");
        formData.append("id", this.idJobToEdit);
      }

      try {
        Utils.toggleLoader(true);

        // Guardar el estado de edición ANTES de hacer la petición
        const isEditing = this.jobEdit;

        const response = await axios.post(
          APP_CONFIG.API_BASE_URL + "php/add_edit_job.php",
          formData
        );

        $("#modalAddJob").modal("hide");
        this.resetForm();

        // Usar la variable local en lugar de this.jobEdit
        const message = isEditing
          ? "Trabajo editado exitosamente"
          : "Trabajo agregado exitosamente";
        Utils.showSuccess(message);

        await this.getJobs();
      } catch (error) {
        // Manejar errores de validación del servidor
        if (error.response?.data?.errors) {
          // Mostrar los errores del servidor en el formulario
          this.errors = error.response.data.errors;
        } else {
          // Error genérico
          Utils.showError(
            "Error al guardar trabajo: " +
              (error.response?.data?.message || error.message)
          );
        }
      } finally {
        Utils.toggleLoader(false);
      }
    },

    validateJobForm() {
      this.errors = [];

      if (!this.position.trim()) {
        this.errors.push("La posición es obligatoria");
      }
      if (!this.location.trim()) {
        this.errors.push("La ubicación es obligatoria");
      }
      if (!this.job_function.trim()) {
        this.errors.push("La función laboral es obligatoria");
      }
      if (!this.employment_type.trim()) {
        this.errors.push("El tipo de empleo es obligatorio");
      }

      return this.errors.length === 0;
    },

    editJob(id) {
      const job = this.jobs.find((j) => j.id == id);
      if (!job) {
        Utils.showError("Trabajo no encontrado");
        return;
      }

      this.jobEdit = true;
      this.idJobToEdit = id;
      this.position = job.position;
      this.location = job.location;
      this.job_function = job.job_function;
      this.employment_type = job.employment_type;
      this.description = job.description;
      this.link = job.link || "";
      this.titleForm = "Editar Trabajo";

      $("#modalAddJob").modal("show");
    },

    resetForm() {
      this.jobEdit = false;
      this.idJobToEdit = false;
      this.position = "";
      this.location = "";
      this.job_function = "";
      this.employment_type = "";
      this.description = "";
      this.link = "";
      this.errors = [];
      this.titleForm = "Agregar Trabajo";
    },

    setIdJobToDelete(id) {
      this.idJobToDelete = id;
    },

    async deleteJob() {
      if (!this.idJobToDelete) {
        return;
      }

      const formData = new FormData();
      formData.append("id", this.idJobToDelete);

      try {
        Utils.toggleLoader(true);

        await Utils.makeRequest(APP_CONFIG.API_BASE_URL + "php/deleteJob.php", {
          method: "POST",
          data: formData,
        });

        $("#formDelete").modal("hide");
        this.idJobToDelete = "";
        Utils.showSuccess("Trabajo eliminado exitosamente");

        await this.getJobs();
      } catch (error) {
        Utils.showError("Error al eliminar trabajo: " + error.message);
      } finally {
        Utils.toggleLoader(false);
      }
    },

    async changeStatus(id, currentStatus) {
      const formData = new FormData();
      formData.append("id", id);
      formData.append("status", currentStatus);

      try {
        Utils.toggleLoader(true);

        await Utils.makeRequest(
          APP_CONFIG.API_BASE_URL + "php/changeStatusJob.php",
          {
            method: "POST",
            data: formData,
          }
        );

        Utils.showSuccess("Estado cambiado exitosamente");
        await this.getJobs();
      } catch (error) {
        Utils.showError("Error al cambiar estado: " + error.message);
      } finally {
        Utils.toggleLoader(false);
      }
    },

    showDescription(description) {
      $("#modalJobDescription").modal("show");
      this.jobDescription = description;
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
  },
});
