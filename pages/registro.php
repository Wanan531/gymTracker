<?php 
session_start();
require_once '../config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email  = trim($_POST['email']);
    $pass   = $_POST['password'];
    $pass2  = $_POST['password2'];

    if (empty($nombre) || empty($email) || empty($pass)) {
        $error = 'Rellena todos los campos.';
    } elseif ($pass !== $pass2) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($pass) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM usuario WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Ese email ya está registrado.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuario (nombre, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$nombre, $email, $hash]);
            header('Location: login.php?registro=1');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Gymtracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php require_once '../includes/head_meta.php'; ?>
</head>
<body class="auth-page">

<div class="auth-card">
    <h1>Crear cuenta</h1>
    <p class="auth-subtitle">Únete a gYmtraCker</p>

    <?php if ($error): ?>
        <p class="msg-error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="msg-success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre"
               value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
               placeholder="Tu nombre" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               placeholder="tu@email.com" required>

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password"
               placeholder="Mínimo 6 caracteres" required>

        <label for="password2">Repetir contraseña</label>
        <input type="password" id="password2" name="password2"
               placeholder="Repite la contraseña" required>

        <button type="submit" class="btn-primary block">Crear cuenta</button>
    </form>

    <p class="auth-link">¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a></p>
</div>

</body>
</html>