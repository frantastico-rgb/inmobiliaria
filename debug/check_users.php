<?php
require_once '../conexion.php';

echo "<h2>Verificación de Usuarios</h2>";

$users = ['agente_senior1', 'agente_junior1', 'admin', 'administrador'];

foreach ($users as $username) {
    echo "<h4>Buscando usuario: $username</h4>";
    
    $sql = "SELECT id, usuario, nombre, email, rol, activo FROM usuarios WHERE usuario = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo "<pre>";
        print_r($row);
        echo "</pre>";
        
        // Verificar password
        echo "<p><strong>Testing password '123456':</strong> ";
        if (password_verify('123456', $row['password_hash'])) {
            echo "<span style='color: green;'>✅ Password correcto</span>";
        } else {
            echo "<span style='color: red;'>❌ Password incorrecto</span>";
        }
        echo "</p>";
        
    } else {
        echo "<p style='color: red;'>❌ Usuario no encontrado</p>";
    }
    echo "<hr>";
}
?>

<h3>Crear usuarios de prueba si no existen:</h3>
<form method="POST" action="">
    <input type="hidden" name="create_test_users" value="1">
    <button type="submit">Crear usuarios de prueba</button>
</form>

<?php
if (isset($_POST['create_test_users'])) {
    $test_users = [
        ['agente_senior1', 'María González Senior', 'maria.senior@test.com', 'agente_senior'],
        ['agente_junior1', 'Carlos López Junior', 'carlos.junior@test.com', 'agente_junior']
    ];
    
    foreach ($test_users as [$username, $nombre, $email, $rol]) {
        $password_hash = password_hash('123456', PASSWORD_BCRYPT);
        $sql = "INSERT INTO usuarios (usuario, password_hash, nombre, email, rol, activo) VALUES (?, ?, ?, ?, ?, 1)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $username, $password_hash, $nombre, $email, $rol);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✅ Usuario $username creado exitosamente</p>";
        } else {
            echo "<p style='color: red;'>❌ Error creando $username: " . $conn->error . "</p>";
        }
    }
}