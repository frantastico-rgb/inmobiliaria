<?php
require_once '../conexion.php';

echo "<h2>Corrección de Roles de Usuario</h2>";

// Actualizar agente_junior1
$sql = "UPDATE usuarios SET rol = 'agente_junior' WHERE usuario = 'agente_junior1'";
if ($conn->query($sql)) {
    echo "<p style='color: green;'>✅ agente_junior1 -> rol actualizado a 'agente_junior'</p>";
} else {
    echo "<p style='color: red;'>❌ Error actualizando agente_junior1: " . $conn->error . "</p>";
}

// Actualizar agente_senior1
$sql = "UPDATE usuarios SET rol = 'agente_senior' WHERE usuario = 'agente_senior1'";
if ($conn->query($sql)) {
    echo "<p style='color: green;'>✅ agente_senior1 -> rol actualizado a 'agente_senior'</p>";
} else {
    echo "<p style='color: red;'>❌ Error actualizando agente_senior1: " . $conn->error . "</p>";
}

// Verificar resultados
echo "<h3>Verificación de roles:</h3>";
$result = $conn->query("SELECT usuario, rol, nombre FROM usuarios WHERE usuario IN ('agente_junior1', 'agente_senior1')");

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
} else {
    echo "<p>No se encontraron usuarios.</p>";
}

echo "<br><p><a href='../auth/login.php'>← Volver al Login</a></p>";
?>