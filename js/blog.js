let app = new Vue({
  el: "#app",
  data: {
    posts: [],
    isLoading: false,
    language: "es", // Se detectará automáticamente
    pagination: {
      current_page: 1,
      total_pages: 1,
      total: 0,
      per_page: 9, // 3x3 grid
      has_prev: false,
      has_next: false,
      showing_from: 0,
      showing_to: 0,
    },
  },

  mounted() {
    this.detectLanguage();
    this.loadPosts();
  },

  methods: {
    /**
     * Detectar idioma automáticamente basado en la URL
     */
    detectLanguage() {
      const currentPath = window.location.pathname;

      // Si la URL contiene '/en/' es inglés, sino español
      if (currentPath.includes("/en/")) {
        this.language = "en";
      } else {
        this.language = "es";
      }
    },

    /**
     * Obtener la URL base de la API según el idioma actual
     */
    getApiBaseUrl() {
      if (this.language === "en") {
        // Desde /en/blog.php necesita subir un nivel: ../api/
        return "../api/getPostsPublic.php";
      } else {
        // Desde /blog.php puede acceder directamente: api/
        return "api/getPostsPublic.php";
      }
    },

    /**
     * Cargar posts desde la API
     */
    async loadPosts(page = 1) {
      this.isLoading = true;

      try {
        const params = new URLSearchParams({
          page: page,
          per_page: this.pagination.per_page,
          language: this.language,
          status: "active",
        });

        const apiUrl = this.getApiBaseUrl();
        const response = await axios.get(`${apiUrl}?${params}`);

        if (response.data && response.data.success) {
          this.posts = this.processPostsData(
            response.data.data || response.data.posts || []
          );
          this.pagination = response.data.pagination || this.pagination;
        } else {
          console.error("Error en respuesta de API:", response.data);
          this.posts = [];
        }
      } catch (error) {
        console.error("Error al cargar posts:", error);
        this.posts = [];
      } finally {
        this.isLoading = false;
      }
    },

    /**
     * Procesar datos de posts para agregar información adicional
     */
    processPostsData(posts) {
      return posts.map((post) => {
        // Generar URL de la imagen
        let imageUrl = null;
        if (post.listing_image_path) {
          imageUrl = this.getImageUrl(post.listing_image_path);
        } else if (post.listing_image) {
          imageUrl = this.getImageUrl(post.listing_image);
        }

        // Formatear fecha según idioma
        const formattedDate = this.formatDate(post.created_at);

        // Generar excerpt del contenido
        const excerpt = this.getContentExcerpt(post.content, 120);

        return {
          ...post,
          image_url: imageUrl,
          formatted_date: formattedDate,
          excerpt: excerpt,
        };
      });
    },

    /**
     * Obtener URL completa de imagen
     */
    getImageUrl(imagePath) {
      if (!imagePath) return null;

      // Si ya es URL completa
      if (imagePath.startsWith("http")) {
        return imagePath;
      }

      // Construir URL relativa
      return "/" + imagePath.replace(/^\/+/, "");
    },

    /**
     * Formatear fecha según el idioma
     */
    formatDate(dateString) {
      const date = new Date(dateString);

      if (this.language === "es") {
        // Formato español
        const months = [
          "enero",
          "febrero",
          "marzo",
          "abril",
          "mayo",
          "junio",
          "julio",
          "agosto",
          "septiembre",
          "octubre",
          "noviembre",
          "diciembre",
        ];

        const day = date.getDate();
        const month = months[date.getMonth()];
        const year = date.getFullYear();

        return `${day} de ${month} de ${year}`;
      } else {
        // Formato inglés
        const options = {
          year: "numeric",
          month: "long",
          day: "numeric",
        };

        return date.toLocaleDateString("en-US", options);
      }
    },

    /**
     * Extraer excerpt del contenido HTML
     */
    getContentExcerpt(content, maxLength = 150) {
      if (!content) return "";

      // Decodificar entidades HTML
      const textarea = document.createElement("textarea");
      textarea.innerHTML = content;
      const decodedContent = textarea.value;

      // Remover tags HTML
      const plainText = decodedContent.replace(/<[^>]*>/g, " ");

      // Limpiar espacios múltiples
      const cleanText = plainText.replace(/\s+/g, " ").trim();

      // Truncar texto
      if (cleanText.length <= maxLength) {
        return cleanText;
      }

      const truncated = cleanText.substring(0, maxLength);
      const lastSpace = truncated.lastIndexOf(" ");

      if (lastSpace > 0) {
        return truncated.substring(0, lastSpace) + "...";
      }

      return truncated + "...";
    },

    /**
     * Cambiar página
     */
    changePage(page) {
      if (
        page >= 1 &&
        page <= this.pagination.total_pages &&
        page !== this.pagination.current_page
      ) {
        this.loadPosts(page);

        // Scroll suave al inicio de la sección
        this.$nextTick(() => {
          const blogSection = document.getElementById("blog");
          if (blogSection) {
            blogSection.scrollIntoView({
              behavior: "smooth",
              block: "start",
            });
          }
        });
      }
    },

    /**
     * Obtener rango de páginas para paginación inteligente
     */
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

    /**
     * Obtener textos según el idioma actual
     */
    getText(key) {
      const texts = {
        es: {
          loading: "Cargando contenido...",
          noArticles: "No hay artículos disponibles",
          noArticlesDesc:
            "Aún no se han publicado artículos en nuestro blog. ¡Vuelve pronto para ver las últimas novedades!",
          readMore: "Leer más",
          previous: "Anterior",
          next: "Siguiente",
          showing: "Mostrando",
          to: "a",
          of: "de",
          articles: "artículos",
        },
        en: {
          loading: "Loading content...",
          noArticles: "No articles available",
          noArticlesDesc:
            "No articles have been published on our blog yet. Come back soon to see the latest news!",
          readMore: "Read more",
          previous: "Previous",
          next: "Next",
          showing: "Showing",
          to: "to",
          of: "of",
          articles: "articles",
        },
      };

      return texts[this.language][key] || key;
    },
  },

  computed: {
    /**
     * Verificar si hay posts para mostrar
     */
    hasPosts() {
      return !this.isLoading && this.posts.length > 0;
    },

    /**
     * Verificar si mostrar estado vacío
     */
    showEmptyState() {
      return !this.isLoading && this.posts.length === 0;
    },

    /**
     * Texto de paginación formateado
     */
    paginationText() {
      if (this.pagination.total === 0) return "";

      return `${this.getText("showing")} ${
        this.pagination.showing_from
      } ${this.getText("to")} ${this.pagination.showing_to} ${this.getText(
        "of"
      )} ${this.pagination.total} ${this.getText("articles")}`;
    },
  },
});

/**
 * Función auxiliar para mostrar loader (compatibilidad con código existente)
 */
function loader() {
  const loaderEl = document.getElementById("loader");
  if (loaderEl) {
    loaderEl.style.display = "flex";
  }
}
