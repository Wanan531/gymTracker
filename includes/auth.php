<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /gymtracker/pages/login.php');
    exit;
} // este código verifica si el usuario ha iniciado sesión comprobando si la variable de sesión 'usuario_id' está establecida. Si no lo está, redirige al usuario a 
// la página de inicio de sesión (login.php) y detiene la ejecución del script con exit. Esto asegura que solo los usuarios autenticados puedan acceder a las páginas protegidas del sitio web.
?>