<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'crear') {
        $nombre      = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        $dias        = $_POST['dias'] ?? [];
        if (!empty($nombre)) {
            $stmt = $pdo->prepare("INSERT INTO rutina (usuario_id, nombre, descripcion) VALUES (?, ?, ?)");
            $stmt->execute([$usuario_id, $nombre, $descripcion]);
            $rutina_id = $pdo->lastInsertId();
            foreach ($dias as $dia) {
                $stmt = $pdo->prepare("INSERT INTO rutina_dia (rutina_id, dia_semana) VALUES (?, ?)");
                $stmt->execute([$rutina_id, $dia]);
            }
        }
    }
    if ($_POST['accion'] === 'activar') {
        $stmt = $pdo->prepare("UPDATE rutina SET activa = 1 WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$_POST['rutina_id'], $usuario_id]);
    }
    if ($_POST['accion'] === 'desactivar') {
        $stmt = $pdo->prepare("UPDATE rutina SET activa = 0 WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$_POST['rutina_id'], $usuario_id]);
    }
    if ($_POST['accion'] === 'eliminar') {
        $stmt = $pdo->prepare("DELETE FROM rutina WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$_POST['rutina_id'], $usuario_id]);
    }
    header('Location: rutinas.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM rutina WHERE usuario_id = ? ORDER BY creado_en DESC");
$stmt->execute([$usuario_id]);
$rutinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rutinas as &$r) {
    $stmt = $pdo->prepare("SELECT dia_semana FROM rutina_dia WHERE rutina_id = ?");
    $stmt->execute([$r['id']]);
    $r['dias'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $pdo->prepare("
        SELECT re.*, e.nombre, e.grupo_muscular, e.tipo
        FROM rutina_ejercicio re
        JOIN ejercicio e ON e.id = re.ejercicio_id
        WHERE re.rutina_id = ?
        ORDER BY re.orden ASC
    ");
    $stmt->execute([$r['id']]);
    $r['ejercicios'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
unset($r);

$stmt = $pdo->prepare("
    SELECT *, CASE WHEN usuario_id = ? THEN 1 ELSE 0 END as es_mio
    FROM ejercicio
    WHERE es_predefinido = 1 OR usuario_id = ?
    ORDER BY es_mio DESC, grupo_muscular, nombre
");
$stmt->execute([$usuario_id, $usuario_id]);
$biblioteca = $stmt->fetchAll(PDO::FETCH_ASSOC);

$dia_hoy    = strtolower(date('l'));
$dias_es    = ['monday'=>'lunes','tuesday'=>'martes','wednesday'=>'miercoles','thursday'=>'jueves','friday'=>'viernes','saturday'=>'sabado','sunday'=>'domingo'];
$dia_hoy_es = $dias_es[$dia_hoy];
$rutina_hoy     = null;
$ejercicios_hoy = [];

foreach ($rutinas as $r) {
    if ($r['activa'] && in_array($dia_hoy_es, $r['dias'])) {
        $rutina_hoy   = $r;
        $orden_semana = ['lunes'=>1,'martes'=>2,'miercoles'=>3,'jueves'=>4,'viernes'=>5,'sabado'=>6,'domingo'=>7];
        $dias_rutina  = $r['dias'];
        usort($dias_rutina, fn($a,$b) => $orden_semana[$a] - $orden_semana[$b]);
        $posicion       = array_search($dia_hoy_es, $dias_rutina);
        $numero_dia     = ($posicion !== false) ? $posicion + 1 : 1;
        $ejercicios_hoy = array_values(array_filter($r['ejercicios'], fn($ej) => str_starts_with($ej['dia_nombre'] ?? '', 'Dia ' . $numero_dia)));
        if (empty($ejercicios_hoy)) $ejercicios_hoy = $r['ejercicios'];
        break;
    }
}

$stmt = $pdo->prepare("SELECT rutina_id FROM sesion WHERE usuario_id = ? AND fecha = CURDATE()");
$stmt->execute([$usuario_id]);
$rutinas_entrenadas_hoy = $stmt->fetchAll(PDO::FETCH_COLUMN);

$ya_entreno_hoy_rutina = $rutina_hoy && in_array($rutina_hoy['id'], $rutinas_entrenadas_hoy);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rutinas — Gymtracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/style_rutinas.css">
    <?php require_once '../includes/head_meta.php'; ?>
</head>
<body class="page-rutinas">
<?php require_once '../includes/header.php'; ?>

<main class="container">

    <div class="page-header">
        <h1>Mis rutinas</h1>
        <button class="btn-primary" onclick="abrirModalCrear()">Crear rutina</button>
    </div>

    <?php if (empty($rutinas)): ?>
    <div class="card" style="text-align:center;padding:32px;">
        <p style="color:var(--text2);margin-bottom:14px;">Todavía no tienes rutinas</p>
        <button class="btn-primary" onclick="abrirModalCrear()">Crear mi primera rutina</button>
    </div>
    <?php endif; ?>

    <?php foreach ($rutinas as $r): ?>
    <div class="rutina-card">
        <div class="rutina-card-top">
            <p class="rutina-nombre"><?= htmlspecialchars($r['nombre']) ?></p>
            <span class="badge <?= $r['activa'] ? 'badge-activa' : 'badge-inactiva' ?>">
                <?= $r['activa'] ? 'activa' : 'guardada' ?>
            </span>
        </div>

        <?php if ($r['descripcion']): ?>
            <p class="rutina-descripcion"><?= htmlspecialchars($r['descripcion']) ?></p>
        <?php endif; ?>

        <div style="margin-bottom:10px;">
            <?php foreach ($r['dias'] as $dia): ?>
                <span class="tag-dia"><?= ucfirst($dia) ?></span>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($r['ejercicios'])): ?>
        <hr class="divider">
        <?php
        $ejercicios_por_dia = [];
        foreach ($r['ejercicios'] as $ej) {
            $dia_key = $ej['dia_nombre'] ?? 'Sin día';
            $ejercicios_por_dia[$dia_key][] = $ej;
        }
        ?>
        <?php foreach ($ejercicios_por_dia as $dia_nombre => $ejercicios_dia): ?>
        <div class="dia-bloque">
            <?php if ($dia_nombre !== 'Sin día'): ?>
            <div class="dia-header" onclick="toggleDia(this)">
                <p class="dia-header-nombre"><?= htmlspecialchars($dia_nombre) ?></p>
                <div class="dia-header-meta">
                    <span class="dia-header-count"><?= count($ejercicios_dia) ?> ejercicios</span>
                    <span class="dia-flecha">▼</span>
                </div>
            </div>
            <?php endif; ?>
            <div class="dia-contenido" style="max-height:<?= $dia_nombre === 'Sin día' ? 'none' : '0' ?>;">
                <div>
                    <?php foreach ($ejercicios_dia as $ej):
                        $unidad = ($ej['tipo'] ?? 'reps') === 'tiempo' ? 'seg' : 'reps';
                    ?>
                    <div class="ejercicio-item">
                        <div>
                            <p class="ejercicio-nombre"><?= htmlspecialchars($ej['nombre']) ?></p>
                            <p class="ejercicio-meta">
                                <?= $ej['series'] ?> × <?= $ej['repeticiones'] ?> <?= $unidad ?> · <?= $ej['peso_kg'] ?> kg
                            </p>
                        </div>
                        <div style="display:flex;gap:6px;align-items:center;">
                            <button class="btn-edit" onclick="abrirModalEditar(
                                <?= $ej['id'] ?>,
                                '<?= htmlspecialchars($ej['nombre'], ENT_QUOTES) ?>',
                                <?= $ej['series'] ?>,
                                <?= $ej['repeticiones'] ?>,
                                <?= $ej['peso_kg'] ?>
                            )">✎</button>
                            <button class="btn-remove" onclick="eliminarEjercicio(<?= $ej['id'] ?>)">✕</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <button class="btn-add" onclick="abrirBiblioteca(<?= $r['id'] ?>)">+ Añadir ejercicio</button>
        <?php else: ?>
        <button class="btn-add" onclick="abrirBiblioteca(<?= $r['id'] ?>)">+ Añadir ejercicio</button>
        <?php endif; ?>

        <div class="rutina-acciones">
            <?php if (!$r['activa']): ?>
            <form method="POST" style="flex:1;">
                <input type="hidden" name="accion" value="activar">
                <input type="hidden" name="rutina_id" value="<?= $r['id'] ?>">
                <button type="submit" class="btn-accion btn-activar">Activar</button>
            </form>
            <?php else: ?>
            <form method="POST" style="flex:1;">
                <input type="hidden" name="accion" value="desactivar">
                <input type="hidden" name="rutina_id" value="<?= $r['id'] ?>">
                <button type="submit" class="btn-accion btn-desactivar">Desactivar</button>
            </form>
            <?php endif; ?>

            <?php if (in_array($r['id'], $rutinas_entrenadas_hoy)): ?>
                <button class="btn-accion btn-completado" disabled>✓ Completado hoy</button>
            <?php else: ?>
                <button class="btn-accion btn-entrenar"
                    onclick="window.location.href='entreno.php?rutina_id=<?= $r['id'] ?>'">
                    Entrenar
                </button>
            <?php endif; ?>

            <form method="POST" onsubmit="return confirm('¿Eliminar esta rutina?')">
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="rutina_id" value="<?= $r['id'] ?>">
                <button type="submit" class="btn-accion btn-eliminar">Eliminar</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>

    <button class="btn-crear-ejercicio" onclick="abrirModalEjercicio()">+ Crear ejercicio propio</button>

    <!-- Generador IA -->
    <div class="generador-ia">
        <h3>Generar rutina con IA</h3>
        <div class="generador-campos">
            <div>
                <label>Objetivo</label>
                <select id="ia-objetivo">
                    <option value="hipertrofia">Hipertrofia</option>
                    <option value="fuerza">Fuerza</option>
                    <option value="perdida de peso">Pérdida de peso</option>
                    <option value="resistencia">Resistencia</option>
                    <option value="definicion">Definición</option>
                </select>
            </div>
            <div>
                <label>Nivel</label>
                <select id="ia-nivel">
                    <option value="principiante">Principiante</option>
                    <option value="intermedio">Intermedio</option>
                    <option value="avanzado">Avanzado</option>
                </select>
            </div>
            <div>
                <label>Días / semana</label>
                <select id="ia-dias">
                    <option value="3">3 días</option>
                    <option value="4">4 días</option>
                    <option value="5">5 días</option>
                    <option value="6">6 días</option>
                </select>
            </div>
            <div>
                <label>Equipo</label>
                <select id="ia-equipo">
                    <option value="gimnasio completo">Gimnasio completo</option>
                    <option value="solo mancuernas">Solo mancuernas</option>
                    <option value="en casa sin equipo">En casa sin equipo</option>
                    <option value="barras y mancuernas">Barras y mancuernas</option>
                </select>
            </div>
        </div>
        <textarea id="ia-notas" placeholder="Notas adicionales (preferencias...)"></textarea>
        <label class="ia-label">Lesiones o limitaciones</label>
        <textarea id="ia-lesiones" placeholder="Ej: rodilla derecha, lumbar, hombro izquierdo..."></textarea>
        <button onclick="generarRutinaIA()" id="btn-ia">Generar rutina con IA</button>
        <div id="ia-resultado"></div>
    </div>

    <!-- Sesión de hoy -->
    <?php if ($rutina_hoy): ?>
    <div class="sesion-hoy">
        <div class="sesion-hoy-top">
            <p class="sesion-hoy-label">Sesión de hoy</p>
            <?php if ($ya_entreno_hoy_rutina): ?>
                <span class="badge badge-activa">✓ Completado</span>
            <?php else: ?>
                <a href="entreno.php?rutina_id=<?= $rutina_hoy['id'] ?>" class="btn-primary">Entrenar →</a>
            <?php endif; ?>
        </div>

        <p class="sesion-hoy-nombre"><?= htmlspecialchars($rutina_hoy['nombre']) ?></p>

        <?php if ($ya_entreno_hoy_rutina): ?>
            <div class="sesion-completada">
                <p class="sesion-completada-emoji">💪</p>
                <p class="sesion-completada-titulo">¡Entrenamiento completado!</p>
                <p class="sesion-completada-sub">Has terminado el <?= htmlspecialchars($ejercicios_hoy[0]['dia_nombre'] ?? 'entrenamiento') ?> de hoy. ¡Buen trabajo!</p>
            </div>
        <?php else: ?>
            <?php if (!empty($ejercicios_hoy)): ?>
                <p class="sesion-hoy-dia"><?= htmlspecialchars($ejercicios_hoy[0]['dia_nombre'] ?? '') ?></p>
                <?php foreach ($ejercicios_hoy as $ej):
                    $unidad = ($ej['tipo'] ?? 'reps') === 'tiempo' ? 'seg' : 'reps';
                ?>
                <div class="ejercicio-item">
                    <p class="ejercicio-nombre"><?= htmlspecialchars($ej['nombre']) ?></p>
                    <p class="ejercicio-meta">
                        <?= $ej['series'] ?> × <?= $ej['repeticiones'] ?> <?= $unidad ?> · <?= $ej['peso_kg'] ?> kg
                    </p>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="font-size:13px;color:var(--text3);">No hay ejercicios asignados para hoy.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <p style="color:var(--text3);font-size:13px;margin-top:20px;font-family:var(--font-mono);">
        No tienes rutina activa para hoy.
    </p>
    <?php endif; ?>

</main>

<!-- Modal crear rutina -->
<div class="modal-overlay" id="modal-crear">
    <div class="modal">
        <div class="modal-top">
            <h2>Nueva rutina</h2>
            <button class="modal-close" onclick="cerrarModalCrear()">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="accion" value="crear">
            <label>Nombre</label>
            <input type="text" name="nombre" placeholder="Ej: Push Pull Legs" required>
            <label>Descripción (opcional)</label>
            <input type="text" name="descripcion" placeholder="Ej: Rutina de 6 días">
            <label>Días de entrenamiento</label>
            <div class="dias-grid">
                <?php foreach (['lunes','martes','miercoles','jueves','viernes','sabado','domingo'] as $dia): ?>
                <label>
                    <input type="checkbox" name="dias[]" value="<?= $dia ?>" style="display:none;"
                           onchange="this.nextElementSibling.classList.toggle('selected', this.checked)">
                    <span class="dia-btn"><?= ucfirst($dia) ?></span>
                </label>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn-primary block">Crear rutina</button>
        </form>
    </div>
</div>

<!-- Modal biblioteca -->
<div class="modal-overlay" id="modal-biblioteca">
    <div class="modal">
        <div class="modal-top">
            <h2>Añadir ejercicio</h2>
            <button class="modal-close" onclick="cerrarBiblioteca()">✕</button>
        </div>
        <input type="text" id="buscador-ejercicio" class="buscador"
               placeholder="Buscar ejercicio..." oninput="filtrarEjercicios()">
        <div id="filtros-musculo" class="filtros">
            <button class="filtro-btn active" onclick="filtrarMusculo('todos', this)">Todos</button>
            <?php foreach (['Pecho','Espalda','Hombros','Pierna','Bíceps','Tríceps','Antebrazo','Core','Cardio'] as $m): ?>
            <button class="filtro-btn" onclick="filtrarMusculo('<?= $m ?>', this)"><?= $m ?></button>
            <?php endforeach; ?>
        </div>
        <div id="lista-biblioteca">
            <?php foreach ($biblioteca as $ej): ?>
            <div class="biblioteca-item"
                 data-nombre="<?= strtolower($ej['nombre']) ?>"
                 data-musculo="<?= htmlspecialchars($ej['grupo_muscular']) ?>">
                <div style="display:flex;align-items:center;">
                    <div class="img-ejercicio" onclick="ampliarImagen('<?= BASE_URL . htmlspecialchars($ej['gif_url']) ?>')">
                        <img src="<?= BASE_URL . htmlspecialchars($ej['gif_url']) ?>" alt="">
                    </div>
                    <div>
                        <p class="biblioteca-nombre">
                            <?= htmlspecialchars($ej['nombre']) ?>
                            <?php if ($ej['es_mio']): ?>
                                <span class="badge-mio">mío</span>
                            <?php endif; ?>
                        </p>
                        <p class="biblioteca-musculo"><?= htmlspecialchars($ej['grupo_muscular']) ?></p>
                    </div>
                </div>
                <button class="btn-add" onclick="anadirEjercicio(<?= $ej['id'] ?>, '<?= htmlspecialchars($ej['nombre']) ?>')">+ Añadir</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button class="btn-scroll-top" id="btn-arriba-modal" onclick="subirModal()">↑</button>
    </div>
    <div id="modal-imagen" class="modal-overlay" onclick="cerrarImagen()">
        <img id="img-ampliada" src="" alt="Ejercicio">
    </div>
</div>

<!-- Modal crear ejercicio propio -->
<div class="modal-overlay" id="modal-ejercicio-propio">
    <div class="modal">
        <div class="modal-top">
            <h2>Nuevo ejercicio</h2>
            <button class="modal-close" onclick="cerrarModalEjercicio()">✕</button>
        </div>
        <label>Nombre *</label>
        <input type="text" id="ej-nombre" placeholder="Ej: Curl con barra Z">
        <label>Grupo muscular *</label>
        <select id="ej-musculo">
            <option value="">— Selecciona —</option>
            <?php foreach (['Pecho','Espalda','Hombros','Pierna','Bíceps','Tríceps','Antebrazo','Core','Cardio','Otro','Calentamiento'] as $m): ?>
            <option value="<?= $m ?>"><?= $m ?></option>
            <?php endforeach; ?>
        </select>
        <label>Tipo de medida *</label>
        <select id="ej-tipo">
            <option value="reps">Por repeticiones</option>
            <option value="tiempo">Por tiempo (segundos)</option>
        </select>
        <label>Descripción (opcional)</label>
        <textarea id="ej-descripcion" placeholder="Cómo ejecutar el ejercicio..."></textarea>
        <label>Imagen / GIF (opcional)</label>
        <label for="ej-gif-file" class="gif-upload-label">
            <span style="font-size:18px;"></span>
            <span id="ej-gif-label">Seleccionar archivo...</span>
        </label>
        <input type="file" id="ej-gif-file" accept="image/*,.gif" style="display:none;" onchange="previsualizarGif(this)">
        <img id="ej-gif-preview" src="" alt="">
        <p id="ej-error"></p>
        <button onclick="guardarEjercicioPropio()" class="btn-primary block" style="margin-top:8px;">
            Guardar ejercicio
        </button>
    </div>
</div>

<!-- Modal editar ejercicio -->
<div class="modal-overlay" id="modal-editar-ejercicio">
    <div class="modal">
        <div class="modal-top">
            <h2>Editar ejercicio</h2>
            <button class="modal-close" onclick="cerrarModalEditar()">✕</button>
        </div>
        <p id="editar-nombre-ejercicio" style="font-weight:600;margin-bottom:16px;color:var(--text1);"></p>
        <input type="hidden" id="editar-ejercicio-id">
        <label>Series</label>
        <input type="number" id="editar-series" min="1" max="20">
        <label>Repeticiones / segundos</label>
        <input type="number" id="editar-reps" min="1" max="300">
        <label>Peso (kg)</label>
        <input type="number" id="editar-peso" min="0" step="0.5">
        <button onclick="guardarEdicionEjercicio()" class="btn-primary block" style="margin-top:8px;">
            Guardar cambios
        </button>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<!-- Lógica de rutinas -->
<script src="../assets/js/script_rutinas.js"></script>

</body>
</html>