<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$usuario_id = $_SESSION['usuario_id'];

$nombre      = trim($data['nombre'] ?? '');
$musculo     = trim($data['grupo_muscular'] ?? '');
$descripcion = trim($data['descripcion'] ?? '');
$gif_base64  = $data['gif_base64'] ?? null;
$tipo        = in_array($data['tipo'] ?? '', ['reps', 'tiempo']) ? $data['tipo'] : 'reps';
$gif_url     = null;

if (!$nombre || !$musculo) {
    echo json_encode(['ok' => false, 'error' => 'Nombre y grupo muscular son obligatorios']);
    exit;
}

if ($gif_base64) {
    preg_match('/^data:(image\/[\w+]+);base64,/', $gif_base64, $matches);
    
    if (empty($matches[1])) {
        echo json_encode(['ok' => false, 'error' => 'Formato de imagen inválido']);
        exit;
    }

    $mime = $matches[1];
    $ext  = match($mime) {
        'image/gif'  => 'gif',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        default      => 'jpg'
    };

    $datos = base64_decode(preg_replace('/^data:image\/[\w+]+;base64,/', '', $gif_base64));
    
    if (!$datos) {
        echo json_encode(['ok' => false, 'error' => 'Error decodificando imagen']);
        exit;
    }

    $carpeta = '../assets/uploads/ejercicios/';
    if (!is_dir($carpeta)) mkdir($carpeta, 0755, true);

    $nombre_archivo = 'ej_' . $usuario_id . '_' . time() . '.' . $ext;
    $resultado = file_put_contents($carpeta . $nombre_archivo, $datos);

    if ($resultado === false) {
        echo json_encode(['ok' => false, 'error' => 'No se pudo guardar la imagen']);
        exit;
    }

    $gif_url = '/assets/uploads/ejercicios/' . $nombre_archivo;
}

$stmt = $pdo->prepare("
    INSERT INTO ejercicio (nombre, grupo_muscular, descripcion, gif_url, usuario_id, es_predefinido, tipo)
    VALUES (?, ?, ?, ?, ?, 0, ?)
");
$stmt->execute([$nombre, $musculo, $descripcion ?: null, $gif_url, $usuario_id, $tipo]);

echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);
?>