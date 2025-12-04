<?php
require_once 'conexion.php';

echo "<h2>Solucionando el problema de sesiones</h2>";

try {
    // Crear tabla de sesiones si no existe
    $create_sessions_sql = "CREATE TABLE IF NOT EXISTS sesiones_usuario (
        id VARCHAR(64) PRIMARY KEY COMMENT 'ID único de sesión',
        usuario_id INT NOT NULL COMMENT 'ID del usuario',
        ip_address VARCHAR(45) COMMENT 'Dirección IP',
        user_agent TEXT COMMENT 'User Agent del navegador',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación',
        fecha_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última actividad',
        activa TINYINT(1) DEFAULT 1 COMMENT 'Sesión activa',
        INDEX idx_usuario (usuario_id),
        INDEX idx_activa (activa),
        INDEX idx_fecha_actividad (fecha_actividad)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Gestión de sesiones de usuario'";

    if ($conn->query($create_sessions_sql)) {
        echo "✅ Tabla 'sesiones_usuario' creada/verificada correctamente<br>";
    } else {
        echo "❌ Error creando tabla 'sesiones_usuario': " . $conn->error . "<br>";
    }

    // Verificar si los usuarios demo tienen hash de contraseña
    echo "<h3>Verificando usuarios demo:</h3>";
    
    $demo_users = [
        'admin' => 'admin123',
        'agente1' => 'agente123',
        'agente2' => 'agente123'
    ];
    
    foreach ($demo_users as $username => $password) {
        // Verificar si existe
        $check_sql = "SELECT id, password_hash FROM usuarios WHERE usuario = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (empty($user['password_hash'])) {
                // Agregar hash de contraseña
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $update_sql = "UPDATE usuarios SET password_hash = ? WHERE usuario = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ss", $hash, $username);
                
                if ($update_stmt->execute()) {
                    echo "✅ Hash de contraseña agregado para usuario '$username'<br>";
                } else {
                    echo "❌ Error actualizando usuario '$username': " . $conn->error . "<br>";
                }
            } else {
                echo "ℹ️ Usuario '$username' ya tiene hash de contraseña<br>";
            }
        } else {
            echo "❌ Usuario '$username' no encontrado<br>";
        }
    }
    
    // Mostrar usuarios para verificar
    echo "<h3>Estado actual de usuarios:</h3>";
    $users_sql = "SELECT usuario, email, rol, activo, 
                  CASE WHEN password_hash IS NOT NULL AND password_hash != '' THEN 'Sí' ELSE 'No' END as tiene_hash
                  FROM usuarios ORDER BY usuario";
    $result = $conn->query($users_sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Usuario</th><th>Email</th><th>Rol</th><th>Activo</th><th>Tiene Hash</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['usuario']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['rol'] ?? '') . "</td>";
            echo "<td>" . ($row['activo'] ? 'Sí' : 'No') . "</td>";
            echo "<td>" . $row['tiene_hash'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<br><h3>✅ Reparación completada</h3>";
    echo "<p>Ahora puedes intentar hacer login con:</p>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> usuario 'admin', contraseña 'admin123'</li>";
    echo "<li><strong>Agente:</strong> usuario 'agente1', contraseña 'agente123'</li>";
    echo "</ul>";
    
    echo "<p><a href='auth/login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Probar Login</a></p>";

} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Error durante la reparación:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
} finally {
    $conn->close();
}
?>