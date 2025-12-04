<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php';
session_start();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $cod_ins = $_GET['id'];

    // Preparar la consulta SQL para la eliminación
    $sql = "DELETE FROM inspeccion WHERE cod_ins = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cod_ins);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Inspección eliminada con éxito.";
        header("Location: lista_inspecciones.php");
        exit();
    } else {
        echo "Error al eliminar la inspección: " . $stmt->error;
    }

    $stmt->close();

} else {
    $_SESSION['mensaje'] = "ID de inspección no válido.";
    header("Location: lista_inspecciones.php");
    exit();
}

$conn->close();
?>