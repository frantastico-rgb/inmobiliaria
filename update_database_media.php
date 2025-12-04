<?php
// Script para agregar las columnas de foto secundaria y video a la tabla inmuebles
require_once 'conexion.php';

echo "<h2>🔧 Actualizando Base de Datos para Multimedia</h2>\n";

try {
    // Verificar si las columnas ya existen
    $sql_check = "SHOW COLUMNS FROM inmuebles LIKE 'foto_2'";
    $result = $conn->query($sql_check);
    
    if ($result->num_rows == 0) {
        // Agregar columna foto_2
        $sql_foto2 = "ALTER TABLE inmuebles ADD COLUMN foto_2 VARCHAR(255) NULL AFTER foto";
        if ($conn->query($sql_foto2) === TRUE) {
            echo "✅ Columna 'foto_2' agregada exitosamente.<br>\n";
        } else {
            echo "❌ Error al agregar columna 'foto_2': " . $conn->error . "<br>\n";
        }
    } else {
        echo "ℹ️ Columna 'foto_2' ya existe.<br>\n";
    }
    
    // Verificar si la columna video existe
    $sql_check_video = "SHOW COLUMNS FROM inmuebles LIKE 'video'";
    $result_video = $conn->query($sql_check_video);
    
    if ($result_video->num_rows == 0) {
        // Agregar columna video
        $sql_video = "ALTER TABLE inmuebles ADD COLUMN video VARCHAR(255) NULL AFTER foto_2";
        if ($conn->query($sql_video) === TRUE) {
            echo "✅ Columna 'video' agregada exitosamente.<br>\n";
        } else {
            echo "❌ Error al agregar columna 'video': " . $conn->error . "<br>\n";
        }
    } else {
        echo "ℹ️ Columna 'video' ya existe.<br>\n";
    }
    
    // Verificar estructura final
    echo "<br><h3>📋 Estructura Actual de la Tabla 'inmuebles':</h3>\n";
    $sql_structure = "DESCRIBE inmuebles";
    $result_structure = $conn->query($sql_structure);
    
    if ($result_structure->num_rows > 0) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por Defecto</th><th>Extra</th></tr>\n";
        while ($row = $result_structure->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
    
    echo "<br><h3>🎯 Resumen de Campos Multimedia:</h3>\n";
    echo "<ul>\n";
    echo "<li><strong>foto:</strong> Foto principal del inmueble</li>\n";
    echo "<li><strong>foto_2:</strong> Foto secundaria/adicional</li>\n";
    echo "<li><strong>video:</strong> Video promocional del inmueble (máx. 2 min)</li>\n";
    echo "</ul>\n";
    
    echo "<br><p>🚀 <strong>La base de datos está lista para multimedia!</strong></p>\n";
    echo "<p><a href='inmuebles.php'>➡️ Ir a Agregar Inmueble</a> | ";
    echo "<a href='lista_inmuebles.php'>📋 Ver Lista de Inmuebles</a></p>\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>\n";
}

$conn->close();
?>