document.addEventListener("DOMContentLoaded", function() {
    // Selecciona todos los botones con la clase "collapsible"
    var coll = document.getElementsByClassName("collapsible");

    for (var i = 0; i < coll.length; i++) {
        coll[i].addEventListener("click", function() {
            // Alterna la clase "active" al botón (opcional, para estilos)
            this.classList.toggle("active");

            // Obtiene el elemento siguiente, el div .content
            var content = this.nextElementSibling;
            // Accedemos al icono para cambiar su estado
            var icon = this.querySelector("span.icon-toggle i");

            if (content.style.display === "block") {
                // Primero removemos la clase que realiza el desplazamiento
                content.classList.remove("expanded");
                // Después, removemos el contenido (podrías sincronizar con la transición si lo deseas)
                setTimeout(function() {
                    content.style.display = "none";
                }, 300); // 300ms coincide con la duración de la transición
                // Actualizamos el icono a + al cerrar
                if (icon) {
                    icon.classList.remove("fa-minus");
                    icon.classList.add("fa-plus");
                }
            } else {
                // Mostramos el contenido
                content.style.display = "block";
                // Para que la transición se aplique, se agrega la clase tras un breve retardo (permite que el navegador registre el cambio)
                setTimeout(function() {
                    content.classList.add("expanded");
                }, 10);
                // Actualizamos el icono a - al abrir
                if (icon) {
                    icon.classList.remove("fa-plus");
                    icon.classList.add("fa-minus");
                }
            }
        });
    }
});
