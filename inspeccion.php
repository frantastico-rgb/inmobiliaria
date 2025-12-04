<?php
// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

// Consultar la tabla de inmuebles para el desplegable
$sql_inmuebles = "SELECT cod_inm, dir_inm FROM inmuebles";
$resultado_inmuebles = $conn->query($sql_inmuebles);
$inmuebles = [];
if ($resultado_inmuebles->num_rows > 0) {
    while ($fila = $resultado_inmuebles->fetch_assoc()) {
        $inmuebles[$fila['cod_inm']] = $fila['dir_inm'];
    }
}

// Consultar la tabla de empleados para el desplegable
$sql_empleados = "SELECT cod_emp, nom_emp FROM empleados";
$resultado_empleados = $conn->query($sql_empleados);
$empleados = [];
if ($resultado_empleados->num_rows > 0) {
    while ($fila = $resultado_empleados->fetch_assoc()) {
        $empleados[$fila['cod_emp']] = $fila['nom_emp'];
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Nueva Inspección</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class=logo-icono>
        <img src="uploads/logo_casa_meta.png" alt="logo">
    </div>
    <hr>
    <h1>Registrar Nueva Inspección</h1>

    <form action="guardar_inspeccion.php" method="post">
        <div>
            <label for="fecha_ins">Fecha de la Inspección:</label>
            <input type="date" id="fecha_ins" name="fecha_ins" required>
        </div>

        <div>
            <label for="fk_cod_inm">Inmueble:</label>
            <select id="fk_cod_inm" name="fk_cod_inm" required>
                <option value="">Seleccionar Inmueble</option>
                <?php foreach ($inmuebles as $cod => $direccion): ?>
                    <option value="<?php echo $cod; ?>"><?php echo $direccion; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="fk_cod_emp">Empleado:</label>
            <select id="fk_cod_emp" name="fk_cod_emp" required>
                <option value="">Seleccionar Empleado</option>
                <?php foreach ($empleados as $cod => $nombre): ?>
                    <option value="<?php echo $cod; ?>"><?php echo $nombre; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="comentario">Comentarios:</label>
            <input type="text" id="comentario" name="comentario" maxlength="255">
        </div>

        <div>
            <button type="submit">Guardar Inspección</button>
            <a href="lista_inspecciones.php">Cancelar</a>
        </div>
    </form>
</body>
</html>