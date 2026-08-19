<?php
require __DIR__ . '/lib.php';
iniciar_sesion();

// Si ya inició sesión, va directo al panel
if (esta_autenticado()) redir('panel.php');

$modo = hay_credenciales() ? 'login' : 'configurar';
$error = '';
$aviso = '';

// Pequeña defensa contra fuerza bruta
if (!isset($_SESSION['intentos'])) $_SESSION['intentos'] = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();

    if ($modo === 'configurar') {
        // Creación de la cuenta compartida (primera vez)
        $usuario = trim($_POST['usuario'] ?? '');
        $clave   = $_POST['clave'] ?? '';
        $clave2  = $_POST['clave2'] ?? '';
        $nombre  = trim($_POST['nombre'] ?? '') ?: 'Personal CETPRO';

        if (strlen($usuario) < 3) {
            $error = 'El usuario debe tener al menos 3 caracteres.';
        } elseif (strlen($clave) < 8) {
            $error = 'La contraseña debe tener al menos 8 caracteres.';
        } elseif ($clave !== $clave2) {
            $error = 'Las dos contraseñas no coinciden.';
        } elseif (!crear_credenciales($usuario, $clave, $nombre)) {
            $error = 'No se pudo guardar la cuenta. Revisa los permisos de la carpeta "datos".';
        } else {
            $_SESSION['auth'] = true;
            redir('panel.php');
        }
    } else {
        // Inicio de sesión normal
        if ($_SESSION['intentos'] >= 6) {
            $error = 'Demasiados intentos fallidos. Espera un momento y recarga la página.';
        } else {
            $usuario = trim($_POST['usuario'] ?? '');
            $clave   = $_POST['clave'] ?? '';
            if (verificar_credenciales($usuario, $clave)) {
                session_regenerate_id(true);
                $_SESSION['auth'] = true;
                $_SESSION['intentos'] = 0;
                redir('panel.php');
            } else {
                $_SESSION['intentos']++;
                usleep(600000); // 0.6 s de retardo ante fallo
                $error = 'Usuario o contraseña incorrectos.';
            }
        }
    }
}
$token = csrf_token();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $modo === 'configurar' ? 'Configurar acceso' : 'Acceso al panel' ?> — CETPRO César Vallejo</title>
<meta name="robots" content="noindex,nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="panel.css">
</head>
<body class="pantalla-acceso">
  <main class="tarjeta-acceso">
    <div class="logo-acceso">
      <div class="logo-cv">CV</div>
      <div>
        <strong>CETPRO César Vallejo</strong>
        <small>Panel de noticias</small>
      </div>
    </div>

    <?php if ($modo === 'configurar'): ?>
      <h1>Configura el acceso</h1>
      <p class="intro-acceso">Es la primera vez que entras. Crea el usuario y la
         contraseña que usará el personal para publicar noticias. Guárdalos en un
         lugar seguro.</p>

      <?php if ($error): ?><div class="alerta-error"><?= limpiar($error) ?></div><?php endif; ?>

      <form method="post" autocomplete="off" class="form-acceso">
        <input type="hidden" name="csrf" value="<?= $token ?>">
        <label>Nombre para mostrar (opcional)
          <input type="text" name="nombre" placeholder="Personal CETPRO" maxlength="60">
        </label>
        <label>Usuario
          <input type="text" name="usuario" required minlength="3" maxlength="40" autofocus>
        </label>
        <label>Contraseña (mínimo 8 caracteres)
          <input type="password" name="clave" required minlength="8" maxlength="100">
        </label>
        <label>Repite la contraseña
          <input type="password" name="clave2" required minlength="8" maxlength="100">
        </label>
        <button type="submit" class="btn-panel">Crear cuenta y entrar</button>
      </form>

    <?php else: ?>
      <h1>Acceso al panel</h1>
      <p class="intro-acceso">Ingresa con el usuario y la contraseña del personal.</p>

      <?php if ($error): ?><div class="alerta-error"><?= limpiar($error) ?></div><?php endif; ?>

      <form method="post" autocomplete="off" class="form-acceso">
        <input type="hidden" name="csrf" value="<?= $token ?>">
        <label>Usuario
          <input type="text" name="usuario" required autofocus>
        </label>
        <label>Contraseña
          <input type="password" name="clave" required>
        </label>
        <button type="submit" class="btn-panel">Entrar</button>
      </form>
    <?php endif; ?>

    <a class="volver-sitio" href="../noticias.php">← Volver a las noticias</a>
  </main>
</body>
</html>
