<?php
require_once 'conexion.php';
session_start();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $empleado_id = $_GET['id'];

    // Preparar la consulta SQL para eliminar el empleado
    $sql = "DELETE FROM empleados WHERE cod_emp = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $empleado_id);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Empleado eliminado con éxito.";
    } else {
        $_SESSION['mensaje'] = "Error al eliminar el empleado: " . $stmt->error;
    }

    $stmt->close();
} else {
    $_SESSION['mensaje'] = "ID de empleado inválido.";
}

$conn->close();

header("Location: lista_empleados.php");
exit();
?>