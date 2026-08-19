<?php
require __DIR__ . '/incluir.php';
require __DIR__ . '/panel/lib.php';

$id = $_GET['id'] ?? '';
$n = buscar_noticia($id);

// Si no existe, se muestra un aviso amable
$titulo_pagina = $n ? $n['titulo'] . ' — Noticias' : 'Noticia no encontrada';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= limpiar($titulo_pagina) ?> | CETPRO César Vallejo</title>
<?php if ($n): ?>
<meta name="description" content="<?= limpiar(mb_strimwidth(trim($n['resumen'] ?: preg_replace('/\s+/', ' ', $n['cuerpo'])), 0, 160, '…', 'UTF-8')) ?>">
<?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="estilos.css?v=4">
</head>
<body>

<?php menu_sitio('noticias'); ?>

<?php if (!$n): ?>

  <section class="hero-galeria">
    <div class="contenedor">
      <span class="etiqueta">Noticias</span>
      <h1>Noticia no encontrada</h1>
      <p>Es posible que la noticia haya sido eliminada o que el enlace no sea correcto.</p>
    </div>
  </section>
  <section class="seccion">
    <div class="contenedor" style="text-align:center">
      <a class="btn btn-celeste" href="noticias.php">Ver todas las noticias</a>
    </div>
  </section>

<?php else: ?>

  <article class="articulo-noticia">
    <!-- Encabezado del artículo -->
    <header class="cabecera-noticia">
      <div class="contenedor">
        <a class="volver-noticias" href="noticias.php">← Todas las noticias</a>
        <?php if (!empty($n['categoria'])): ?>
          <span class="etiqueta-cat"><?= limpiar($n['categoria']) ?></span>
        <?php endif; ?>
        <h1><?= limpiar($n['titulo']) ?></h1>
        <p class="meta-noticia">
          <?= fecha_legible($n['fecha'] ?? '') ?>
          <?php if (!empty($n['autor'])): ?> · Publicado por <?= limpiar($n['autor']) ?><?php endif; ?>
        </p>
      </div>
    </header>

    <div class="contenedor cuerpo-articulo">
      <?php if (!empty($n['imagen'])): ?>
        <figure class="foto-articulo">
          <img src="<?= URL_IMAGENES_PUBLICA ?>/<?= limpiar($n['imagen']) ?>" alt="<?= limpiar($n['titulo']) ?>">
        </figure>
      <?php endif; ?>

      <div class="texto-articulo">
        <?= cuerpo_a_html($n['cuerpo'] ?? '') ?>
      </div>

      <div class="fin-articulo">
        <a class="btn btn-celeste" href="noticias.php">← Volver a noticias</a>
      </div>
    </div>
  </article>

<?php endif; ?>

<?php pie_sitio(); ?>

<script>
(function(){
  const caja = document.getElementById('contador');
  if(!caja) return;
  fetch('contador.php').then(r => r.json()).then(d => {
    if(d && typeof d.value === 'number'){
      caja.querySelector('.cifra').textContent = d.value.toLocaleString('es-PE') + ' visitas';
      caja.hidden = false;
    }
  }).catch(() => {});
})();
</script>

</body>
</html>
