<?php
// Incluye el archivo de conexión a la base de datos
require_once 'conexion.php';

// Inicia la sesión para poder acceder a las variables de sesión
session_start();

// Verifica si hay un mensaje de éxito en la sesión
if (isset($_SESSION['mensaje'])) {
    echo "<p style='color: green;'>" . $_SESSION['mensaje'] . "</p>";
    unset($_SESSION['mensaje']); // Elimina el mensaje de la sesión
}

// Realiza la consulta para obtener todas las oficinas
$sql = "SELECT Id_ofi, nom_ofi, dir_ofi, tel_ofi, email_ofi, latitud, longitud FROM oficina";
$resultado = $conn->query($sql);

// Preparar datos para el mapa
$oficinas_mapa = [];
if ($resultado->num_rows > 0) {
    $resultado_mapa = $conn->query($sql); // Nueva consulta para el mapa
    while ($fila_mapa = $resultado_mapa->fetch_assoc()) {
        if ($fila_mapa['latitud'] && $fila_mapa['longitud']) {
            $oficinas_mapa[] = [
                'lat' => floatval($fila_mapa['latitud']),
                'lng' => floatval($fila_mapa['longitud']),
                'id' => $fila_mapa['Id_ofi'],
                'nombre' => $fila_mapa['nom_ofi'],
                'direccion' => $fila_mapa['dir_ofi'],
                'telefono' => $fila_mapa['tel_ofi'] ?: 'No especificado',
                'email' => $fila_mapa['email_ofi'] ?: 'No especificado'
            ];
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Oficinas</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="css/leaflet-maps.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
</head>
<body>
    <div class=logo-icono>
        <img src="uploads/logo_casa_meta.png" alt="logo">
    </div>
    <hr>
    <h1>Lista de Oficinas</h1>

    <?php if ($resultado->num_rows > 0): ?>
        <table border='1'>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Dirección</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $fila['Id_ofi']; ?></td>
                        <td><?php echo $fila['nom_ofi']; ?></td>
                        <td><?php echo $fila['dir_ofi']; ?></td>
                        <td><?php echo $fila['tel_ofi']; ?></td>
                        <td><?php echo $fila['email_ofi']; ?></td>
                        <td>
                            <a href='editar_oficina.php?id=<?php echo $fila['Id_ofi']; ?>'>Editar</a> |
                            <a href='eliminar_oficina.php?id=<?php echo $fila['Id_ofi']; ?>' onclick="return confirm('¿Está seguro que desea eliminar esta oficina?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hay oficinas registradas.</p>
    <?php endif; ?>

    <p><a href="oficinas.php">Agregar Nueva Oficina</a></p>
</body>
</html>

<?php
$conn->close();
?>