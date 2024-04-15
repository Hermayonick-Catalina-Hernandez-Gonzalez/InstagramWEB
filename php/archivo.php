<?php

// Imports de archivos requieridos
require "config.php";
require "./sesion_requerida.php";
require "./connection.php";

$now = new DateTime();  // fecha hora actual de la ejecución

// Validamos el parámetro URL "id", que se haya recibido y que sea un int
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
// Si la validación es correcta, $id sera un int con el valor del parámetro URL,
// de otra forma regresará un false.

// Evaluamos si el parémtro es incorrecto, para regresar un status code 400.
// NOTA: si 0 es un posible valor que podemos recibir, la evaluación de 
// if (!$id)... no nos serviría, porque al recibir un valor 0 se evalua como
// false, por lo que si uno de los valores aceptables es 0 se debe usar la
// siguiente evaluación if ($id === false || $id === NULL) ...
if (!$id) {  
    http_response_code(400);  // Regresamos error 400 = Bad Request
    exit();  // Fin de la ejecución.
}

// Consulta del registro del archivo en DB
$sqlCmd = "SELECT * FROM fotos WHERE id = ?";
$sqlParam = [$id]; // Parámetros de la consulta
$stmt = $connection->prepare($sqlCmd);  // obtención del statement
$stmt->execute($sqlParam);  // ejecución de la consulta
$archivo = $stmt->fetch();  // obtenemos el primer registro que nos regresa el query

// Validación di la consulta no regresó resultados -> archivo no existe
if (!$archivo) {  // Si no existe el registro del archivo
    http_response_code(404);  // Regresamos error 404 = Not Found.
    exit();  // Fin de la ejecución.
}

// Obtenemos la ruta completa del archivo, en la carpeta de archivos subidos.
$rutaArchivo = "C:/xampp/htdocs/xampp/InstagramWEB/fotos/" . $archivo["nombre_archivo_guardado"];
if (!file_exists($rutaArchivo)) {   // Si no exite el archivo.
    http_response_code(404);  // Regresamos error 404 = Not Found.
    exit();  // Fin de la ejecución.
}

// Indica si se está obteniendo el archivo solo como un preview, esto es
// para mostrar una miniatura de este (en el caso de imágenes)
$preview = filter_input(INPUT_GET, "preview", FILTER_VALIDATE_BOOLEAN);

// Parámetro URL de tipo boolean (&descargar=1 o &descargar=true) que indica si
// el archivo se va a descargar o solo se va a mostrar
$descargar = filter_input(INPUT_GET, "descargar", FILTER_VALIDATE_BOOLEAN);

// Verificamos si la visualización del archivo está en preview
if (!$preview) {  // Si no es preview

    // Actualizamos el contador de las descargar del archivo
    $sqlCmd = "UPDATE archivos SET cant_descargas = ? WHERE id = ?";
    $cant_descargas = $archivo["cant_descargas"] + 1;
    $sqlParam = [$cant_descargas, $id];
    $stmt = $db->prepare($sqlCmd);
    $stmt->execute($sqlParam);

    // Se guarda la operación  en tabla archivos_log_general
    $sqlCmd =  // Sentencia SQL del Insert en archivos_log_general
            "INSERT INTO archivos_log_general " .
            "    (archivo_id, usuario_id, fecha_hora, accion_realizada, " .
            "     ip_realiza_operacion)" .
            "  VALUES (?, ?, ?, ?, ?)";
    $accionRealizada = $descargar ? "DESCARGAR" : "VISUALIZAR";
    $fechaHora = $now->format("Y-m-d H:i:s");
    $sqlParam = [  // array con los datos a guardar, según los placeholders '?'
        $id, $USUARIO_ID, $fechaHora, $accionRealizada, $REQUEST_IP_ADDRESS
    ];
    $stmt = $db->prepare($sqlCmd);  // obtenemos el statement de la ejecución
    $stmt->execute($sqlParam);  // ejecutamos el statement con los parámetros

    // Obtenemos el id del registro insertado en archivos_log_general, por si lo ocupamos
    $idEnLog = (int)$db->lastInsertId();  // la función regresa string, convertimos a int
}

// Determinamos el content-type a partir de la extensión del archivo.
$contentType = 
        array_key_exists($archivo["extension"], $CONTENT_TYPES_EXT) ? 
        $CONTENT_TYPES_EXT[$archivo["extension"]] : $CONTENT_TYPES_EXT["bin"];

// Especificamos el tipo de respuesta.
header("Content-Type: $contentType");

// Nombre del archivo a enviar en el response
$nombreArchivo = $archivo["nombre_archivo"];  

// En el header del response Content-Disposition, podemos especificar como el
// web browser se va a comportar con el archivo de la respuesta, que va a estar
// en función del parámetro URL "descargar" obenido anteriormente,
// para decirle en la respuesta al web browser que fuerce la descarga del archivo
// (attachment), así como le podermos decir que lo muestre (inline) si es un tipo
// de archivo que puede mostrar (imágenes, videos, PDF, txt...)
// NOTA: El web browser evalúa si puede mostrar el archivo según el header
// Content-Type, no por la extensión del archivo; además si es un tipo de archivo
// que no puede mostrar, aunque le hayamos puesto "inline", este se va a 
// descargar a fuerza (ej. un archivo ZIP).
$disposition = $descargar ? "attachment" : "inline";
header("Content-Disposition: $disposition; filename=\"$nombreArchivo\"");

// Enviamos el tamaño de la respuesta, que será el tamaño del archivo.
$tamaño = $archivo["tamaño"];
header("Content-Length: $tamaño");

// Enviamos el archivo como respuesta.
readfile($rutaArchivo);
//echo file_get_contents($rutaArchivo);  // Otra forma de regresar archivo como respuesta.
