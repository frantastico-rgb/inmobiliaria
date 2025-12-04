<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir los datos del formulario
    $tipo_empresa = $_POST['tipo_empresa'];
    $tipo_doc = $_POST['tipo_doc'];
    $num_doc = $_POST['num_doc'];
    $nom_prop = $_POST['nom_prop'];
    $dir_prop = $_POST['dir_prop'];
    $tel_prop = $_POST['tel_prop'];
    $email_prop = $_POST['email_prop'];
    $contacto_prop = $_POST['contacto_prop'];
    $tel_contacto = $_POST['tel_contacto'];
    $email_contacto = $_POST['email_contacto'];

    // Preparar la consulta SQL para insertar el nuevo propietario
    $sql = "INSERT INTO propietarios (tipo_empresa, tipo_doc, num_doc, nom_prop, dir_prop, tel_prop, email_prop, contacto_prop, tel_contacto, email_contacto)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssss", $tipo_empresa, $tipo_doc, $num_doc, $nom_prop, $dir_prop, $tel_prop, $email_prop, $contacto_prop, $tel_contacto, $email_contacto);

    if ($stmt->execute()) {
        // Si la inserción fue exitosa, redirigir a la lista de propietarios con un mensaje
        header("Location: lista_propietarios.php?mensaje=propietario_creado");
        exit();
    } else {
        // Si hubo un error, mostrar un mensaje
        echo "Error al guardar el propietario: " . $stmt->error;
    }

    $stmt->close();
} else {
    // Si se intenta acceder a este archivo por GET, mostrar un mensaje
    echo "Acceso no permitido.";
}

$conn->close();
?>