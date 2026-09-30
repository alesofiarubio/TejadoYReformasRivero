// MENÚ PARA MÓVIL

const botonMenu = document.querySelector(".boton-menu");
const menu = document.querySelector("nav");

botonMenu.addEventListener("click", function () {
    menu.classList.toggle("mostrar");
});


// CERRAR MENÚ AL PULSAR UN ENLACE

const enlacesMenu = document.querySelectorAll("nav a");

enlacesMenu.forEach(function (enlace) {

    enlace.addEventListener("click", function () {
        menu.classList.remove("mostrar");
    });

});


// MOSTRAR MÁS SERVICIOS

const listaServicios = document.querySelector(".servicios");
const articulosServicio = document.querySelectorAll(".servicio");
const botonServicios = document.querySelector(".boton-servicios");

if (articulosServicio.length > 3) {
    listaServicios.classList.add("servicios-compactos");
    botonServicios.hidden = false;
}

botonServicios.addEventListener("click", function () {
    const expandido = listaServicios.classList.toggle("servicios-abiertos");

    botonServicios.textContent = expandido ? "Ver menos" : "Ver más";
    botonServicios.setAttribute("aria-expanded", expandido);
});


// FILTRO DE TRABAJOS

const botonesFiltro = document.querySelectorAll(".filtro");
const fotos = document.querySelectorAll(".foto");
const galeria = document.querySelector(".galeria");
const controlAnterior = document.querySelector(".control-galeria-anterior");
const controlSiguiente = document.querySelector(".control-galeria-siguiente");

function actualizarControlesGaleria() {
    controlAnterior.disabled = galeria.scrollLeft <= 1;
    controlSiguiente.disabled = galeria.scrollLeft + galeria.clientWidth >= galeria.scrollWidth - 1;
}

function moverGaleria(direccion) {
    const primeraFotoVisible = Array.from(fotos).find(function (foto) {
        return foto.style.display !== "none";
    });
    const espacio = parseFloat(getComputedStyle(galeria).gap) || 0;

    galeria.scrollBy({
        left: direccion * (primeraFotoVisible.offsetWidth + espacio),
        behavior: "smooth"
    });
}

controlAnterior.addEventListener("click", function () {
    moverGaleria(-1);
});

controlSiguiente.addEventListener("click", function () {
    moverGaleria(1);
});

galeria.addEventListener("scroll", actualizarControlesGaleria);
window.addEventListener("resize", actualizarControlesGaleria);
actualizarControlesGaleria();

botonesFiltro.forEach(function (boton) {

    boton.addEventListener("click", function () {

        botonesFiltro.forEach(function (otroBoton) {
            otroBoton.classList.remove("activo");
        });

        boton.classList.add("activo");

        const filtro = boton.getAttribute("data-filtro");

        fotos.forEach(function (foto) {

            if (filtro === "todos") {
                foto.style.display = "block";
            }

            else if (foto.classList.contains(filtro)) {
                foto.style.display = "block";
            }

            else {
                foto.style.display = "none";
            }

        });

        galeria.scrollTo({ left: 0, behavior: "smooth" });
        actualizarControlesGaleria();

    });

});


// FORMULARIO

const formulario = document.querySelector(".formulario");

const nombre = formulario.querySelector("[name='nombre']");
const telefono = formulario.querySelector("[name='telefono']");
const email = formulario.querySelector("[name='email']");
const mensaje = formulario.querySelector("[name='mensaje']");

const botonEnviar = formulario.querySelector("button[type='submit']");
const mensajeFormulario = formulario.querySelector(".mensaje-formulario");


// MOSTRAR ERROR

function mostrarError(campo, texto) {

    const contenedor = campo.parentElement;
    const mensajeError = contenedor.querySelector(".mensaje-error");

    campo.classList.remove("campo-correcto");
    campo.classList.add("campo-error");

    mensajeError.textContent = texto;
}


// MARCAR CAMPO COMO CORRECTO

function campoCorrecto(campo) {

    const contenedor = campo.parentElement;
    const mensajeError = contenedor.querySelector(".mensaje-error");

    campo.classList.remove("campo-error");
    campo.classList.add("campo-correcto");

    mensajeError.textContent = "";
}


// VALIDAR NOMBRE

function validarNombre() {

    const valor = nombre.value.trim();

    if (valor === "") {
        mostrarError(nombre, "Escribe tu nombre.");
        return false;
    }

    if (valor.length < 2) {
        mostrarError(nombre, "Escribe un nombre válido.");
        return false;
    }

    campoCorrecto(nombre);
    return true;
}


// VALIDAR TELÉFONO

function validarTelefono() {

    const valor = telefono.value.trim();

    // El teléfono es opcional.
    if (valor === "") {
        telefono.classList.remove("campo-error");
        telefono.classList.remove("campo-correcto");

        telefono.parentElement.querySelector(".mensaje-error").textContent = "";

        return true;
    }

    const numero = valor.replace(/\s/g, "");

    if (!/^[0-9+()-]{9,15}$/.test(numero)) {
        mostrarError(telefono, "Escribe un teléfono válido.");
        return false;
    }

    campoCorrecto(telefono);
    return true;
}


// VALIDAR EMAIL

function validarEmail() {

    const valor = email.value.trim();

    if (valor === "") {
        mostrarError(email, "Escribe tu email.");
        return false;
    }

    const formatoEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!formatoEmail.test(valor)) {
        mostrarError(email, "Escribe un email válido.");
        return false;
    }

    campoCorrecto(email);
    return true;
}


// VALIDAR MENSAJE

function validarMensaje() {

    const valor = mensaje.value.trim();

    if (valor === "") {
        mostrarError(mensaje, "Cuéntanos qué trabajo necesitas.");
        return false;
    }

    if (valor.length < 10) {
        mostrarError(
            mensaje,
            "Escribe un poco más de información sobre el trabajo."
        );

        return false;
    }

    campoCorrecto(mensaje);
    return true;
}


// VALIDACIÓN EN TIEMPO REAL

nombre.addEventListener("input", validarNombre);

telefono.addEventListener("input", validarTelefono);

email.addEventListener("input", validarEmail);

mensaje.addEventListener("input", validarMensaje);


// VALIDAR AL ENVIAR

formulario.addEventListener("submit", function (evento) {

    const nombreCorrecto = validarNombre();
    const telefonoCorrecto = validarTelefono();
    const emailCorrecto = validarEmail();
    const mensajeCorrecto = validarMensaje();

    if (
        !nombreCorrecto ||
        !telefonoCorrecto ||
        !emailCorrecto ||
        !mensajeCorrecto
    ) {
        evento.preventDefault();

        mensajeFormulario.textContent =
            "Revisa los campos marcados antes de enviar.";

        mensajeFormulario.className =
            "mensaje-formulario error";

        return;
    }

    botonEnviar.textContent = "Enviando...";
    botonEnviar.disabled = true;

});