<?php
// Script para agregar columnas de país y enlace de video
require_once 'conexion.php';

echo "<h2>🔧 Agregando Campos de País y Enlace de Video</h2>\n";

try {
    $changes = [];
    
    // 1. Agregar columna pais_inm
    $sql_check_pais = "SHOW COLUMNS FROM inmuebles LIKE 'pais_inm'";
    $result_pais = $conn->query($sql_check_pais);
    
    if ($result_pais->num_rows == 0) {
        $sql_pais = "ALTER TABLE inmuebles ADD COLUMN pais_inm VARCHAR(100) DEFAULT 'Colombia' AFTER ciudad_inm";
        if ($conn->query($sql_pais) === TRUE) {
            $changes[] = "✅ Columna 'pais_inm' agregada exitosamente";
        } else {
            echo "❌ Error al agregar columna 'pais_inm': " . $conn->error . "<br>\n";
        }
    } else {
        $changes[] = "ℹ️ Columna 'pais_inm' ya existe";
    }
    
    // 2. Agregar columna video_url para enlaces externos
    $sql_check_video_url = "SHOW COLUMNS FROM inmuebles LIKE 'video_url'";
    $result_video_url = $conn->query($sql_check_video_url);
    
    if ($result_video_url->num_rows == 0) {
        $sql_video_url = "ALTER TABLE inmuebles ADD COLUMN video_url VARCHAR(500) NULL AFTER video";
        if ($conn->query($sql_video_url) === TRUE) {
            $changes[] = "✅ Columna 'video_url' agregada exitosamente";
        } else {
            echo "❌ Error al agregar columna 'video_url': " . $conn->error . "<br>\n";
        }
    } else {
        $changes[] = "ℹ️ Columna 'video_url' ya existe";
    }
    
    // Mostrar cambios realizados
    foreach ($changes as $change) {
        echo "<p>$change</p>\n";
    }
    
    // Verificar estructura final multimedia
    echo "<br><h3>📋 Campos Multimedia y Ubicación Actualizados:</h3>\n";
    $sql_multimedia = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_COMMENT
                       FROM INFORMATION_SCHEMA.COLUMNS 
                       WHERE TABLE_SCHEMA = 'inmobil' 
                       AND TABLE_NAME = 'inmuebles' 
                       AND COLUMN_NAME IN ('ciudad_inm', 'pais_inm', 'foto', 'foto_2', 'video', 'video_url')
                       ORDER BY ORDINAL_POSITION";
    
    $result_multimedia = $conn->query($sql_multimedia);
    
    if ($result_multimedia->num_rows > 0) {
        echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>Campo</th><th>Tipo</th><th>Permite NULL</th><th>Por Defecto</th><th>Descripción</th>";
        echo "</tr>\n";
        
        while ($row = $result_multimedia->fetch_assoc()) {
            $descripcion = '';
            switch ($row['COLUMN_NAME']) {
                case 'ciudad_inm':
                    $descripcion = '🏙️ Ciudad del inmueble';
                    break;
                case 'pais_inm':
                    $descripcion = '🌍 País del inmueble';
                    break;
                case 'foto':
                    $descripcion = '📷 Foto principal (archivo)';
                    break;
                case 'foto_2':
                    $descripcion = '📸 Foto secundaria (archivo)';
                    break;
                case 'video':
                    $descripcion = '🎥 Video local (archivo hasta 50MB)';
                    break;
                case 'video_url':
                    $descripcion = '🔗 Enlace a video externo (YouTube, Instagram, etc.)';
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
    
    echo "<br><h3>🎯 Nuevas Capacidades:</h3>\n";
    echo "<ul>\n";
    echo "<li><strong>📍 País:</strong> Campo para ubicación internacional</li>\n";
    echo "<li><strong>🎥 Video Local:</strong> Archivo subido (máx. 50MB, 2 min recomendado)</li>\n";
    echo "<li><strong>🔗 Video Externo:</strong> Enlace a YouTube, Instagram, Vimeo, etc.</li>\n";
    echo "</ul>\n";
    
    echo "<br><h3>💡 Beneficios del Video Externo:</h3>\n";
    echo "<ul>\n";
    echo "<li>✅ <strong>Sin límite de duración</strong> - Videos largos de tours completos</li>\n";
    echo "<li>✅ <strong>Sin límite de tamaño</strong> - Alta calidad sin preocupaciones</li>\n";
    echo "<li>✅ <strong>Fácil compartir</strong> - Links directos desde redes sociales</li>\n";
    echo "<li>✅ <strong>Ancho de banda optimizado</strong> - No consume servidor local</li>\n";
    echo "<li>✅ <strong>SEO mejorado</strong> - Videos en YouTube mejoran posicionamiento</li>\n";
    echo "</ul>\n";
    
    echo "<br><p style='color: green;'>🚀 <strong>¡Base de datos actualizada! El sistema ahora soporta videos híbridos.</strong></p>\n";
    echo "<p><a href='editar_inmueble.php?id=5' style='background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>✏️ Probar Edición con Nuevos Campos</a></p>\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>\n";
}

$conn->close();
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { width: 100%; margin: 15px 0; }
    th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
    th { background-color: #f2f2f2; font-weight: bold; }
    ul { margin: 10px 0; }
    li { margin: 5px 0; }
</style>