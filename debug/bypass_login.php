<?php
require_once '../conexion.php';
require_once '../auth/AuthManager.php';

$auth = new AuthManager($conn);

// Simular login directo para agente_junior1
$_SESSION['user_id'] = 8;
$_SESSION['username'] = 'agente_junior1';
$_SESSION['rol'] = 'agente_junior';
$_SESSION['nombre_completo'] = 'Carlos López';
$_SESSION['login_time'] = time();

echo "<h2>Login Bypass Ejecutado</h2>";
echo "<p>Sesión creada para agente_junior1</p>";
echo "<p><a href='../index.php'>→ Ir al Panel Operativo</a></p>";
echo "<p><a href='../auth/logout.php'>→ Logout</a></p>";

echo "<h3>Datos de sesión:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>