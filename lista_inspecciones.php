<?php
// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

// Iniciar la sesión para los mensajes
session_start();

// Consultar todas las inspecciones, uniendo con las tablas relacionadas
$sql = "SELECT
    i.cod_ins,
    i.fecha_ins,
    inm.dir_inm AS direccion_inmueble,
    emp.nom_emp AS nombre_empleado,
    i.comentario
FROM inspeccion i
JOIN inmuebles inm ON i.fk_cod_inm = inm.cod_inm
JOIN empleados emp ON i.fk_cod_emp = emp.cod_emp";

$resultado = $conn->query($sql);
$inspecciones = [];
if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $inspecciones[] = $fila;
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Inspecciones</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class=logo-icono>
        <img src="uploads/logo_casa_meta.png" alt="logo">
    </div>
    <hr>
    <h1>Lista de Inspecciones</h1>

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
                <th>Inmueble</th>
                <th>Empleado</th>
                <th>Comentarios</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($inspecciones)): ?>
                <tr><td colspan="6">No hay inspecciones registradas.</td></tr>
            <?php else: ?>
                <?php foreach ($inspecciones as $inspeccion): ?>
                    <tr>
                        <td><?php echo $inspeccion['cod_ins']; ?></td>
                        <td><?php echo $inspeccion['fecha_ins']; ?></td>
                        <td><?php echo $inspeccion['direccion_inmueble']; ?></td>
                        <td><?php echo $inspeccion['nombre_empleado']; ?></td>
                        <td><?php echo $inspeccion['comentario']; ?></td>
                        <td>
                            <a href="editar_inspeccion.php?id=<?php echo $inspeccion['cod_ins']; ?>">Editar</a>
                            <a href="eliminar_inspeccion.php?id=<?php echo $inspeccion['cod_ins']; ?>" onclick="return confirm('¿Desea eliminar esta inspección?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <p><a href="inspeccion.php">Registrar Nueva Inspección</a></p>
</body>
</html>