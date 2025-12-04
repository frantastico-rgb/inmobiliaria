<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nuevo Propietario</title>
    <link rel="stylesheet" href="estilos.css"> </head>
<body>
    <div class=logo-icono>
        <img src="uploads/logo_casa_meta.png" alt="logo">
    </div>
    <hr>
    <h1>Crear Nuevo Propietario</h1>

    <form action="guardar_propietario.php" method="post">
        <div>
            <label for="tipo_empresa">Tipo de Empresa:</label>
            <select id="tipo_empresa" name="tipo_empresa">
                <option value="Persona Natural">Persona Natural</option>
                <option value="Jurídica">Jurídica</option>
            </select>
        </div>

        <div>
            <label for="tipo_doc">Tipo de Documento:</label>
            <select id="tipo_doc" name="tipo_doc">
                <option value="CC">Cédula de Ciudadanía</option>
                <option value="NIT">NIT</option>
                <option value="CE">Cédula de Extranjería</option>
            </select>
        </div>

        <div>
            <label for="num_doc">Número de Documento:</label>
            <input type="number" id="num_doc" name="num_doc">
        </div>

        <div>
            <label for="nom_prop">Nombre Completo / Razón Social:</label>
            <input type="text" id="nom_prop" name="nom_prop" required>
        </div>

        <div>
            <label for="dir_prop">Dirección:</label>
            <input type="text" id="dir_prop" name="dir_prop">
        </div>

        <div>
            <label for="tel_prop">Teléfono:</label>
            <input type="tel" id="tel_prop" name="tel_prop">
        </div>

        <div>
            <label for="email_prop">Email:</label>
            <input type="email" id="email_prop" name="email_prop">
        </div>

        <div>
            <label for="contacto_prop">Nombre del Contacto (Opcional):</label>
            <input type="text" id="contacto_prop" name="contacto_prop">
        </div>

        <div>
            <label for="tel_contacto">Teléfono del Contacto (Opcional):</label>
            <input type="tel" id="tel_contacto" name="tel_contacto">
        </div>

        <div>
            <label for="email_contacto">Email del Contacto (Opcional):</label>
            <input type="email" id="email_contacto" name="email_contacto">
        </div>

        <div>
            <button type="submit">Guardar Propietario</button>
            <a href="lista_propietarios.php">Cancelar</a>
        </div>
    </form>
</body>
</html>