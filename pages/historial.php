<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

$usuario_id = $_SESSION['usuario_id'];

// ── Rutinas del usuario para el filtro ──
$stmt = $pdo->prepare("SELECT id, nombre FROM rutina WHERE usuario_id = ? ORDER BY nombre ASC");
$stmt->execute([$usuario_id]);
$rutinas_filtro = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Todas las sesiones del usuario (Vue filtra en cliente) ──
$stmt = $pdo->prepare("
    SELECT s.id, s.fecha, s.duracion_min, s.notas,
           r.nombre as rutina_nombre, r.id as rutina_id,
           COUNT(DISTINCT ss.ejercicio_id) as num_ejercicios,
           COALESCE(SUM(ss.peso_kg * ss.repeticiones), 0) as volumen
    FROM sesion s
    LEFT JOIN rutina r ON r.id = s.rutina_id
    LEFT JOIN sesion_serie ss ON ss.sesion_id = s.id
    WHERE s.usuario_id = :uid
    GROUP BY s.id, s.fecha, s.duracion_min, s.notas, r.nombre, r.id
    ORDER BY s.fecha DESC, s.id DESC
");
$stmt->execute([':uid' => $usuario_id]);
$sesiones_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Detalle de todas las sesiones ──
$detalle = [];
if (!empty($sesiones_raw)) {
    $ids = array_column($sesiones_raw, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("
        SELECT ss.sesion_id, e.nombre, e.grupo_muscular, e.tipo,
               ss.num_serie, ss.repeticiones, ss.peso_kg
        FROM sesion_serie ss
        JOIN ejercicio e ON e.id = ss.ejercicio_id
        WHERE ss.sesion_id IN ($placeholders)
        ORDER BY ss.sesion_id, e.nombre, ss.num_serie ASC
    ");
    $stmt->execute($ids);
    $series_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($series_raw as $row) {
        $sid = $row['sesion_id'];
        $nom = $row['nombre'];
        if (!isset($detalle[$sid][$nom])) {
            $detalle[$sid][$nom] = [
                'grupo_muscular' => $row['grupo_muscular'],
                'tipo'           => $row['tipo'],
                'series'         => []
            ];
        }
        $detalle[$sid][$nom]['series'][] = [
            'num'  => $row['num_serie'],
            'reps' => $row['repeticiones'],
            'peso' => $row['peso_kg'],
        ];
    }
}

// ── Construir array final para Vue ──
$sesiones_json = [];
$dias_es  = ['1'=>'Lunes','2'=>'Martes','3'=>'Miércoles','4'=>'Jueves','5'=>'Viernes','6'=>'Sábado','7'=>'Domingo'];
$meses_es = ['1'=>'ene','2'=>'feb','3'=>'mar','4'=>'abr','5'=>'may','6'=>'jun',
             '7'=>'jul','8'=>'ago','9'=>'sep','10'=>'oct','11'=>'nov','12'=>'dic'];

foreach ($sesiones_raw as $s) {
    $dt       = new DateTime($s['fecha']);
    $dia_sem  = $dt->format('N');
    $mes_num  = (string)(int)$dt->format('n');
    $fecha_fmt = $dias_es[$dia_sem] . ', ' . $dt->format('j') . ' ' . $meses_es[$mes_num] . ' ' . $dt->format('Y');
    $mes_key  = $dt->format('Y-m');

    $ejercicios = [];
    foreach ($detalle[$s['id']] ?? [] as $nombre_ej => $ej_data) {
        $ejercicios[] = [
            'nombre'         => $nombre_ej,
            'grupo_muscular' => $ej_data['grupo_muscular'],
            'tipo'           => $ej_data['tipo'],
            'series'         => $ej_data['series'],
        ];
    }

    $sesiones_json[] = [
        'id'            => (int)$s['id'],
        'fecha'         => $s['fecha'],
        'fecha_fmt'     => $fecha_fmt,
        'mes_key'       => $mes_key,
        'duracion_min'  => (int)$s['duracion_min'],
        'notas'         => $s['notas'],
        'rutina_nombre' => $s['rutina_nombre'] ?? null,
        'rutina_id'     => $s['rutina_id'] ? (int)$s['rutina_id'] : null,
        'num_ejercicios'=> (int)$s['num_ejercicios'],
        'volumen'       => (float)$s['volumen'],
        'ejercicios'    => $ejercicios,
    ];
}

// ── Stats generales ──
$total_sesiones = count($sesiones_raw);

$stmt = $pdo->prepare("SELECT COALESCE(SUM(duracion_min),0) FROM sesion WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$total_minutos = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(ss.peso_kg * ss.repeticiones), 0)
    FROM sesion_serie ss
    JOIN sesion s ON s.id = ss.sesion_id
    WHERE s.usuario_id = ?
");
$stmt->execute([$usuario_id]);
$volumen_total = $stmt->fetchColumn();

// ── Meses disponibles ──
$meses_nombres = ['01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril',
                  '05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto',
                  '09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'];

$meses_disponibles = [];
$meses_vistos = [];
foreach ($sesiones_json as $s) {
    if (!in_array($s['mes_key'], $meses_vistos)) {
        $meses_vistos[] = $s['mes_key'];
        [$y, $m] = explode('-', $s['mes_key']);
        $meses_disponibles[] = [
            'key'    => $s['mes_key'],
            'label'  => ($meses_nombres[$m] ?? $m) . ' ' . $y,
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial — Gymtracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/style_historial.css">
    <?php require_once '../includes/head_meta.php'; ?>
    <!-- Vue 3 CDN -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
</head>
<body>

<?php require_once '../includes/header.php'; ?>

<main class="container">

    <div id="app">

        <div class="page-header">
            <h1>Historial</h1>
        </div>

        <!-- Stats rápidas -->
        <div class="metric-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:16px;">
            <div class="metric-card">
                <p class="metric-label">Total</p>
                <p class="metric-value"><?= $total_sesiones ?></p>
                <p class="metric-sub">sesiones</p>
            </div>
            <div class="metric-card">
                <p class="metric-label">Horas</p>
                <p class="metric-value"><?= round($total_minutos / 60, 1) ?></p>
                <p class="metric-sub">entrenadas</p>
            </div>
            <div class="metric-card">
                <p class="metric-label">Volumen</p>
                <?php if ($volumen_total >= 1000): ?>
                    <p class="metric-value"><?= round($volumen_total / 1000, 1) ?></p>
                    <p class="metric-sub">toneladas</p>
                <?php else: ?>
                    <p class="metric-value"><?= number_format($volumen_total, 0, ',', '.') ?></p>
                    <p class="metric-sub">kg totales</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- ──────────────────────────────────────────
             FILTROS — controlados por Vue
        ────────────────────────────────────────── -->
        <div class="filtros-wrap">
            <!-- Filtro rutina -->
            <select v-model="filtroRutina" class="filtro-select">
                <option value="">Todas las rutinas</option>
                <?php foreach ($rutinas_filtro as $r): ?>
                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                <?php endforeach; ?>
            </select>

            <!-- Filtro mes -->
            <select v-model="filtroMes" class="filtro-select">
                <option value="">Todos los meses</option>
                <?php foreach ($meses_disponibles as $mes): ?>
                <option value="<?= $mes['key'] ?>"><?= $mes['label'] ?></option>
                <?php endforeach; ?>
            </select>

            <!-- Botón limpiar filtros -->
            <a v-if="filtroRutina || filtroMes"
               href="#"
               class="btn-limpiar"
               @click.prevent="limpiarFiltros">✕ Limpiar</a>
        </div>

        <!-- Contador resultados -->
        <p v-if="filtroRutina || filtroMes" class="filtro-resultado">
            <span>{{ sesionesFiltradas.length }}</span>
            resultado{{ sesionesFiltradas.length !== 1 ? 's' : '' }}
            encontrado{{ sesionesFiltradas.length !== 1 ? 's' : '' }}
        </p>

        <!-- ──────────────────────────────────────────
             LISTA DE SESIONES
        ────────────────────────────────────────── -->

        <!-- Estado vacío -->
        <div v-if="sesionesPagina.length === 0" class="card empty-state">
            <p class="empty-icon">🏋️</p>
            <p class="empty-title">
                {{ (filtroRutina || filtroMes) ? 'Sin resultados' : 'Aún no hay entrenamientos' }}
            </p>
            <p class="empty-sub">
                {{ (filtroRutina || filtroMes) ? 'Prueba con otros filtros' : 'Completa tu primer entreno para verlo aquí' }}
            </p>
        </div>

        <!-- Tarjetas de sesión -->
        <div v-for="sesion in sesionesPagina" :key="sesion.id" class="sesion-card">

            <!-- Cabecera clicable -->
            <div class="sesion-header" @click="toggleSesion(sesion.id)">
                <div style="flex:1;min-width:0;">
                    <p class="sesion-fecha">{{ sesion.fecha_fmt }}</p>
                    <p class="sesion-rutina">{{ sesion.rutina_nombre || 'Entrenamiento libre' }}</p>
                    <div class="sesion-chips">
                        <span v-if="sesion.duracion_min" class="chip">⏱ {{ sesion.duracion_min }} min</span>
                        <span v-if="sesion.num_ejercicios > 0" class="chip">{{ sesion.num_ejercicios }} ejercicios</span>
                        <span v-if="sesion.volumen > 0" class="chip chip-green">
                            {{ sesion.volumen >= 1000
                                ? (sesion.volumen / 1000).toFixed(1) + ' t'
                                : formatNum(sesion.volumen) + ' kg' }}
                        </span>
                    </div>
                </div>
                <span class="sesion-flecha" :class="{ abierto: abiertos.includes(sesion.id) }">▼</span>
            </div>

            <!-- Detalle expandible -->
            <div class="sesion-detalle"
                 :style="{ maxHeight: abiertos.includes(sesion.id) ? alturas[sesion.id] + 'px' : '0' }"
                 :ref="el => { if(el) detalleRefs[sesion.id] = el }">
                <div class="sesion-detalle-inner">

                    <template v-if="sesion.ejercicios.length > 0">
                        <div v-for="ej in sesion.ejercicios" :key="ej.nombre" class="ejercicio-bloque">
                            <div class="ejercicio-titulo">
                                {{ ej.nombre }}
                                <span class="ejercicio-musculo">{{ ej.grupo_muscular }}</span>
                            </div>
                            <div class="series-grid">
                                <div v-for="serie in ej.series" :key="serie.num" class="serie-pill">
                                    <p class="serie-num">Serie {{ serie.num }}</p>
                                    <p class="serie-dato">{{ serie.reps }} {{ ej.tipo === 'tiempo' ? 'seg' : 'reps' }}</p>
                                    <p v-if="serie.peso > 0"
                                       class="serie-dato"
                                       style="font-size:11px;color:var(--text3);font-weight:400;">
                                        {{ serie.peso }} kg
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>
                    <p v-else style="font-size:13px;color:var(--text3);margin-top:10px;">Sin series registradas.</p>

                    <div v-if="sesion.notas" class="notas-sesion">{{ sesion.notas }}</div>
                </div>
            </div>
        </div>

        <!-- ──────────────────────────────────────────
             PAGINACIÓN — controlada por Vue
        ────────────────────────────────────────── -->
        <div v-if="totalPaginas > 1" class="paginacion">
            <a href="#"
               class="pag-btn"
               :class="{ disabled: paginaActual <= 1 }"
               @click.prevent="irPagina(paginaActual - 1)">← Anterior</a>

            <template v-for="p in paginasVisibles" :key="p">
                <span v-if="p === '...'" class="pag-btn disabled">…</span>
                <a v-else
                   href="#"
                   class="pag-btn"
                   :class="{ active: p === paginaActual }"
                   @click.prevent="irPagina(p)">{{ p }}</a>
            </template>

            <a href="#"
               class="pag-btn"
               :class="{ disabled: paginaActual >= totalPaginas }"
               @click.prevent="irPagina(paginaActual + 1)">Siguiente →</a>
        </div>

    </div><!-- #app -->

</main>

<?php require_once '../includes/footer.php'; ?>

<!-- ── Datos inyectados desde PHP → JS global ── -->
<script>
    const SESIONES = <?= json_encode($sesiones_json, JSON_UNESCAPED_UNICODE) ?>;
</script>

<!-- Lógica Vue -->
<script src="../assets/js/script_historial.js"></script>

</body>
</html>