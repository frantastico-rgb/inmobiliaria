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

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Nuevo Empleado</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <h1>Agregar Nuevo Empleado</h1>

    <form action="guardar_empleado.php" method="post">
        <div>
            <label for="tipo_doc">Tipo de Documento:</label>
            <select id="tipo_doc" name="tipo_doc">
                <option value="CEDULA">Cédula</option>
                <option value="CE">Cédula de Extranjería</option>
                <option value="TI">Tarjeta de Identidad</option>
            </select>
        </div>

        <div>
            <label for="ced_emp">Número de Documento:</label>
            <input type="number" id="ced_emp" name="ced_emp">
        </div>

        <div>
            <label for="nom_emp">Nombre Completo:</label>
            <input type="text" id="nom_emp" name="nom_emp" required>
        </div>

        <div>
            <label for="dir_emp">Dirección:</label>
            <input type="text" id="dir_emp" name="dir_emp">
        </div>

        <div>
            <label for="tel_emp">Teléfono:</label>
            <input type="tel" id="tel_emp" name="tel_emp">
        </div>

        <div>
            <label for="email_emp">Email:</label>
            <input type="email" id="email_emp" name="email_emp">
        </div>

        <div>
            <label for="rh_emp">RH:</label>
            <input type="text" id="rh_emp" name="rh_emp" maxlength="3">
        </div>

        <div>
            <label for="fecha_nac">Fecha de Nacimiento:</label>
            <input type="date" id="fecha_nac" name="fecha_nac">
        </div>

        <div>
            <label for="cod_cargo">Cargo:</label>
            <select id="cod_cargo" name="cod_cargo">
                <option value="">Seleccionar Cargo</option>
                <?php foreach ($cargos as $cod => $nombre): ?>
                    <option value="<?php echo $cod; ?>"><?php echo $nombre; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="salario">Salario:</label>
            <input type="number" id="salario" name="salario">
        </div>

        <div>
            <label for="comision">Comisión:</label>
            <input type="number" id="comision" name="comision">
        </div>

        <div>
            <label for="fecha_ing">Fecha de Ingreso:</label>
            <input type="date" id="fecha_ing" name="fecha_ing">
        </div>

        
        <div>
            <label for="gastos">Gastos:</label>
            <input type="number" id="gastos" name="gastos">
        </div>

        <div>
            <label for="fecha_ret">Fecha de Retiro (opcional):</label>
            <input type="date" id="fecha_ret" name="fecha_ret">
        </div>

        <div>
            <label for="nom_contacto">Nombre del Contacto de Emergencia:</label>
            <input type="text" id="nom_contacto" name="nom_contacto">
        </div>

        <div>
            <label for="dir_contacto">Dirección del Contacto de Emergencia:</label>
            <input type="text" id="dir_contacto" name="dir_contacto">
        </div>

        <div>
            <label for="tel_contacto">Teléfono del Contacto de Emergencia:</label>
            <input type="tel" id="tel_contacto" name="tel_contacto">
        </div>

        <div>
            <label for="email_contacto">Email del Contacto de Emergencia (opcional):</label>
            <input type="email" id="email_contacto" name="email_contacto">
        </div>

        <div>
            <label for="relacion_contacto">Relación con el Contacto de Emergencia:</label>
            <input type="text" id="relacion_contacto" name="relacion_contacto" maxlength="30">
        </div>

        <div>
            <label for="cod_ofi">Oficina:</label>
            <select id="cod_ofi" name="cod_ofi">
                <option value="">Seleccionar Oficina</option>
                <?php foreach ($oficinas as $id => $nombre): ?>
                    <option value="<?php echo $id; ?>"><?php echo $nombre; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <button type="submit">Guardar Empleado</button>
            <a href="lista_empleados.php">Cancelar</a>
        </div>
    </form>

</body>
</html>