<?php
require "../php/registro_helper.php";

session_start();
$mensaje = "";

if ($_POST) {
    // Limpiar y validar los campos de entrada
    $nombre = filter_input(INPUT_POST, "nombre", FILTER_SANITIZE_STRING);
    $apellidos = filter_input(INPUT_POST, "apellido", FILTER_SANITIZE_STRING);
    $fechaNacimiento = filter_input(INPUT_POST, "fecha-nacimiento", FILTER_SANITIZE_STRING);
    $genero = filter_input(INPUT_POST, "genero", FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, "correo", FILTER_VALIDATE_EMAIL);
    $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING);
    $confirm_password = filter_input(INPUT_POST, "confirm_password", FILTER_SANITIZE_STRING);

    // Validaciones
    if ($email === false) {
        $mensaje = "Correo electrónico no válido.";
    } elseif ($password !== $confirm_password) {
        $mensaje = "Las contraseñas no coinciden.";
    } elseif (!esMayorDeEdad($fechaNacimiento)) {
        $mensaje = "Debes ser mayor de edad para registrarte.";
    } else {
        // Registrar el usuario
        $registrar = registrar($nombre, $apellidos, $fechaNacimiento, $genero, $email, $username, $password);
        if ($registrar) {
            header('Location: ./login.php');
            exit;
        } else {
            $mensaje = "Ocurrió un error al registrar el usuario.";
        }
    }
}

// Función para verificar la edad
function esMayorDeEdad($fechaNacimiento) {
    try {
        $fechaNacimiento = new DateTime($fechaNacimiento);
        $hoy = new DateTime();
        $edad = $hoy->diff($fechaNacimiento)->y;
        return $edad >= 18;
    } catch (Exception $e) {
        return false; // Si hay un error en la fecha, no dejar continuar
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse</title>
    <link rel="icon" href="../img/Logo.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/styleregister.css">
    <script src="../js/validarEdad.js"></script>
</head>

<body>
    <div class="card">
        <h1>Registrarse</h1>
        <form class="ingresos" action="registrarse.php" method="post">
            <label id="error-label"><?php echo htmlspecialchars($mensaje); ?></label>
            <label>Nombre:</label>
            <input type="text" placeholder="Usuario..." name="nombre" required>
            <label>Apellido:</label>
            <input type="text" placeholder="Apellido..." name="apellido">
            <label>Fecha de Nacimiento:</label>
            <input type="date" id="fecha-nacimiento" name="fecha-nacimiento" required>
            <label>Genero:</label>
            <select id="genero" name="genero" required>
                <option value="" disabled selected>Selecciona tu género...</option>
                <option value="M">Masculino</option>
                <option value="F">Femenino</option>
                <option value="X">Prefiero no especificar</option>
            </select>

            <label>Correo electrónico:</label>
            <input type="email" placeholder="Correo electrónico..." name="correo" required>
            <label>Username:</label>
            <input type="text" placeholder="Username..." name="username" required>
            <label>Contraseña:</label>
            <input type="password" placeholder="Contraseña..." name="password" required>
            <label>Confirmar Contraseña:</label>
            <input type="password" placeholder="Confirmar contraseña..." name="confirm_password" required>
            <div class="cont-btn">
                <button type="submit" class="registrar">Registrar</button>
                <button type="button" class="salir" onclick="window.location.href = 'login.php'">Salir</button>
            </div>
        </form>
    </div>

</body>

</html>