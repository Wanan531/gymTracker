<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {

    if ($_POST['accion'] === 'actualizar') {

        $nombre           = trim($_POST['nombre']);

        if (isset($_POST['peso_kg']) && $_POST['peso_kg'] !== '') {
                $peso_kg = $_POST['peso_kg'];
            } else {
                $peso_kg = null;
        }

        if (isset($_POST['altura_cm']) && $_POST['altura_cm'] != '') {
                $altura_cm = $_POST['altura_cm'];
        } else {
            $altura_cm = null;
        }

        if (isset($_POST['fecha_nacimiento']) && $_POST['fecha_nacimiento'] != '') {
                $fecha_nacimiento = $_POST['fecha_nacimiento'];
        } else {
            $fecha_nacimiento = null;
        }
       
        $objetivo         =  $_POST['objetivo'];

        // Aquí Upsert(upadate + insert). El objetivo es que el usuario no tenga dos registros de peso del mismo día.
        if ($peso_kg) {
            $stmt = $pdo->prepare("SELECT id FROM progreso_corporal WHERE usuario_id = ? AND fecha = CURDATE()"); // busca en la tabla progreso_corporal si ya existe una entrada para ese usuario_id con la fecha de hoy(CURDATE()).
            $stmt->execute([$usuario_id]);
            $hoy = $stmt->fetchColumn();

            if ($hoy) { // si existe ejecuta un update y sobrescribe el peso_kg 
                $stmt = $pdo->prepare("UPDATE progreso_corporal SET peso_kg = ? WHERE id = ?");
                $stmt->execute([$peso_kg, $hoy]);
            } else { // si no existe ejecuta un insert
                $stmt = $pdo->prepare("INSERT INTO progreso_corporal (usuario_id, fecha, peso_kg) VALUES (?, CURDATE(), ?)");
                $stmt->execute([$usuario_id, $peso_kg]);
            }
        }

        $stmt = $pdo->prepare("UPDATE usuario SET nombre = ?, peso_kg = ?, altura_cm = ?, fecha_nacimiento = ?, objetivo = ? WHERE id = ?");
        $stmt->execute([$nombre, $peso_kg, $altura_cm, $fecha_nacimiento, $objetivo, $usuario_id]);
        $_SESSION['usuario_nombre'] = $nombre;
        $success = 'Perfil actualizado correctamente.';
    }

    // cambiar contraseña
    if ($_POST['accion'] === 'cambiar_password') {
        $pass_actual = $_POST['password_actual'];
        $pass_nueva  = $_POST['password_nueva'];
        $pass_nueva2 = $_POST['password_nueva2'];

        $stmt = $pdo->prepare("SELECT password_hash FROM usuario WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $hash = $stmt->fetchColumn();

        if (!password_verify($pass_actual, $hash)) {
            $error_pass = 'La contraseña actual no es correcta.';
        } elseif ($pass_nueva !== $pass_nueva2) {
            $error_pass = 'Las contraseñas nuevas no coinciden.';
        } elseif (strlen($pass_nueva) < 6) {
            $error_pass = 'La nueva contraseña debe tener al menos 6 caracteres.';
        } else {
            $nuevo_hash = password_hash($pass_nueva, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuario SET password_hash = ? WHERE id = ?");
            $stmt->execute([$nuevo_hash, $usuario_id]);
            $success_pass = 'Contraseña cambiada correctamente.';
        }
    }

    // - Subida de foto de perfil -
    if ($_POST['accion'] === 'subir_foto') {
        $archivo = $_FILES['foto_perfil'] ?? null;

        if ($archivo && $archivo['error'] === UPLOAD_ERR_OK) {
            $tipo         = mime_content_type($archivo['tmp_name']);
            $permitidos   = ['image/jpeg', 'image/png', 'image/webp'];

            if (!in_array($tipo, $permitidos)) {
                $error_foto = 'Solo se permiten imágenes JPG, PNG o WEBP.';
            } elseif ($archivo['size'] > 2 * 1024 * 1024) {
                $error_foto = 'La imagen no puede superar los 2MB.';
            } else {
                // Borrar foto anterior si existe
                $stmt = $pdo->prepare("SELECT foto_perfil FROM usuario WHERE id = ?");
                $stmt->execute([$usuario_id]);
                $foto_anterior = $stmt->fetchColumn();
                if ($foto_anterior && file_exists(__DIR__ . '/../' . $foto_anterior)) {
                    unlink(__DIR__ . '/../' . $foto_anterior);
                }

                $extension = match($tipo) {
                    'image/jpeg' => '.jpg',
                    'image/png'  => '.png',
                    'image/webp' => '.webp',
                };
                $nombre_archivo = 'avatar_' . $usuario_id . '_' . time() . $extension;
                $ruta_relativa = 'assets/uploads/avatars/' . $nombre_archivo;
                $ruta_absoluta = __DIR__ . '/../' . $ruta_relativa;

                if (move_uploaded_file($archivo['tmp_name'], $ruta_absoluta)) {
                    $stmt = $pdo->prepare("UPDATE usuario SET foto_perfil = ? WHERE id = ?");
                    $stmt->execute([$ruta_relativa, $usuario_id]);
                    $success_foto = 'Foto actualizada correctamente.';
                    $usuario['foto_perfil'] = $ruta_relativa;
                } else {
                    $error_foto = 'Error al guardar la imagen. Inténtalo de nuevo.';
                }
            }
        } else {
            $error_foto = 'No se recibió ningún archivo.';
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM usuario WHERE id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT COUNT(*) FROM sesion WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$total_sesiones = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT SUM(duracion_min) FROM sesion WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$total_minutos = $stmt->fetchColumn() ?? 0;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM marca_personal WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$total_records = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM usuario_logro WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$total_logros = $stmt->fetchColumn();

$iniciales = strtoupper(substr($usuario['nombre'], 0, 1));
if (strpos($usuario['nombre'], ' ') !== false) {
    $partes    = explode(' ', $usuario['nombre']);
    $iniciales = strtoupper($partes[0][0] . $partes[1][0]);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil — Gymtracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/style_perfil.css">
    <?php require_once '../includes/head_meta.php'; ?>
</head>

<?php require_once '../includes/header.php'; ?>

<body>
    
<main class="container">

    <div style="margin-bottom:20px;">
        <h1 style="font-size:26px;font-weight:700;letter-spacing:-0.5px;">Perfil</h1>
    </div>

    <!-- Avatar y nombre -->
<div class="perfil-header">
    <div class="avatar-wrapper" onclick="document.getElementById('input-foto').click()">
        <?php if (!empty($usuario['foto_perfil']) && file_exists(__DIR__ . '/../' . $usuario['foto_perfil'])): ?>
            <img src="../<?= htmlspecialchars($usuario['foto_perfil']) ?>?v=<?= time() ?>"
                 class="avatar avatar-img" alt="Foto de perfil">
        <?php else: ?>
            <div class="avatar"><?= $iniciales ?></div>
        <?php endif; ?>
        <div class="avatar-overlay"></div>
    </div>

    <?php if (!empty($error_foto)): ?>
        <p class="msg-error" style="margin-top:8px;"><?= htmlspecialchars($error_foto) ?></p>
    <?php endif; ?>
    <?php if (!empty($success_foto)): ?>
        <p class="msg-success" style="margin-top:8px;"><?= htmlspecialchars($success_foto) ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="form-foto">
        <input type="hidden" name="accion" value="subir_foto">
        <input type="file"
               id="input-foto"
               name="foto_perfil"
               accept="image/jpeg,image/png,image/webp"
               style="display:none;">
    </form>

    <p class="perfil-nombre"><?= htmlspecialchars($usuario['nombre']) ?></p>
    <p class="perfil-desde">
        <?php
            $meses = [1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',5=>'mayo',6=>'junio',
                      7=>'julio',8=>'agosto',9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre'];
            $fecha = new DateTime($usuario['creado_en']);
            echo 'Miembro desde ' . $meses[(int)$fecha->format('n')] . ' ' . $fecha->format('Y');
        ?>
    </p>
</div>

    <!-- Estadísticas -->
    <div class="metric-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:16px;">
        <a class="metric-card" href="dashboard.php#entrenos">
            <p class="metric-label">Entrenos</p>
            <p class="metric-value"><?= $total_sesiones ?></p>
        </a>
        <a class="metric-card" href="dashboard.php#horas">
            <p class="metric-label">Horas</p>
            <p class="metric-value"><?= round($total_minutos / 60, 1) ?></p>
        </a>
        <a class="metric-card" href="progreso.php#marcas">
            <p class="metric-label">Récords</p>
            <p class="metric-value"><?= $total_records ?></p>
        </a>
        <a class="metric-card" href="logros.php">
            <p class="metric-label">Logros</p>
            <p class="metric-value"><?= $total_logros ?></p>
        </a>
    </div>

    <?php if (!empty($success)): ?>
        <p class="msg-success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <!-- Datos personales -->
    <p class="section-title">Datos personales</p>
    <div class="form-card">
        <form method="POST">
            <input type="hidden" name="accion" value="actualizar">
            <label>Nombre</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
            <label>Peso actual (kg)</label>
            <input type="number" name="peso_kg" step="0.1" value="<?= $usuario['peso_kg'] ?>" placeholder="78.5">
            <label>Altura (cm)</label>
            <input type="number" name="altura_cm" value="<?= $usuario['altura_cm'] ?>" placeholder="180">
            <label>Fecha de nacimiento</label>
            <input type="date" name="fecha_nacimiento" value="<?= $usuario['fecha_nacimiento'] ?>">
            <label>Objetivo</label>
            <div class="objetivo-grid">
                <?php foreach (['hipertrofia'=>'Hipertrofia','fuerza'=>'Fuerza','definicion'=>'Definición','resistencia'=>'Resistencia'] as $val => $lbl): ?>
                <label>
                    <input type="radio" name="objetivo" value="<?= $val ?>"
                           <?= $usuario['objetivo'] === $val ? 'checked' : '' ?>
                           style="display:none;" class="obj-radio">
                    <span class="objetivo-btn <?= $usuario['objetivo'] === $val ? 'selected' : '' ?>"
                          onclick="seleccionarObjetivo(this)">
                        <?= $lbl ?>
                    </span>
                </label>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn-primary block">Guardar cambios</button>
        </form>
    </div>

    <!-- Cambiar contraseña -->
    <p class="section-title">Cambiar contraseña</p>
    <div class="form-card">
        <?php if (!empty($error_pass)): ?>
            <p class="msg-error"><?= htmlspecialchars($error_pass) ?></p>
        <?php endif; ?>
        <?php if (!empty($success_pass)): ?>
            <p class="msg-success"><?= htmlspecialchars($success_pass) ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="accion" value="cambiar_password">
            <label>Contraseña actual</label>
            <input type="password" name="password_actual" required>
            <label>Nueva contraseña</label>
            <input type="password" name="password_nueva" required>
            <label>Repetir nueva contraseña</label>
            <input type="password" name="password_nueva2" required>
            <button type="submit" class="btn-primary block" style="margin-top:14px;">Cambiar contraseña</button>
        </form>
    </div>

    <!-- Cerrar sesión -->
    <a href="logout.php" class="btn-danger">Cerrar sesión</a>

</main>

<script>
function seleccionarObjetivo(span) {
    document.querySelectorAll('.objetivo-btn').forEach(b => b.classList.remove('selected'));
    span.classList.add('selected');
    span.previousElementSibling.checked = true;
}

document.getElementById('input-foto').addEventListener('change', function() {
    if (this.files && this.files.length > 0) {
        document.getElementById('form-foto').submit();
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>

</body>
</html>