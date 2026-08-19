<?php
/* Crea o actualiza una noticia */
require __DIR__ . '/lib.php';
requerir_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redir('panel.php');
verificar_csrf();

$id       = trim($_POST['id'] ?? '');
$titulo   = trim($_POST['titulo'] ?? '');
$fecha    = trim($_POST['fecha'] ?? '');
$categoria= trim($_POST['categoria'] ?? '');
$resumen  = trim($_POST['resumen'] ?? '');
$cuerpo   = trim($_POST['cuerpo'] ?? '');

// Validación mínima
if ($titulo === '' || $cuerpo === '') {
    exit('Faltan datos obligatorios (título y contenido). Vuelve atrás e inténtalo de nuevo.');
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    $fecha = date('Y-m-d');
}

// Imagen subida (opcional)
$sub = procesar_imagen('imagen');
if (!$sub['ok']) {
    exit($sub['error'] . ' Vuelve atrás e inténtalo de nuevo.');
}

$lista = leer_json(ARCH_NOTICIAS, []);
$ahora = date('c');

if ($id !== '') {
    // ---- Actualizar noticia existente ----
    $encontrada = false;
    foreach ($lista as &$n) {
        if (($n['id'] ?? '') === $id) {
            $encontrada = true;
            $n['titulo']    = $titulo;
            $n['fecha']     = $fecha;
            $n['categoria'] = $categoria;
            $n['resumen']   = $resumen;
            $n['cuerpo']    = $cuerpo;
            $n['actualizado'] = $ahora;

            if (!empty($_POST['quitar_imagen'])) {
                borrar_imagen($n['imagen'] ?? null);
                $n['imagen'] = null;
            }
            if ($sub['archivo']) {
                borrar_imagen($n['imagen'] ?? null); // reemplaza la anterior
                $n['imagen'] = $sub['archivo'];
            }
            break;
        }
    }
    unset($n);
    if (!$encontrada) exit('No se encontró la noticia que intentas editar.');
    guardar_noticias_lista($lista);
    redir('panel.php?ok=actualizada');
} else {
    // ---- Crear noticia nueva ----
    $nueva = [
        'id'          => nuevo_id(),
        'titulo'      => $titulo,
        'fecha'       => $fecha,
        'categoria'   => $categoria,
        'resumen'     => $resumen,
        'cuerpo'      => $cuerpo,
        'imagen'      => $sub['archivo'],
        'autor'       => nombre_cuenta(),
        'creado'      => $ahora,
        'actualizado' => $ahora,
    ];
    $lista[] = $nueva;
    guardar_noticias_lista($lista);
    redir('panel.php?ok=creada');
}
