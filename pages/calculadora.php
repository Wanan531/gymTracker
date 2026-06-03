<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

$usuario_id = $_SESSION['usuario_id'];

// Datos del usuario para prellenar
$stmt = $pdo->prepare("SELECT peso_kg, altura_cm, fecha_nacimiento FROM usuario WHERE id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$peso_usuario   = $usuario['peso_kg']   ?? '';
$altura_usuario = $usuario['altura_cm'] ?? '';
$edad_usuario   = '';

// Si tiene fecha de nacimiento calcula la edad, si no, la deja vacía
if (!empty($usuario['fecha_nacimiento'])) {
    $edad_usuario = (new DateTime($usuario['fecha_nacimiento']))->diff(new DateTime())->y;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadoras — Gymtracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/style_calculadora.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <?php require_once '../includes/head_meta.php'; ?>
</head>
<body
    data-peso="<?= htmlspecialchars($peso_usuario) ?>"
    data-altura="<?= htmlspecialchars($altura_usuario) ?>"
    data-edad="<?= htmlspecialchars($edad_usuario) ?>">

<?php require_once '../includes/header.php'; ?>

<main class="container">

    <div class="page-header">
        <h1>Calculadoras</h1>
    </div>

    <!-- Menú de pestañas -->
    <div class="tabs">
        <button class="tab active" onclick="cambiarTab('1rm', this)">1RM</button>
        <button class="tab" onclick="cambiarTab('imc', this)">IMC</button>
        <button class="tab" onclick="cambiarTab('tdee', this)">TDEE</button>
        <button class="tab" onclick="cambiarTab('macros', this)">Macros</button>
    </div>

    <!-- ══════════════ TAB 1RM ══════════════ -->
    <div id="tab-1rm" class="tab-content active">
        <div class="calc-card">
            <h2>Repetición máxima (1RM)</h2>
            <p class="calc-desc">Estima el peso máximo que puedes levantar una sola vez en cualquier ejercicio.</p>

            <label>Peso levantado (kg)</label>
            <input type="number" id="rm-peso" placeholder="Ej: 80" min="1" step="0.5"
                   value="<?= htmlspecialchars($peso_usuario) ?>">

            <label>Repeticiones realizadas</label>
            <input type="number" id="rm-reps" placeholder="Ej: 8" min="1" max="36">

            <label>Fórmula</label>
            <select id="rm-formula">
                <option value="epley">Epley (recomendada)</option>
                <option value="brzycki">Brzycki</option>
                <option value="lombardi">Lombardi</option>
                <option value="mayhew">Mayhew</option>
            </select>

            <button class="btn-calcular" onclick="calcular1RM()">Calcular 1RM</button>

            <div class="resultado" id="res-1rm">
                <div class="resultado-principal">
                    <p class="resultado-valor" id="res-1rm-valor">—</p>
                    <p class="resultado-label">kg (1RM estimado)</p>
                </div>
                <div class="resultado-grid" id="res-1rm-tabla"></div>
                <p class="resultado-nota">
                    Estas cifras son estimaciones. El 1RM real puede variar según el ejercicio, tu estado físico y el día.
                </p>
            </div>
        </div>
    </div>

    <!-- ══════════════ TAB IMC ══════════════ -->
    <div id="tab-imc" class="tab-content">
        <div class="calc-card">
            <h2>Índice de masa corporal (IMC)</h2>
            <p class="calc-desc">Relaciona tu peso con tu altura para estimar si estás en un rango de peso saludable.</p>

            <label>Peso (kg)</label>
            <input type="number" id="imc-peso" placeholder="Ej: 75" min="30" step="0.1"
                   value="<?= htmlspecialchars($peso_usuario) ?>">

            <label>Altura (cm)</label>
            <input type="number" id="imc-altura" placeholder="Ej: 178" min="100" max="250"
                   value="<?= htmlspecialchars($altura_usuario) ?>">

            <button class="btn-calcular" onclick="calcularIMC()">Calcular IMC</button>

            <div class="resultado" id="res-imc">
                <div class="resultado-principal">
                    <p class="resultado-valor" id="res-imc-valor">—</p>
                    <p class="resultado-label" id="res-imc-cat">kg/m²</p>
                </div>
                <div class="imc-barra-wrap">
                    <div class="imc-barra">
                        <div class="imc-marcador" id="imc-marcador" style="left:0%"></div>
                    </div>
                    <div class="imc-categorias">
                        <span>Bajo peso</span>
                        <span>Normal</span>
                        <span>Sobrepeso</span>
                        <span>Obesidad</span>
                    </div>
                </div>
                <div class="resultado-grid" style="margin-top:14px;">
                    <div class="resultado-item">
                        <p class="resultado-item-valor" id="imc-peso-ideal">—</p>
                        <p class="resultado-item-label">Peso ideal (kg)</p>
                    </div>
                    <div class="resultado-item">
                        <p class="resultado-item-valor" id="imc-rango">—</p>
                        <p class="resultado-item-label">Rango normal</p>
                    </div>
                </div>
                <p class="resultado-nota">
                    El IMC no distingue entre masa muscular y grasa. Atletas con mucha masa muscular pueden tener IMC elevado sin ser obesos.
                </p>
            </div>
        </div>
    </div>

    <!-- ══════════════ TAB TDEE ══════════════ -->
    <div id="tab-tdee" class="tab-content">
        <div class="calc-card">
            <h2>Gasto calórico diario (TDEE)</h2>
            <p class="calc-desc">Calcula las calorías que quemas al día según tu metabolismo basal y nivel de actividad.</p>

            <label>Género</label>
            <div class="genero-grid">
                <button class="genero-btn selected" id="tdee-hombre" onclick="seleccionarGenero('hombre')">Hombre</button>
                <button class="genero-btn" id="tdee-mujer"  onclick="seleccionarGenero('mujer')">Mujer</button>
            </div>

            <label>Edad (años)</label>
            <input type="number" id="tdee-edad" placeholder="Ej: 25" min="15" max="90"
                   value="<?= htmlspecialchars($edad_usuario) ?>">

            <label>Peso (kg)</label>
            <input type="number" id="tdee-peso" placeholder="Ej: 75" min="30" step="0.1"
                   value="<?= htmlspecialchars($peso_usuario) ?>">

            <label>Altura (cm)</label>
            <input type="number" id="tdee-altura" placeholder="Ej: 178" min="100" max="250"
                   value="<?= htmlspecialchars($altura_usuario) ?>">

            <label>Nivel de actividad</label>
            <select id="tdee-actividad">
                <option value="1.2">Sedentario (sin ejercicio)</option>
                <option value="1.375">Ligero (1-3 días/semana)</option>
                <option value="1.55" selected>Moderado (3-5 días/semana)</option>
                <option value="1.725">Activo (6-7 días/semana)</option>
                <option value="1.9">Muy activo (2 veces al día)</option>
            </select>

            <button class="btn-calcular" onclick="calcularTDEE()">Calcular TDEE</button>

            <div class="resultado" id="res-tdee">
                <div class="resultado-principal">
                    <p class="resultado-valor" id="res-tdee-valor">—</p>
                    <p class="resultado-label">kcal / día (TDEE)</p>
                </div>
                <div class="resultado-grid" id="res-tdee-grid"></div>
                <p class="resultado-nota" id="res-tdee-nota"></p>
            </div>
        </div>
    </div>

    <!-- ══════════════ TAB MACROS ══════════════ -->
    <div id="tab-macros" class="tab-content">
        <div class="calc-card">
            <h2>Distribución de macros</h2>
            <p class="calc-desc">Calcula proteínas, carbohidratos y grasas según tu objetivo y calorías diarias.</p>

            <label>Calorías diarias (kcal)</label>
            <input type="number" id="mac-calorias" placeholder="Ej: 2500" min="1000" step="50">

            <label>Peso corporal (kg)</label>
            <input type="number" id="mac-peso" placeholder="Ej: 75" min="30" step="0.1"
                   value="<?= htmlspecialchars($peso_usuario) ?>">

            <label>Objetivo</label>
            <select id="mac-objetivo">
                <option value="volumen">Volumen / Ganar músculo</option>
                <option value="mantenimiento" selected>Mantenimiento</option>
                <option value="definicion">Definición / Perder grasa</option>
            </select>

            <button class="btn-calcular" onclick="calcularMacros()">Calcular macros</button>

            <div class="resultado" id="res-macros">

                <!-- Gráfica doughnut + leyenda -->
                <div class="macro-chart-wrap">
                    <div class="macro-chart-canvas">
                        <canvas id="grafica-macros"></canvas>
                        <div class="macro-chart-center">
                            <span class="macro-chart-center-val" id="mac-chart-total">—</span>
                            <span class="macro-chart-center-label">kcal</span>
                        </div>
                    </div>
                    <div class="macro-leyenda" id="mac-leyenda"></div>
                </div>

                <!-- Barras de porcentaje -->
                <div style="margin-top:4px;" id="res-mac-barras"></div>

                <!-- Grid de gramos -->
                <div class="resultado-grid" id="res-mac-grid" style="margin-top:12px;"></div>

                <p class="resultado-nota" id="res-mac-nota"></p>
            </div>
        </div>
    </div>

</main>

<?php require_once '../includes/footer.php'; ?>

<script src="../assets/js/script_calculadora.js"></script>
</body>
</html>