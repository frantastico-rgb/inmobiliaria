<?php
require_once 'conexion.php';
session_start();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $oficina_id = $_GET['id'];

    // Preparar la consulta SQL para eliminar la oficina
    $sql = "DELETE FROM oficina WHERE Id_ofi = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $oficina_id);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Oficina eliminada con éxito.";
    } else {
        $_SESSION['mensaje'] = "Error al eliminar la oficina: " . $stmt->error;
    }

    $stmt->close();
} else {
    $_SESSION['mensaje'] = "ID de oficina inválido.";
}

$conn->close();

header("Location: lista_oficinas.php");
exit();
?>