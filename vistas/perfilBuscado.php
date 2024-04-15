<?php
require "../php/sesion_requerida.php";
require "../php/connection.php";

// Obtener el ID del usuario de la URL
$usuario_id = filter_input(INPUT_GET, "usuario_id", FILTER_SANITIZE_NUMBER_INT);

// Obtener la información del usuario de la base de datos
$sql = "SELECT * FROM usuarios WHERE id = :usuario_id";
$stmt = $connection->prepare($sql);
$stmt->execute([':usuario_id' => $usuario_id]);

// Verificar si se encontró el usuario
if ($stmt->rowCount() > 0) {
  $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

  // Consulta para obtener el número de publicaciones
  $sql_publicaciones = "SELECT COUNT(*) AS num_publicaciones FROM fotos WHERE usuario_subio_id = :usuario_id AND eliminado = 0";
  $stmt_publicaciones = $connection->prepare($sql_publicaciones);
  $stmt_publicaciones->execute([':usuario_id' => $usuario_id]);
  $resultado_publicaciones = $stmt_publicaciones->fetch(PDO::FETCH_ASSOC);
  $num_publicaciones = $resultado_publicaciones['num_publicaciones'];

  // Consulta para obtener el número de seguidores
  $sql_seguidores = "SELECT COUNT(*) AS num_seguidores FROM seguidores WHERE usuario_siguiendo_id = :usuario_id AND eliminado = 0";
  $stmt_seguidores = $connection->prepare($sql_seguidores);
  $stmt_seguidores->execute([':usuario_id' => $usuario_id]);
  $resultado_seguidores = $stmt_seguidores->fetch(PDO::FETCH_ASSOC);
  $num_seguidores = $resultado_seguidores['num_seguidores'];

  // Consulta para obtener el número de seguidos
  $sql_seguidos = "SELECT COUNT(*) AS num_seguidos FROM seguidores WHERE usuario_seguidor_id = :usuario_id AND eliminado = 0";
  $stmt_seguidos = $connection->prepare($sql_seguidos);
  $stmt_seguidos->execute([':usuario_id' => $usuario_id]);
  $resultado_seguidos = $stmt_seguidos->fetch(PDO::FETCH_ASSOC);
  $num_seguidos = $resultado_seguidos['num_seguidos'];

  // Consulta para obtener las publicaciones del usuario
  $sql_publicaciones_usuario = "SELECT * FROM fotos WHERE usuario_subio_id = :usuario_id AND eliminado = 0";
  $stmt_publicaciones_usuario = $connection->prepare($sql_publicaciones_usuario);
  $stmt_publicaciones_usuario->execute([':usuario_id' => $usuario_id]);
  $publicaciones_usuario = $stmt_publicaciones_usuario->fetchAll(PDO::FETCH_ASSOC);

  // Obtener la ruta de la imagen del usuario
  $imagen_usuario = "../fotos_perfil/" . $usuario['foto_perfil'];
}

// Función para verificar si el usuario actual sigue a otro usuario
function esta_siguiendo_usuario($usuario_id, $usuario_seguir_id)
{
  global $connection;
  $sql = "SELECT COUNT(*) AS num_filas FROM seguidores WHERE usuario_seguidor_id = :usuario_id AND usuario_siguiendo_id = :usuario_seguir_id";
  $stmt = $connection->prepare($sql);
  $stmt->execute([':usuario_id' => $usuario_id, ':usuario_seguir_id' => $usuario_seguir_id]);
  $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
  return $resultado['num_filas'] > 0;
}

// Manejar la acción de seguir o dejar de seguir a un usuario
if (isset($_POST['toggle_seguir'])) {
  $usuario_seguir_id = $usuario['id'];
  if (esta_siguiendo_usuario($usuario_id, $usuario_seguir_id)) {
    // Si ya está siguiendo al usuario, dejar de seguirlo
    $sql_dejar_seguir_usuario = "DELETE FROM seguidores WHERE usuario_seguidor_id = :usuario_id AND usuario_siguiendo_id = :usuario_seguir_id";
    $stmt_dejar_seguir_usuario = $connection->prepare($sql_dejar_seguir_usuario);
    $stmt_dejar_seguir_usuario->execute([':usuario_id' => $usuario_id, ':usuario_seguir_id' => $usuario_seguir_id]);
    // Decrementar el número de seguidores del usuario seguido
    $sql_actualizar_seguidores = "UPDATE usuarios SET num_seguidores = num_seguidores - 1 WHERE id = :usuario_seguir_id";
    $stmt_actualizar_seguidores = $connection->prepare($sql_actualizar_seguidores);
  } else {
    // Si no está siguiendo al usuario, seguirlo
    $sql_seguir_usuario = "INSERT INTO seguidores (usuario_seguidor_id, usuario_siguiendo_id, fecha_hora) VALUES (:usuario_id, :usuario_seguir_id, NOW())";
    $stmt_seguir_usuario = $connection->prepare($sql_seguir_usuario);
    $stmt_seguir_usuario->execute([':usuario_id' => $usuario_id, ':usuario_seguir_id' => $usuario_seguir_id]);
    // Incrementar el número de seguidores del usuario seguido
    $sql_actualizar_seguidores = "UPDATE usuarios SET num_seguidores = num_seguidores + 1 WHERE id = :usuario_seguir_id";
    $stmt_actualizar_seguidores = $connection->prepare($sql_actualizar_seguidores);
  }
  // Redireccionar a la misma página para evitar envíos de formulario duplicados
  header("Location: {$_SERVER['PHP_SELF']}?usuario_id=$usuario_id");
  exit;
}
// Manejar la acción de eliminar una publicación
if (isset($_POST['eliminar_publicacion'])) {
  // Obtener el ID de la publicación a eliminar
  $publicacion_id = $_POST['publicacion_id'];

  // Consulta para eliminar la publicación
  $sql_eliminar_publicacion = "UPDATE fotos SET eliminado = 1 WHERE id = :publicacion_id";
  $stmt_eliminar_publicacion = $connection->prepare($sql_eliminar_publicacion);
  $stmt_eliminar_publicacion->execute([':publicacion_id' => $publicacion_id]);

  // Redireccionar a la misma página para evitar envíos de formulario duplicados
  header("Location: {$_SERVER['PHP_SELF']}?usuario_id=$usuario_id");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil buscado</title>
  <link rel="icon" href="../img/Logo.png" type="image/x-icon">
  <link rel="stylesheet" href="../css/stylesPerfil.css">
</head>

<body>
  <div class="panel">
    <div class="opcion" id="lydch"><a href="#"><img src="../img/Logo.png" alt="LYDCH" style="width: 60px; height: 60px;"><span style="font-size: larger; font-weight: bold;">LYDCH</span></a></div>
    <div class="espacio"></div>
    <div class="opcion"><a href="./inicio.php"><img src="../img/Inicio.png" alt="Inicio"><span>Inicio</span></a></div>
    <div class="opcion"><a href="./buscador.html"><img src="../img/Buscador.png" alt="Buscador"><span>Buscador</span></a></div>
    <div class="opcion"><a href="./crear.php"><img src="../img/Crear.png" alt="Crear"><span>Crear</span></a></div>
    <div class="opcion" id="perfil"><a href="./perfil.php"><img src="../img/usuario.png" alt="Perfil"><span>Perfil</span></a></div>
    <div class="opcion"><a href="../php/logout.php"><img src="../img/Salir.png" alt="Salir"><span>Salir</span></a></div>
  </div>

  <div class="contenedor">
    <div class="perfil">
      <div class="foto-usuario">
        <?php if (isset($imagen_usuario) && file_exists($imagen_usuario)) : ?>
          <img src="<?php echo $imagen_usuario; ?>" alt="Foto de Usuario" class="foto-usuario">
        <?php else : ?>
          <img src="../fotos_perfil/image.png" alt="Foto de Usuario" class="foto-usuario">
        <?php endif; ?>
      </div>

      <div class="info-usuario">
        <div class="nombre-usuario"><?php echo $usuario['username']; ?></div>
        <form method="post">
          <button class="editar-perfil" type="submit" name="toggle_seguir">
            <?php if (esta_siguiendo_usuario($usuario_id, $usuario['id'])) : ?>
              Dejar de seguir
            <?php else : ?>
              Seguir
            <?php endif; ?>
          </button>
        </form>
      </div>

      <div class="datos-usuario">
        <span class="informacion-detallada"><?php echo $num_publicaciones; ?> publicaciones</span>
        <span class="informacion-detallada"><?php echo $num_seguidores; ?> seguidores</span>
        <span class="informacion-detallada"><?php echo $num_seguidos; ?> seguidos</span>
      </div>
      <!-- Mostrar la galería solo si hay publicaciones -->
      <?php if (isset($publicaciones_usuario) && !empty($publicaciones_usuario)) : ?>
        <div class="galeria">
          <?php foreach ($publicaciones_usuario as $publicacion) : ?>
            <div class="publicacion">
              <img src="../fotos/<?php echo $publicacion['secure_id'] . "." . $publicacion['extension']; ?>" alt="<?php echo $publicacion['descripcion']; ?>">
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

</body>

</html>