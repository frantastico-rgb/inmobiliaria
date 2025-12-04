<?php
require_once 'conexion.php';

echo "<h2>Limpiando duplicación de contraseñas</h2>";

try {
    // 1. Verificar usuarios con contraseñas duplicadas
    echo "<h3>Estado actual de contraseñas:</h3>";
    $result = $conn->query("SELECT id, usuario, clave, 
                           CASE WHEN password_hash IS NOT NULL THEN 'Sí' ELSE 'No' END as tiene_hash
                           FROM usuarios ORDER BY id");
    
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Usuario</th><th>Clave Texto</th><th>Tiene Hash</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['usuario'] . "</td>";
            echo "<td>" . (strlen($row['clave'] ?? '') > 10 ? 'Encriptada' : htmlspecialchars($row['clave'] ?? 'NULL')) . "</td>";
            echo "<td>" . $row['tiene_hash'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
    
    // 2. Para usuarios principales, limpiar campo 'clave' y dejar solo hash
    echo "<h3>Limpiando campo 'clave' para usuarios con hash:</h3>";
    
    $clean_sql = "UPDATE usuarios 
                  SET clave = NULL 
                  WHERE password_hash IS NOT NULL 
                  AND password_hash != '' 
                  AND usuario IN ('admin', 'agente1', 'agente2')";
    
    if ($conn->query($clean_sql)) {
        echo "✅ Campo 'clave' limpiado para usuarios con hash seguro<br>";
    } else {
        echo "❌ Error limpiando: " . $conn->error . "<br>";
    }
    
    // 3. Mostrar estado final
    echo "<h3>Estado final:</h3>";
    $final_result = $conn->query("SELECT usuario, 
                                  CASE WHEN clave IS NULL THEN 'Limpio' ELSE 'Con texto' END as estado_clave,
                                  CASE WHEN password_hash IS NOT NULL THEN 'Sí' ELSE 'No' END as tiene_hash
                                  FROM usuarios ORDER BY id");
    
    if ($final_result) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Usuario</th><th>Estado Clave</th><th>Tiene Hash</th><th>Recomendación</th></tr>";
        while ($row = $final_result->fetch_assoc()) {
            $recomendacion = ($row['tiene_hash'] === 'Sí' && $row['estado_clave'] === 'Limpio') ? 'OK' : 'Revisar';
            echo "<tr>";
            echo "<td>" . $row['usuario'] . "</td>";
            echo "<td>" . $row['estado_clave'] . "</td>";
            echo "<td>" . $row['tiene_hash'] . "</td>";
            echo "<td style='color: " . ($recomendacion === 'OK' ? 'green' : 'orange') . ";'>" . $recomendacion . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<br><h3>✅ Limpieza de contraseñas completada</h3>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
} finally {
    $conn->close();
}
?>