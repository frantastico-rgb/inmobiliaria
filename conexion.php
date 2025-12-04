<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inmobil";
//create conection
$conn = new mysqli($servername, $username, $password, $dbname);
//echo "Intentando conectar...<br>"; // Añade esta línea

//Check connection
if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
}

//echo "Conexión exitosa (dentro de conexion.php)!<br>"; // Añade esta línea

//$conn->close();
?>