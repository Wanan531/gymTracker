<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

$stmt = $pdo->prepare("
    SELECT MAX(orden) as max_orden FROM rutina_ejercicio WHERE rutina_id = ?
");
$stmt->execute([$data['rutina_id']]);
$max = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    INSERT INTO rutina_ejercicio (rutina_id, ejercicio_id, series, repeticiones, peso_kg, orden)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->execute([
    $data['rutina_id'],
    $data['ejercicio_id'],
    $data['series'],
    $data['repeticiones'],
    $data['peso_kg'],
    ($max ?? 0) + 1
]);

echo json_encode(['ok' => true]);
?>