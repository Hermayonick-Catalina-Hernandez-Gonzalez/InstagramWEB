<?php
require "./config.php";
require "sesion_requerida.php";
require "connection.php";

$nombre = filter_input(INPUT_POST, "nombre");
$apellidos = filter_input(INPUT_POST, "apellidos");
$genero = filter_input(INPUT_POST, "genero");
$fecha_Nac = filter_input(INPUT_POST, "fecha-nacimiento");
$email = filter_input(INPUT_POST, "correo");

if (!$nombre && !$email) {
    $mensaje = "No se enviaron los datos requeridos";
    require "../vistas/editarperfil.php";
    exit();
}

$sql = "UPDATE usuarios SET nombre = ?, email = ?";
$params = [$nombre, $email];

if ($apellidos) {
    $sql .= ", apellidos = ?";
    $params[] = $apellidos;
}

if ($genero) {
    $sql .= ", genero = ?";
    $params[] = $genero;
}

if ($fecha_Nac) {
    $sql .= ", fecha_nacimiento = ?";
    $params[] = $fecha_Nac;
}

if ($_FILES || isset($_FILES["imagen"]) || $_FILES["imagen"]["name"]) {
    $archivoSubido = $_FILES["imagen"];

    $nombreArchivo = $archivoSubido["name"];  // el nombre de archivo original
    $nombreArchivoParts = explode(".", $nombreArchivo);  // obtenemos array por "."
    $extension = strtolower($nombreArchivoParts[count($nombreArchivoParts) - 1]);

    if (in_array($extension, $EXT_ARCHIVOS_FOTOS)) {
        $ruta = "C:/xampp/htdocs/xampp/InstagramWEB/fotos_perfil/" . $usuarioID . "_" . $nombre . "." . $extension;  // ruta donde se guardará el archivo

        //Revisar si existe algun archivo con ese nombre y borrarlo para reemplazarlo
        if (file_exists($ruta)) {
            $seBorro = unlink($ruta);
            if (!$seBorro) {
                $mensaje = "Hay una imagen de perfil que no fue posible borrar";
                require "../vistas/editarperfil.php";
                exit();
            }
        }
        $seGuardo = move_uploaded_file($archivoSubido["tmp_name"], $ruta);

        // Si no se guardo el archivo, regresamos un error
        if (!$seGuardo) {
            $mensaje = "No se logro guardar el mensaje";
            require "../vistas/editarperfil.php";
            exit();
        } else {
            $sql .= ", foto_perfil = ?";
            $params[] = $usuarioID . "_" . $nombre . "." . $extension;
        }
    } else {
        $mensaje = "Tipo de archivo no válido, solo se admiten imágenes";
        require "../vistas/editarperfil.php";
        exit();
    }
}

$sql .= "WHERE id = ?";
$params[] = $usuarioID;

$stmt = $connection->prepare($sql);
if ($stmt->execute($params)) {
    header("Location: ../vistas/perfil.php");
} else {
    $mensaje = "No se pudo modificar la informacion";
    require "../vistas/editarperfil.php";
    exit();
}
