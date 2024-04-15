<?php
require "../php/sesion_requerida.php";
require "../php/connection.php";

// Obtener el ID del usuario que inició sesión
$usuario_id = $usuarioID;

// Obtener la información del usuario de la base de datos
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $connection->prepare($sql);
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Consulta para obtener el número de publicaciones
$sql_publicaciones = "SELECT COUNT(*) AS num_publicaciones FROM fotos WHERE usuario_subio_id = ? AND eliminado = 0";
$stmt_publicaciones = $connection->prepare($sql_publicaciones);
$stmt_publicaciones->execute([$usuario_id]);
$resultado_publicaciones = $stmt_publicaciones->fetch(PDO::FETCH_ASSOC);
$num_publicaciones = $resultado_publicaciones['num_publicaciones'];

// Consulta para obtener el número de seguidores
$sql_seguidores = "SELECT COUNT(*) AS num_seguidores FROM seguidores WHERE usuario_siguiendo_id = ? AND eliminado = 0";
$stmt_seguidores = $connection->prepare($sql_seguidores);
$stmt_seguidores->execute([$usuario_id]);
$resultado_seguidores = $stmt_seguidores->fetch(PDO::FETCH_ASSOC);
$num_seguidores = $resultado_seguidores['num_seguidores'];

// Consulta para obtener el número de seguidos
$sql_seguidos = "SELECT COUNT(*) AS num_seguidos FROM seguidores WHERE usuario_seguidor_id = ? AND eliminado = 0";
$stmt_seguidos = $connection->prepare($sql_seguidos);
$stmt_seguidos->execute([$usuario_id]);
$resultado_seguidos = $stmt_seguidos->fetch(PDO::FETCH_ASSOC);
$num_seguidos = $resultado_seguidos['num_seguidos'];

// Consulta para obtener las publicaciones del usuario
$sql_publicaciones_usuario = "SELECT * FROM fotos WHERE usuario_subio_id = ? AND eliminado = 0";
$stmt_publicaciones_usuario = $connection->prepare($sql_publicaciones_usuario);
$stmt_publicaciones_usuario->execute([$usuario_id]);
$publicaciones_usuario = $stmt_publicaciones_usuario->fetchAll(PDO::FETCH_ASSOC);

// Obtener la ruta de la imagen del usuario
$imagen_usuario = "../fotos_perfil/" . $usuario["foto_perfil"];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mi perfil</title>
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
        <button class="editar-perfil"><a href="./editarperfil.php">Editar</a></button>
      </div>

      <div class="datos-usuario">
        <span class="informacion-detallada"><?php echo $num_publicaciones; ?> publicaciones</span>
        <span class="informacion-detallada"><?php echo $num_seguidores; ?> seguidores</span>
        <span class="informacion-detallada"><?php echo $num_seguidos; ?> seguidos</span>
      </div>


      <div class="galeria">
        <?php if (isset($publicaciones_usuario) && !empty($publicaciones_usuario)) : ?>
          <?php foreach ($publicaciones_usuario as $publicacion) : ?>
            <div class="publicacion">
            <img src="../fotos/<?php echo $publicacion['secure_id'] . "." . $publicacion['extension']; ?>" alt="<?php echo $publicacion['descripcion']; ?>">
              <form method="post" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta publicación?');" action="../php/borrar_archivo.php">
                <input type="hidden" name="publicacion_id" value="<?php echo $publicacion['id']; ?>" id="publicacion_id">
                <button class="eliminar-publicacion" type="submit" name="eliminar_publicacion">Eliminar</button>
              </form>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>

</html>