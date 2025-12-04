<?php
require_once '../conexion.php';

echo "<h2>Actualizar Estructura de Tabla Usuarios</h2>";

// Modificar el ENUM para incluir los nuevos roles
$sql = "ALTER TABLE usuarios MODIFY COLUMN rol enum('administrador','usuario','agente_senior','agente_junior','secretaria') NOT NULL DEFAULT 'usuario'";

echo "<h3>1. Actualizando estructura de tabla:</h3>";
echo "<p><strong>SQL:</strong> <code>$sql</code></p>";

if ($conn->query($sql)) {
    echo "<p style='color: green;'>✅ Estructura de tabla actualizada exitosamente</p>";
    
    echo "<h3>2. Asignando roles a usuarios:</h3>";
    
    // Actualizar agente_senior1
    $sql2 = "UPDATE usuarios SET rol = 'agente_senior' WHERE usuario = 'agente_senior1'";
    if ($conn->query($sql2)) {
        echo "<p style='color: green;'>✅ agente_senior1 → agente_senior (filas afectadas: " . $conn->affected_rows . ")</p>";
    } else {
        echo "<p style='color: red;'>❌ Error agente_senior1: " . $conn->error . "</p>";
    }
    
    // Actualizar agente_junior1  
    $sql3 = "UPDATE usuarios SET rol = 'agente_junior' WHERE usuario = 'agente_junior1'";
    if ($conn->query($sql3)) {
        echo "<p style='color: green;'>✅ agente_junior1 → agente_junior (filas afectadas: " . $conn->affected_rows . ")</p>";
    } else {
        echo "<p style='color: red;'>❌ Error agente_junior1: " . $conn->error . "</p>";
    }
    
    echo "<h3>3. Verificación final:</h3>";
    $result = $conn->query("SELECT usuario, rol, nombre FROM usuarios WHERE usuario IN ('agente_senior1', 'agente_junior1')");
    
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Usuario</th><th>Rol</th><th>Nombre</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['usuario']) . "</td>";
            echo "<td><strong>" . htmlspecialchars($row['rol']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<br><p style='color: blue;'><strong>¡Listo! Ahora puedes probar el login.</strong></p>";
    echo "<p><a href='../auth/login.php'>→ Ir al Login</a></p>";
    
} else {
    echo "<p style='color: red;'>❌ Error actualizando estructura: " . $conn->error . "</p>";
}
?>