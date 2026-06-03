<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

$usuario_id = $_SESSION['usuario_id'];

// ── Logros del usuario ──
$stmt = $pdo->prepare("
    SELECT l.*,
           ul.conseguido_en,
           CASE WHEN ul.id IS NOT NULL THEN 1 ELSE 0 END as desbloqueado
    FROM logro l
    LEFT JOIN usuario_logro ul ON ul.logro_id = l.id AND ul.usuario_id = ?
    ORDER BY desbloqueado DESC, l.id ASC
");
$stmt->execute([$usuario_id]);
$logros = $stmt->fetchAll(PDO::FETCH_ASSOC);

$desbloqueados = count(array_filter($logros, fn($l) => $l['desbloqueado']));
$total_logros  = count($logros);

// ── Stats base ──
$stmt = $pdo->prepare("SELECT COUNT(*) FROM sesion WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$total_sesiones = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT fecha FROM sesion WHERE usuario_id = ? ORDER BY fecha DESC");
$stmt->execute([$usuario_id]);
$fechas = $stmt->fetchAll(PDO::FETCH_COLUMN);

$racha = 0;
$hoy   = new DateTime();
foreach ($fechas as $fecha) {
    $diff = $hoy->diff(new DateTime($fecha))->days;
    if ($diff === $racha) { $racha++; $hoy = new DateTime($fecha); }
    else break;
}

// ── Stats extendidas ──
$stats = [
    'sesiones_total'              => $total_sesiones,
    'racha'                       => $racha,
    'volumen_total'               => 0,
    'tiempo_total'                => 0,
    'ejercicios_distintos'        => 0,
    'sesiones_semana'             => 0,
    'marcas_personales_mejoradas' => 0,
    'sesiones_lunes'              => 0,
];

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(ss.peso_kg * ss.repeticiones), 0)
    FROM sesion_serie ss
    JOIN sesion s ON s.id = ss.sesion_id
    WHERE s.usuario_id = ?
");
$stmt->execute([$usuario_id]);
$stats['volumen_total'] = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT ss.ejercicio_id)
    FROM sesion_serie ss
    JOIN sesion s ON s.id = ss.sesion_id
    WHERE s.usuario_id = ?
");
$stmt->execute([$usuario_id]);
$stats['ejercicios_distintos'] = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM sesion
    WHERE usuario_id = ? AND YEARWEEK(fecha, 1) = YEARWEEK(CURDATE(), 1)
");
$stmt->execute([$usuario_id]);
$stats['sesiones_semana'] = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM sesion
    WHERE usuario_id = ? AND DAYOFWEEK(fecha) = 2
");
$stmt->execute([$usuario_id]);
$stats['sesiones_lunes'] = $stmt->fetchColumn();

// ── Funciones ──
function getIcono($condicion): string {
    if (str_contains($condicion, 'volumen_total'))            return '🏋️';
    if (str_contains($condicion, 'racha'))                    return '🔥';
    if (str_contains($condicion, 'sesiones_semana'))          return '📆';
    if (str_contains($condicion, 'sesiones_lunes'))           return '🗓️';
    if (str_contains($condicion, 'sesiones_total'))           return '🏅';
    if (str_contains($condicion, 'marcas_personales'))        return '📈';
    if (str_contains($condicion, 'marca_'))                   return '💪';
    if (str_contains($condicion, 'ejercicios_distintos_mes')) return '🗓️';
    if (str_contains($condicion, 'ejercicios_distintos'))     return '🎯';
    if (str_contains($condicion, 'tiempo_'))                  return '⏱️';
    if (str_contains($condicion, 'enero'))                    return '🎆';
    if (str_contains($condicion, 'comeback'))                 return '💫';
    if (str_contains($condicion, '666'))                      return '😈';
    return '🏆';
}

function calcularProgreso($condicion, $stats): ?array {
    $mapeo = [
        'sesiones_total'              => $stats['sesiones_total'],
        'racha'                       => $stats['racha'],
        'volumen_total'               => $stats['volumen_total'],
        'tiempo_total'                => $stats['tiempo_total'],
        'ejercicios_distintos'        => $stats['ejercicios_distintos'],
        'sesiones_semana'             => $stats['sesiones_semana'],
        'marcas_personales_mejoradas' => $stats['marcas_personales_mejoradas'],
        'sesiones_lunes'              => $stats['sesiones_lunes'],
    ];
    foreach ($mapeo as $clave => $valor) {
        if (str_contains($condicion, $clave)) {
            preg_match('/\d+/', $condicion, $m);
            $meta = (int)$m[0];
            return ['actual' => min((int)$valor, $meta), 'meta' => $meta];
        }
    }
    return null; // booleanos (comeback_kid, sesion_enero_1...) no tienen barra
}

$desbloqueados_lista = array_filter($logros, fn($l) =>  $l['desbloqueado']);
$bloqueados_lista    = array_filter($logros, fn($l) => !$l['desbloqueado']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logros — Gymtracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/style_logros.css">
    <?php require_once '../includes/head_meta.php'; ?>
</head>

<?php require_once '../includes/header.php'; ?>

<body>

    <main class="container">

    <div style="margin-bottom:20px;">
        <h1 style="font-size:26px;font-weight:700;letter-spacing:-0.5px;">Logros</h1>
    </div>

    <div class="resumen-logros">
        <div>
            <p class="resumen-label">Desbloqueados</p>
            <p class="resumen-num"><?= $desbloqueados ?> <span style="font-size:18px;font-weight:400;color:var(--text3);">/ <?= $total_logros ?></span></p>
        </div>
        <div style="text-align:right;">
            <p class="resumen-label">Progreso</p>
            <p class="resumen-num"><?= $total_logros > 0 ? round($desbloqueados / $total_logros * 100) : 0 ?>%</p>
        </div>
    </div>

    <?php if (!empty($desbloqueados_lista)): ?>
    <div class="card" style="margin-bottom:12px;">
        <p class="card-label">Conseguidos</p>
        <?php foreach ($desbloqueados_lista as $logro): ?>
        <div class="logro-card">
            <div class="logro-icono desbloqueado">
                <?= getIcono($logro['condicion']) ?>
            </div>
            <div class="logro-info">
                <p class="logro-nombre"><?= htmlspecialchars($logro['nombre']) ?></p>
                <p class="logro-desc"><?= htmlspecialchars($logro['descripcion']) ?></p>
                <p class="logro-fecha">Conseguido el <?= date('d/m/Y', strtotime($logro['conseguido_en'])) ?></p>
            </div>
            <span style="font-size:18px;">✅</span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($bloqueados_lista)): ?>
    <div class="card">
        <p class="card-label">Próximos</p>
        <?php foreach ($bloqueados_lista as $logro): ?>
        <?php $progreso = calcularProgreso($logro['condicion'], $stats); ?>
        <div class="logro-card">
            <div class="logro-icono bloqueado">
                <?= getIcono($logro['condicion']) ?>
            </div>
            <div class="logro-info">
                <p class="logro-nombre bloqueado"><?= htmlspecialchars($logro['nombre']) ?></p>
                <p class="logro-desc"><?= htmlspecialchars($logro['descripcion']) ?></p>
                <?php if ($progreso): ?>
                <div class="progress-meta">
                    <span><?= $progreso['actual'] ?> / <?= $progreso['meta'] ?></span>
                    <span><?= round($progreso['actual'] / $progreso['meta'] * 100) ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width:<?= round($progreso['actual'] / $progreso['meta'] * 100) ?>%"></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <button id="btn-arriba-modal" onclick="subirModal()">↑</button>

</main>

<?php require_once '../includes/footer.php'; ?>

</body>

<script>
const btnArriba = document.getElementById('btn-arriba-modal');
window.addEventListener('scroll', function () {
    if (btnArriba) btnArriba.style.display = window.scrollY > 200 ? 'inline-block' : 'none';
});
function subirModal() { window.scrollTo({ top: 0, behavior: 'smooth' }); }
</script>

</html>
