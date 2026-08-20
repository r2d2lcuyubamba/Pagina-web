<?php
/* ============================================================
   NÚCLEO DE LA SECCIÓN DE NOTICIAS — CETPRO César Vallejo
   Funciones compartidas: sesión, seguridad, almacenamiento.
   Sin base de datos: todo se guarda en archivos JSON, igual
   que el contador de visitas guarda en visitas.txt.
   ============================================================ */

// --- Rutas de datos -----------------------------------------
define('DIR_DATOS',    __DIR__ . '/datos');
define('ARCH_USUARIOS', DIR_DATOS . '/usuarios.json');
define('ARCH_NOTICIAS', DIR_DATOS . '/noticias.json');
// Las imágenes de las noticias viven en /img/noticias (accesible al público)
define('DIR_IMAGENES', dirname(__DIR__) . '/img/noticias');
define('URL_IMAGENES_PUBLICA', 'img/noticias');   // usada desde la raíz del sitio

// --- Sesión segura ------------------------------------------
function iniciar_sesion() {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => $https,
    ]);
    session_name('CETPRO_PANEL');
    session_start();
}

// --- Token anti-CSRF ----------------------------------------
function csrf_token() {
    iniciar_sesion();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function verificar_csrf() {
    iniciar_sesion();
    $enviado = $_POST['csrf'] ?? '';
    if (!$enviado || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $enviado)) {
        http_response_code(400);
        exit('Solicitud no válida (token de seguridad incorrecto). Vuelve atrás y recarga la página.');
    }
}

// --- Lectura/escritura de JSON con bloqueo ------------------
function leer_json($archivo, $por_defecto) {
    if (!file_exists($archivo)) return $por_defecto;
    $txt = file_get_contents($archivo);
    if ($txt === false || trim($txt) === '') return $por_defecto;
    $dato = json_decode($txt, true);
    return is_array($dato) ? $dato : $por_defecto;
}
function escribir_json($archivo, $dato) {
    if (!is_dir(DIR_DATOS)) { @mkdir(DIR_DATOS, 0775, true); }
    $json = json_encode($dato, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return file_put_contents($archivo, $json, LOCK_EX) !== false;
}

// --- Credenciales (cuenta compartida) -----------------------
function hay_credenciales() {
    $u = leer_json(ARCH_USUARIOS, []);
    return !empty($u['hash']) && !empty($u['usuario']);
}
function crear_credenciales($usuario, $clave, $nombre) {
    $dato = [
        'usuario' => $usuario,
        'hash'    => password_hash($clave, PASSWORD_DEFAULT),
        'nombre'  => $nombre,
    ];
    return escribir_json(ARCH_USUARIOS, $dato);
}
function verificar_credenciales($usuario, $clave) {
    $u = leer_json(ARCH_USUARIOS, []);
    if (empty($u['hash']) || empty($u['usuario'])) return false;
    if (!hash_equals($u['usuario'], $usuario)) return false;
    return password_verify($clave, $u['hash']);
}
function nombre_cuenta() {
    $u = leer_json(ARCH_USUARIOS, []);
    return $u['nombre'] ?? 'Personal';
}

// --- Autenticación ------------------------------------------
function esta_autenticado() {
    iniciar_sesion();
    return !empty($_SESSION['auth']) && $_SESSION['auth'] === true;
}
function requerir_auth() {
    if (!esta_autenticado()) {
        redir('index.php');
    }
}
function redir($url) {
    header('Location: ' . $url);
    exit;
}
// Página de error con el mismo estilo del panel; "volver" conserva lo escrito.
function pagina_error($mensaje) {
    http_response_code(400);
    $m = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
       . '<meta name="robots" content="noindex,nofollow"><title>Aviso</title>'
       . '<link rel="stylesheet" href="panel.css"></head>'
       . '<body class="pantalla-acceso"><main class="tarjeta-acceso">'
       . '<div class="logo-acceso"><div class="logo-cv">CV</div><div>'
       . '<strong>Panel de noticias</strong><small>No se pudo guardar</small></div></div>'
       . '<div class="alerta-error">' . $m . '</div>'
       . '<a class="btn-panel" href="#" onclick="history.back();return false;">← Volver e intentar de nuevo</a>'
       . '</main></body></html>';
    exit;
}

// --- Noticias -----------------------------------------------
function cargar_noticias() {
    $n = leer_json(ARCH_NOTICIAS, []);
    // Orden: más recientes primero (por fecha de publicación, luego por creación)
    usort($n, function ($a, $b) {
        $fa = ($a['fecha'] ?? '') . ($a['creado'] ?? '');
        $fb = ($b['fecha'] ?? '') . ($b['creado'] ?? '');
        return strcmp($fb, $fa);
    });
    return $n;
}
function buscar_noticia($id) {
    foreach (leer_json(ARCH_NOTICIAS, []) as $n) {
        if (($n['id'] ?? '') === $id) return $n;
    }
    return null;
}
function guardar_noticias_lista($lista) {
    return escribir_json(ARCH_NOTICIAS, $lista);
}
function nuevo_id() {
    return date('Ymd') . '-' . bin2hex(random_bytes(4));
}

// --- Utilidades de texto ------------------------------------
function limpiar($txt) {
    return htmlspecialchars(trim((string)$txt), ENT_QUOTES, 'UTF-8');
}
// Convierte texto plano en párrafos HTML seguros
function cuerpo_a_html($txt) {
    $txt = trim((string)$txt);
    $bloques = preg_split('/\n\s*\n/', $txt);   // separa por líneas en blanco
    $html = '';
    foreach ($bloques as $b) {
        $b = trim($b);
        if ($b === '') continue;
        $b = htmlspecialchars($b, ENT_QUOTES, 'UTF-8');
        $b = nl2br($b);                          // saltos de línea simples
        $html .= '<p>' . $b . '</p>';
    }
    return $html;
}
// Fecha en formato legible en español (ej. 19 de agosto de 2026)
function fecha_legible($iso) {
    if (!$iso) return '';
    $ts = strtotime($iso);
    if ($ts === false) return limpiar($iso);
    $meses = [1=>'enero','febrero','marzo','abril','mayo','junio','julio',
              'agosto','septiembre','octubre','noviembre','diciembre'];
    return (int)date('j', $ts) . ' de ' . $meses[(int)date('n', $ts)] . ' de ' . date('Y', $ts);
}

// --- Subida de imágenes -------------------------------------
function procesar_imagen($campo) {
    if (empty($_FILES[$campo]) || $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'archivo' => null];   // sin imagen: es opcional
    }
    $f = $_FILES[$campo];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        $limite = ini_get('upload_max_filesize');
        $mapa = [
            UPLOAD_ERR_INI_SIZE   => "La imagen pesa más de lo que permite el servidor (límite actual: {$limite}). Redúcela (por ejemplo en tinypng.com) o súbela más liviana.",
            UPLOAD_ERR_FORM_SIZE  => 'La imagen supera el tamaño permitido por el formulario.',
            UPLOAD_ERR_PARTIAL    => 'La imagen se subió solo a medias (posible corte de conexión). Inténtalo de nuevo.',
            UPLOAD_ERR_NO_TMP_DIR => 'El servidor no tiene carpeta temporal para subidas. Avisa a tu proveedor de hosting.',
            UPLOAD_ERR_CANT_WRITE => 'El servidor no pudo escribir la imagen en el disco (revisa permisos o espacio).',
            UPLOAD_ERR_EXTENSION  => 'Una extensión de PHP bloqueó la subida de la imagen.',
        ];
        return ['ok' => false, 'error' => $mapa[$f['error']] ?? 'Hubo un problema al subir la imagen.'];
    }
    if ($f['size'] > 8 * 1024 * 1024) {
        return ['ok' => false, 'error' => 'La imagen supera el límite de 8 MB. Redúcela e inténtalo de nuevo.'];
    }
    $info = @getimagesize($f['tmp_name']);
    if ($info === false) {
        return ['ok' => false, 'error' => 'El archivo no es una imagen válida.'];
    }
    $ext_por_tipo = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_WEBP => 'webp',
        IMAGETYPE_GIF  => 'gif',
    ];
    if (!isset($ext_por_tipo[$info[2]])) {
        return ['ok' => false, 'error' => 'Formato no permitido. Usa JPG, PNG, WEBP o GIF.'];
    }
    if (!is_dir(DIR_IMAGENES)) { @mkdir(DIR_IMAGENES, 0775, true); }
    $nombre = 'noticia-' . date('Ymd') . '-' . bin2hex(random_bytes(5)) . '.' . $ext_por_tipo[$info[2]];
    $destino = DIR_IMAGENES . '/' . $nombre;
    if (!move_uploaded_file($f['tmp_name'], $destino)) {
        return ['ok' => false, 'error' => 'No se pudo guardar la imagen en el servidor.'];
    }
    return ['ok' => true, 'archivo' => $nombre];
}
function borrar_imagen($nombre) {
    if (!$nombre) return;
    $ruta = DIR_IMAGENES . '/' . basename($nombre);
    if (is_file($ruta)) @unlink($ruta);
}
