<?php
require_once 'conexion.php';
session_start();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $inmueble_id = $_GET['id'];

    // Preparar la consulta SQL para eliminar el inmueble
    $sql = "DELETE FROM inmuebles WHERE cod_inm = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $inmueble_id);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Inmueble eliminado con éxito.";
    } else {
        $_SESSION['mensaje'] = "Error al eliminar el inmueble: " . $stmt->error;
    }

    $stmt->close();
} else {
    $_SESSION['mensaje'] = "ID de inmueble inválido.";
}

$conn->close();

header("Location: lista_inmuebles.php");
exit();
?>