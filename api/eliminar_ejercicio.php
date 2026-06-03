<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

$stmt = $pdo->prepare("DELETE FROM rutina_ejercicio WHERE id = ?");
$stmt->execute([$data['id']]);

echo json_encode(['ok' => true]);
?>