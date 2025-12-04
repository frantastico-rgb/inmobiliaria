<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $propietario_id = $_GET['id'];

    // Preparar la consulta SQL para eliminar el propietario
    $sql = "DELETE FROM propietarios WHERE cod_prop = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $propietario_id);

    if ($stmt->execute()) {
        // Si la eliminación fue exitosa, redirigir a la lista de propietarios con un mensaje
        header("Location: lista_propietarios.php?mensaje=propietario_eliminado");
        exit();
    } else {
        // Si hubo un error, mostrar un mensaje
        echo "Error al eliminar el propietario: " . $stmt->error;
    }

    $stmt->close();
} else {
    // Si no se recibió un ID válido, mostrar un mensaje y redirigir
    echo "ID de propietario inválido.";
    // Puedes redirigir a la lista de propietarios
    header("Location: lista_propietarios.php");
    exit();
}

$conn->close();
?>