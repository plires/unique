$(document).ready(function () {
  // Manejar click en imágenes para modal
  $(".post-content-image").on("click", function () {
    const imageSrc = $(this).data("image-src");
    const imageAlt = $(this).data("image-alt");

    $("#modalImage").attr("src", imageSrc).attr("alt", imageAlt);
    $("#modalImageCaption").text(imageAlt);

    $("#imageModal").modal("show");
  });

  // Manejar teclas en modal
  $("#imageModal").on("keydown", function (e) {
    if (e.key === "Escape") {
      $("#imageModal").modal("hide");
    }
  });

  // Focus trap para accesibilidad
  $("#imageModal").on("shown.bs.modal", function () {
    // En Bootstrap 4 el botón de cierre tiene clase 'close' en lugar de 'btn-close'
    $(this).find(".close").focus();
  });
});
