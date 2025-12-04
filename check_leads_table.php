<?php
require_once 'conexion.php';

echo "<h2>Verificando estructura de tabla leads</h2>";

try {
    // Verificar estructura de la tabla leads
    $result = $conn->query("DESCRIBE leads");
    if ($result) {
        echo "<h3>Estructura de tabla leads:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Default</th></tr>";
        
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
        
        // Verificar si existe fecha_creacion
        if (in_array('fecha_creacion', $columns)) {
            echo "✅ La columna 'fecha_creacion' existe en tabla leads<br>";
        } else {
            echo "❌ La columna 'fecha_creacion' NO existe en tabla leads<br>";
            echo "Columnas disponibles: " . implode(', ', $columns) . "<br>";
            
            // Buscar columnas similares
            $date_columns = array_filter($columns, function($col) {
                return strpos(strtolower($col), 'fecha') !== false || strpos(strtolower($col), 'date') !== false || strpos(strtolower($col), 'created') !== false;
            });
            
            if (!empty($date_columns)) {
                echo "Columnas de fecha encontradas: " . implode(', ', $date_columns) . "<br>";
            }
        }
        
    } else {
        echo "❌ Error consultando tabla leads: " . $conn->error . "<br>";
    }
    
    // Verificar algunos registros de ejemplo
    echo "<h3>Registros de ejemplo en leads:</h3>";
    $sample = $conn->query("SELECT * FROM leads LIMIT 3");
    if ($sample && $sample->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        $first_row = true;
        while ($row = $sample->fetch_assoc()) {
            if ($first_row) {
                echo "<tr>";
                foreach ($row as $key => $value) {
                    echo "<th>" . htmlspecialchars($key) . "</th>";
                }
                echo "</tr>";
                $first_row = false;
            }
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No hay registros en la tabla leads o la tabla no existe.<br>";
    }

} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
} finally {
    $conn->close();
}
?>