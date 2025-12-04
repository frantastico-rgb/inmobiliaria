<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir los datos del formulario de edición
    $cod_con = $_POST['cod_con'];
    $fk_cod_cli = $_POST['fk_cod_cli'];
    $fecha_con = $_POST['fecha_con'];
    $fecha_ini = $_POST['fecha_ini'];
    $fecha_fin = $_POST['fecha_fin'];
    $meses = $_POST['meses'];
    $valor_con = $_POST['valor_con'];
    $deposito_con = $_POST['deposito_con'];
    $metodo_pago_con = $_POST['metodo_pago_con'];
    $dato_pago = $_POST['dato_pago'];
    $archivo_con_nombre = $_FILES['archivo_con']['name'];
    $archivo_con_actual = $_POST['archivo_con_actual']; // Campo oculto con el nombre del archivo actual

    // Preparar la consulta SQL para la actualización
    $sql = "UPDATE contratos SET
            fk_cod_cli = ?,
            fecha_con = ?,
            fecha_ini = ?,
            fecha_fin = ?,
            meses = ?,
            valor_con = ?,
            deposito_con = ?,
            metodo_pago_con = ?,
            dato_pago = ?";

    // Solo actualizar el archivo si se selecciona uno nuevo
    if (!empty($archivo_con_nombre)) {
        $sql .= ", archivo_con = ?";
    }

    $sql .= " WHERE cod_con = ?";

    $stmt = $conn->prepare($sql);

    // Bind de parámetros dinámico según si se actualiza el archivo
    if (!empty($archivo_con_nombre)) {
        $stmt->bind_param("ssssiiisssi", $fk_cod_cli, $fecha_con, $fecha_ini, $fecha_fin, $meses, $valor_con, $deposito_con, $metodo_pago_con, $dato_pago, $archivo_con_nombre, $cod_con);
        // Aquí podrías añadir lógica para guardar el nuevo archivo en el servidor y eliminar el antiguo si es necesario
    } else {
        $stmt->bind_param("ssssiiissi", $fk_cod_cli, $fecha_con, $fecha_ini, $fecha_fin, $meses, $valor_con, $deposito_con, $metodo_pago_con, $dato_pago, $cod_con);
    }

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Contrato actualizado con éxito.";
        header("Location: lista_contratos.php");
        exit();
    } else {
        echo "Error al actualizar el contrato: " . $stmt->error;
    }

    $stmt->close();

} else {
    echo "Acceso no permitido.";
}

$conn->close();
?>