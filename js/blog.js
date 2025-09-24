let app = new Vue({
  el: "#app",
  data: {},

  mounted() {},

  methods: {},

  computed: {},
});

/**
 * Funcion para mostrar el spin de load cuando se ejecuta al guna operacion asyncronica
 */
function loader() {
  $("#loader").css("display", "flex");
  return;
}
