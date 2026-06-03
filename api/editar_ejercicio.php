<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$id          = (int)$data['id'];
$series      = (int)$data['series'];
$repeticiones = (int)$data['repeticiones'];
$peso_kg     = (float)$data['peso_kg'];

// Verificar que el ejercicio pertenece a una rutina del usuario
$stmt = $pdo->prepare("
    UPDATE rutina_ejercicio re
    JOIN rutina r ON r.id = re.rutina_id
    SET re.series = ?, re.repeticiones = ?, re.peso_kg = ?
    WHERE re.id = ? AND r.usuario_id = ?
");
$stmt->execute([$series, $repeticiones, $peso_kg, $id, $_SESSION['usuario_id']]);

echo json_encode(['ok' => true]);
?>