<?php
// Script para actualizar la tabla oficina con nuevos campos multimedia y geolocalización
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php';

echo "<h2>🔧 Actualizando Base de Datos - Tabla Oficinas</h2>";

// Array de columnas a agregar con sus definiciones SQL
$nuevasColumnas = [
    'ciudad_ofi' => "VARCHAR(100) DEFAULT NULL COMMENT 'Ciudad donde se ubica la oficina'",
    'pais_ofi' => "VARCHAR(100) DEFAULT 'Colombia' COMMENT 'País donde se ubica la oficina'",
    'foto_secundaria_ofi' => "VARCHAR(255) DEFAULT NULL COMMENT 'Ruta de la foto secundaria de la oficina'",
    'video_ofi' => "VARCHAR(255) DEFAULT NULL COMMENT 'Ruta del video local de la oficina'",
    'video_url_ofi' => "TEXT DEFAULT NULL COMMENT 'URL de video externo (YouTube, Instagram, etc.)'",
    'web_p1_ofi' => "TEXT DEFAULT NULL COMMENT 'Enlace web página 1 de la oficina'",
    'web_p2_ofi' => "TEXT DEFAULT NULL COMMENT 'Enlace web página 2 de la oficina'"
];

$errores = 0;
$exitos = 0;

foreach ($nuevasColumnas as $columna => $definicion) {
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc; border-radius: 5px;'>";
    echo "<strong>Agregando columna: $columna</strong><br>";
    
    // Verificar si la columna ya existe
    $checkSql = "SHOW COLUMNS FROM oficina LIKE '$columna'";
    $checkResult = $conn->query($checkSql);
    
    if ($checkResult && $checkResult->num_rows > 0) {
        echo "⚠️ La columna '$columna' ya existe. Saltando...<br>";
        echo "</div>";
        continue;
    }
    
    // Agregar la columna
    $sql = "ALTER TABLE oficina ADD COLUMN $columna $definicion";
    
    if ($conn->query($sql) === TRUE) {
        echo "✅ Columna '$columna' agregada exitosamente<br>";
        echo "📋 Definición: $definicion<br>";
        $exitos++;
    } else {
        echo "❌ Error al agregar columna '$columna': " . $conn->error . "<br>";
        $errores++;
    }
    echo "</div>";
}

echo "<hr>";
echo "<div style='padding: 15px; background-color: #f0f8ff; border-radius: 5px; margin-top: 20px;'>";
echo "<h3>📊 Resumen de Actualización</h3>";
echo "<p><strong>✅ Columnas agregadas exitosamente:</strong> $exitos</p>";
echo "<p><strong>❌ Errores:</strong> $errores</p>";

if ($errores == 0) {
    echo "<p style='color: green; font-weight: bold;'>🎉 ¡Base de datos actualizada completamente!</p>";
    echo "<p>La tabla 'oficina' ahora tiene soporte para:</p>";
    echo "<ul>";
    echo "<li>📍 Ciudad y país</li>";
    echo "<li>📸 Foto principal y secundaria</li>";
    echo "<li>🎬 Video local y enlaces externos</li>";
    echo "<li>🌐 Enlaces web adicionales</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red; font-weight: bold;'>⚠️ Hubo errores en la actualización. Revise los mensajes arriba.</p>";
}
echo "</div>";

echo "<hr>";
echo "<div style='text-align: center; margin-top: 20px;'>";
echo "<a href='oficinas.php' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏢 Crear Nueva Oficina</a> ";
echo "<a href='lista_oficinas.php' style='background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📋 Ver Lista de Oficinas</a>";
echo "</div>";

$conn->close();
?>