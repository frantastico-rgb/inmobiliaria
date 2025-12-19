<?php
// Lee las credenciales de la base de datos desde las variables de entorno
// getenv() es una forma segura de obtener variables en entornos como Docker y Render
$servername = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: 'inmobil';

// --- Conexión Segura con SSL ---

// Ruta al certificado CA dentro del contenedor de Docker/Render
$ssl_ca = "/var/www/html/ca.pem";

// Inicializar un objeto mysqli
$conn = mysqli_init();
if (!$conn) {
    die("mysqli_init falló");
}

// Establecer las opciones de SSL
// El primer y segundo parámetro (key, cert) son NULL porque solo necesitamos verificar el CA del servidor
if (!mysqli_ssl_set($conn, NULL, NULL, $ssl_ca, NULL, NULL)) {
    die("mysqli_ssl_set falló: " . mysqli_error($conn));
}

// Establecer la conexión real de forma segura
// Se usa mysqli_real_connect en lugar de new mysqli() para usar las opciones SSL
if (!mysqli_real_connect($conn, $servername, $username, $password, $dbname)) {
    // Verificar si el error es por la conexión SSL o por otra causa
    if (mysqli_connect_errno()) {
        die("Error de conexión segura (" . mysqli_connect_errno() . "): " . mysqli_connect_error());
    }
}
// A partir de aquí, el resto de tu aplicación puede usar la variable $conn como siempre.


?>