<?php
// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

// Verificar si se recibió el ID del contrato a editar
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $cod_con = $_GET['id'];

    // Consultar el contrato específico y la tabla de clientes
    $sql = "SELECT
        c.cod_con,
        c.fk_cod_cli,
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
    JOIN clientes cli ON c.fk_cod_cli = cli.cod_cli
    WHERE c.cod_con = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cod_con);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $contrato = $resultado->fetch_assoc();

        // Consultar todos los clientes para el desplegable
        $sql_clientes = "SELECT cod_cli, nom_cli FROM clientes";
        $resultado_clientes = $conn->query($sql_clientes);
        $clientes = [];
        if ($resultado_clientes->num_rows > 0) {
            while ($fila = $resultado_clientes->fetch_assoc()) {
                $clientes[$fila['cod_cli']] = $fila['nom_cli'];
            }
        }

    } else {
        $_SESSION['mensaje'] = "Contrato no encontrado.";
        header("Location: lista_contratos.php");
        exit();
    }

    $stmt->close();

} else {
    $_SESSION['mensaje'] = "ID de contrato no válido.";
    header("Location: lista_contratos.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Contrato</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class=logo-icono>
        <img src="uploads/logo_casa_meta.png" alt="logo">
    </div>
    <hr>
    <h1>Editar Contrato</h1>

    <form action="guardar_cambios_contrato.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="cod_con" value="<?php echo $contrato['cod_con']; ?>">

        <div>
            <label for="fk_cod_cli">Cliente:</label>
            <select id="fk_cod_cli" name="fk_cod_cli" required>
                <option value="">Seleccionar Cliente</option>
                <?php foreach ($clientes as $cod => $nombre): ?>
                    <option value="<?php echo $cod; ?>" <?php if ($contrato['fk_cod_cli'] == $cod) echo 'selected'; ?>><?php echo $nombre; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="fecha_con">Fecha del Contrato:</label>
            <input type="date" id="fecha_con" name="fecha_con" value="<?php echo $contrato['fecha_con']; ?>" required>
        </div>

        <div>
            <label for="fecha_ini">Fecha de Inicio:</label>
            <input type="date" id="fecha_ini" name="fecha_ini" value="<?php echo $contrato['fecha_ini']; ?>" required>
        </div>

        <div>
            <label for="fecha_fin">Fecha de Finalización:</label>
            <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo $contrato['fecha_fin']; ?>">
        </div>

        <div>
            <label for="meses">Duración (Meses):</label>
            <input type="number" id="meses" name="meses" min="1" value="<?php echo $contrato['meses']; ?>">
        </div>

        <div>
            <label for="valor_con">Valor del Contrato:</label>
            <input type="number" id="valor_con" name="valor_con" min="0" value="<?php echo $contrato['valor_con']; ?>" required>
        </div>

        <div>
            <label for="deposito_con">Depósito:</label>
            <input type="number" id="deposito_con" name="deposito_con" min="0" value="<?php echo $contrato['deposito_con']; ?>">
        </div>

        <div>
            <label for="metodo_pago_con">Método de Pago:</label>
            <select id="metodo_pago_con" name="metodo_pago_con">
                <option value="">Seleccionar Método</option>
                <option value="transferencia" <?php if ($contrato['metodo_pago_con'] == 'transferencia') echo 'selected'; ?>>Transferencia</option>
                <option value="efectivo" <?php if ($contrato['metodo_pago_con'] == 'efectivo') echo 'selected'; ?>>Efectivo</option>
            </select>
        </div>

        <div>
            <label for="dato_pago">Dato de Pago (Ref.):</label>
            <input type="text" id="dato_pago" name="dato_pago" maxlength="20" value="<?php echo $contrato['dato_pago']; ?>">
        </div>

        <div>
            <label for="archivo_con">Archivo del Contrato:</label>
            <input type="file" id="archivo_con" name="archivo_con">
            <p>Archivo actual: <?php echo $contrato['archivo_con']; ?></p>
        </div>

        <div>
            <button type="submit">Guardar Cambios</button>
            <a href="lista_contratos.php">Cancelar</a>
        </div>
    </form>
</body>
</html>