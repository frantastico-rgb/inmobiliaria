<?php
// Lee las credenciales de la base de datos desde las variables de entorno
// getenv() es una forma segura de obtener variables en entornos como Docker y Render
$servername = getenv('DB_HOST') ?: '127.0.0.1';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: 'inmobil';
$dbport = getenv('DB_PORT') ?: 3306;

// --- Conexión Segura con SSL (para la nube) y Fallback para local ---

// Ruta al certificado CA. En Render, estará en la raíz.
$ssl_ca = __DIR__ . '/ca.pem';

// Inicializar un objeto mysqli
$conn = mysqli_init();
if (!$conn) {
    die("mysqli_init falló");
}

// Lógica inteligente: Si estamos en un entorno de producción (como Render)
// y el certificado CA existe, usamos una conexión SSL.
if (getenv('ENV_MODE') === 'production' && file_exists($ssl_ca)) {
    
    // Establecer las opciones de SSL
    if (!mysqli_ssl_set($conn, NULL, NULL, $ssl_ca, NULL, NULL)) {
        die("mysqli_ssl_set falló: " . mysqli_error($conn));
    }

    // Conectarse usando SSL
    if (!mysqli_real_connect($conn, $servername, $username, $password, $dbname, $dbport)) {
        die("Error de conexión segura SSL (" . mysqli_connect_errno() . "): " . mysqli_connect_error());
    }

} else {
    // Entorno local (XAMPP/Docker): Conexión normal sin SSL
    if (!mysqli_real_connect($conn, $servername, $username, $password, $dbname, $dbport)) {
        die("Error de conexión local (" . mysqli_connect_errno() . "): " . mysqli_connect_error());
    }
}

// Establecer el charset a utf8mb4 para compatibilidad
if (!$conn->set_charset("utf8mb4")) {
    // No es un error fatal, pero es bueno registrarlo
    // error_log("Error cargando el conjunto de caracteres utf8mb4: " . $conn->error);
}

// La variable $conn ya está lista para ser usada en otros scripts.
?>