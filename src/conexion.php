<?php

// Archivo de conexión a la base de datos
$servername = "localhost";
$username = "root"; // Cambia por tu usuario real
$password = "";    // Cambia por tu contraseña real
$dbname = "inmobil"; // Cambia por el nombre real de tu base de datos

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
	die("Conexión fallida: " . $conn->connect_error);
}
