<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir los datos del formulario
    $fk_cod_cli = $_POST['fk_cod_cli'];
    $fecha_con = $_POST['fecha_con'];
    $fecha_ini = $_POST['fecha_ini'];
    $fecha_fin = $_POST['fecha_fin'];
    $meses = $_POST['meses'];
    $valor_con = $_POST['valor_con'];
    $deposito_con = $_POST['deposito_con'];
    $metodo_pago_con = $_POST['metodo_pago_con'];
    $dato_pago = $_POST['dato_pago'];
    $archivo_con_nombre = $_FILES['archivo_con']['name']; // Solo obtenemos el nombre del archivo por ahora

    // Preparar la consulta SQL para la inserción
    $sql = "INSERT INTO contratos (fk_cod_cli, fecha_con, fecha_ini, fecha_fin, meses, valor_con, deposito_con, metodo_pago_con, dato_pago, archivo_con)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssiiisss", $fk_cod_cli, $fecha_con, $fecha_ini, $fecha_fin, $meses, $valor_con, $deposito_con, $metodo_pago_con, $dato_pago, $archivo_con_nombre);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Contrato registrado con éxito.";
        header("Location: lista_contratos.php");
        exit();
    } else {
        echo "Error al guardar el contrato: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Acceso no permitido.";
}

$conn->close();
?>