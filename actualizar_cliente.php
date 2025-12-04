<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir los datos del formulario
    $cod_cli = $_POST['cod_cli'];
    $nom_cli = $_POST['nom_cli'];
    $doc_cli = $_POST['doc_cli'];
    $tipo_doc_cli = $_POST['tipo_doc_cli'];
    $dir_cli = $_POST['dir_cli'];
    $tel_cli = $_POST['tel_cli'];
    $email_cli = $_POST['email_cli'];
    $cod_tipoinm = $_POST['cod_tipoinm'];
    $valor_maximo = $_POST['valor_maximo'];
    $notas_cliente = $_POST['notas_cliente'];
    $fk_cod_emp_gestion = $_POST['fk_cod_emp_gestion'];

    // Preparar la consulta SQL para actualizar los datos del cliente
    $sql = "UPDATE clientes SET
                nom_cli = ?,
                doc_cli = ?,
                tipo_doc_cli = ?,
                dir_cli = ?,
                tel_cli = ?,
                email_cli = ?,
                cod_tipoinm = ?,
                valor_maximo = ?,
                notas_cliente = ?,
                fk_cod_emp_gestion = ?
            WHERE cod_cli = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssiisssi", $nom_cli, $doc_cli, $tipo_doc_cli, $dir_cli, $tel_cli, $email_cli, $cod_tipoinm, $valor_maximo, $notas_cliente, $fk_cod_emp_gestion, $cod_cli);

    if ($stmt->execute()) {
        // Si la actualización fue exitosa, redirigir a la lista de clientes con un mensaje
        header("Location: lista_clientes.php?mensaje=cliente_actualizado");
        exit();
    } else {
        // Si hubo un error, mostrar un mensaje
        echo "Error al actualizar el cliente: " . $stmt->error;
    }

    $stmt->close();
} else {
    // Si se intenta acceder a este archivo por GET, mostrar un mensaje
    echo "Acceso no permitido.";
}

$conn->close();
?>