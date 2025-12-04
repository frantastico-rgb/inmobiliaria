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

// Realiza la consulta para obtener todos los empleados con la información de cargo y oficina
$sql = "SELECT
            e.cod_emp,
            e.tipo_doc,
            e.ced_emp,
            e.nom_emp,
            e.dir_emp,
            e.tel_emp,
            e.email_emp,
            e.rh_emp,
            e.fecha_nac,
            c.nom_cargo AS cargo,
            e.salario,
            e.comision,
            e.fecha_ing,
            o.nom_ofi AS oficina
        FROM empleados e
        INNER JOIN cargos c ON e.cod_cargo = c.cod_cargo
        INNER JOIN oficina o ON e.cod_ofi = o.Id_ofi";
$resultado = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Empleados</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class=logo-icono>
        <img src="uploads/logo_casa_meta.png" alt="logo">
    </div>
    <hr>
    <h1>Lista de Empleados</h1>

    <?php if ($resultado->num_rows > 0): ?>
        <table border='1'>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo Doc.</th>
                    <th>Documento</th>
                    <th>Nombre</th>
                    <th>Dirección</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>RH</th>
                    <th>Fecha Nac.</th>
                    <th>Cargo</th>
                    <th>Salario</th>
                    <th>Comisión</th>
                    <th>Fecha Ingreso</th>
                    <th>Oficina</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $fila['cod_emp']; ?></td>
                        <td><?php echo $fila['tipo_doc']; ?></td>
                        <td><?php echo $fila['ced_emp']; ?></td>
                        <td><?php echo $fila['nom_emp']; ?></td>
                        <td><?php echo $fila['dir_emp']; ?></td>
                        <td><?php echo $fila['tel_emp']; ?></td>
                        <td><?php echo $fila['email_emp']; ?></td>
                        <td><?php echo $fila['rh_emp']; ?></td>
                        <td><?php echo $fila['fecha_nac']; ?></td>
                        <td><?php echo $fila['cargo']; ?></td>
                        <td><?php echo $fila['salario']; ?></td>
                        <td><?php echo $fila['comision']; ?></td>
                        <td><?php echo $fila['fecha_ing']; ?></td>
                        <td><?php echo $fila['oficina']; ?></td>
                        <td>
                            <a href='editar_empleado.php?id=<?php echo $fila['cod_emp']; ?>'>Editar</a> |
                            <a href='eliminar_empleado.php?id=<?php echo $fila['cod_emp']; ?>' onclick="return confirm('¿Está seguro que desea eliminar a <?php echo $fila['nom_emp']; ?>?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hay empleados registrados.</p>
    <?php endif; ?>

    <p><a href="empleados.php">Agregar Nuevo Empleado</a></p>
</body>
</html>

<?php
$conn->close();
?>