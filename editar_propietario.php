<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

// Verificar si se recibió el ID del propietario por la URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $propietario_id = $_GET['id'];

    // Preparar la consulta SQL para obtener los datos del propietario
    $sql = "SELECT * FROM propietarios WHERE cod_prop = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $propietario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar si se encontró el propietario
    if ($result->num_rows == 1) {
        $propietario = $result->fetch_assoc();
    } else {
        // Si no se encontró el propietario, mostrar un mensaje y redirigir
        echo "Propietario no encontrado.";
        // Puedes redirigir a la lista de propietarios después de un breve mensaje
        // header("Location: lista_propietarios.php");
        // exit();
    }

    $stmt->close();
} else {
    // Si no se recibió un ID válido, mostrar un mensaje y redirigir
    echo "ID de propietario inválido.";
    // Puedes redirigir a la lista de propietarios
    // header("Location: lista_propietarios.php");
    // exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Propietario</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class=logo-icono>
        <img src="uploads/logo_casa_meta.png" alt="logo">
    </div>
    <hr>
    <h1>Editar Propietario</h1>

    <?php if (isset($propietario)): ?>
        <form action="guardar_cambios_propietario.php" method="post">
            <input type="hidden" name="cod_prop" value="<?php echo $propietario['cod_prop']; ?>">

            <div>
                <label for="tipo_empresa">Tipo de Empresa:</label>
                <select id="tipo_empresa" name="tipo_empresa">
                    <option value="Persona Natural" <?php if ($propietario['tipo_empresa'] == 'Persona Natural') echo 'selected'; ?>>Persona Natural</option>
                    <option value="Jurídica" <?php if ($propietario['tipo_empresa'] == 'Jurídica') echo 'selected'; ?>>Jurídica</option>
                </select>
            </div>

            <div>
                <label for="tipo_doc">Tipo de Documento:</label>
                <select id="tipo_doc" name="tipo_doc">
                    <option value="CC" <?php if ($propietario['tipo_doc'] == 'CC') echo 'selected'; ?>>Cédula de Ciudadanía</option>
                    <option value="NIT" <?php if ($propietario['tipo_doc'] == 'NIT') echo 'selected'; ?>>NIT</option>
                    <option value="CE" <?php if ($propietario['tipo_doc'] == 'CE') echo 'selected'; ?>>Cédula de Extranjería</option>
                </select>
            </div>

            <div>
                <label for="num_doc">Número de Documento:</label>
                <input type="number" id="num_doc" name="num_doc" value="<?php echo $propietario['num_doc']; ?>">
            </div>

            <div>
                <label for="nom_prop">Nombre Completo / Razón Social:</label>
                <input type="text" id="nom_prop" name="nom_prop" value="<?php echo $propietario['nom_prop']; ?>" required>
            </div>

            <div>
                <label for="dir_prop">Dirección:</label>
                <input type="text" id="dir_prop" name="dir_prop" value="<?php echo $propietario['dir_prop']; ?>">
            </div>

            <div>
                <label for="tel_prop">Teléfono:</label>
                <input type="tel" id="tel_prop" name="tel_prop" value="<?php echo $propietario['tel_prop']; ?>">
            </div>

            <div>
                <label for="email_prop">Email:</label>
                <input type="email" id="email_prop" name="email_prop" value="<?php echo $propietario['email_prop']; ?>">
            </div>

            <div>
                <label for="contacto_prop">Nombre del Contacto (Opcional):</label>
                <input type="text" id="contacto_prop" name="contacto_prop" value="<?php echo $propietario['contacto_prop']; ?>">
            </div>

            <div>
                <label for="tel_contacto">Teléfono del Contacto (Opcional):</label>
                <input type="tel" id="tel_contacto" name="tel_contacto" value="<?php echo $propietario['tel_contacto']; ?>">
            </div>

            <div>
                <label for="email_contacto">Email del Contacto (Opcional):</label>
                <input type="email" id="email_contacto" name="email_contacto" value="<?php echo $propietario['email_contacto']; ?>">
            </div>

            <div>
                <button type="submit">Guardar Cambios</button>
                <a href="lista_propietarios.php">Cancelar</a>
            </div>
        </form>
    <?php endif; ?>

</body>
</html>