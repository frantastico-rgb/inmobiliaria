<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Propietarios</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class=logo-icono>
        <img src="uploads/logo_casa_meta.png" alt="logo">
    </div>
    <hr>


<?php

// El resto de tu código de lista_propietarios.php ...
// El resto de tu código de lista_propietarios.php ...
// Incluye el archivo de conexión a la base de datos
require_once 'conexion.php';

// Inicia la sesión para poder acceder a las variables de sesión
session_start();

// Verifica si hay un mensaje de éxito en la sesión
if (isset($_SESSION['mensaje'])) {
    echo "<p style='color: green;'>" . $_SESSION['mensaje'] . "</p>";
    // Elimina el mensaje de la sesión para que no se muestre de nuevo
    unset($_SESSION['mensaje']);
}

// Verifica si hay un mensaje de eliminación en la URL
if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'propietario_eliminado') {
    echo "<p style='color: green;'>Propietario eliminado con éxito.</p>";
}

// Realiza la consulta para obtener todos los propietarios
$sql = "SELECT cod_prop, nom_prop, tipo_doc, num_doc, dir_prop, tel_prop FROM propietarios";
$resultado = $conn->query($sql);

// Verifica si la consulta fue exitosa
if ($resultado->num_rows > 0) {
    echo "<h2>Lista de Propietarios</h2>";
    echo "<table border='1'>";
    echo "<thead><tr><th>ID</th><th>Nombre</th><th>Tipo Doc.</th><th>Num. Doc.</th><th>Teléfono</th><th>Dirección</th><th>Acciones</th></tr></thead>";
    echo "<tbody>";

    // Itera sobre cada fila de resultado
    while ($fila = $resultado->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $fila['cod_prop'] . "</td>";
        echo "<td>" . $fila['nom_prop'] . "</td>";
        echo "<td>" . $fila['tipo_doc'] . "</td>";
        echo "<td>" . $fila['num_doc'] . "</td>";
        echo "<td>" . $fila['tel_prop'] . "</td>";
        echo "<td>" . $fila['dir_prop'] . "</td>";
        echo "<td><a href='editar_propietario.php?id=" . $fila['cod_prop'] . "'>Editar</a> | <a href='eliminar_propietario.php?id=" . $fila['cod_prop'] . "' onclick=\"return confirm('¿Está seguro que desea eliminar al propietario: " . $fila['nom_prop'] . "?')\">Eliminar</a></td>";
        
        
        }

    echo "</tbody>";
    echo "</table>";
} else {
    echo "<p>No hay propietarios registrados.</p>";
}

// Cierra la conexión a la base de datos
$conn->close();
?>
<p><a href="propietarios.php">Agregar Nuevo Propietario</a></p>


</body>
</html>