<?php
// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger los datos del formulario
    $nom_cli = $_POST["nom_cli"];
    $doc_cli = $_POST["doc_cli"];
    $tipo_doc_cli = $_POST["tipo_doc_cli"];
    $dir_cli = $_POST["dir_cli"];
    $tel_cli = $_POST["tel_cli"];
    $email_cli = $_POST["email_cli"];
    $cod_tipoinm = $_POST["cod_tipoinm"];
    $valor_maximo = $_POST["valor_maximo"];
    $notas_cliente = $_POST["notas_cliente"];
    $fk_cod_emp_gestion = $_POST["fk_cod_emp_gestion"];

    // Primero, verificar si el correo electrónico ya existe
    $sql_check = "SELECT cod_cli FROM clientes WHERE email_cli = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email_cli);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // Si el correo ya existe, redirigir con un mensaje de error
        $stmt_check->close();
        header("Location: clientes.php?error=email_existente");
        exit();
    } else {
        // Si el correo no existe, proceder con la inserción
        $stmt_check->close();
        // Preparar la consulta SQL para la inserción
        $sql = "INSERT INTO clientes (nom_cli, doc_cli, tipo_doc_cli, dir_cli, tel_cli, email_cli, cod_tipoinm, valor_maximo, notas_cliente, fk_cod_emp_gestion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        // Preparar la sentencia
        $stmt = $conn->prepare($sql);

        // Vincular los parámetros
        $stmt->bind_param("sisssssisi", $nom_cli, $doc_cli, $tipo_doc_cli, $dir_cli, $tel_cli, $email_cli, $cod_tipoinm, $valor_maximo, $notas_cliente, $fk_cod_emp_gestion);

        // Ejecutar la sentencia
        if ($stmt->execute()) {
            // Redirigir a una página de éxito
            header("Location: cliente_creado.html");
            exit();
        } else {
            // Manejar otros posibles errores de inserción
            echo "Error al crear el cliente: " . $stmt->error;
        }
        // Cerrar la sentencia de inserción
        $stmt->close();
    }
}

// Cerrar la conexión
$conn->close();
?>