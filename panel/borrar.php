<?php
/* Elimina una noticia (y su imagen si tiene) */
require __DIR__ . '/lib.php';
requerir_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redir('panel.php');
verificar_csrf();

$id = trim($_POST['id'] ?? '');
if ($id === '') redir('panel.php');

$lista = leer_json(ARCH_NOTICIAS, []);
$nueva = [];
foreach ($lista as $n) {
    if (($n['id'] ?? '') === $id) {
        borrar_imagen($n['imagen'] ?? null);
        continue; // se omite = se borra
    }
    $nueva[] = $n;
}
guardar_noticias_lista($nueva);
redir('panel.php?ok=borrada');
