<?php
// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

// Consultar la tabla de clientes para el desplegable
$sql_clientes = "SELECT cod_cli, nom_cli FROM clientes";
$resultado_clientes = $conn->query($sql_clientes);
$clientes = [];
if ($resultado_clientes->num_rows > 0) {
    while ($fila = $resultado_clientes->fetch_assoc()) {
        $clientes[$fila['cod_cli']] = $fila['nom_cli'];
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Nuevo Contrato</title>
    <link rel="stylesheet" href="estilos.css">
    
</head>
<body>
    <div class=logo-icono>
        <img src="uploads/logo_casa_meta.png" alt="logo">
    </div>
    <hr>
    <h1>Registrar Nuevo Contrato</h1>

    <form action="guardar_contrato.php" method="post" enctype="multipart/form-data">
        <div>
            <label for="fk_cod_cli">Cliente:</label>
            <select id="fk_cod_cli" name="fk_cod_cli" required>
                <option value="">Seleccionar Cliente</option>
                <?php foreach ($clientes as $cod => $nombre): ?>
                    <option value="<?php echo $cod; ?>"><?php echo $nombre; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="fecha_con">Fecha del Contrato:</label>
            <input type="date" id="fecha_con" name="fecha_con" required>
        </div>

        <div>
            <label for="fecha_ini">Fecha de Inicio:</label>
            <input type="date" id="fecha_ini" name="fecha_ini" required>
        </div>

        <div>
            <label for="fecha_fin">Fecha de Finalización:</label>
            <input type="date" id="fecha_fin" name="fecha_fin">
        </div>

        <div>
            <label for="meses">Duración (Meses):</label>
            <input type="number" id="meses" name="meses" min="1">
        </div>

        <div>
            <label for="valor_con">Valor del Contrato:</label>
            <input type="number" id="valor_con" name="valor_con" min="0" required>
        </div>

        <div>
            <label for="deposito_con">Depósito:</label>
            <input type="number" id="deposito_con" name="deposito_con" min="0">
        </div>

        <div>
            <label for="metodo_pago_con">Método de Pago:</label>
            <select id="metodo_pago_con" name="metodo_pago_con">
                <option value="">Seleccionar Método</option>
                <option value="transferencia">Transferencia</option>
                <option value="efectivo">Efectivo</option>
            </select>
        </div>

        <div>
            <label for="dato_pago">Dato de Pago (Ref.):</label>
            <input type="text" id="dato_pago" name="dato_pago" maxlength="20">
        </div>

        <div>
            <label for="archivo_con">Archivo del Contrato:</label>
            <input type="file" id="archivo_con" name="archivo_con">
        </div>

        <div>
            <button type="submit">Guardar Contrato</button>
            <a href="lista_contratos.php">Cancelar</a>
        </div>
    </form>
</body>
</html>