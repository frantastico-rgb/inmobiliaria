<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../src/conexion.php';
session_start();
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $cod_vis = $_GET['id'];
    $sql = "DELETE FROM visitas WHERE cod_vis = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cod_vis);
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Visita eliminada con éxito.";
        header("Location: lista_visitas.php");
        exit();
    } else {
        echo "Error al eliminar la visita: " . $stmt->error;
    }
    $stmt->close();
} else {
    $_SESSION['mensaje'] = "ID de visita no válido.";
    header("Location: lista_visitas.php");
    exit();
}
$conn->close();
