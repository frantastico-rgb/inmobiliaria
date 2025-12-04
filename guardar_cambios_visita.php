<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir los datos del formulario de edición
    $cod_vis = $_POST['cod_vis'];
    $fecha_vis = $_POST['fecha_vis'];
    $fk_cod_cli = $_POST['fk_cod_cli'];
    $fk_cod_emp = $_POST['fk_cod_emp'];
    $fk_cod_inm = $_POST['fk_cod_inm'];
    $comenta_vis = $_POST['comenta_vis'];

    // Preparar la consulta SQL para la actualización
    $sql = "UPDATE visitas SET
            fecha_vis = ?,
            fk_cod_cli = ?,
            fk_cod_emp = ?,
            fk_cod_inm = ?,
            comenta_vis = ?
            WHERE cod_vis = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siissi", $fecha_vis, $fk_cod_cli, $fk_cod_emp, $fk_cod_inm, $comenta_vis, $cod_vis);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Visita actualizada con éxito.";
        header("Location: lista_visitas.php");
        exit();
    } else {
        echo "Error al actualizar la visita: " . $stmt->error;
    }

    $stmt->close();

} else {
    echo "Acceso no permitido.";
}

$conn->close();
?>