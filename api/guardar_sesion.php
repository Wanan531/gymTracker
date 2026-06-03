<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

header('Content-Type: application/json');

$data       = json_decode(file_get_contents('php://input'), true);
$usuario_id = $_SESSION['usuario_id'];
$rutina_id  = $data['rutina_id'];
$duracion   = $data['duracion_min'];
$series     = $data['series'];

try {
    // Crear la sesión
    $stmt = $pdo->prepare("
        INSERT INTO sesion (usuario_id, rutina_id, fecha, duracion_min)
        VALUES (?, ?, CURDATE(), ?)
    ");
    $stmt->execute([$usuario_id, $rutina_id, $duracion]);
    $sesion_id = $pdo->lastInsertId();

    // Guardar cada serie
    foreach ($series as $serie) {
        $stmt = $pdo->prepare("
            INSERT INTO sesion_serie (sesion_id, ejercicio_id, num_serie, repeticiones, peso_kg)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $sesion_id,
            $serie['ejercicioId'],
            $serie['numSerie'],
            $serie['reps'],
            $serie['peso']
        ]);

        // Actualizar marca personal si es un nuevo récord
        $stmt = $pdo->prepare("
            SELECT id FROM marca_personal
            WHERE usuario_id = ? AND ejercicio_id = ? AND peso_kg >= ?
        ");
        $stmt->execute([$usuario_id, $serie['ejercicioId'], $serie['peso']]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("
                INSERT INTO marca_personal (usuario_id, ejercicio_id, peso_kg, repeticiones, fecha)
                VALUES (?, ?, ?, ?, CURDATE())
                ON DUPLICATE KEY UPDATE
                    peso_kg = IF(peso_kg < VALUES(peso_kg), VALUES(peso_kg), peso_kg),
                    repeticiones = IF(peso_kg < VALUES(peso_kg), VALUES(repeticiones), repeticiones),
                    fecha = IF(peso_kg < VALUES(peso_kg), VALUES(fecha), fecha)
            ");
            $stmt->execute([$usuario_id, $serie['ejercicioId'], $serie['peso'], $serie['reps']]);
        }
    }

    // Comprobar logros
    comprobarLogros($pdo, $usuario_id);

    echo json_encode(['ok' => true, 'sesion_id' => $sesion_id]);

} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}

function comprobarLogros($pdo, $usuario_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sesion WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $total = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT fecha FROM sesion WHERE usuario_id = ? ORDER BY fecha DESC
    ");
    $stmt->execute([$usuario_id]);
    $fechas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $racha = 0;
    $hoy = new DateTime();
    foreach ($fechas as $fecha) {
        $diff = $hoy->diff(new DateTime($fecha))->days;
        if ($diff === $racha) { $racha++; $hoy = new DateTime($fecha); }
        else break;
    }

    $logros_condiciones = [
        'sesiones_total >= 1'  => $total >= 1,
        'sesiones_total >= 10' => $total >= 10,
        'sesiones_total >= 50' => $total >= 50,
        'sesiones_total >= 100'=> $total >= 100,
        'racha >= 7'           => $racha >= 7,
        'racha >= 30'          => $racha >= 30,
    ];

    foreach ($logros_condiciones as $condicion => $cumplida) {
        if (!$cumplida) continue;
        $stmt = $pdo->prepare("SELECT id FROM logro WHERE condicion = ?");
        $stmt->execute([$condicion]);
        $logro = $stmt->fetch();
        if (!$logro) continue;

        $stmt = $pdo->prepare("
            SELECT id FROM usuario_logro WHERE usuario_id = ? AND logro_id = ?
        ");
        $stmt->execute([$usuario_id, $logro['id']]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("
                INSERT INTO usuario_logro (usuario_id, logro_id) VALUES (?, ?)
            ");
            $stmt->execute([$usuario_id, $logro['id']]);
        }
    }
}
?>