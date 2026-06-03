<?php
$host     = 'localhost';
$dbname   = 'nombre base de datos';
$user     = 'nombre usuario';
$password = 'contraseña';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host_url = $_SERVER['HTTP_HOST'];
// Detecta la carpeta raíz del proyecto automáticamente
$base_path = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\') . '/';
define('BASE_URL', $protocol . '://' . $host_url . $base_path);

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['error' => $e->getMessage()]));
}
?>