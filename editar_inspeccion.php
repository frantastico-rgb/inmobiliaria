<?php
// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

// Verificar si se recibió el ID de la inspección a editar
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $cod_ins = $_GET['id'];

    // Consultar la inspección específica y las tablas relacionadas
    $sql = "SELECT
        i.cod_ins,
        i.fecha_ins,
        i.fk_cod_inm,
        inm.dir_inm AS direccion_inmueble,
        i.fk_cod_emp,
        emp.nom_emp AS nombre_empleado,
        i.comentario
    FROM inspeccion i
    JOIN inmuebles inm ON i.fk_cod_inm = inm.cod_inm
    JOIN empleados emp ON i.fk_cod_emp = emp.cod_emp
    WHERE i.cod_ins = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cod_ins);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $inspeccion = $resultado->fetch_assoc();

        // Consultar todos los inmuebles para el desplegable
        $sql_inmuebles = "SELECT cod_inm, dir_inm FROM inmuebles";
        $resultado_inmuebles = $conn->query($sql_inmuebles);
        $inmuebles = [];
        if ($resultado_inmuebles->num_rows > 0) {
            while ($fila = $resultado_inmuebles->fetch_assoc()) {
                $inmuebles[$fila['cod_inm']] = $fila['dir_inm'];
            }
        }

        // Consultar todos los empleados para el desplegable
        $sql_empleados = "SELECT cod_emp, nom_emp FROM empleados";
        $resultado_empleados = $conn->query($sql_empleados);
        $empleados = [];
        if ($resultado_empleados->num_rows > 0) {
            while ($fila = $resultado_empleados->fetch_assoc()) {
                $empleados[$fila['cod_emp']] = $fila['nom_emp'];
            }
        }

    } else {
        $_SESSION['mensaje'] = "Inspección no encontrada.";
        header("Location: lista_inspecciones.php");
        exit();
    }

    $stmt->close();

} else {
    $_SESSION['mensaje'] = "ID de inspección no válido.";
    header("Location: lista_inspecciones.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Inspección</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class=logo-icono>
        <img src="uploads/logo_casa_meta.png" alt="logo">
    </div>
    <hr>
    <h1>Editar Inspección</h1>

    <form action="guardar_cambios_inspeccion.php" method="post">
        <input type="hidden" name="cod_ins" value="<?php echo $inspeccion['cod_ins']; ?>">

        <div>
            <label for="fecha_ins">Fecha de la Inspección:</label>
            <input type="date" id="fecha_ins" name="fecha_ins" value="<?php echo $inspeccion['fecha_ins']; ?>" required>
        </div>

        <div>
            <label for="fk_cod_inm">Inmueble:</label>
            <select id="fk_cod_inm" name="fk_cod_inm" required>
                <option value="">Seleccionar Inmueble</option>
                <?php foreach ($inmuebles as $cod => $direccion): ?>
                    <option value="<?php echo $cod; ?>" <?php if ($inspeccion['fk_cod_inm'] == $cod) echo 'selected'; ?>><?php echo $direccion; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="fk_cod_emp">Empleado:</label>
            <select id="fk_cod_emp" name="fk_cod_emp" required>
                <option value="">Seleccionar Empleado</option>
                <?php foreach ($empleados as $cod => $nombre): ?>
                    <option value="<?php echo $cod; ?>" <?php if ($inspeccion['fk_cod_emp'] == $cod) echo 'selected'; ?>><?php echo $nombre; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="comentario">Comentarios:</label>
            <input type="text" id="comentario" name="comentario" maxlength="255" value="<?php echo $inspeccion['comentario']; ?>">
        </div>

        <div>
            <button type="submit">Guardar Cambios</button>
            <a href="lista_inspecciones.php">Cancelar</a>
        </div>
    </form>
</body>
</html>