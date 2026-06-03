<?php

require_once '../includes/auth.php';
require_once '../config/db.php';

$usuario_id = $_SESSION['usuario_id'];
$nombre = $_SESSION['usuario_nombre'];

// Métricas de la semana actual
$inicio_semana = date('Y-m-d', strtotime('monday this week'));
$fin_semana    = date('Y-m-d', strtotime('sunday this week'));
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total
    FROM sesion
    WHERE usuario_id = ?
    AND fecha BETWEEN ? AND ?
");
$stmt->execute([$usuario_id, $inicio_semana, $fin_semana]);
$entrenos_semana = $stmt->fetchColumn();

// Racha actual
$stmt = $pdo->prepare("
    SELECT fecha FROM sesion
    WHERE usuario_id = ?
    ORDER BY fecha DESC
");
$stmt->execute([$usuario_id]);
$fechas = $stmt->fetchAll(PDO::FETCH_COLUMN);

$racha = 0;
$hoy = new DateTime();
foreach ($fechas as $fecha) {
    $diff = $hoy->diff(new DateTime($fecha))->days;
    if ($diff === $racha) {
        $racha++;
        $hoy = new DateTime($fecha);
    } else {
        break;
    }
}

// Volumen total
$stmt = $pdo->prepare("
    SELECT SUM(repeticiones * peso_kg)
    FROM sesion_serie ss
    JOIN sesion s ON s.id = ss.sesion_id
    WHERE s.usuario_id = ?
");
$stmt->execute([$usuario_id]);
$volumen_total = $stmt->fetchColumn();

// Rutina activa para hoy
$dias_es = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
$dia_hoy = $dias_es[date('w')];

$stmt = $pdo->prepare("
    SELECT r.*, rd.dia_nombre
    FROM rutina r
    INNER JOIN rutina_dia rd ON rd.rutina_id = r.id
    WHERE r.usuario_id = ?
    AND r.activa = 1
    AND rd.dia_semana = ?
    LIMIT 1
");
$stmt->execute([$usuario_id, $dia_hoy]);
$rutina_hoy = $stmt->fetch(PDO::FETCH_ASSOC);

// Actividad últimos 7 días
$stmt = $pdo->prepare("
    SELECT fecha, COUNT(*) as entrenos
    FROM sesion
    WHERE usuario_id = ?
    AND fecha >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY fecha
");
$stmt->execute([$usuario_id]);
$actividad_raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$actividad = [];
for ($i = 6; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("-$i days"));
    $actividad[$fecha] = $actividad_raw[$fecha] ?? 0;
}

// Total sesiones históricas
$stmt = $pdo->prepare("SELECT COUNT(*) FROM sesion WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$total_sesiones = $stmt->fetchColumn();

// Días entrenados este mes
$stmt = $pdo->prepare("
    SELECT DISTINCT DATE(fecha) as fecha
    FROM sesion
    WHERE usuario_id = ?
    AND YEAR(fecha) = YEAR(CURDATE())
    AND MONTH(fecha) = MONTH(CURDATE())
");
$stmt->execute([$usuario_id]);
$dias_entrenados = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Detalle de sesiones por día este mes
$stmt = $pdo->prepare("
    SELECT
        DATE(s.fecha) as fecha,
        r.nombre as rutina,
        s.duracion_min,
        GROUP_CONCAT(DISTINCT e.nombre ORDER BY re.orden SEPARATOR '||') as ejercicios
    FROM sesion s
    LEFT JOIN rutina r ON r.id = s.rutina_id
    LEFT JOIN sesion_serie ss ON ss.sesion_id = s.id
    LEFT JOIN ejercicio e ON e.id = ss.ejercicio_id
    LEFT JOIN rutina_ejercicio re ON re.ejercicio_id = e.id AND re.rutina_id = s.rutina_id
    WHERE s.usuario_id = ?
    AND YEAR(s.fecha) = YEAR(CURDATE())
    AND MONTH(s.fecha) = MONTH(CURDATE())
    GROUP BY s.id, DATE(s.fecha), r.nombre, s.duracion_min
    ORDER BY s.fecha ASC
");
$stmt->execute([$usuario_id]);
$detalle_sesiones_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

$detalle_sesiones = [];
foreach ($detalle_sesiones_raw as $s) {
    $detalle_sesiones[$s['fecha']][] = $s;
}

// Comprobar si ya entrenó hoy
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM sesion
    WHERE usuario_id = ? AND rutina_id = ? AND fecha = CURDATE()
");
$stmt->execute([$usuario_id, $rutina_hoy['id'] ?? 0]);
$ya_entreno_hoy = $stmt->fetchColumn() > 0;

// Horas por día (últimos 30 días)
$stmt = $pdo->prepare("
    SELECT fecha, ROUND(SUM(duracion_min) / 60, 1) as horas
    FROM sesion
    WHERE usuario_id = ? AND fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY fecha
    ORDER BY fecha ASC
");
$stmt->execute([$usuario_id]);
$horas_dia = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Horas por semana (últimos 30 días)
$stmt = $pdo->prepare("
    SELECT YEARWEEK(fecha, 1) as semana_key, WEEK(MIN(fecha), 1) as num_semana,
           YEAR(MIN(fecha)) as anio, ROUND(SUM(duracion_min) / 60, 1) as horas
    FROM sesion
    WHERE usuario_id = ? AND fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY YEARWEEK(fecha, 1)
    ORDER BY YEARWEEK(fecha, 1) ASC
");
$stmt->execute([$usuario_id]);
$horas_semana = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Horas por mes (últimos 12 meses)
$stmt = $pdo->prepare("
    SELECT DATE_FORMAT(fecha, '%Y-%m') as mes, ROUND(SUM(duracion_min) / 60, 1) as horas
    FROM sesion
    WHERE usuario_id = ? AND fecha >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(fecha, '%Y-%m')
    ORDER BY DATE_FORMAT(fecha, '%Y-%m') ASC
");
$stmt->execute([$usuario_id]);
$horas_mes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Horas por año (últimos 5 años)
$stmt = $pdo->prepare("
    SELECT YEAR(fecha) as anio, ROUND(SUM(duracion_min) / 60, 1) as horas
    FROM sesion
    WHERE usuario_id = ? AND fecha >= DATE_SUB(CURDATE(), INTERVAL 5 YEAR)
    GROUP BY YEAR(fecha)
    ORDER BY YEAR(fecha) ASC
");
$stmt->execute([$usuario_id]);
$horas_anio = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Labels y dias para actividad (PHP)
$dias_label = [];
$dias_es_short = ['Mon'=>'lun','Tue'=>'mar','Wed'=>'mié','Thu'=>'jue','Fri'=>'vie','Sat'=>'sáb','Sun'=>'dom'];
foreach ($actividad as $fecha => $entrenos) {
    $dias_label[$fecha] = $dias_es_short[(new DateTime($fecha))->format('D')] ?? '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Gymtracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/style_dashboard.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <?php require_once '../includes/head_meta.php'; ?>
</head>

<body class="page-dashboard">
<?php require_once '../includes/header.php'; ?>
<main class="container">

    <div class="page-header">
        <h1>Buenas, <?= htmlspecialchars($nombre) ?></h1>
    </div>

    <!-- Rutina de hoy / descanso -->
    <?php if ($rutina_hoy): ?>
    <div class="card-highlight <?= $ya_entreno_hoy ? 'completado' : '' ?>">
        <p class="card-label"><?= $ya_entreno_hoy ? '✓ completado' : 'hoy toca' ?></p>
        <p class="card-title"><?= htmlspecialchars($rutina_hoy['nombre']) ?></p>
        <?php if (!empty($rutina_hoy['dia_nombre'])): ?>
            <p class="card-description" style="font-weight:600;color:var(--text);margin-bottom:4px;">
                <?= htmlspecialchars($rutina_hoy['dia_nombre']) ?>
            </p>
        <?php endif; ?>
        <p class="card-description"><?= htmlspecialchars($rutina_hoy['descripcion']) ?></p>
        <?php if ($ya_entreno_hoy): ?>
            <p style="color:var(--text2);font-size:14px;margin-top:8px;">
                ¡Buen trabajo! Ya has completado el entrenamiento de hoy 💪
            </p>
        <?php else: ?>
            <a href="entreno.php?rutina_id=<?= $rutina_hoy['id'] ?>" class="btn-primary">
                Empezar entreno
            </a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="card-rest">
        <p class="card-label">Hoy</p>
        <p class="card-title">Día de descanso</p>
    </div>
    <?php endif; ?>

    <!-- Métricas -->
    <div class="metric-grid" style="grid-template-columns: 1fr 1fr;">
        <div class="metric-card">
            <p class="metric-label">Esta semana</p>
            <p class="metric-value"><?= $entrenos_semana ?></p>
            <p class="metric-sub">días</p>
        </div>
        <div class="metric-card">
            <p class="metric-label">Racha actual</p>
            <p class="metric-value"><?= $racha ?></p>
            <p class="metric-sub">días</p>
        </div>
        <div class="metric-card">
            <p class="metric-label">Volumen total</p>
            <?php if ($volumen_total >= 1000): ?>
                <p class="metric-value"><?= round($volumen_total / 1000, 1) ?></p>
                <p class="metric-sub">toneladas</p>
            <?php else: ?>
                <p class="metric-value"><?= $volumen_total ?? 0 ?></p>
                <p class="metric-sub">kg</p>
            <?php endif; ?>
        </div>
        <div class="metric-card">
            <p class="metric-label">Total entrenamientos</p>
            <p class="metric-value"><?= $total_sesiones ?></p>
            <p class="metric-sub">históricas</p>
        </div>
    </div>

    <!-- Actividad últimos 7 días — cuadrados -->
    <div id="tab-entrenos" class="tab-content activo">
        <div class="card">
            <p class="card-label">Actividad últimos 7 días</p>
            <div class="actividad-grid">
                <?php foreach ($actividad as $fecha => $entrenos): ?>
                <div class="actividad-item">
                    <div class="actividad-cuadrado <?= $entrenos > 0 ? 'entrenado' : '' ?>"></div>
                    <span class="actividad-label"><?= $dias_label[$fecha] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Horas de entreno con toggle día/semana/mes/año -->
    <div id="tab-horas" class="tab-content activo">
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                <p class="card-label" style="margin-bottom:0;">Horas de entreno</p>
                <div style="display:flex; gap:4px;">
                    <button class="btn-toggle active" id="btn-dia"    onclick="cambiarGraficaHoras('dia')">Día</button>
                    <button class="btn-toggle"         id="btn-semana" onclick="cambiarGraficaHoras('semana')">Semana</button>
                    <button class="btn-toggle"         id="btn-mes"    onclick="cambiarGraficaHoras('mes')">Mes</button>
                    <button class="btn-toggle"         id="btn-anio"   onclick="cambiarGraficaHoras('anio')">Año</button>
                </div>
            </div>
            <canvas id="grafica-horas" height="140"></canvas>
        </div>
    </div>

    <!-- Calendario -->
    <div class="card">
        <div class="calendario-header">
            <span id="mes-nombre"></span>
        </div>
        <div class="calendario-dias-semana">
            <span>L</span><span>M</span><span>X</span>
            <span>J</span><span>V</span><span>S</span><span>D</span>
        </div>
        <div class="calendario-grid" id="calendario-grid"></div>
        <div class="calendario-leyenda">
            <span class="leyenda-punto entrenado"></span> Entrenado
            <span class="leyenda-punto hoy"></span> Hoy
        </div>
    </div>

</main>

<!-- Popup detalle día -->
<div id="popup-dia">
    <div class="popup-inner">
        <div class="popup-header">
            <h3 id="popup-fecha"></h3>
            <button class="popup-close" onclick="cerrarPopup()">✕</button>
        </div>
        <div id="popup-contenido"></div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<!-- Datos de PHP pasados a JS como variables globales -->
<script>
    const detalleSesiones = <?= json_encode($detalle_sesiones) ?>;
    const diasEntrenados  = <?= json_encode($dias_entrenados) ?>;
    const horasDia        = <?= json_encode($horas_dia) ?>;
    const horasSemana     = <?= json_encode($horas_semana) ?>;
    const horasMes        = <?= json_encode($horas_mes) ?>;
    const horasAnio       = <?= json_encode($horas_anio) ?>;
</script>

<!-- Lógica del dashboard -->
<script src="../assets/js/script_dashboard.js"></script>

</body>
</html>