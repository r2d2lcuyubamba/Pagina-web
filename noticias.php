<?php
require __DIR__ . '/incluir.php';
require __DIR__ . '/panel/lib.php';
$noticias = cargar_noticias();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Noticias — CETPRO César Vallejo | Pucallpa</title>
<meta name="description" content="Noticias, avisos y novedades del CETPRO César Vallejo de Pucallpa, Ucayali: matrículas, eventos, logros y comunicados institucionales.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="estilos.css?v=4">
</head>
<body>

<?php menu_sitio('noticias'); ?>

<!-- ==================== ENCABEZADO ==================== -->
<section class="hero-galeria">
  <div class="contenedor">
    <span class="etiqueta">Noticias</span>
    <h1>Novedades y avisos</h1>
    <p>Mantente al día con las últimas noticias, comunicados y eventos
       del CETPRO César Vallejo de Pucallpa.</p>
  </div>
</section>

<!-- ==================== LISTA DE NOTICIAS ==================== -->
<section class="seccion">
  <div class="contenedor">

    <?php if (empty($noticias)): ?>
      <div class="noticias-vacio revelar">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8V6z"/></svg>
        <h2>Aún no hay noticias publicadas</h2>
        <p>Muy pronto compartiremos aquí las novedades de la institución.</p>
      </div>
    <?php else: ?>
      <div class="grilla-noticias">
        <?php foreach ($noticias as $n): ?>
          <?php
            $url = 'noticia.php?id=' . urlencode($n['id']);
            $tieneImg = !empty($n['imagen']);
            // Resumen: usa el campo resumen; si no hay, recorta el cuerpo
            $resumen = trim($n['resumen'] ?? '');
            if ($resumen === '') {
                $plano = trim(preg_replace('/\s+/', ' ', $n['cuerpo'] ?? ''));
                $resumen = mb_strimwidth($plano, 0, 160, '…', 'UTF-8');
            }
          ?>
          <article class="noticia-card revelar">
            <a class="noticia-foto" href="<?= $url ?>">
              <?php if ($tieneImg): ?>
                <img src="<?= URL_IMAGENES_PUBLICA ?>/<?= limpiar($n['imagen']) ?>" alt="<?= limpiar($n['titulo']) ?>" loading="lazy">
              <?php else: ?>
                <span class="noticia-sinfoto">
                  <svg viewBox="0 0 24 24" fill="none" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h4l2 3h7a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2z"/></svg>
                </span>
              <?php endif; ?>
              <?php if (!empty($n['categoria'])): ?>
                <span class="noticia-cat"><?= limpiar($n['categoria']) ?></span>
              <?php endif; ?>
            </a>
            <div class="noticia-cuerpo">
              <span class="noticia-fecha"><?= fecha_legible($n['fecha'] ?? '') ?></span>
              <h3><a href="<?= $url ?>"><?= limpiar($n['titulo']) ?></a></h3>
              <p><?= limpiar($resumen) ?></p>
              <a class="enlace" href="<?= $url ?>">Leer más</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php pie_sitio(); ?>

<script>
/* Aparición al hacer scroll */
const obs = new IntersectionObserver((es) => {
  es.forEach(e => { if (e.isIntersecting){ e.target.classList.add('visible'); obs.unobserve(e.target); } });
}, { threshold: .1 });
document.querySelectorAll('.revelar').forEach(el => obs.observe(el));

/* Contador de visitas propio */
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
