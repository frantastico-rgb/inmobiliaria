<?php
// Script simple para probar login_process.php directamente
echo "<h2>Test directo de login_process.php</h2>";

// Simular POST data
$_POST['username'] = 'agente_junior1';
$_POST['password'] = '123456';
$_SERVER['REQUEST_METHOD'] = 'POST';

echo "<h3>Datos de entrada:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h3>Incluir archivos de autenticación:</h3>";
try {
    require_once '../conexion.php';
    echo "✅ conexion.php incluido<br>";
    
    require_once '../auth/AuthManager.php';
    echo "✅ AuthManager.php incluido<br>";
    
    $auth = new AuthManager($conn);
    echo "✅ AuthManager instanciado<br>";
    
    echo "<h3>Intentando login:</h3>";
    $result = $auth->login($_POST['username'], $_POST['password'], false);
    
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
    if ($result['success']) {
        echo "<h3>Probando getRedirectUrl:</h3>";
        $redirect = $auth->getRedirectUrl($result['user']['rol']);
        echo "Rol: " . $result['user']['rol'] . "<br>";
        echo "Redirect URL: " . $redirect . "<br>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ ERROR:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>