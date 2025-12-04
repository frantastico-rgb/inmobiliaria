<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $cliente_id = $_GET['id'];

    // Preparar la consulta SQL para eliminar el cliente
    $sql = "DELETE FROM clientes WHERE cod_cli = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cliente_id);

    if ($stmt->execute()) {
        // Si la eliminación fue exitosa, redirigir a la lista de clientes con un mensaje
        header("Location: lista_clientes.php?mensaje=cliente_eliminado");
        exit();
    } else {
        // Si hubo un error, mostrar un mensaje
        echo "Error al eliminar el cliente: " . $stmt->error;
    }

    $stmt->close();
} else {
    // Si no se recibió un ID válido, mostrar un mensaje
    echo "ID de cliente inválido.";
}

$conn->close();
?>