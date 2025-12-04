<?php
// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

// Consultar la tabla de cargos para el desplegable
$sql_cargos = "SELECT cod_cargo, nom_cargo FROM cargos";
$resultado_cargos = $conn->query($sql_cargos);
$cargos = [];
if ($resultado_cargos->num_rows > 0) {
    while ($fila = $resultado_cargos->fetch_assoc()) {
        $cargos[$fila['cod_cargo']] = $fila['nom_cargo'];
    }
}

// Consultar la tabla de oficinas para el desplegable
$sql_oficinas = "SELECT Id_ofi, nom_ofi FROM oficina";
$resultado_oficinas = $conn->query($sql_oficinas);
$oficinas = [];
if ($resultado_oficinas->num_rows > 0) {
    while ($fila = $resultado_oficinas->fetch_assoc()) {
        $oficinas[$fila['Id_ofi']] = $fila['nom_ofi'];
    }
}

// Verificar si se recibió el ID del empleado a editar
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $empleado_id = $_GET['id'];

    // Consulta para obtener la información del empleado
    $sql_empleado = "SELECT * FROM empleados WHERE cod_emp = ?";
    $stmt_empleado = $conn->prepare($sql_empleado);
    $stmt_empleado->bind_param("i", $empleado_id);
    $stmt_empleado->execute();
    $resultado_empleado = $stmt_empleado->get_result();

    if ($resultado_empleado->num_rows == 1) {
        $empleado = $resultado_empleado->fetch_assoc();
    } else {
        // Si el ID no es válido o no se encuentra el empleado, redirigir con un mensaje de error
        $_SESSION['mensaje'] = "Empleado no encontrado.";
        header("Location: lista_empleados.php");
        exit();
    }

} else {
    // Si no se recibió un ID válido, redirigir con un mensaje de error
    $_SESSION['mensaje'] = "ID de empleado inválido.";
    header("Location: lista_empleados.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empleado</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class=logo-icono>
        <img src="uploads/logo_casa_meta.png" alt="logo">
    </div>
    <hr>
    <h1>Editar Empleado</h1>

    <form action="guardar_cambios_empleado.php" method="post">
        <input type="hidden" name="cod_emp" value="<?php echo $empleado['cod_emp']; ?>">

        <div>
            <label for="tipo_doc">Tipo de Documento:</label>
            <select id="tipo_doc" name="tipo_doc">
                <option value="CEDULA" <?php if ($empleado['tipo_doc'] == 'CEDULA') echo 'selected'; ?>>Cédula</option>
                <option value="CE" <?php if ($empleado['tipo_doc'] == 'CE') echo 'selected'; ?>>Cédula de Extranjería</option>
                <option value="TI" <?php if ($empleado['tipo_doc'] == 'TI') echo 'selected'; ?>>Tarjeta de Identidad</option>
            </select>
        </div>

        <div>
            <label for="ced_emp">Número de Documento:</label>
            <input type="number" id="ced_emp" name="ced_emp" value="<?php echo $empleado['ced_emp']; ?>">
        </div>

        <div>
            <label for="nom_emp">Nombre Completo:</label>
            <input type="text" id="nom_emp" name="nom_emp" value="<?php echo $empleado['nom_emp']; ?>" required>
        </div>

        <div>
            <label for="dir_emp">Dirección:</label>
            <input type="text" id="dir_emp" name="dir_emp" value="<?php echo $empleado['dir_emp']; ?>">
        </div>

        <div>
            <label for="tel_emp">Teléfono:</label>
            <input type="tel" id="tel_emp" name="tel_emp" value="<?php echo $empleado['tel_emp']; ?>">
        </div>

        <div>
            <label for="email_emp">Email:</label>
            <input type="email" id="email_emp" name="email_emp" value="<?php echo $empleado['email_emp']; ?>">
        </div>

        <div>
            <label for="rh_emp">RH:</label>
            <input type="text" id="rh_emp" name="rh_emp" value="<?php echo $empleado['rh_emp']; ?>" maxlength="3">
        </div>

        <div>
            <label for="fecha_nac">Fecha de Nacimiento:</label>
            <input type="date" id="fecha_nac" name="fecha_nac" value="<?php echo $empleado['fecha_nac']; ?>">
        </div>

        <div>
            <label for="cod_cargo">Cargo:</label>
            <select id="cod_cargo" name="cod_cargo">
                <option value="">Seleccionar Cargo</option>
                <?php foreach ($cargos as $cod => $nombre): ?>
                    <option value="<?php echo $cod; ?>" <?php if ($empleado['cod_cargo'] == $cod) echo 'selected'; ?>><?php echo $nombre; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="salario">Salario:</label>
            <input type="number" id="salario" name="salario" value="<?php echo $empleado['salario']; ?>">
        </div>

        <div>
            <label for="comision">Comisión:</label>
            <input type="number" id="comision" name="comision" value="<?php echo $empleado['comision']; ?>">
        </div>

        <div>
            <label for="fecha_ing">Fecha de Ingreso:</label>
            <input type="date" id="fecha_ing" name="fecha_ing" value="<?php echo $empleado['fecha_ing']; ?>">
        </div>

       
        <div>
            <label for="gastos">Gastos:</label>
            <input type="number" id="gastos" name="gastos" value="<?php echo $empleado['gastos']; ?>">
        </div>

        <div>
            <label for="fecha_ret">Fecha de Retiro (opcional):</label>
            <input type="date" id="fecha_ret" name="fecha_ret" value="<?php echo $empleado['fecha_ret']; ?>">
        </div>

        <div>
            <label for="nom_contacto">Nombre del Contacto de Emergencia:</label>
            <input type="text" id="nom_contacto" name="nom_contacto" value="<?php echo $empleado['nom_contacto']; ?>">
        </div>

        <div>
            <label for="dir_contacto">Dirección del Contacto de Emergencia:</label>
            <input type="text" id="dir_contacto" name="dir_contacto" value="<?php echo $empleado['dir_contacto']; ?>">
        </div>

        <div>
            <label for="tel_contacto">Teléfono del Contacto de Emergencia:</label>
            <input type="tel" id="tel_contacto" name="tel_contacto" value="<?php echo $empleado['tel_contacto']; ?>">
        </div>

        <div>
            <label for="email_contacto">Email del Contacto de Emergencia (opcional):</label>
            <input type="email" id="email_contacto" name="email_contacto" value="<?php echo $empleado['email_contacto']; ?>">
        </div>

        <div>
            <label for="relacion_contacto">Relación con el Contacto de Emergencia:</label>
            <input type="text" id="relacion_contacto" name="relacion_contacto" value="<?php echo $empleado['relacion_contacto']; ?>" maxlength="30">
        </div>

        <div>
            <label for="cod_ofi">Oficina:</label>
            <select id="cod_ofi" name="cod_ofi">
                <option value="">Seleccionar Oficina</option>
                <?php foreach ($oficinas as $id => $nombre): ?>
                    <option value="<?php echo $id; ?>" <?php if ($empleado['cod_ofi'] == $id) echo 'selected'; ?>><?php echo $nombre; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <button type="submit">Guardar Cambios</button>
            <a href="lista_empleados.php">Cancelar</a>
        </div>
    </form>

        
</body>
</html>