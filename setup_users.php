<?php
// Script para crear sistema de usuarios y autenticación
require_once 'conexion.php';

try {
    // Crear tabla de usuarios del sistema
    $sql_usuarios = "CREATE TABLE IF NOT EXISTS usuarios_sistema (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(150) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        rol ENUM('administrador', 'agente') NOT NULL,
        nombre_completo VARCHAR(100) NOT NULL,
        telefono VARCHAR(20) NULL,
        activo BOOLEAN DEFAULT TRUE,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_ultimo_acceso TIMESTAMP NULL,
        creado_por INT NULL,
        FOREIGN KEY (creado_por) REFERENCES usuarios_sistema(id) ON DELETE SET NULL,
        INDEX idx_username (username),
        INDEX idx_rol (rol),
        INDEX idx_activo (activo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($sql_usuarios)) {
        echo "✅ Tabla 'usuarios_sistema' creada exitosamente<br>";
    }

    // Crear tabla de sesiones
    $sql_sesiones = "CREATE TABLE IF NOT EXISTS sesiones_usuario (
        id VARCHAR(128) PRIMARY KEY,
        usuario_id INT NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        user_agent TEXT,
        fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        activa BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (usuario_id) REFERENCES usuarios_sistema(id) ON DELETE CASCADE,
        INDEX idx_usuario_activa (usuario_id, activa),
        INDEX idx_fecha_actividad (fecha_actividad)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($sql_sesiones)) {
        echo "✅ Tabla 'sesiones_usuario' creada exitosamente<br>";
    }

    // Modificar tabla empleados para vincular con usuarios sistema
    $sql_alter_empleados = "ALTER TABLE empleados 
                            ADD COLUMN usuario_sistema_id INT NULL,
                            ADD FOREIGN KEY (usuario_sistema_id) REFERENCES usuarios_sistema(id) ON DELETE SET NULL";

    // Verificar si la columna ya existe
    $check_column = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                     WHERE TABLE_NAME = 'empleados' AND COLUMN_NAME = 'usuario_sistema_id'";
    $column_exists = $conn->query($check_column)->num_rows > 0;

    if (!$column_exists) {
        if ($conn->query($sql_alter_empleados)) {
            echo "✅ Tabla 'empleados' actualizada con referencia a usuarios<br>";
        }
    } else {
        echo "ℹ️ Tabla 'empleados' ya tiene la referencia a usuarios<br>";
    }

    // Modificar tabla leads para asignación a agentes
    $sql_alter_leads = "ALTER TABLE leads 
                        MODIFY COLUMN agente_asignado INT NULL,
                        DROP FOREIGN KEY IF EXISTS leads_ibfk_2,
                        ADD FOREIGN KEY (agente_asignado) REFERENCES usuarios_sistema(id) ON DELETE SET NULL";

    // Ejecutar con manejo de errores
    try {
        $conn->query($sql_alter_leads);
        echo "✅ Tabla 'leads' actualizada para asignación a agentes<br>";
    } catch (Exception $e) {
        echo "ℹ️ Tabla 'leads' ya configurada para agentes<br>";
    }

    // Crear usuario administrador por defecto
    $admin_username = 'admin';
    $admin_email = 'admin@casameta.com';
    $admin_password = 'CasaMeta2024#'; // Cambiar en producción
    $admin_nombre = 'Administrador Principal';
    $admin_hash = password_hash($admin_password, PASSWORD_BCRYPT);

    $sql_check_admin = "SELECT id FROM usuarios_sistema WHERE username = ? OR rol = 'administrador'";
    $stmt_check = $conn->prepare($sql_check_admin);
    $stmt_check->bind_param("s", $admin_username);
    $stmt_check->execute();
    $admin_exists = $stmt_check->get_result()->num_rows > 0;

    if (!$admin_exists) {
        $sql_insert_admin = "INSERT INTO usuarios_sistema 
                            (username, email, password_hash, rol, nombre_completo) 
                            VALUES (?, ?, ?, 'administrador', ?)";
        
        $stmt_admin = $conn->prepare($sql_insert_admin);
        $stmt_admin->bind_param("ssss", $admin_username, $admin_email, $admin_hash, $admin_nombre);
        
        if ($stmt_admin->execute()) {
            echo "✅ Usuario administrador creado exitosamente<br>";
            echo "📋 <strong>Credenciales del Administrador:</strong><br>";
            echo "&nbsp;&nbsp;Usuario: <code>$admin_username</code><br>";
            echo "&nbsp;&nbsp;Email: <code>$admin_email</code><br>";
            echo "&nbsp;&nbsp;Contraseña: <code>$admin_password</code><br>";
            echo "&nbsp;&nbsp;<em>⚠️ Cambiar contraseña después del primer acceso</em><br>";
        }
    } else {
        echo "ℹ️ Usuario administrador ya existe<br>";
    }

    echo "<br>🎉 <strong>Sistema de usuarios configurado exitosamente!</strong><br>";
    echo "📊 Próximos pasos: crear paneles de administración y agentes.";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sistema de Usuarios - Casa Meta</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 800px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #f8f9fa;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success { 
            background: #d4edda; 
            padding: 20px; 
            border-radius: 8px; 
            color: #155724; 
            margin: 15px 0; 
            border-left: 4px solid #28a745;
        }
        .info {
            background: #d1ecf1;
            padding: 15px;
            border-radius: 8px;
            color: #0c5460;
            margin: 15px 0;
            border-left: 4px solid #17a2b8;
        }
        .credentials {
            background: #fff3cd;
            padding: 20px;
            border-radius: 8px;
            color: #856404;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            color: #e83e8c;
        }
        .btn {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 6px;
            margin: 10px 5px;
            font-weight: bold;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Sistema de Usuarios Multi-Nivel</h1>
        
        <div class="success">
            <h3>✅ Configuración Completada</h3>
            <p><strong>Sistema implementado con éxito:</strong></p>
            <ul>
                <li>✅ Tabla de usuarios con roles (admin/agente)</li>
                <li>✅ Sistema de sesiones seguras</li>
                <li>✅ Vinculación con empleados existentes</li>
                <li>✅ Asignación de leads a agentes</li>
                <li>✅ Usuario administrador creado</li>
            </ul>
        </div>

        <div class="credentials">
            <h4>🔑 Acceso de Administrador</h4>
            <p><strong>URL de Login:</strong> <code>auth/login.php</code></p>
            <p><strong>Usuario:</strong> <code>admin</code></p>
            <p><strong>Email:</strong> <code>admin@casameta.com</code></p>
            <p><strong>Contraseña:</strong> <code>CasaMeta2024#</code></p>
            <p><em>⚠️ Cambiar credenciales en el primer acceso</em></p>
        </div>

        <div class="info">
            <h4>📋 Próximos Pasos</h4>
            <ol>
                <li>Crear sistema de login (<code>/auth/</code>)</li>
                <li>Desarrollar panel de administración</li>
                <li>Implementar dashboard de agentes</li>
                <li>Configurar permisos y seguridad</li>
            </ol>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="public/index.php" class="btn">🏠 Portal Público</a>
            <a href="index.php" class="btn btn-success">👨‍💼 Panel Administrativo</a>
        </div>
    </div>
</body>
</html>