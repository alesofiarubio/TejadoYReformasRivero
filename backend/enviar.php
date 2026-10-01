<?php

// =========================
// CONFIGURACIÓN DEL CORREO
// =========================

$smtpServidor = "mail.spacemail.com";
$smtpPuerto = 465;

$correoEmpresa = "info@tejadosyreformasrivero.com";

// Configura SMTP_PASSWORD en el entorno del servidor.
$smtpUsuario = "info@tejadosyreformasrivero.com";
$smtpPassword = getenv("SMTP_PASSWORD") ?: "";

$nombreEmpresa = "Tejados y Reformas Rivero";


// =========================
// FUNCIONES
// =========================

function limpiarTexto($texto)
{
    return trim(strip_tags($texto));
}

function enviarCorreoSMTP(
    $servidor,
    $puerto,
    $usuario,
    $password,
    $remitente,
    $destinatario,
    $nombreRemitente,
    $asunto,
    $mensajeTexto
) {
    $errorNumero = 0;
    $errorMensaje = "";

    $conexion = stream_socket_client(
        "ssl://" . $servidor . ":" . $puerto,
        $errorNumero,
        $errorMensaje,
        30
    );

    if (!$conexion) {
        return false;
    }

    stream_set_timeout($conexion, 30);

    $respuesta = fgets($conexion, 515);

    if (!$respuesta || substr($respuesta, 0, 3) !== "220") {
        fclose($conexion);
        return false;
    }

    fwrite($conexion, "EHLO tejadosyreformasrivero.com\r\n");
    leerRespuestaSMTP($conexion);

    fwrite($conexion, "AUTH LOGIN\r\n");
    $respuesta = leerRespuestaSMTP($conexion);

    if (substr($respuesta, 0, 3) !== "334") {
        fclose($conexion);
        return false;
    }

    fwrite($conexion, base64_encode($usuario) . "\r\n");
    $respuesta = leerRespuestaSMTP($conexion);

    if (substr($respuesta, 0, 3) !== "334") {
        fclose($conexion);
        return false;
    }

    fwrite($conexion, base64_encode($password) . "\r\n");
    $respuesta = leerRespuestaSMTP($conexion);

    if (substr($respuesta, 0, 3) !== "235") {
        fclose($conexion);
        return false;
    }

    fwrite($conexion, "MAIL FROM:<" . $remitente . ">\r\n");
    $respuesta = leerRespuestaSMTP($conexion);

    if (substr($respuesta, 0, 3) !== "250") {
        fclose($conexion);
        return false;
    }

    fwrite($conexion, "RCPT TO:<" . $destinatario . ">\r\n");
    $respuesta = leerRespuestaSMTP($conexion);

    if (substr($respuesta, 0, 3) !== "250") {
        fclose($conexion);
        return false;
    }

    fwrite($conexion, "DATA\r\n");
    $respuesta = leerRespuestaSMTP($conexion);

    if (substr($respuesta, 0, 3) !== "354") {
        fclose($conexion);
        return false;
    }

    $fecha = date("r");

    $cabeceras  = "From: " . $nombreRemitente . " <" . $remitente . ">\r\n";
    $cabeceras .= "To: <" . $destinatario . ">\r\n";
    $cabeceras .= "Subject: =?UTF-8?B?" . base64_encode($asunto) . "?=\r\n";
    $cabeceras .= "Date: " . $fecha . "\r\n";
    $cabeceras .= "MIME-Version: 1.0\r\n";
    $cabeceras .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $cabeceras .= "Content-Transfer-Encoding: 8bit\r\n";
    $cabeceras .= "\r\n";

    $contenido = $cabeceras . $mensajeTexto;

    // Protección SMTP frente a líneas que empiezan por punto.
    $contenido = preg_replace("/\r?\n\./", "\r\n..", $contenido);

    fwrite($conexion, $contenido . "\r\n.\r\n");

    $respuesta = leerRespuestaSMTP($conexion);

    if (substr($respuesta, 0, 3) !== "250") {
        fclose($conexion);
        return false;
    }

    fwrite($conexion, "QUIT\r\n");

    fclose($conexion);

    return true;
}


function leerRespuestaSMTP($conexion)
{
    $respuestaCompleta = "";

    while (($linea = fgets($conexion, 515)) !== false) {
        $respuestaCompleta .= $linea;

        // Una respuesta SMTP final tiene el código seguido de un espacio.
        if (isset($linea[3]) && $linea[3] === " ") {
            break;
        }
    }

    return $respuestaCompleta;
}


// =========================
// COMPROBAR MÉTODO
// =========================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.html");
    exit;
}


// =========================
// RECIBIR FORMULARIO
// =========================

$nombre = limpiarTexto($_POST["nombre"] ?? "");
$telefono = limpiarTexto($_POST["telefono"] ?? "");
$email = trim($_POST["email"] ?? "");
$mensaje = limpiarTexto($_POST["mensaje"] ?? "");


// =========================
// VALIDACIONES
// =========================

$errores = [];

if ($nombre === "" || strlen($nombre) < 2) {
    $errores[] = "El nombre no es válido.";
}

if ($telefono !== "" && !preg_match("/^[0-9+\s()\-]{9,20}$/", $telefono)) {
    $errores[] = "El teléfono no es válido.";
}

if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = "El email no es válido.";
}

if ($mensaje === "" || strlen($mensaje) < 10) {
    $errores[] = "El mensaje es demasiado corto.";
}


// =========================
// SI HAY ERRORES
// =========================

if (!empty($errores)) {
    echo "<!DOCTYPE html>";
    echo "<html lang='es'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<title>Error en el formulario</title>";
    echo "<meta http-equiv='refresh' content='4;url=../index.html#contacto'>";
    echo "</head>";
    echo "<body>";
    echo "<h2>No se ha podido enviar el formulario.</h2>";
    echo "<p>Revisa los datos introducidos y vuelve a intentarlo.</p>";
    echo "<p>Volviendo al formulario...</p>";
    echo "</body>";
    echo "</html>";
    exit;
}


// =========================
// CORREO PARA LA EMPRESA
// =========================

$asuntoEmpresa = "Nueva solicitud de presupuesto - " . $nombre;

$mensajeEmpresa =
"Has recibido una nueva solicitud de presupuesto desde la web.

DATOS DEL CLIENTE
----------------------------

Nombre: " . $nombre . "

Teléfono: " . ($telefono !== "" ? $telefono : "No indicado") . "

Email: " . $email . "

MENSAJE
----------------------------

" . $mensaje . "

----------------------------

Este mensaje ha sido enviado desde la web de Tejados y Reformas Rivero.";


// =========================
// CORREO DE CONFIRMACIÓN
// =========================

$asuntoCliente = "Hemos recibido tu solicitud - Tejados y Reformas Rivero";

$mensajeCliente =
"Hola " . $nombre . ",

Hemos recibido correctamente tu solicitud de presupuesto.

Gracias por contactar con Tejados y Reformas Rivero.

Hemos recibido el siguiente mensaje:

----------------------------

" . $mensaje . "

----------------------------

Nos pondremos en contacto contigo lo antes posible.

Un saludo,

Tejados y Reformas Rivero
info@tejadosyreformasrivero.com";


// =========================
// ENVIAR A LA EMPRESA
// =========================

$correoEmpresaEnviado = enviarCorreoSMTP(
    $smtpServidor,
    $smtpPuerto,
    $smtpUsuario,
    $smtpPassword,
    $correoEmpresa,
    $correoEmpresa,
    $nombreEmpresa,
    $asuntoEmpresa,
    $mensajeEmpresa
);


// =========================
// ENVIAR CONFIRMACIÓN AL CLIENTE
// =========================

$correoClienteEnviado = enviarCorreoSMTP(
    $smtpServidor,
    $smtpPuerto,
    $smtpUsuario,
    $smtpPassword,
    $correoEmpresa,
    $email,
    $nombreEmpresa,
    $asuntoCliente,
    $mensajeCliente
);


// =========================
// COMPROBAR RESULTADO
// =========================

if ($correoEmpresaEnviado && $correoClienteEnviado) {

    header("Location: ../index.html?envio=correcto#contacto");
    exit;
}


// =========================
// ERROR DE ENVÍO
// =========================

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>Error al enviar</title>";
echo "</head>";
echo "<body>";
echo "<h2>No se ha podido enviar el formulario.</h2>";
echo "<p>Ha ocurrido un problema al enviar el correo.</p>";
echo "<p>Vuelve atrás e inténtalo de nuevo.</p>";
echo "</body>";
echo "</html>";

?>