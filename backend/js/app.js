let root = "http://unique.test/backend/";

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
  mounted() {
    this.getJobs();
    this.getUser();
  },
  methods: {
    showDescription(description) {
      $("#modalJobDescription").modal("show");
      this.jobDescription = description;
    },

    checkFormUser: function () {
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

      return this.errorsUser;
    },

    submitFormUser(e) {
      e.preventDefault();
      this.checkFormUser();

      if (this.errorsUser.length === 0) {
        let data = $("#formUser").serialize();

        let url = root + "php/editUser.php";

        loader();
        $.ajax({
          type: "POST",
          url: url,
          data: data,
          success: function (response) {
            if (response) {
              $("#loader").fadeOut(500);
              $("#modalFormUser").modal("hide");
              msgSuccess("El usuario se editó existosamente.");
              app.getUser();
            }
          },
          fail: function () {
            $("#loader").fadeOut(500);
            alert("Hubo un error, intente nuevamente.");
          },
        });
      }
    },

    rememberPassword() {
      this.changePass = !this.changePass;
    },

    getJobs() {
      loader();

      axios.all([axios.get(root + "php/getJobs.php")]).then(
        axios.spread((Jobs) => {
          let JobsOrderByName = Jobs.data.sort((a, b) =>
            a.id - b.id ? 1 : -1
          ); // Oredenamos por id mayor a menor
          this.jobs = JobsOrderByName;
        })
      );

      $("#loader").fadeOut(500);
    },

    getUser() {
      loader();

      axios.all([axios.get(root + "php/getUser.php")]).then(
        axios.spread((user) => {
          this.user = user.data;
        })
      );

      $("#loader").fadeOut(500);
    },

    editJob(id) {
      this.jobEdit = true;
      this.idJobToEdit = id;
      let job = this.jobs.filter((student) => student.id == id);

      this.position = job[0].position;
      this.location = job[0].location;
      this.job_function = job[0].job_function;
      this.employment_type = job[0].employment_type;
      this.description = job[0].description;

      if (job[0].link != 0) {
        this.link = job[0].link;
      } else {
        this.link = "";
      }

      this.titleForm = "Editar Trabajo";

      $("#modalAddJob").modal("show");
    },

    setIdJobToDelete(id) {
      this.idJobToDelete = id;
    },

    deleteJob() {
      loader();

      let formData = new FormData();
      formData.append("id", this.idJobToDelete);

      axios
        .post(root + "php/deleteJob.php", formData)
        .then((response) => {
          if (response.data) {
            $("#formDelete").modal("hide");
            this.idJobToDelete = "";
            msgSuccess("El registro se eliminó existosamente.");
            app.getJobs();
          } else {
            this.idJobToDelete = "";
            alert("Hubo un error. por favor intente mas tarde");
          }

          $("#loader").fadeOut(500);
        })
        .catch((e) => {
          alert(e);
        });
    },

    changeStatus(id, status) {
      loader();

      let formData = new FormData();
      formData.append("id", id);
      formData.append("status", status);

      axios
        .post(root + "php/changeStatusJob.php", formData)
        .then((response) => {
          if (response.data) {
            msgSuccess("El registro cambió su estado existosamente.");
            app.getJobs();
          } else {
            alert("Hubo un error. por favor intente mas tarde");
          }

          $("#loader").fadeOut(500);
        })
        .catch((e) => {
          alert(e);
        });
    },

    resetForm() {
      this.position = "";
      this.location = "";
      this.job_function = "";
      this.employment_type = "";
      this.description = "";
      this.link = "";
      this.titleForm = "Agregar Trabajo";
      this.jobEdit = false;
      this.idJobToEdit = false;
    },

    checkForm: function (e) {
      e.preventDefault();

      this.errors = [];

      if (!this.position) {
        this.errors.push("Agregar la posición del trabajo");
      }

      if (!this.location) {
        this.errors.push("Agregar la ubicación del trabajo");
      }

      if (!this.job_function) {
        this.errors.push("Agregar la función laboral del trabajo");
      }

      if (!this.employment_type) {
        this.errors.push("Agregar el tipo de empleo");
      }

      if (!this.description) {
        this.errors.push("Agregar una descripción de puesto");
      }

      if (this.link) {
        let isUrl = this.validURL(this.link);

        if (!isUrl) {
          this.errors.push("Verifique la URL");
        }
      }

      if (this.errors.length == 0) {
        this.submitForm();
      }
    },

    validURL(str) {
      var pattern = new RegExp(
        "^(https?:\\/\\/)?" + // protocol
          "((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|" + // domain name
          "((\\d{1,3}\\.){3}\\d{1,3}))" + // OR ip (v4) address
          "(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*" + // port and path
          "(\\?[;&a-z\\d%_.~+=-]*)?" + // query string
          "(\\#[-a-z\\d_]*)?$",
        "i"
      ); // fragment locator
      return !!pattern.test(str);
    },

    submitForm() {
      loader();

      let form = document.getElementById("formData");
      let formData = new FormData(form);

      if (app.jobEdit) {
        formData.append("edit", true);
        formData.append("id", this.idJobToEdit);
      }

      axios
        .post(root + "php/add_edit_job.php", formData)
        .then((response) => {
          if (response.data) {
            $("#modalAddJob").modal("hide");
            msgSuccess("El registro se agregó / editó existosamente.");
            app.getJobs();
          } else {
            alert("Hubo un error. por favor intente mas tarde");
          }

          $("#loader").fadeOut(500);
        })
        .catch((e) => {
          alert(e);
        });
    },
  },

  computed: {
    searchJob: function () {
      var string = "";
      var substring = "";

      let jobsFilter = [];
      for (let i = 0; i < this.jobs.length; i++) {
        string = this.jobs[i].position.toString().toLowerCase();
        substring = this.q.toLowerCase();

        if (string.includes(substring)) {
          jobsFilter.push(this.jobs[i]);
        }
      }

      return jobsFilter;
    },
  },
});

/**
 * Funcion para mostrar el spin de load cuando se ejecuta al guna operacion asyncronica
 */
function loader() {
  $("#loader").css("display", "flex");
  return;
}

function msgSuccess(msg) {
  app.messages.push(msg);
  setTimeout(function () {
    $("#messages").fadeOut("slow", function () {
      app.messages = [];
    });
  }, 3000);
}

// Validacion del Formulario
(function () {
  "use strict";
  window.addEventListener(
    "load",
    function () {
      // Fetch all the forms we want to apply custom Bootstrap validation styles to
      var forms = document.getElementsByClassName("needs-validation");
      // Loop over them and prevent submission
      var validation = Array.prototype.filter.call(forms, function (form) {
        form.addEventListener(
          "submit",
          function (event) {
            if (form.checkValidity() === false) {
              event.preventDefault();
              event.stopPropagation();
            }
            form.classList.add("was-validated");
          },
          false
        );
      });
    },
    false
  );
})();
