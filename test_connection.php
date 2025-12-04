<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Conexión - Sistema Inmobiliario</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .success { color: green; background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #ffe8e8; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; background: #e8f0ff; padding: 15px; border-radius: 5px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>🔧 Test de Conexión y Estado del Sistema</h1>
    
    <?php
    echo "<div class='info'><strong>Información del Servidor:</strong><br>";
    echo "PHP Version: " . phpversion() . "<br>";
    echo "Servidor: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
    echo "Fecha/Hora: " . date('Y-m-d H:i:s') . "<br></div>";
    
    try {
        require_once 'conexion.php';
        echo "<div class='success'>✅ Conexión a la base de datos exitosa</div>";
        
        // Verificar tablas existentes
        $sql = "SHOW TABLES";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            echo "<h3>📋 Tablas en la base de datos 'inmobil':</h3>";
            echo "<table><tr><th>Tabla</th><th>Registros</th></tr>";
            
            while($row = $result->fetch_array()) {
                $table_name = $row[0];
                $count_sql = "SELECT COUNT(*) as count FROM $table_name";
                $count_result = $conn->query($count_sql);
                $count = $count_result->fetch_assoc()['count'];
                
                echo "<tr><td>$table_name</td><td>$count registros</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='error'>⚠️ No se encontraron tablas. Ejecute setup_database.php primero.</div>";
        }
        
        // Verificar directorios importantes
        echo "<h3>📁 Estado de Directorios:</h3>";
        echo "<ul>";
        echo "<li>uploads/: " . (is_dir('uploads') ? '✅ Existe' : '❌ No existe') . "</li>";
        echo "<li>estilos.css: " . (file_exists('estilos.css') ? '✅ Existe' : '❌ No existe') . "</li>";
        echo "</ul>";
        
        $conn->close();
        
    } catch(Exception $e) {
        echo "<div class='error'>❌ Error de conexión: " . $e->getMessage() . "</div>";
    }
    ?>
    
    <hr>
    <h3>🚀 Enlaces de Acceso Rápido:</h3>
    <p><a href="index.php" style="background: #2196F3; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">🏠 Página Principal</a></p>
    <p><a href="setup_database.php" style="background: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">⚙️ Configurar Base de Datos</a></p>
    
</body>
</html>