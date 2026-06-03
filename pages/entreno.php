<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

$usuario_id = $_SESSION['usuario_id'];
$rutina_id  = $_GET['rutina_id'] ?? null;

if (!$rutina_id) { header('Location: rutinas.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM rutina WHERE id = ? AND usuario_id = ?");
$stmt->execute([$rutina_id, $usuario_id]);
$rutina = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$rutina) { header('Location: rutinas.php'); exit; }

// ── Día de hoy ──
$dia_hoy    = strtolower(date('l'));
$dias_es    = [
    'monday'    => 'lunes',
    'tuesday'   => 'martes',
    'wednesday' => 'miercoles',
    'thursday'  => 'jueves',
    'friday'    => 'viernes',
    'saturday'  => 'sabado',
    'sunday'    => 'domingo'
];
$dia_hoy_es = $dias_es[$dia_hoy] ?? null;

$dia_rutina_hoy = null;
if ($dia_hoy_es) {
    $stmtDiaHoy = $pdo->prepare("
        SELECT dia_nombre FROM rutina_dia
        WHERE rutina_id = ? AND dia_semana = ? AND dia_nombre IS NOT NULL
        LIMIT 1
    ");
    $stmtDiaHoy->execute([$rutina_id, $dia_hoy_es]);
    $dia_rutina_hoy = $stmtDiaHoy->fetchColumn() ?: null;
}

$dia_elegido = $_GET['dia'] ?? null;
$dia_activo  = $dia_rutina_hoy ?? $dia_elegido ?? null;

$stmtDias = $pdo->prepare("
    SELECT dia_semana, COALESCE(dia_nombre, CONCAT('Día ', dia_semana)) as dia_nombre
    FROM rutina_dia
    WHERE rutina_id = ?
    ORDER BY FIELD(dia_semana,'lunes','martes','miercoles','jueves','viernes','sabado','domingo')
");
$stmtDias->execute([$rutina_id]);
$dias_disponibles = $stmtDias->fetchAll(PDO::FETCH_ASSOC);

// ── Cargar ejercicios ──
$ejercicios       = [];
$mostrar_selector = false;

if ($dia_activo) {
    $stmt = $pdo->prepare("
        SELECT re.*, e.nombre, e.grupo_muscular, e.gif_url, e.descripcion, e.tipo
        FROM rutina_ejercicio re
        JOIN ejercicio e ON e.id = re.ejercicio_id
        WHERE re.rutina_id = ? AND re.dia_nombre = ?
        ORDER BY re.orden ASC
    ");
    $stmt->execute([$rutina_id, $dia_activo]);
    $ejercicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (empty($ejercicios)) {
    $stmt = $pdo->prepare("
        SELECT re.*, e.nombre, e.grupo_muscular, e.gif_url, e.descripcion, e.tipo
        FROM rutina_ejercicio re
        JOIN ejercicio e ON e.id = re.ejercicio_id
        WHERE re.rutina_id = ?
        ORDER BY re.orden ASC
    ");
    $stmt->execute([$rutina_id]);
    $ejercicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($ejercicios)) {
        $dia_activo = null;
    }
}

if (empty($ejercicios)) {
    $mostrar_selector = true;
    $dia_activo       = null;
}

$marcas = [];
if (!$mostrar_selector) {
    foreach ($ejercicios as $ej) {
        $stmt = $pdo->prepare("
            SELECT MAX(peso_kg) as max_peso, MAX(repeticiones) as max_reps
            FROM marca_personal
            WHERE usuario_id = ? AND ejercicio_id = ?
        ");
        $stmt->execute([$usuario_id, $ej['ejercicio_id']]);
        $marcas[$ej['ejercicio_id']] = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// ── Durations for tiempo-type exercises (passed to JS) ──
$duraciones_tiempo = [];
foreach ($ejercicios as $i => $ej) {
    if (($ej['tipo'] ?? 'reps') === 'tiempo') {
        $duraciones_tiempo[$i] = (int)$ej['repeticiones'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrenando — Gymtracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/style_entreno.css">
    <?php require_once '../includes/head_meta.php'; ?>
</head>
<body>
<?php require_once '../includes/header.php'; ?>
<main class="container">

<?php if ($mostrar_selector): ?>

    <div class="page-header">
        <div>
            <h1><?= htmlspecialchars($rutina['nombre']) ?></h1>
            <p style="font-family:var(--font-m);font-size:11px;color:var(--text3);margin-top:4px;text-transform:uppercase;letter-spacing:1px;">
                Hoy no tienes entreno asignado — elige un día
            </p>
        </div>
        <a href="rutinas.php"><button class="btn-cancelar">Volver</button></a>
    </div>

    <?php if (!empty($dias_disponibles)): ?>
    <div style="display:flex; flex-direction:column; gap:10px; margin-top:24px;">
        <?php foreach ($dias_disponibles as $dia): ?>
        <a href="?rutina_id=<?= $rutina_id ?>&dia=<?= urlencode($dia['dia_nombre']) ?>"
           style="text-decoration:none;">
            <div style="background:var(--surface);border:0.5px solid var(--border2);border-radius:14px;padding:16px 18px;display:flex;justify-content:space-between;align-items:center;transition:border-color 0.2s;"
                 onmouseover="this.style.borderColor='var(--green)'"
                 onmouseout="this.style.borderColor='var(--border2)'">
                <div>
                    <div style="font-size:15px;font-weight:600;color:var(--text);">
                        <?= htmlspecialchars($dia['dia_nombre']) ?>
                    </div>
                    <div style="font-family:var(--font-m);font-size:11px;color:var(--text3);text-transform:uppercase;letter-spacing:1px;margin-top:3px;">
                        <?= htmlspecialchars($dia['dia_semana']) ?>
                    </div>
                </div>
                <span style="color:var(--green);font-size:18px;">→</span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="card" style="text-align:center;padding:32px;margin-top:24px;">
        <p style="color:var(--text2);margin-bottom:14px;">Esta rutina no tiene ejercicios todavía.</p>
        <a href="rutinas.php" class="btn-primary">Volver a rutinas</a>
    </div>
    <?php endif; ?>

<?php else: ?>

    <div class="page-header">
        <div>
            <h1><?= htmlspecialchars($rutina['nombre']) ?></h1>
            <?php if ($dia_activo): ?>
                <p style="font-family:var(--font-m);font-size:11px;color:var(--green);text-transform:uppercase;letter-spacing:1px;margin-top:4px;">
                    <?= htmlspecialchars($dia_activo) ?>
                </p>
            <?php endif; ?>
            <p style="color:var(--text3);font-size:12px;margin-top:4px;" id="contador-ejercicio">
                Ejercicio 1 de <?= count($ejercicios) ?>
            </p>
        </div>
        <button class="btn-cancelar" onclick="cancelarEntreno()">Cancelar</button>
    </div>

    <!-- ── DESCANSO GLOBAL ── -->
    <div class="descanso-global-bar">
        <span>Descanso global</span>
        <div class="descanso-btns">
            <button onclick="setDescansoGlobal(60,  this)">60s</button>
            <button onclick="setDescansoGlobal(90,  this)" class="activo">90s</button>
            <button onclick="setDescansoGlobal(120, this)">120s</button>
            <button onclick="setDescansoGlobal(180, this)">180s</button>
        </div>
    </div>

    <div class="timer-container">
        <span id="display-tiempo">00:00:00</span>
        <input type="hidden" id="segundos_totales" value="0">
    </div>

    <div class="progress-dots" id="progress-dots">
        <?php foreach ($ejercicios as $i => $ej): ?>
            <span class="<?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>"></span>
        <?php endforeach; ?>
    </div>

    <?php foreach ($ejercicios as $i => $ej):
        $marca    = $marcas[$ej['ejercicio_id']] ?? null;
        $esTiempo = ($ej['tipo'] ?? 'reps') === 'tiempo';
    ?>
    <div class="ejercicio-panel <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>">

        <?php if ($ej['gif_url']): ?>
        <div class="gif-container">
            <img src="<?= BASE_URL . htmlspecialchars($ej['gif_url']) ?>" alt="<?= htmlspecialchars($ej['nombre']) ?>" loading="lazy">
        </div>
        <?php endif; ?>

        <h2><?= htmlspecialchars($ej['nombre']) ?></h2>
        <p class="grupo"><?= htmlspecialchars($ej['grupo_muscular']) ?></p>

        <?php if ($marca && $marca['max_peso']): ?>
        <div class="record-badge">
            <span class="lbl">Récord personal</span>
            <span class="val">
                <?= $marca['max_peso'] ?> kg × <?= $marca['max_reps'] ?> <?= $esTiempo ? 'seg' : 'reps' ?>
            </span>
        </div>
        <?php endif; ?>

        <table class="set-table">
            <thead>
                <tr>
                    <th>Serie</th>
                    <th>Kg</th>
                    <th><?= $esTiempo ? 'Seg' : 'Reps' ?></th>
                    <th><?= $esTiempo ? 'Empezar' : 'Hecho' ?></th>
                </tr>
            </thead>
            <tbody>
                <?php for ($s = 1; $s <= $ej['series']; $s++): ?>
                <tr>
                    <td><?= $s ?></td>
                    <td><input type="number" class="input-peso" value="<?= $ej['peso_kg'] ?>" min="0" step="0.5"></td>
                    <td><input type="number" class="input-reps" value="<?= $ej['repeticiones'] ?>" min="0"></td>
                    <td>
                    <?php if ($esTiempo): ?>
                        <button class="set-done set-iniciar"
                                onclick="iniciarSerie(this, <?= $ej['ejercicio_id'] ?>, <?= $s ?>, <?= $ej['repeticiones'] ?>)">
                            ▶
                        </button>
                    <?php else: ?>
                        <button class="set-done"
                                onclick="completarSerie(this, <?= $ej['ejercicio_id'] ?>, <?= $s ?>)">
                            ✓
                        </button>
                    <?php endif; ?>
                    </td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <!-- ── Descanso por ejercicio ── -->
        <div class="descanso-ej-bar">
            <span>Descanso aquí</span>
            <div class="descanso-btns">
                <button onclick="setDescansoEj(<?= $i ?>, 60,  this)">60s</button>
                <button onclick="setDescansoEj(<?= $i ?>, 90,  this)" data-ej-default="<?= $i ?>">90s</button>
                <button onclick="setDescansoEj(<?= $i ?>, 120, this)">120s</button>
                <button onclick="setDescansoEj(<?= $i ?>, 180, this)">180s</button>
            </div>
        </div>

        <?php if ($esTiempo): ?>
        <div class="timer-box timer-ejercicio" id="timer-ej-<?= $i ?>">
            <p>Ejecutando serie</p>
            <div class="timer-display" id="timer-ej-display-<?= $i ?>">0:00</div>
            <div class="timer-bar"><div class="timer-bar-fill" id="timer-ej-fill-<?= $i ?>" style="width:100%"></div></div>
            <button onclick="saltarEjercicio(<?= $i ?>)">Saltar ejercicio</button>
        </div>
        <?php endif; ?>

        <div class="timer-box" id="timer-<?= $i ?>">
            <p>Descanso</p>
            <div class="timer-display" id="timer-display-<?= $i ?>">1:30</div>
            <div class="timer-bar"><div class="timer-bar-fill" id="timer-fill-<?= $i ?>" style="width:100%"></div></div>
            <button onclick="saltarDescanso(<?= $i ?>)">Saltar descanso</button>
        </div>

        <div class="nav-ejercicios">
            <?php if ($i > 0): ?>
                <button onclick="irA(<?= $i - 1 ?>)">← Anterior</button>
            <?php else: ?>
                <button disabled>← Anterior</button>
            <?php endif; ?>
            <?php if ($i < count($ejercicios) - 1): ?>
                <button class="siguiente" onclick="irA(<?= $i + 1 ?>)">Siguiente →</button>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <button class="btn-finalizar" id="btn-finalizar" onclick="finalizarEntreno()">
        Finalizar entreno
    </button>

    <!-- ── POPUP CELEBRACIÓN ── -->
    <div id="celebracion-overlay">
        <div id="celebracion-popup">
            <div style="font-size:40px; margin-bottom:8px;">🏆</div>
            <h2 id="celebracion-kg" style="font-family:'Syne',sans-serif; font-size:42px; color:#72c442; margin:0; line-height:1; letter-spacing:-1px;"></h2>
            <p style="font-family:'DM Mono',monospace; font-size:11px; color:#4a6044; text-transform:uppercase; letter-spacing:1px; margin:6px 0 16px;">levantados hoy</p>

            <div id="comp-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:16px;"></div>

            <div style="display:flex; gap:10px; justify-content:center; margin-bottom:16px;">
                <div style="background:#111a10; border-radius:8px; padding:8px 14px; text-align:center;">
                    <div id="cel-series" style="font-size:20px; font-weight:600; color:#e8f5e0;"></div>
                    <div style="font-size:10px; color:#4a6044; font-family:'DM Mono',monospace; text-transform:uppercase;">series</div>
                </div>
                <div style="background:#111a10; border-radius:8px; padding:8px 14px; text-align:center;">
                    <div id="cel-duracion" style="font-size:20px; font-weight:600; color:#e8f5e0;"></div>
                    <div style="font-size:10px; color:#4a6044; font-family:'DM Mono',monospace; text-transform:uppercase;">minutos</div>
                </div>
            </div>

            <button onclick="confirmarFin()" style="background:rgba(58,110,32,0.5); color:#72c442; border:0.5px solid rgba(114,196,66,0.35); border-radius:10px; padding:12px 0; width:100%; font-family:'Syne',sans-serif; font-size:15px; font-weight:600; cursor:pointer; letter-spacing:0.3px;">
                Guardar entreno
            </button>
        </div>
    </div>

<?php endif; ?>

</main>
<?php require_once '../includes/footer.php'; ?>

<!-- Datos de PHP pasados a JS como variables globales -->
<script>
    const RUTINA_ID           = <?= json_encode((int)$rutina_id) ?>;
    const TOTAL_EJERCICIOS    = <?= json_encode(count($ejercicios)) ?>;
    const duracionesEjercicio = <?= json_encode($duraciones_tiempo) ?>;
</script>

<!-- Lógica del entreno -->
<script src="../assets/js/script_entreno.js"></script>

</body>
</html>