const contentNav = document.getElementsByClassName("content_navegacion")[0];
const btnMenuMobile = document.getElementById("btn-menu-mobile");
const header = document.getElementsByTagName("header")[0];
const nav = document.getElementsByClassName("btn_nav");

let counterNav = 1;

for (let i = 0; i < nav.length; i++) {
  nav[i].addEventListener("click", function () {
    removeAllActiveClassFromSectionInstitucional();
    setActiveClass(this);
    counterNav = 1;
    contentNav.classList.remove("open");
  });
}

function removeAllActiveClassFromSectionInstitucional() {
  for (let i = 0; i < nav.length; i++) {
    nav[i].classList.remove("active");
  }
}

function setActiveClass(link) {
  link.classList.add("active");
}

btnMenuMobile.addEventListener("click", function () {
  setNavOpenOrClose();
});

function setNavOpenOrClose() {
  if (counterNav == 1) {
    contentNav.classList.add("open");
    counterNav = 0;
  } else {
    counterNav = 1;
    contentNav.classList.remove("open");
  }
}

/* Scroll header */
$(window).scroll(function () {
  headerScroll();
});

function headerScroll() {
  if ($(document).scrollTop() > header.offsetHeight) {
    $("header").addClass("header_scroll");
  } else {
    $("header").removeClass("header_scroll");
  }
}

$(function () {
  $(".btn_nav").bind("click", function (event) {
    var $anchor = $(this);
    $("html, body")
      .stop()
      .animate(
        {
          scrollTop: $($anchor.attr("href")).offset().top - 90,
        },
        1500,
        "easeInOutExpo"
      );
    event.preventDefault();
  });
});

// Inicializa Wow
new WOW().init();

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

setTimeout(function () {
  $(".eapps-link").remove();
  $(".css-1tmxu1x").css("display:none !important;");
}, 3000);

// Primero obtenemos la altura de la ventana gráfica y la multiplicamos por 1% para obtener un valor para una unidad vh
let vh = window.innerHeight * 0.01;
// Luego establecemos el valor en la propiedad personalizada --vh en la raíz del documento
document.documentElement.style.setProperty("--vh", `${vh}px`);

// Escuchamos el evento de cambio de tamaño
window.addEventListener("resize", () => {
  // Ejecutamos el mismo script que antes
  let vh = window.innerHeight * 0.01;
  document.documentElement.style.setProperty("--vh", `${vh}px`);
});
