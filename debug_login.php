<?php
require_once 'conexion.php';

echo "<h2>Diagnóstico del Sistema de Login</h2>";

// Verificar conexión
if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Error de conexión: " . $conn->connect_error . "</p>";
    exit;
}
echo "<p style='color: green;'>✅ Conexión a base de datos exitosa</p>";

// Verificar estructura de tabla usuarios
echo "<h3>Estructura de tabla usuarios:</h3>";
$result = $conn->query("DESCRIBE usuarios");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
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
} else {
    echo "<p style='color: red;'>❌ Error: " . $conn->error . "</p>";
}

// Verificar usuarios existentes
echo "<h3>Usuarios en la base de datos:</h3>";
$result = $conn->query("SELECT id, usuario, clave, password_hash, nombre, email, rol, activo FROM usuarios");
if ($result) {
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Usuario</th><th>Clave (antigua)</th><th>Password Hash</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Activo</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['usuario'] . "</td>";
            echo "<td>" . substr($row['clave'] ?? 'NULL', 0, 20) . "...</td>";
            echo "<td>" . (isset($row['password_hash']) ? 'Sí (' . strlen($row['password_hash']) . ' chars)' : 'No') . "</td>";
            echo "<td>" . ($row['nombre'] ?? 'NULL') . "</td>";
            echo "<td>" . ($row['email'] ?? 'NULL') . "</td>";
            echo "<td>" . ($row['rol'] ?? 'NULL') . "</td>";
            echo "<td>" . ($row['activo'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No hay usuarios en la tabla</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Error consultando usuarios: " . $conn->error . "</p>";
}

// Verificar si existen usuarios demo específicos
echo "<h3>Verificación de usuarios demo:</h3>";
$demo_users = ['admin', 'agente1', 'agente2'];
foreach ($demo_users as $user) {
    $stmt = $conn->prepare("SELECT usuario, password_hash, activo FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo "<p>✅ Usuario '$user': ";
        echo "Hash=" . (isset($data['password_hash']) ? 'Sí' : 'No') . ", ";
        echo "Activo=" . ($data['activo'] ? 'Sí' : 'No') . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Usuario '$user' no encontrado</p>";
    }
}

// Test de verificación de contraseñas
echo "<h3>Test de verificación de contraseñas:</h3>";
$test_passwords = [
    'admin' => 'admin123',
    'agente1' => 'agente123'
];

foreach ($test_passwords as $user => $pass) {
    $stmt = $conn->prepare("SELECT password_hash FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        if (isset($data['password_hash']) && !empty($data['password_hash'])) {
            $verify = password_verify($pass, $data['password_hash']);
            echo "<p>" . ($verify ? "✅" : "❌") . " Usuario '$user' con contraseña '$pass': " . ($verify ? "Correcta" : "Incorrecta") . "</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Usuario '$user': Sin hash de contraseña</p>";
        }
    }
}

// Crear usuarios demo si no existen
echo "<h3>Creando usuarios demo si no existen:</h3>";
$demo_data = [
    ['admin', 'admin123', 'Administrador Sistema', 'admin@inmobiliaria.com', 'administrador'],
    ['agente1', 'agente123', 'Juan Pérez - Agente', 'agente1@inmobiliaria.com', 'agente'],
    ['agente2', 'agente123', 'María García - Agente', 'agente2@inmobiliaria.com', 'agente']
];

foreach ($demo_data as $data) {
    list($user, $pass, $nombre, $email, $rol) = $data;
    
    // Verificar si existe
    $check = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ?");
    $check->bind_param("s", $user);
    $check->execute();
    $exists = $check->get_result()->num_rows > 0;
    
    if (!$exists) {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $insert = $conn->prepare("INSERT INTO usuarios (usuario, clave, password_hash, nombre, email, rol, activo) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $insert->bind_param("ssssss", $user, $pass, $hash, $nombre, $email, $rol);
        
        if ($insert->execute()) {
            echo "<p style='color: green;'>✅ Usuario '$user' creado exitosamente</p>";
        } else {
            echo "<p style='color: red;'>❌ Error creando '$user': " . $conn->error . "</p>";
        }
    } else {
        // Actualizar hash si no existe
        $update = $conn->prepare("UPDATE usuarios SET password_hash = ?, nombre = ?, email = ?, rol = ?, activo = 1 WHERE usuario = ?");
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $update->bind_param("sssss", $hash, $nombre, $email, $rol, $user);
        
        if ($update->execute()) {
            echo "<p style='color: blue;'>🔄 Usuario '$user' actualizado</p>";
        } else {
            echo "<p style='color: red;'>❌ Error actualizando '$user': " . $conn->error . "</p>";
        }
    }
}

echo "<h3>✅ Diagnóstico completado</h3>";
echo "<p><a href='auth/login.php'>Probar Login</a></p>";

$conn->close();
?>