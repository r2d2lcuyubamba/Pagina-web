<?php
/* ============================================================
   Contador de visitas propio — CETPRO César Vallejo
   Guarda la cuenta en un archivo de texto en tu propio hosting.
   No depende de ningún servicio externo.
   ============================================================ */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$archivo = __DIR__ . '/visitas.txt';

// Si no existe, lo crea empezando en 0
if (!file_exists($archivo)) {
    file_put_contents($archivo, '0');
}

// Lee, suma 1 y guarda (con bloqueo para evitar errores si entran varios a la vez)
$fp = fopen($archivo, 'c+');
if (flock($fp, LOCK_EX)) {
    $valor = (int) trim(fgets($fp));
    $valor++;
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, (string) $valor);
    flock($fp, LOCK_UN);
} else {
    $valor = (int) trim(file_get_contents($archivo));
}
fclose($fp);

echo json_encode(['value' => $valor]);
