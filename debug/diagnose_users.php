<?php
require_once '../conexion.php';

echo "<h2>Diagnóstico de Tabla Usuarios</h2>";

// Verificar estructura de la tabla
echo "<h3>1. Estructura de la tabla 'usuarios':</h3>";
$result = $conn->query("DESCRIBE usuarios");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

// Verificar datos actuales
echo "<h3>2. Usuarios actuales con agente en el nombre:</h3>";
$result = $conn->query("SELECT id, usuario, nombre, rol, activo FROM usuarios WHERE usuario LIKE '%agente%'");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Rol</th><th>Activo</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['usuario']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($row['rol'] ?: 'VACÍO') . "</strong></td>";
        echo "<td>" . ($row['activo'] ? 'Sí' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No se encontraron usuarios con 'agente' en el nombre.</p>";
}

// Intentar actualización manual directa
echo "<h3>3. Actualización manual:</h3>";
echo "<form method='POST'>";
echo "<button type='submit' name='fix_roles' value='1'>Corregir roles manualmente</button>";
echo "</form>";

if (isset($_POST['fix_roles'])) {
    echo "<h4>Ejecutando corrección:</h4>";
    
    // Actualizar agente_junior1
    $sql = "UPDATE usuarios SET rol = 'agente_junior' WHERE usuario = 'agente_junior1'";
    echo "<p>Query: <code>$sql</code></p>";
    $result = $conn->query($sql);
    if ($result) {
        echo "<p style='color: green;'>✅ agente_junior1 actualizado (filas afectadas: " . $conn->affected_rows . ")</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . $conn->error . "</p>";
    }
    
    // Actualizar agente_senior1
    $sql = "UPDATE usuarios SET rol = 'agente_senior' WHERE usuario = 'agente_senior1'";
    echo "<p>Query: <code>$sql</code></p>";
    $result = $conn->query($sql);
    if ($result) {
        echo "<p style='color: green;'>✅ agente_senior1 actualizado (filas afectadas: " . $conn->affected_rows . ")</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . $conn->error . "</p>";
    }
    
    echo "<p><a href=''>Recargar para ver cambios</a></p>";
}
?>