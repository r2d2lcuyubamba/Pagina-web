<?php
require __DIR__ . '/lib.php';
requerir_auth();

$noticias = cargar_noticias();

// ¿Estamos editando o creando?
$editando = null;
if (isset($_GET['editar'])) {
    $editando = buscar_noticia($_GET['editar']);
}
$es_nueva = isset($_GET['nueva']) || (isset($_GET['editar']) && $editando);
$token = csrf_token();

// Mensajes tras redirección
$mensajes = [
    'creada'      => 'La noticia se publicó correctamente.',
    'actualizada' => 'Los cambios se guardaron correctamente.',
    'borrada'     => 'La noticia se eliminó.',
];
$ok = $mensajes[$_GET['ok'] ?? ''] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel de noticias — CETPRO César Vallejo</title>
<meta name="robots" content="noindex,nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="panel.css">
</head>
<body class="panel">

<header class="barra-panel">
  <div class="barra-inner">
    <div class="logo-acceso compacto">
      <div class="logo-cv">CV</div>
      <div>
        <strong>Panel de noticias</strong>
        <small>Sesión: <?= limpiar(nombre_cuenta()) ?></small>
      </div>
    </div>
    <nav class="acciones-barra">
      <a href="../noticias.php" target="_blank" rel="noopener">Ver la página pública ↗</a>
      <a href="cambiar_clave.php">Cambiar contraseña</a>
      <a href="salir.php" class="salir">Cerrar sesión</a>
    </nav>
  </div>
</header>

<main class="contenido-panel">

  <?php if ($ok): ?><div class="alerta-ok"><?= limpiar($ok) ?></div><?php endif; ?>

  <!-- ============ FORMULARIO NUEVA / EDITAR ============ -->
  <section class="bloque-panel">
    <h2><?= $editando ? 'Editar noticia' : 'Publicar una noticia' ?></h2>
    <form method="post" action="guardar.php" enctype="multipart/form-data" class="form-noticia">
      <input type="hidden" name="csrf" value="<?= $token ?>">
      <?php if ($editando): ?>
        <input type="hidden" name="id" value="<?= limpiar($editando['id']) ?>">
      <?php endif; ?>

      <label>Título *
        <input type="text" name="titulo" required maxlength="140"
               value="<?= $editando ? limpiar($editando['titulo']) : '' ?>">
      </label>

      <div class="fila-2">
        <label>Fecha *
          <input type="date" name="fecha" required
                 value="<?= $editando ? limpiar($editando['fecha']) : date('Y-m-d') ?>">
        </label>
        <label>Categoría (opcional)
          <input type="text" name="categoria" maxlength="40" placeholder="Ej.: Matrícula, Evento, Aviso"
                 value="<?= $editando ? limpiar($editando['categoria'] ?? '') : '' ?>">
        </label>
      </div>

      <label>Resumen breve (opcional, se muestra en la lista)
        <textarea name="resumen" rows="2" maxlength="300"><?= $editando ? limpiar($editando['resumen'] ?? '') : '' ?></textarea>
      </label>

      <label>Contenido *
        <textarea name="cuerpo" rows="10" required placeholder="Escribe aquí la noticia. Deja una línea en blanco para separar párrafos."><?= $editando ? limpiar($editando['cuerpo'] ?? '') : '' ?></textarea>
      </label>

      <label>Imagen de portada (opcional · JPG, PNG, WEBP o GIF · máx. 5 MB)
        <input type="file" name="imagen" accept="image/*">
      </label>

      <?php if ($editando && !empty($editando['imagen'])): ?>
        <div class="imagen-actual">
          <img src="../<?= URL_IMAGENES_PUBLICA ?>/<?= limpiar($editando['imagen']) ?>" alt="Imagen actual">
          <label class="quitar-img">
            <input type="checkbox" name="quitar_imagen" value="1"> Quitar la imagen actual
          </label>
        </div>
      <?php endif; ?>

      <div class="botones-form">
        <button type="submit" class="btn-panel"><?= $editando ? 'Guardar cambios' : 'Publicar noticia' ?></button>
        <?php if ($editando): ?>
          <a href="panel.php" class="btn-panel gris">Cancelar edición</a>
        <?php endif; ?>
      </div>
    </form>
  </section>

  <!-- ============ LISTA DE NOTICIAS ============ -->
  <section class="bloque-panel">
    <h2>Noticias publicadas (<?= count($noticias) ?>)</h2>

    <?php if (empty($noticias)): ?>
      <p class="vacio">Todavía no hay noticias. Publica la primera con el formulario de arriba.</p>
    <?php else: ?>
      <ul class="lista-admin">
        <?php foreach ($noticias as $n): ?>
          <li>
            <div class="mini-portada">
              <?php if (!empty($n['imagen'])): ?>
                <img src="../<?= URL_IMAGENES_PUBLICA ?>/<?= limpiar($n['imagen']) ?>" alt="">
              <?php else: ?>
                <span class="sin-img">Sin foto</span>
              <?php endif; ?>
            </div>
            <div class="info-admin">
              <strong><?= limpiar($n['titulo']) ?></strong>
              <small><?= fecha_legible($n['fecha'] ?? '') ?>
                <?php if (!empty($n['categoria'])): ?> · <?= limpiar($n['categoria']) ?><?php endif; ?>
              </small>
            </div>
            <div class="acciones-admin">
              <a href="../noticia.php?id=<?= urlencode($n['id']) ?>" target="_blank" rel="noopener">Ver</a>
              <a href="panel.php?editar=<?= urlencode($n['id']) ?>">Editar</a>
              <form method="post" action="borrar.php" onsubmit="return confirm('¿Eliminar esta noticia? Esta acción no se puede deshacer.');">
                <input type="hidden" name="csrf" value="<?= $token ?>">
                <input type="hidden" name="id" value="<?= limpiar($n['id']) ?>">
                <button type="submit" class="enlace-borrar">Eliminar</button>
              </form>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>

</main>
</body>
</html>
