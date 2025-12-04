<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php';
session_start();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $cod_con = $_GET['id'];

    // Preparar la consulta SQL para la eliminación
    $sql = "DELETE FROM contratos WHERE cod_con = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cod_con);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Contrato eliminado con éxito.";
        header("Location: lista_contratos.php");
        exit();
    } else {
        echo "Error al eliminar el contrato: " . $stmt->error;
    }

    $stmt->close();

} else {
    $_SESSION['mensaje'] = "ID de contrato no válido.";
    header("Location: lista_contratos.php");
    exit();
}

$conn->close();
?>