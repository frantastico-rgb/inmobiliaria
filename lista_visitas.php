<?php
// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

// Iniciar la sesión para los mensajes
session_start();

// Consultar todas las visitas, uniendo con las tablas relacionadas para obtener más información
$sql = "SELECT
    v.cod_vis,
    v.fecha_vis,
    c.nom_cli AS nombre_cliente,
    e.nom_emp AS nombre_empleado,
    i.dir_inm AS direccion_inmueble,
    v.comenta_vis
FROM visitas v
JOIN clientes c ON v.fk_cod_cli = c.cod_cli
JOIN empleados e ON v.fk_cod_emp = e.cod_emp
JOIN inmuebles i ON v.fk_cod_inm = i.cod_inm";

$resultado = $conn->query($sql);
$visitas = [];
if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $visitas[] = $fila;
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Visitas</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class=logo-icono>
        <img src="uploads/logo_casa_meta.png" alt="logo">
    </div>
    <hr>
    <h1>Lista de Visitas</h1>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="mensaje">
            <?php echo $_SESSION['mensaje']; ?>
            <?php unset($_SESSION['mensaje']); ?>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Empleado</th>
                <th>Inmueble</th>
                <th>Comentarios</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($visitas)): ?>
                <tr><td colspan="7">No hay visitas registradas.</td></tr>
            <?php else: ?>
                <?php foreach ($visitas as $visita): ?>
                    <tr>
                        <td><?php echo $visita['cod_vis']; ?></td>
                        <td><?php echo $visita['fecha_vis']; ?></td>
                        <td><?php echo $visita['nombre_cliente']; ?></td>
                        <td><?php echo $visita['nombre_empleado']; ?></td>
                        <td><?php echo $visita['direccion_inmueble']; ?></td>
                        <td><?php echo $visita['comenta_vis']; ?></td>
                        <td>
                            <a href="editar_visita.php?id=<?php echo $visita['cod_vis']; ?>">Editar</a>
                            <a href="eliminar_visita.php?id=<?php echo $visita['cod_vis']; ?>" onclick="return confirm('¿Desea eliminar esta visita?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <p><a href="visitas.php">Registrar Nueva Visita</a></p>
</body>
</html>