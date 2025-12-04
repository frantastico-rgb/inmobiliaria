<?php
require_once '../conexion.php';
require_once 'AuthManager.php';

$auth = new AuthManager($conn);
$result = $auth->logout();

// Limpiar cualquier cookie de sesión adicional
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Redireccionar al login con mensaje
$message = urlencode('Sesión cerrada exitosamente');
header("Location: login.php?success=$message");
exit;
?>