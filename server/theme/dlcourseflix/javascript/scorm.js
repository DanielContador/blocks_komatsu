document.addEventListener('DOMContentLoaded', function() {
    // Seleccionamos el botón original y lo ocultamos.
    var toggleBtn = document.querySelector('#scorm_toc_toggle_btn');
    if (toggleBtn) {
        toggleBtn.style.display = "none";
    }

    // Seleccionamos el contenedor en el que se ubica el botón.
    var toggleTOC = document.querySelector('#scorm_toc_toggle');
    if (toggleTOC) {
        // Creamos un nuevo elemento span para el ícono.
        var customIcon = document.createElement('span');
        customIcon.setAttribute("aria-hidden", "true");
        // Estado inicial: usaremos "block-undock", en este ejemplo invertido.
        customIcon.setAttribute("data-flex-icon", "block-undock");
        customIcon.setAttribute("title", "Minimizar en la barra lateral");
        customIcon.classList.add("flex-icon", "ft-fw", "ft", "lucide-arrow-big-left-dash", "fs-18");
        customIcon.id = "custom-icon";
        customIcon.classList.add("fs-flip");

        // Insertamos el nuevo ícono sin borrar el contenido existente.
        toggleTOC.appendChild(customIcon);

        // Agregamos el event listener para alternar el ícono al hacer clic en el contenedor.
        var counter = 0;
        toggleTOC.addEventListener("click", function() {
            var iconSpan = document.querySelector('#custom-icon');
            // Si el ícono actual es "block-undock", se cambia a "block-dock" y viceversa.
            if (counter == 0) {
                counter++;
                iconSpan.setAttribute("data-flex-icon", "block-undock");
                iconSpan.title = "Minimizar en la barra lateral";
                iconSpan.classList.remove("fs-flip");
            } else {
                counter--;
                iconSpan.setAttribute("data-flex-icon", "block-dock");
                iconSpan.title = "Desacoplar todo";
                iconSpan.classList.add("fs-flip");
            }
        });
    }
});
