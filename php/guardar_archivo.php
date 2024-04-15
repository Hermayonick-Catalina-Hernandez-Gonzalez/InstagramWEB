<?php

// import de los archivos de código necesarios para la ejecución de este PHP file
require "./config.php";  // Configuraciones generales de la aplicación
require "./sesion_requerida.php";  // para acceder a las variables de sesión
require "./connection.php";  // para el acceso a datos

// Indicamos que la respuesta es de tipo JSON, porque la petición a esta ejecución
// será por AJAX
header("Content-Type: application/json");

$errores = [];  // Array para guardar los errores obtenidos.
$now = new DateTime();  // fecha hora actual de la ejecución

// Se valida que el usuario está autenticado, de lo contrario se regresa un
// json con el error (usuario no autenticado)
if (!$usuarioAutenticado) {
    $errores[] = "Usuario no autenticado";
    echo json_encode(["errores" => $errores]);
    exit();  // fin de la ejecución de este archivo PHP
}

// Se valida que se haya enviado el archivo en el request
if (
    empty($_FILES) || !isset($_FILES["archivo"]) ||
    empty($_FILES["archivo"]["name"])
) {
    $errores[] = "No es especificó el archivo.";
    echo json_encode(["errores" => $errores]);
    exit();  // fin de la ejecución de este archivo PHP
}

// Obtenemos el assoc array con los datos del archivo subido
$archivoSubido = $_FILES["archivo"];

// Para obtener el dato de la descripción del archivo, si es que se envió
$descripcion = filter_input(INPUT_POST, "descripcion");
$descripcion = $descripcion && strlen(trim($descripcion)) ? // valor establecido?
    trim($descripcion) : NULL;  // SI -> quitamos espacios en blanco : NO -> NULL

// Se obtienen los datos del archivo que se subió
$nombreArchivo = $archivoSubido["name"];  // el nombre de archivo original
$nombreArchivoParts = explode(".", $nombreArchivo);  // obtenemos array por "."
$extension = strtolower($nombreArchivoParts[count($nombreArchivoParts) - 1]);
$tamaño = $archivoSubido["size"];  // tamaño del archivo subido

// Validación del tipo de archivo que se subió, que su extensión corresponda a
// algún tipo de archivo de imagen
if (!in_array($extension, $EXT_ARCHIVOS_FOTOS)) {
    $errores[] = "Tipo de archivo no válido, solo se admiten imágenes";
    echo json_encode(["errores" => $errores]);
    exit();  // fin de la ejecución de este archivo PHP
}

// Generamos un nombre de archivo random (que para este caso será un número
// hexadecimal) de longitud de 64 chars según 32 bytes random
$nombreArchivoGuardado = strtoupper(bin2hex(random_bytes(32)));
$ruta = "C:/xampp/htdocs/xampp/InstagramWEB/fotos/" . $nombreArchivoGuardado . "." . $extension;  // ruta donde se guardará el archivo
$seGuardo = move_uploaded_file($archivoSubido["tmp_name"], $ruta);

// Si no se guardo el archivo, regresamos un error
if (!$seGuardo) {
    $errores[] = "ERROR de IO al guardar el archivo";
    echo json_encode(["errores" => $errores]);
    exit();  // fin de la ejecución de este archivo PHP
}

// Obtenemos el SHA256 del archivo
$hashSha256 = strtoupper(hash_file("sha256", $ruta));

// Se obtiene la fecha-hora actual en string con formato yyyy-MM-dd HH:mm:ss
// (ej. "2024-12-31 23:59:59") porque es el formato que el MySQL/MariaDB 
// reconoce como una fecha-hora válida
$fechaSubido = $now->format("Y-m-d H:i:s");

// Ejecutamos la operación de insert del registro del archivo en DB
$sqlCmd =  // Sentencia SQL del INSERT
    "INSERT INTO fotos (secure_id, extension, usuario_subio_id, nombre_archivo, tamaño, descripcion, fecha_subido) VALUES (?, ?, ?, ?, ?, ?, ?)";
$sqlParam = [  // array con los datos a guardar, según los placeholders '?'
    $nombreArchivoGuardado, $extension, $usuarioID, $nombreArchivo, $tamaño, $descripcion, $fechaSubido
];

$stmt = $connection->prepare($sqlCmd);  // obtenemos el statement de la ejecución
$stmt->execute($sqlParam);

// Obtenemos el id del registro que insertamos en tabla archivos, dado que 
// este se calcula en DB al ser autoincrement
$id = (int)$connection->lastInsertId(); // la función regresa string, convertimos a int

// Regresamos una respuesta como un JSON string, aquí pueden ir los diferentes datos que
// pudieramos necesitar en el front-end.
$resObj = [
    "errores" => $errores,
    "archivo" => [
        "id" => $id,
        "nombreArchivo" => $nombreArchivo,
        "extension" => $extension,
        "descripcion" => $descripcion,
        "nombreArchivoGuardado" => $nombreArchivoGuardado,
        "fechaSubido" => $fechaSubido,
        "tamaño" => $tamaño
    ]
];

echo json_encode($resObj);  // JSON a regresar como respuesta
