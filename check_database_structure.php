<?php
require_once 'conexion.php';

echo "<h2>Verificando estructura de tablas relacionadas</h2>";

try {
    // Verificar tabla tipo_inmueble
    echo "<h3>Estructura de tabla tipo_inmueble:</h3>";
    $result = $conn->query("DESCRIBE tipo_inmueble");
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Tipo</th></tr>";
        
        $tipo_columns = [];
        while ($row = $result->fetch_assoc()) {
            $tipo_columns[] = $row['Field'];
            echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td></tr>";
        }
        echo "</table><br>";
        echo "Columnas encontradas: " . implode(', ', $tipo_columns) . "<br><br>";
    }
    
    // Verificar algunos registros
    echo "<h3>Registros en tipo_inmueble:</h3>";
    $result = $conn->query("SELECT * FROM tipo_inmueble LIMIT 5");
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        $first = true;
        while ($row = $result->fetch_assoc()) {
            if ($first) {
                echo "<tr>";
                foreach (array_keys($row) as $col) {
                    echo "<th>" . $col . "</th>";
                }
                echo "</tr>";
                $first = false;
            }
            echo "<tr>";
            foreach ($row as $val) {
                echo "<td>" . htmlspecialchars($val ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table><br>";
    }
    
    // Verificar tabla inmuebles
    echo "<h3>Estructura de tabla inmuebles (campos relevantes):</h3>";
    $result = $conn->query("DESCRIBE inmuebles");
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th></tr>";
        while ($row = $result->fetch_assoc()) {
            if (strpos($row['Field'], 'tipo') !== false || strpos($row['Field'], 'id') !== false) {
                echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td></tr>";
            }
        }
        echo "</table><br>";
    }
    
    echo "<h3>✅ Verificación completada</h3>";

} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
} finally {
    $conn->close();
}
?>