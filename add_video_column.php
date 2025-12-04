<?php
// Script para agregar SOLO la columna video a la tabla inmuebles
require_once 'conexion.php';

echo "<h2>🎥 Agregando Columna VIDEO a la tabla inmuebles</h2>\n";

try {
    // Verificar si la columna video ya existe
    $sql_check_video = "SHOW COLUMNS FROM inmuebles LIKE 'video'";
    $result_video = $conn->query($sql_check_video);
    
    if ($result_video->num_rows == 0) {
        // Agregar columna video después de foto_2
        $sql_video = "ALTER TABLE inmuebles ADD COLUMN video VARCHAR(255) NULL AFTER foto_2";
        if ($conn->query($sql_video) === TRUE) {
            echo "✅ <strong>Columna 'video' agregada exitosamente!</strong><br>\n";
        } else {
            echo "❌ Error al agregar columna 'video': " . $conn->error . "<br>\n";
        }
    } else {
        echo "ℹ️ Columna 'video' ya existe en la tabla.<br>\n";
    }
    
    // Verificar la estructura final específica para multimedia
    echo "<br><h3>📋 Campos Multimedia en la tabla 'inmuebles':</h3>\n";
    $sql_multimedia = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
                       FROM INFORMATION_SCHEMA.COLUMNS 
                       WHERE TABLE_SCHEMA = 'inmobil' 
                       AND TABLE_NAME = 'inmuebles' 
                       AND COLUMN_NAME IN ('foto', 'foto_1', 'foto_2', 'video')
                       ORDER BY ORDINAL_POSITION";
    
    $result_multimedia = $conn->query($sql_multimedia);
    
    if ($result_multimedia->num_rows > 0) {
        echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>\n";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>Campo</th><th>Tipo</th><th>Permite NULL</th><th>Por Defecto</th><th>Descripción</th>";
        echo "</tr>\n";
        
        while ($row = $result_multimedia->fetch_assoc()) {
            $descripcion = '';
            switch ($row['COLUMN_NAME']) {
                case 'foto':
                    $descripcion = '📷 Foto principal del inmueble';
                    break;
                case 'foto_1':
                    $descripcion = '📸 Foto alternativa 1';
                    break;
                case 'foto_2':
                    $descripcion = '📸 Foto alternativa 2';
                    break;
                case 'video':
                    $descripcion = '🎥 Video promocional (máx. 2 min)';
                    break;
            }
            
            echo "<tr>";
            echo "<td><strong>" . $row['COLUMN_NAME'] . "</strong></td>";
            echo "<td>" . $row['DATA_TYPE'] . "</td>";
            echo "<td>" . $row['IS_NULLABLE'] . "</td>";
            echo "<td>" . ($row['COLUMN_DEFAULT'] ?: 'NULL') . "</td>";
            echo "<td>" . $descripcion . "</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
    
    echo "<br><h3>🎯 SQL para Inserción Actualizada:</h3>\n";
    echo "<pre style='background-color: #f5f5f5; padding: 10px; border-radius: 5px;'>";
    echo "INSERT INTO inmuebles (\n";
    echo "    dir_inm, barrio_inm, ciudad_inm, pais_inm,\n";
    echo "    latitude, longitud,\n";
    echo "    foto, foto_2, video,  -- 📸🎥 MULTIMEDIA\n";
    echo "    web_p1, web_p2,\n";
    echo "    cod_tipoinm, num_hab, precio_alq,\n";
    echo "    cod_prop, caract_inm, notas_inm\n";
    echo ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)\n";
    echo "</pre>";
    
    echo "<br><p style='color: green;'>🚀 <strong>¡Base de datos lista para videos!</strong></p>\n";
    echo "<p><a href='inmuebles.php' style='background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>➡️ Probar Formulario con Video</a></p>\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>\n";
}

$conn->close();
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { width: 100%; margin: 10px 0; }
    th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
    th { background-color: #f2f2f2; }
    pre { overflow-x: auto; }
</style>