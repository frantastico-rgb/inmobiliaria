<?php
// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

// Verificar si se recibió el ID de la visita a editar
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $cod_vis = $_GET['id'];

    // Consultar la visita específica y las tablas relacionadas
    $sql = "SELECT
        v.cod_vis,
        v.fecha_vis,
        v.fk_cod_cli,
        c.nom_cli AS nombre_cliente,
        v.fk_cod_emp,
        e.nom_emp AS nombre_empleado,
        v.fk_cod_inm,
        i.dir_inm AS direccion_inmueble,
        v.comenta_vis
    FROM visitas v
    JOIN clientes c ON v.fk_cod_cli = c.cod_cli
    JOIN empleados e ON v.fk_cod_emp = e.cod_emp
    JOIN inmuebles i ON v.fk_cod_inm = i.cod_inm
    WHERE v.cod_vis = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cod_vis);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $visita = $resultado->fetch_assoc();

        // Consultar todas las clientes para el desplegable
        $sql_clientes = "SELECT cod_cli, nom_cli FROM clientes";
        $resultado_clientes = $conn->query($sql_clientes);
        $clientes = [];
        if ($resultado_clientes->num_rows > 0) {
            while ($fila = $resultado_clientes->fetch_assoc()) {
                $clientes[$fila['cod_cli']] = $fila['nom_cli'];
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

        // Consultar todos los inmuebles para el desplegable
        $sql_inmuebles = "SELECT cod_inm, dir_inm FROM inmuebles";
        $resultado_inmuebles = $conn->query($sql_inmuebles);
        $inmuebles = [];
        if ($resultado_inmuebles->num_rows > 0) {
            while ($fila = $resultado_inmuebles->fetch_assoc()) {
                $inmuebles[$fila['cod_inm']] = $fila['dir_inm'];
            }
        }

    } else {
        $_SESSION['mensaje'] = "Visita no encontrada.";
        header("Location: lista_visitas.php");
        exit();
    }

    $stmt->close();

} else {
    $_SESSION['mensaje'] = "ID de visita no válido.";
    header("Location: lista_visitas.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Visita</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class=logo-icono>
        <img src="uploads/logo_casa_meta.png" alt="logo">
    </div>
    <hr>
    <h1>Editar Visita</h1>

    <form action="guardar_cambios_visita.php" method="post">
        <input type="hidden" name="cod_vis" value="<?php echo $visita['cod_vis']; ?>">

        <div>
            <label for="fecha_vis">Fecha de la Visita:</label>
            <input type="date" id="fecha_vis" name="fecha_vis" value="<?php echo $visita['fecha_vis']; ?>" required>
        </div>

        <div>
            <label for="fk_cod_cli">Cliente:</label>
            <select id="fk_cod_cli" name="fk_cod_cli" required>
                <option value="">Seleccionar Cliente</option>
                <?php foreach ($clientes as $cod => $nombre): ?>
                    <option value="<?php echo $cod; ?>" <?php if ($visita['fk_cod_cli'] == $cod) echo 'selected'; ?>><?php echo $nombre; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="fk_cod_emp">Empleado:</label>
            <select id="fk_cod_emp" name="fk_cod_emp" required>
                <option value="">Seleccionar Empleado</option>
                <?php foreach ($empleados as $cod => $nombre): ?>
                    <option value="<?php echo $cod; ?>" <?php if ($visita['fk_cod_emp'] == $cod) echo 'selected'; ?>><?php echo $nombre; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="fk_cod_inm">Inmueble:</label>
            <select id="fk_cod_inm" name="fk_cod_inm" required>
                <option value="">Seleccionar Inmueble</option>
                <?php foreach ($inmuebles as $cod => $direccion): ?>
                    <option value="<?php echo $cod; ?>" <?php if ($visita['fk_cod_inm'] == $cod) echo 'selected'; ?>><?php echo $direccion; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="comenta_vis">Comentarios:</label>
            <textarea id="comenta_vis" name="comenta_vis" rows="4" cols="50"><?php echo $visita['comenta_vis']; ?></textarea>
        </div>

        <div>
            <button type="submit">Guardar Cambios</button>
            <a href="lista_visitas.php">Cancelar</a>
        </div>
    </form>
</body>
</html>