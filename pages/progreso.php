<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'guardar_peso') {
        $stmt = $pdo->prepare("SELECT id FROM progreso_corporal WHERE usuario_id = ? AND fecha = CURDATE()");
        $stmt->execute([$usuario_id]);
        $hoy = $stmt->fetchColumn();

        if ($hoy) {
            $stmt = $pdo->prepare("UPDATE progreso_corporal SET peso_kg = ?, pecho_cm = ?, cintura_cm = ?, brazo_cm = ? WHERE id = ?");
            $stmt->execute([$_POST['peso_kg'], $_POST['pecho_cm'] ?: null, $_POST['cintura_cm'] ?: null, $_POST['brazo_cm'] ?: null, $hoy]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO progreso_corporal (usuario_id, fecha, peso_kg, pecho_cm, cintura_cm, brazo_cm) VALUES (?, CURDATE(), ?, ?, ?, ?)");
            $stmt->execute([$usuario_id, $_POST['peso_kg'], $_POST['pecho_cm'] ?: null, $_POST['cintura_cm'] ?: null, $_POST['brazo_cm'] ?: null]);
        }
        $stmt = $pdo->prepare("UPDATE usuario SET peso_kg = ? WHERE id = ?");
        $stmt->execute([$_POST['peso_kg'], $usuario_id]);

        header('Location: progreso.php');
        exit;
    }
}

// LIMIT 365 para que el filtro "año" tenga datos suficientes
$stmt = $pdo->prepare("SELECT fecha, peso_kg FROM progreso_corporal WHERE usuario_id = ? ORDER BY fecha ASC LIMIT 365");
$stmt->execute([$usuario_id]);
$historial_peso = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT fecha, pecho_cm, cintura_cm, brazo_cm FROM progreso_corporal WHERE usuario_id = ? AND (pecho_cm IS NOT NULL OR cintura_cm IS NOT NULL OR brazo_cm IS NOT NULL) ORDER BY fecha ASC LIMIT 365");
$stmt->execute([$usuario_id]);
$historial_medidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM progreso_corporal WHERE usuario_id = ? ORDER BY fecha DESC, id DESC LIMIT 1");
$stmt->execute([$usuario_id]);
$ultimo = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT peso_kg FROM progreso_corporal WHERE usuario_id = ? ORDER BY fecha ASC LIMIT 1");
$stmt->execute([$usuario_id]);
$primero = $stmt->fetchColumn();

$diferencia_peso = $ultimo && $primero ? round($ultimo['peso_kg'] - $primero, 1) : null;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM sesion WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$total_sesiones = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT SUM(duracion_min) FROM sesion WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$total_minutos = $stmt->fetchColumn() ?? 0;
$total_horas   = round($total_minutos / 60, 1);

$stmt = $pdo->prepare("
    SELECT mp.ejercicio_id, MAX(mp.peso_kg) as peso_kg,
           mp.repeticiones, mp.fecha, e.nombre, e.grupo_muscular, e.gif_url
    FROM marca_personal mp
    JOIN ejercicio e ON e.id = mp.ejercicio_id
    WHERE mp.usuario_id = ?
    GROUP BY mp.ejercicio_id, e.nombre, e.grupo_muscular, e.gif_url, mp.repeticiones, mp.fecha
    ORDER BY mp.fecha DESC
");
$stmt->execute([$usuario_id]);
$marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$marcas_por_musculo = [];
foreach ($marcas as $marca) {
    $grupo = $marca['grupo_muscular'] ?: 'Otros';
    $marcas_por_musculo[$grupo][] = $marca;
}

$marcas_top5 = array_slice($marcas, 0, 5);

// ── Datos para JS: preparados en PHP, inyectados como globales ──
$js_peso = array_map(fn($r) => [
    'fecha' => $r['fecha'],
    'val'   => (float)$r['peso_kg']
], $historial_peso);

$js_medidas = array_map(fn($r) => [
    'fecha'   => $r['fecha'],
    'pecho'   => $r['pecho_cm']   !== null ? (float)$r['pecho_cm']   : null,
    'cintura' => $r['cintura_cm'] !== null ? (float)$r['cintura_cm'] : null,
    'brazo'   => $r['brazo_cm']   !== null ? (float)$r['brazo_cm']   : null,
], $historial_medidas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progreso — Gymtracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/style_progreso.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <?php require_once '../includes/head_meta.php'; ?>
    <style>
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        .chart-header .card-label { margin: 0; }
        .chart-filters { display: flex; gap: 4px; }
        .filter-btn {
            background: transparent;
            border: 1px solid var(--border, #2a2a2a);
            color: var(--text3, #888);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-family: 'DM Mono', monospace;
            cursor: pointer;
            transition: all 0.18s;
            letter-spacing: 0.3px;
        }
        .filter-btn:hover  { border-color: #72c442; color: #72c442; }
        .filter-btn.active { background: #72c442; border-color: #72c442; color: #0a1a00; font-weight: 500; }
    </style>
</head>
<body>
<?php require_once '../includes/header.php'; ?>

<main class="container">

    <div class="page-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h1 style="font-size:26px;font-weight:700;letter-spacing:-0.5px;">Progreso</h1>
        <button class="btn-primary" onclick="abrirModal()">+ Registrar</button>
    </div>

    <div class="metric-grid" style="grid-template-columns:1fr 1fr;margin-bottom:16px;">
        <div class="metric-card">
            <p class="metric-label">Entrenamientos totales</p>
            <p class="metric-value"><?= $total_sesiones ?></p>
        </div>
        <div class="metric-card">
            <p class="metric-label">Horas entrenando</p>
            <p class="metric-value"><?= $total_horas ?></p>
        </div>
    </div>

    <div class="tabs">
        <button class="tab active" onclick="cambiarTab('peso', this)">Peso</button>
        <button class="tab" onclick="cambiarTab('medidas', this)">Medidas</button>
        <button class="tab" onclick="cambiarTab('marcas', this)">Progreso</button>
    </div>

    <!-- ═══ Tab PESO ═══ -->
    <div id="tab-peso" class="tab-content active">

        <?php if ($ultimo): ?>
        <div class="peso-card">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <p class="card-label" style="margin-bottom:6px;">Peso actual</p>
                    <p class="peso-actual-valor"><?= number_format($ultimo['peso_kg'], 2) ?> <span style="font-size:18px;font-weight:400;color:var(--text2);">kg</span></p>
                </div>
                <?php if ($diferencia_peso !== null): ?>
                <div style="text-align:right;">
                    <p class="card-label" style="margin-bottom:6px;">Desde el inicio</p>
                    <p class="<?= $diferencia_peso > 0 ? 'diff-positivo' : 'diff-negativo' ?>">
                        <?= $diferencia_peso > 0 ? '+' : '' ?><?= number_format($diferencia_peso, 2) ?> kg
                    </p>
                </div>
                <?php endif; ?>
            </div>
            <?php if ($ultimo['pecho_cm'] || $ultimo['cintura_cm'] || $ultimo['brazo_cm']): ?>
            <div class="medidas-fila">
                <?php if ($ultimo['pecho_cm']): ?>
                <div><p class="card-label" style="margin-bottom:4px;">Pecho</p><p class="medida-valor"><?= $ultimo['pecho_cm'] ?> cm</p></div>
                <?php endif; ?>
                <?php if ($ultimo['cintura_cm']): ?>
                <div><p class="card-label" style="margin-bottom:4px;">Cintura</p><p class="medida-valor"><?= $ultimo['cintura_cm'] ?> cm</p></div>
                <?php endif; ?>
                <?php if ($ultimo['brazo_cm']): ?>
                <div><p class="card-label" style="margin-bottom:4px;">Brazo</p><p class="medida-valor"><?= $ultimo['brazo_cm'] ?> cm</p></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($historial_peso)): ?>
        <div class="card">
            <div class="chart-header">
                <p class="card-label">Evolución del peso</p>
                <div id="filters-peso" class="chart-filters">
                    <button class="filter-btn" data-range="week">Sem</button>
                    <button class="filter-btn active" data-range="month">Mes</button>
                    <button class="filter-btn" data-range="year">Año</button>
                </div>
            </div>
            <canvas id="grafica-peso" height="180"></canvas>
        </div>
        <?php else: ?>
        <div class="card" style="text-align:center;padding:32px;">
            <p style="color:var(--text2);margin-bottom:14px;">Todavía no hay registros de peso</p>
            <button class="btn-primary" onclick="abrirModal()">Registrar mi peso</button>
        </div>
        <?php endif; ?>
    </div>

    <!-- ═══ Tab MEDIDAS ═══ -->
    <div id="tab-medidas" class="tab-content">
        <?php if (!empty($historial_medidas)): ?>

        <div class="card" style="margin-bottom:12px;">
            <div class="chart-header">
                <p class="card-label">Pecho (cm)</p>
                <div id="filters-pecho" class="chart-filters">
                    <button class="filter-btn" data-range="week">Sem</button>
                    <button class="filter-btn active" data-range="month">Mes</button>
                    <button class="filter-btn" data-range="year">Año</button>
                </div>
            </div>
            <canvas id="grafica-pecho" height="150"></canvas>
        </div>

        <div class="card" style="margin-bottom:12px;">
            <div class="chart-header">
                <p class="card-label">Cintura (cm)</p>
                <div id="filters-cintura" class="chart-filters">
                    <button class="filter-btn" data-range="week">Sem</button>
                    <button class="filter-btn active" data-range="month">Mes</button>
                    <button class="filter-btn" data-range="year">Año</button>
                </div>
            </div>
            <canvas id="grafica-cintura" height="150"></canvas>
        </div>

        <div class="card" style="margin-bottom:12px;">
            <div class="chart-header">
                <p class="card-label">Brazo (cm)</p>
                <div id="filters-brazo" class="chart-filters">
                    <button class="filter-btn" data-range="week">Sem</button>
                    <button class="filter-btn active" data-range="month">Mes</button>
                    <button class="filter-btn" data-range="year">Año</button>
                </div>
            </div>
            <canvas id="grafica-brazo" height="150"></canvas>
        </div>

        <?php else: ?>
        <div class="card" style="text-align:center;padding:32px;">
            <p style="color:var(--text2);margin-bottom:14px;">Todavía no hay registros de medidas</p>
            <button class="btn-primary" onclick="abrirModal()">Registrar medidas</button>
        </div>
        <?php endif; ?>
    </div>

    <!-- ═══ Tab MARCAS / PROGRESO ═══ -->
    <div id="tab-marcas" class="tab-content">
        <?php if (!empty($marcas)): ?>

        <div id="vista-top5">
            <div class="card" style="margin-bottom:12px;">
                <p class="card-label">Récords recientes</p>
                <?php foreach ($marcas_top5 as $marca): ?>
                <div class="marca-row">
                    <div>
                        <p class="marca-nombre"><?= htmlspecialchars($marca['nombre']) ?></p>
                        <p class="marca-meta"><?= htmlspecialchars($marca['grupo_muscular']) ?> · <?= date('d/m/Y', strtotime($marca['fecha'])) ?></p>
                    </div>
                    <span class="badge-record"><?= $marca['peso_kg'] ?> kg × <?= $marca['repeticiones'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if (count($marcas) > 5): ?>
            <button class="btn-ver-todos" onclick="mostrarTodos()">
                Ver todos (<?= count($marcas) ?> récords) ▼
            </button>
            <?php endif; ?>
        </div>

        <div id="vista-todos" class="oculto">
            <div class="card" style="margin-bottom:12px;">
                <p class="card-label">Récords por grupo muscular</p>
                <?php foreach ($marcas_por_musculo as $grupo => $ejercicios): ?>
                <div class="musculo-bloque">
                    <div class="musculo-header" onclick="toggleMusculo(this)">
                        <p class="musculo-header-nombre"><?= htmlspecialchars($grupo) ?></p>
                        <div class="musculo-header-meta">
                            <span class="musculo-count"><?= count($ejercicios) ?> ejercicios</span>
                            <span class="musculo-flecha">▼</span>
                        </div>
                    </div>
                    <div class="musculo-contenido">
                        <div>
                            <?php foreach ($ejercicios as $i => $marca): ?>
                            <div class="marca-row<?= $i >= 5 ? ' oculto ejercicio-extra-'.preg_replace('/\s+/','-',strtolower($grupo)) : '' ?>">
                                <div>
                                    <p class="marca-nombre"><?= htmlspecialchars($marca['nombre']) ?></p>
                                    <p class="marca-meta"><?= date('d/m/Y', strtotime($marca['fecha'])) ?></p>
                                </div>
                                <span class="badge-record"><?= $marca['peso_kg'] ?> kg × <?= $marca['repeticiones'] ?></span>
                            </div>
                            <?php endforeach; ?>
                            <?php if (count($ejercicios) > 5): ?>
                            <button class="btn-ver-mas" onclick="verMasGrupo('<?= preg_replace('/\s+/','-',strtolower($grupo)) ?>', this)">
                                + Ver <?= count($ejercicios) - 5 ?> más
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <p class="card-label">Peso máximo y predicción 1RM</p>
                <?php foreach ($marcas_por_musculo as $grupo => $ejercicios): ?>
                <p style="font-family:var(--font-mono);font-size:10px;color:var(--text3);text-transform:uppercase;letter-spacing:0.8px;padding:10px 0 6px;border-bottom:0.5px solid var(--border);"><?= htmlspecialchars($grupo) ?></p>
                <?php foreach ($ejercicios as $i => $marca): ?>
                <div class="pesomax-pr<?= $i >= 5 ? ' oculto pesomax-extra-'.preg_replace('/\s+/','-',strtolower($grupo)) : '' ?>">
                    <?php if ($marca['gif_url']): ?>
                        <img src="<?= htmlspecialchars('../' . $marca['gif_url']) ?>"
                             alt=""
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                        <div class="pesomax-placeholder" style="display:none">💪</div>
                    <?php else: ?>
                        <div class="pesomax-placeholder">💪</div>
                    <?php endif; ?>
                    <p class="nombre-ejercicio">
                        <?= htmlspecialchars($marca['nombre']) ?>
                        <span><?= htmlspecialchars($marca['grupo_muscular']) ?></span>
                    </p>
                    <div class="stat-pr" style="margin-right:10px;">
                        <span class="stat-pr-valor"><?= $marca['peso_kg'] ?> kg</span>
                        <span class="stat-pr-label">Peso máx.</span>
                    </div>
                    <div class="stat-pr">
                        <span class="stat-pr-valor"><?= round($marca['peso_kg'] * (1 + $marca['repeticiones'] / 30), 1) ?> kg</span>
                        <span class="stat-pr-label">1RM pred.</span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (count($ejercicios) > 5): ?>
                <button class="btn-ver-mas" onclick="verMasPesomax('<?= preg_replace('/\s+/','-',strtolower($grupo)) ?>', this)">
                    + Ver <?= count($ejercicios) - 5 ?> más
                </button>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <button class="btn-ver-todos" style="margin-top:10px;background:var(--surface2);color:var(--text3);border-color:var(--border2);" onclick="ocultarTodos()">
                ▲ Colapsar vista
            </button>
        </div>

        <?php else: ?>
        <div class="card" style="text-align:center;padding:32px;">
            <p style="color:var(--text2);">Todavía no tienes récords. ¡Empieza a entrenar!</p>
        </div>
        <?php endif; ?>
    </div>

</main>

<!-- Modal registrar peso -->
<div class="modal-overlay" id="modal-peso">
    <div class="modal">
        <div class="modal-top">
            <h2>Registrar medidas</h2>
            <button class="modal-close" onclick="cerrarModal()">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="accion" value="guardar_peso">
            <label>Peso (kg) *</label>
            <input type="number" name="peso_kg" step="0.1" placeholder="78.5" required>
            <label>Pecho (cm)</label>
            <input type="number" name="pecho_cm" step="0.5" placeholder="Opcional">
            <label>Cintura (cm)</label>
            <input type="number" name="cintura_cm" step="0.5" placeholder="Opcional">
            <label>Brazo (cm)</label>
            <input type="number" name="brazo_cm" step="0.5" placeholder="Opcional">
            <button type="submit" class="btn-primary block" style="margin-top:14px;">Guardar</button>
            <button type="button" class="btn-ghost" onclick="cerrarModal()">Cancelar</button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<!-- Datos de PHP pasados a JS como variables globales -->
<script>
    const allPeso    = <?= json_encode($js_peso) ?>;
    const allMedidas = <?= json_encode($js_medidas) ?>;
    const HAY_PESO    = <?= json_encode(!empty($historial_peso)) ?>;
    const HAY_MEDIDAS = <?= json_encode(!empty($historial_medidas)) ?>;
</script>

<!-- Lógica del progreso -->
<script src="../assets/js/script_progreso.js"></script>

</body>
</html>