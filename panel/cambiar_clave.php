<?php
/* Cambia la contraseña (y opcionalmente el usuario) de la cuenta compartida */
require __DIR__ . '/lib.php';
requerir_auth();

$error = '';
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();
    $u        = leer_json(ARCH_USUARIOS, []);
    $actual   = $_POST['actual'] ?? '';
    $usuario  = trim($_POST['usuario'] ?? $u['usuario']);
    $nueva    = $_POST['nueva'] ?? '';
    $nueva2   = $_POST['nueva2'] ?? '';

    if (!verificar_credenciales($u['usuario'], $actual)) {
        $error = 'La contraseña actual no es correcta.';
    } elseif (strlen($usuario) < 3) {
        $error = 'El usuario debe tener al menos 3 caracteres.';
    } elseif (strlen($nueva) < 8) {
        $error = 'La nueva contraseña debe tener al menos 8 caracteres.';
    } elseif ($nueva !== $nueva2) {
        $error = 'Las dos contraseñas nuevas no coinciden.';
    } elseif (!crear_credenciales($usuario, $nueva, $u['nombre'] ?? 'Personal CETPRO')) {
        $error = 'No se pudo guardar el cambio.';
    } else {
        $ok = 'La contraseña se actualizó correctamente.';
    }
}
$token = csrf_token();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cambiar contraseña — Panel CETPRO</title>
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
        <strong>Cambiar contraseña</strong>
        <small>Cuenta del personal</small>
      </div>
    </div>

    <?php if ($error): ?><div class="alerta-error"><?= limpiar($error) ?></div><?php endif; ?>
    <?php if ($ok): ?><div class="alerta-ok"><?= limpiar($ok) ?></div><?php endif; ?>

    <form method="post" autocomplete="off" class="form-acceso">
      <input type="hidden" name="csrf" value="<?= $token ?>">
      <label>Usuario
        <input type="text" name="usuario" value="<?= limpiar((leer_json(ARCH_USUARIOS, [])['usuario'] ?? '')) ?>" required minlength="3" maxlength="40">
      </label>
      <label>Contraseña actual
        <input type="password" name="actual" required>
      </label>
      <label>Nueva contraseña (mínimo 8 caracteres)
        <input type="password" name="nueva" required minlength="8" maxlength="100">
      </label>
      <label>Repite la nueva contraseña
        <input type="password" name="nueva2" required minlength="8" maxlength="100">
      </label>
      <button type="submit" class="btn-panel">Actualizar</button>
    </form>

    <a class="volver-sitio" href="panel.php">← Volver al panel</a>
  </main>
</body>
</html>
