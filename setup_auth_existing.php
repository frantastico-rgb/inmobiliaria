<?php
/**
 * Script para actualizar la tabla usuarios existente y configurar el sistema de autenticación
 * Versión: 2.0 - Adaptado para tabla usuarios existente
 */

require_once 'conexion.php';

echo "<h2>Configuración del Sistema de Autenticación</h2>";
echo "<p>Actualizando tabla usuarios existente...</p>";

try {
    // Verificar estructura actual de la tabla usuarios
    $result = $conn->query("DESCRIBE usuarios");
    echo "<h3>Estructura actual de la tabla usuarios:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th><th>Extra</th></tr>";
    
    $existing_columns = [];
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    // Agregar campos necesarios si no existen
    $required_fields = [
        'password_hash' => "ADD COLUMN password_hash VARCHAR(255) NULL COMMENT 'Hash de la contraseña'",
        'email' => "ADD COLUMN email VARCHAR(150) NULL COMMENT 'Email del usuario'",
        'activo' => "ADD COLUMN activo TINYINT(1) DEFAULT 1 COMMENT 'Usuario activo'",
        'fecha_creacion' => "ADD COLUMN fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación'",
        'fecha_ultimo_acceso' => "ADD COLUMN fecha_ultimo_acceso TIMESTAMP NULL COMMENT 'Último acceso'",
        'intentos_fallidos' => "ADD COLUMN intentos_fallidos INT DEFAULT 0 COMMENT 'Intentos de login fallidos'",
        'bloqueado_hasta' => "ADD COLUMN bloqueado_hasta TIMESTAMP NULL COMMENT 'Bloqueado hasta fecha'"
    ];
    
    echo "<h3>Agregando campos necesarios:</h3>";
    foreach ($required_fields as $field => $sql) {
        if (!in_array($field, $existing_columns)) {
            $alter_sql = "ALTER TABLE usuarios $sql";
            if ($conn->query($alter_sql)) {
                echo "✅ Campo '$field' agregado correctamente<br>";
            } else {
                echo "❌ Error agregando '$field': " . $conn->error . "<br>";
            }
        } else {
            echo "ℹ️ Campo '$field' ya existe<br>";
        }
    }
    
    // Crear tabla de sesiones
    echo "<br><h3>Creando tabla de sesiones:</h3>";
    $sesiones_sql = "CREATE TABLE IF NOT EXISTS sesiones_usuario (
        id VARCHAR(64) PRIMARY KEY COMMENT 'ID único de sesión',
        usuario_id INT NOT NULL COMMENT 'ID del usuario',
        ip_address VARCHAR(45) COMMENT 'Dirección IP',
        user_agent TEXT COMMENT 'User Agent del navegador',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación',
        fecha_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última actividad',
        activa TINYINT(1) DEFAULT 1 COMMENT 'Sesión activa',
        INDEX idx_usuario (usuario_id),
        INDEX idx_activa (activa),
        INDEX idx_fecha_actividad (fecha_actividad),
        CONSTRAINT fk_sesiones_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Gestión de sesiones de usuario'";
    
    if ($conn->query($sesiones_sql)) {
        echo "✅ Tabla 'sesiones_usuario' creada correctamente<br>";
    } else {
        echo "❌ Error creando tabla 'sesiones_usuario': " . $conn->error . "<br>";
    }
    
    // Modificar campo 'rol' si es necesario
    echo "<br><h3>Configurando campo rol:</h3>";
    if (in_array('rol', $existing_columns)) {
        $modify_rol = "ALTER TABLE usuarios MODIFY COLUMN rol ENUM('administrador', 'agente', 'usuario') DEFAULT 'usuario' COMMENT 'Rol del usuario'";
        if ($conn->query($modify_rol)) {
            echo "✅ Campo 'rol' actualizado correctamente<br>";
        } else {
            echo "❌ Error modificando 'rol': " . $conn->error . "<br>";
        }
    }
    
    // Crear usuarios demo si no existen
    echo "<br><h3>Creando usuarios demo:</h3>";
    
    $demo_users = [
        [
            'usuario' => 'admin',
            'clave' => 'admin123', 
            'nombre' => 'Administrador Sistema',
            'email' => 'admin@inmobiliaria.com',
            'rol' => 'administrador'
        ],
        [
            'usuario' => 'agente1',
            'clave' => 'agente123',
            'nombre' => 'Juan Pérez - Agente',
            'email' => 'agente1@inmobiliaria.com',
            'rol' => 'agente'
        ],
        [
            'usuario' => 'agente2',
            'clave' => 'agente123',
            'nombre' => 'María García - Agente',
            'email' => 'agente2@inmobiliaria.com',
            'rol' => 'agente'
        ]
    ];
    
    foreach ($demo_users as $user) {
        // Verificar si el usuario ya existe
        $check_user = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ? OR email = ?");
        $check_user->bind_param("ss", $user['usuario'], $user['email']);
        $check_user->execute();
        $result = $check_user->get_result();
        
        if ($result->num_rows > 0) {
            echo "ℹ️ Usuario '{$user['usuario']}' ya existe<br>";
        } else {
            $password_hash = password_hash($user['clave'], PASSWORD_BCRYPT);
            
            $insert_user = $conn->prepare("INSERT INTO usuarios (usuario, clave, password_hash, nombre, email, rol, activo) VALUES (?, ?, ?, ?, ?, ?, 1)");
            $insert_user->bind_param("ssssss", 
                $user['usuario'], 
                $user['clave'], 
                $password_hash, 
                $user['nombre'], 
                $user['email'], 
                $user['rol']
            );
            
            if ($insert_user->execute()) {
                echo "✅ Usuario '{$user['usuario']}' creado correctamente (email: {$user['email']})<br>";
            } else {
                echo "❌ Error creando usuario '{$user['usuario']}': " . $conn->error . "<br>";
            }
        }
    }
    
    // Agregar índices para optimización
    echo "<br><h3>Optimizando índices:</h3>";
    $indices = [
        "CREATE INDEX idx_usuario_email ON usuarios(email)" => "email",
        "CREATE INDEX idx_usuario_activo ON usuarios(activo)" => "activo",
        "CREATE INDEX idx_usuario_rol ON usuarios(rol)" => "rol"
    ];
    
    foreach ($indices as $sql => $nombre) {
        $result = $conn->query($sql);
        if ($result) {
            echo "✅ Índice '$nombre' creado<br>";
        } else {
            echo "ℹ️ Índice '$nombre' ya existe o no se pudo crear<br>";
        }
    }
    
    echo "<br><h3>✅ Configuración completada exitosamente</h3>";
    echo "<p><strong>Usuarios demo creados:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Administrador:</strong> usuario 'admin', contraseña 'admin123'</li>";
    echo "<li><strong>Agente 1:</strong> usuario 'agente1', contraseña 'agente123'</li>";
    echo "<li><strong>Agente 2:</strong> usuario 'agente2', contraseña 'agente123'</li>";
    echo "</ul>";
    
    echo "<p><a href='auth/login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir al Login</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Error durante la configuración:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
} finally {
    $conn->close();
}
?>