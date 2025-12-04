<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir los datos del formulario de edición
    $cod_ins = $_POST['cod_ins'];
    $fecha_ins = $_POST['fecha_ins'];
    $fk_cod_inm = $_POST['fk_cod_inm'];
    $fk_cod_emp = $_POST['fk_cod_emp'];
    $comentario = $_POST['comentario'];

    // Preparar la consulta SQL para la actualización
    $sql = "UPDATE inspeccion SET
            fecha_ins = ?,
            fk_cod_inm = ?,
            fk_cod_emp = ?,
            comentario = ?
            WHERE cod_ins = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siisi", $fecha_ins, $fk_cod_inm, $fk_cod_emp, $comentario, $cod_ins);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Inspección actualizada con éxito.";
        header("Location: lista_inspecciones.php");
        exit();
    } else {
        echo "Error al actualizar la inspección: " . $stmt->error;
    }

    $stmt->close();

} else {
    echo "Acceso no permitido.";
}

$conn->close();
?>