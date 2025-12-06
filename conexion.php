<?php
// Lee las credenciales de la base de datos desde las variables de entorno
// Render inyecta estas variables con las credenciales de la DB de Postgres
$servername = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'render_user'; // Usar un valor por defecto que no sea root
$password = getenv('DB_PASSWORD') ?: 'password';
$dbname = getenv('DB_NAME') ?: 'inmobil'; // Ya forzaste 'inmobil' al crear la DB

try {
    // CAMBIO CLAVE: Usar PDO con el driver 'pgsql'
    $conn = new PDO("pgsql:host=$servername;dbname=$dbname;user=$username;password=$password");
    
    // Configuración de errores de PDO
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Si la conexión es exitosa, $conn es el objeto PDO
    // Puedes usar $conn para ejecutar consultas (ej. $conn->query())

} catch (PDOException $e) {
    // Si la conexión falla, muestra el error y detiene la aplicación
    die("Connection failed: " . $e->getMessage());
}
?>