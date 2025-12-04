<?php
// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

// Iniciar la sesión para los mensajes
session_start();

// Consultar todos los contratos, uniendo con la tabla de clientes
$sql = "SELECT
    c.cod_con,
    cli.nom_cli AS nombre_cliente,
    c.fecha_con,
    c.fecha_ini,
    c.fecha_fin,
    c.meses,
    c.valor_con,
    c.deposito_con,
    c.metodo_pago_con,
    c.dato_pago,
    c.archivo_con
FROM contratos c
JOIN clientes cli ON c.fk_cod_cli = cli.cod_cli";

$resultado = $conn->query($sql);
$contratos = [];
if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $contratos[] = $fila;
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Contratos</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class=logo-icono>
        <img src="uploads/logo_casa_meta.png" alt="logo">
    </div>
    <hr>
    <h1>Lista de Contratos</h1>

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
                <th>Cliente</th>
                <th>Fecha Contrato</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Meses</th>
                <th>Valor</th>
                <th>Depósito</th>
                <th>Método Pago</th>
                <th>Dato Pago</th>
                <th>Archivo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($contratos)): ?>
                <tr><td colspan="12">No hay contratos registrados.</td></tr>
            <?php else: ?>
                <?php foreach ($contratos as $contrato): ?>
                    <tr>
                        <td><?php echo $contrato['cod_con']; ?></td>
                        <td><?php echo $contrato['nombre_cliente']; ?></td>
                        <td><?php echo $contrato['fecha_con']; ?></td>
                        <td><?php echo $contrato['fecha_ini']; ?></td>
                        <td><?php echo $contrato['fecha_fin']; ?></td>
                        <td><?php echo $contrato['meses']; ?></td>
                        <td><?php echo number_format($contrato['valor_con']); ?></td>
                        <td><?php echo number_format($contrato['deposito_con']); ?></td>
                        <td><?php echo $contrato['metodo_pago_con']; ?></td>
                        <td><?php echo $contrato['dato_pago']; ?></td>
                        <td><?php echo $contrato['archivo_con']; ?></td>
                        <td>
                            <a href="editar_contrato.php?id=<?php echo $contrato['cod_con']; ?>">Editar</a>
                            <a href="eliminar_contrato.php?id=<?php echo $contrato['cod_con']; ?>" onclick="return confirm('¿Desea eliminar este contrato?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <p><a href="contratos.php">Registrar Nuevo Contrato</a></p>
</body>
</html>