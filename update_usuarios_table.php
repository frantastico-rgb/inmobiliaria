<?php
require_once 'conexion.php';

echo "<h2>Actualizando estructura de tabla usuarios</h2>";

try {
    // Agregar columna fecha_creacion si no existe
    $add_column_sql = "ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del usuario'";
    
    if ($conn->query($add_column_sql)) {
        echo "✅ Columna 'fecha_creacion' agregada/verificada correctamente<br>";
    } else {
        echo "ℹ️ Intentando método alternativo...<br>";
        
        // Verificar si la columna existe
        $check_column = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'fecha_creacion'");
        if ($check_column->num_rows == 0) {
            // La columna no existe, agregarla
            $alt_sql = "ALTER TABLE usuarios ADD COLUMN fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del usuario'";
            if ($conn->query($alt_sql)) {
                echo "✅ Columna 'fecha_creacion' agregada correctamente<br>";
            } else {
                echo "❌ Error agregando columna: " . $conn->error . "<br>";
            }
        } else {
            echo "ℹ️ La columna 'fecha_creacion' ya existe<br>";
        }
    }
    
    // Verificar estructura actual
    echo "<h3>Estructura actual de la tabla usuarios:</h3>";
    $result = $conn->query("DESCRIBE usuarios");
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Default</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<br><h3>✅ Actualización completada</h3>";
    echo "<p><a href='admin/dashboard.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir al Dashboard Admin</a></p>";

} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Error durante la actualización:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
} finally {
    $conn->close();
}
?>