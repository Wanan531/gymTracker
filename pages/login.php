<?php
session_start();
require_once '../config/db.php';

if (isset($_GET['registro'])) {
    $success = 'Cuenta creada. Ya puedes iniciar sesión.';
}

if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$_SESSION['intentos'] = $_SESSION['intentos'] ?? 0;

if ($_SESSION['intentos'] >= 5) {
    $error = 'Demasiados intentos. Espera unos minutos.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];
    $stmt  = $pdo->prepare("SELECT * FROM usuario WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($usuario && password_verify($pass, $usuario['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['intentos']       = 0;
        $_SESSION['usuario_id']     = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        header('Location: dashboard.php');
        exit;
    } else {
        $_SESSION['intentos']++;
        $error = 'Email o contraseña incorrectos.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gymtracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php require_once '../includes/head_meta.php'; ?> 
</head>
<body class="auth-page">

<div class="auth-card">
    <h1>gYmtraCker</h1>
    <p class="auth-subtitle">Inicia sesión para continuar</p>

    <?php if (!empty($error)): ?>
        <p class="msg-error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <p class="msg-success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="email">Email</label>
        <input type="email" id="email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               placeholder="tu@email.com" required>

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password"
               placeholder="Tu contraseña" required>

        <button type="submit" class="btn-primary block">Entrar</button>
    </form>

    <p class="auth-link">¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
</div>

</body>
</html>