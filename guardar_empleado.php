<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir los datos del formulario (incluyendo los nuevos campos)
    $tipo_doc = $_POST['tipo_doc'];
    $ced_emp = $_POST['ced_emp'];
    $nom_emp = $_POST['nom_emp'];
    $dir_emp = $_POST['dir_emp'];
    $tel_emp = $_POST['tel_emp'];
    $email_emp = $_POST['email_emp'];
    $rh_emp = $_POST['rh_emp'];
    $fecha_nac = $_POST['fecha_nac'];
    $cod_cargo = $_POST['cod_cargo'];
    $salario = $_POST['salario'];
    $comision = $_POST['comision'];
    $fecha_ing = $_POST['fecha_ing'];
    $gastos = $_POST['gastos'];
    $fecha_ret = $_POST['fecha_ret'];
    $nom_contacto = $_POST['nom_contacto'];
    $dir_contacto = $_POST['dir_contacto'];
    $tel_contacto = $_POST['tel_contacto'];
    $email_contacto = $_POST['email_contacto'];
    $relacion_contacto = $_POST['relacion_contacto'];
    $cod_ofi = $_POST['cod_ofi'];

    // Preparar la consulta SQL para la inserción (incluyendo las nuevas columnas)
    $sql = "INSERT INTO empleados (tipo_doc, ced_emp, nom_emp, dir_emp, tel_emp, email_emp, rh_emp, fecha_nac, cod_cargo, salario, comision, fecha_ing, gastos, fecha_ret, nom_contacto, dir_contacto, tel_contacto, email_contacto, relacion_contacto, cod_ofi)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    // ¡La fiesta de las 's' y las 'i' ha comenzado! Asegúrate de que los tipos coincidan con tu tabla.
    
    $stmt->bind_param("sissssssiiisississsi", $tipo_doc, $ced_emp, $nom_emp, $dir_emp, $tel_emp, $email_emp, $rh_emp, $fecha_nac, $cod_cargo, $salario, $comision, $fecha_ing, $gastos, $fecha_ret, $nom_contacto, $dir_contacto, $tel_contacto, $email_contacto, $relacion_contacto, $cod_ofi);
    

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Empleado agregado con éxito.";
        header("Location: lista_empleados.php");
        exit();
    } else {
        echo "Error al guardar el empleado: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Acceso no permitido.";
}

$conn->close();
?>