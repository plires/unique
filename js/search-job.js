const position = document.getElementById("position");
const place = document.getElementById("place");
const jobFunction = document.getElementById("jobFunction");
const type = document.getElementById("type");

let root = "http://unique.test/backend/";

let app = new Vue({
  el: "#app",
  data: {
    jobs: [],
    arrayJobFiltered: [],
    selectLocation: "",
    selectJobFunction: "",
    selectEmploymentType: "",
    position: "",
    location: "",
    job_function: "",
    employment_type: "",
    formConsultPosition: "",
    formConsultLocation: "",
    formConsultJobFunction: "",
    formConsultEmploymentType: "",
    titleButton: "",
  },

  mounted() {
    this.getJobs();
  },

  methods: {
    changeButtonStyle(element, title, background, color) {
      element.textContent = title;
      element.style.background = background;
      element.style.color = color;
    },

    changeTitleButton(event, lang) {
      let buttonsLinkedin = $(".btn_linkedin");

      const textViewMoreButton = lang == "es" ? "ver más" : "view more";
      const textHideButton = lang == "es" ? "ocultar" : "hide";

      if (event.target.textContent == textViewMoreButton) {
        for (var i = buttonsLinkedin.length - 1; i >= 0; i--) {
          this.changeButtonStyle(
            buttonsLinkedin[i],
            textViewMoreButton,
            "transparent",
            "var(--primary-color)"
          );
        }
        this.changeButtonStyle(
          event.target,
          textHideButton,
          "var(--primary-color)",
          "white"
        );
      } else {
        this.changeButtonStyle(
          event.target,
          textViewMoreButton,
          "transparent",
          "var(--primary-color)"
        );
      }
    },

    prepareForm(id) {
      job = this.jobs.filter((job) => job.id == id);
      this.formConsultPosition = job[0].position;
      this.formConsultLocation = job[0].location;
      this.formConsultJobFunction = job[0].job_function;
      this.formConsultEmploymentType = job[0].employment_type;
    },

    filterSelect: function (value, property) {
      searchLocation = this.location;
      searchJobFunction = this.job_function;
      searchEmploymentType = this.employment_type;

      var result = this.jobs;

      if (searchLocation != "") {
        result = result.filter((job) =>
          job.location.toString().includes(searchLocation)
        );
      }

      if (searchJobFunction != "") {
        result = result.filter((job) =>
          job.job_function.toString().includes(searchJobFunction)
        );
      }

      if (searchEmploymentType != "") {
        result = result.filter((job) =>
          job.employment_type.toString().includes(searchEmploymentType)
        );
      }

      this.refreshSelects(result);
      this.arrayJobFiltered = result;
    },

    getJobs() {
      loader();

      axios.all([axios.get(root + "php/getJobs.php")]).then(
        axios.spread((jobs) => {
          console.log(jobs);
          let jobEnabled = jobs.data.filter((job) => job.status == 1);
          let JobsOrderByName = jobEnabled.sort((a, b) =>
            a.id - b.id ? 1 : -1
          ); // Oredenamos por id mayor a menor
          this.jobs = JobsOrderByName;
          this.arrayJobFiltered = this.jobs;

          this.refreshSelects(this.jobs);
        })
      );

      $("#loader").fadeOut(500);
    },

    refreshSelects(arrayPrincipal) {
      // Llenar select Location

      let array = groupBy(arrayPrincipal, "location");
      let property = Object.keys(array);
      this.selectLocation = property;

      // Llenar select job_function
      array = groupBy(arrayPrincipal, "job_function");
      property = Object.keys(array);
      this.selectJobFunction = property;

      // Llenar select employment_type
      array = groupBy(arrayPrincipal, "employment_type");
      property = Object.keys(array);
      this.selectEmploymentType = property;
    },
  },

  computed: {
    findJob: function () {
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

function groupBy(objectArray, property) {
  return objectArray.reduce((acc, obj) => {
    const key = obj[property];
    if (!acc[key]) {
      acc[key] = [];
    }
    // Add object to list for given key's value
    return acc;
  }, {});
}

/**
 * Funcion para mostrar el spin de load cuando se ejecuta al guna operacion asyncronica
 */
function loader() {
  $("#loader").css("display", "flex");
  return;
}
