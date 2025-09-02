// Ejecutar animacion del paso a paso
document.addEventListener("DOMContentLoaded", () => {
  const pasos = document.querySelectorAll(".paso");
  let index = 0;

  setInterval(() => {
    pasos.forEach((paso, i) => {
      paso.classList.toggle("activo", i === index);
    });

    index = (index + 1) % pasos.length;
  }, 3000);
});
