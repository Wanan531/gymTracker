<?php

header('Content-Type: application/json');
session_start();

require_once '../config/db.php';

$env = parse_ini_file(__DIR__ . '/../.env');
define('GROQ_API_KEY', $env['GROQ_API_KEY']);

$data = json_decode(file_get_contents('php://input'), true);

$objetivo  = htmlspecialchars($data['objetivo'] ?? '');
$nivel     = htmlspecialchars($data['nivel'] ?? '');
$dias_num  = intval($data['dias'] ?? 3);
$equipo    = htmlspecialchars($data['equipo'] ?? '');
$notas     = htmlspecialchars($data['notas'] ?? 'ninguna');
$lesiones  = htmlspecialchars($data['lesiones'] ?? 'ninguna');

$usuario_id = $_SESSION['usuario_id'] ?? null;
if (!$usuario_id) {
    http_response_code(401);
    echo json_encode(['error' => 'No has iniciado sesión']);
    exit;
}

// ── Obtener ejercicios disponibles ──
$stmtEj = $pdo->prepare("
    SELECT nombre, grupo_muscular, tipo
    FROM ejercicio
    WHERE es_predefinido = 1 OR usuario_id = ?
    ORDER BY grupo_muscular, nombre
");
$stmtEj->execute([$usuario_id]);
$ejerciciosDisponibles = $stmtEj->fetchAll(PDO::FETCH_ASSOC);

$listaEjercicios = implode(', ', array_map(
    fn($e) => $e['nombre'] . ($e['tipo'] === 'tiempo' ? ' [tiempo]' : ''),
    $ejerciciosDisponibles
));

// ── Prompt ──
$prompt = "Eres un entrenador personal experto. Crea una rutina de entrenamiento.
Parametros: Objetivo: $objetivo, Nivel: $nivel, Dias: $dias_num, Equipo: $equipo.
Notas: $notas.

LESIONES O LIMITACIONES DEL USUARIO: $lesiones
- Evita COMPLETAMENTE cualquier ejercicio que cargue o impacte las zonas lesionadas.
- Sustituye esos ejercicios por alternativas seguras para esas zonas.
- Indica en el campo 'nota' de cada ejercicio si hay algo importante a tener en cuenta.

IMPORTANTE: Solo puedes usar ejercicios de esta lista exacta: $listaEjercicios
No inventes ni uses ejercicios que no estén en esa lista.

Los ejercicios marcados con [tiempo] van por segundos, no repeticiones.
Para ellos usa el campo 'repeticiones' para indicar los segundos (ej: 30, 45, 60).

Responde SOLAMENTE con el JSON, sin texto extra, sin bloques de código. Formato:
{
  \"nombre\": \"Nombre de la rutina\",
  \"descripcion\": \"Descripcion breve\",
  \"dias\": [
    {
      \"nombre\": \"Dia 1\",
      \"ejercicios\": [
        {\"nombre\": \"ejercicio\", \"series\": 3, \"repeticiones\": \"10\", \"descanso\": \"60s\", \"nota\": \"\"}
      ]
    }
  ]
}";

$body = json_encode([
    'model'       => 'llama-3.3-70b-versatile',
    'messages'    => [['role' => 'user', 'content' => $prompt]],
    'max_tokens'  => 4000,
    'temperature' => 0.7
]);

$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST  => 'POST',
    CURLOPT_POSTFIELDS     => $body,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . GROQ_API_KEY
    ]
]);

$response   = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    echo json_encode(['error' => "Error de cURL: $curl_error"]);
    exit;
}

$result = json_decode($response, true);

if (isset($result['error'])) {
    http_response_code(500);
    echo json_encode(['error' => $result['error']['message']]);
    exit;
}

$texto = $result['choices'][0]['message']['content'];
preg_match('/\{.*\}/s', $texto, $matches);
$rutina = isset($matches[0]) ? json_decode($matches[0], true) : null;

if (!$rutina) {
    http_response_code(500);
    echo json_encode(['error' => 'La IA no devolvió un JSON válido']);
    exit;
}

// ── Guardar en base de datos ──
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO rutina (usuario_id, nombre, descripcion, activa)
        VALUES (?, ?, ?, 1)
    ");
    $stmt->execute([$usuario_id, $rutina['nombre'], $rutina['descripcion']]);
    $rutina_id = $pdo->lastInsertId();

    $orden_global = 1;
    foreach ($rutina['dias'] as $dia) {
        foreach ($dia['ejercicios'] as $ej) {
            $stmtBuscar = $pdo->prepare("
                SELECT id FROM ejercicio
                WHERE nombre LIKE ?
                AND (es_predefinido = 1 OR usuario_id = ?)
                LIMIT 1
            ");
            $stmtBuscar->execute(['%' . $ej['nombre'] . '%', $usuario_id]);
            $ejercicio = $stmtBuscar->fetch();

            if (!$ejercicio) continue;

            $reps = intval(explode('-', $ej['repeticiones'])[0]);

            $stmtInsert = $pdo->prepare("
                INSERT INTO rutina_ejercicio
                    (rutina_id, ejercicio_id, series, repeticiones, peso_kg, orden, dia_nombre)
                VALUES (?, ?, ?, ?, 0, ?, ?)
            ");
            $stmtInsert->execute([
                $rutina_id,
                $ejercicio['id'],
                intval($ej['series']),
                $reps,
                $orden_global++,
                $dia['nombre']
            ]);
        }
    }

    $dias_semana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
    for ($i = 0; $i < $dias_num; $i++) {
        if (!isset($dias_semana[$i])) break;
        $nombre_dia = $rutina['dias'][$i]['nombre'] ?? null;
        $stmtDia = $pdo->prepare("INSERT INTO rutina_dia (rutina_id, dia_semana, dia_nombre) VALUES (?, ?, ?)");
        $stmtDia->execute([$rutina_id, $dias_semana[$i], $nombre_dia]);
    }

    $pdo->commit();
    $rutina['id'] = $rutina_id;
    echo json_encode($rutina);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Error en BD: ' . $e->getMessage()]);
}
?>