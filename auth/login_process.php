<?php
// Limpiar completamente el output buffer
while (ob_get_level()) {
    ob_end_clean();
}

// Iniciar nuevo buffer limpio
ob_start();

// Configuración estricta de errores
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Headers limpios
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

try {
    require_once '../conexion.php';
    require_once 'AuthManager.php';

    // Verificar método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        $response = ['success' => false, 'error' => 'Método no permitido'];
        ob_clean();
        echo json_encode($response);
        exit;
    }

    // Obtener datos
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    // Validar campos
    if (empty($username) || empty($password)) {
        $response = ['success' => false, 'error' => 'Complete todos los campos'];
        ob_clean();
        echo json_encode($response);
        exit;
    }

    // Autenticar
    $auth = new AuthManager($conn);
    $result = $auth->login($username, $password, $remember_me);

    if ($result['success']) {
        $redirect_url = $auth->getRedirectUrl($result['user']['rol']);
        
        $response = [
            'success' => true,
            'user' => [
                'id' => $result['user']['id'],
                'username' => $result['user']['username'],
                'nombre' => $result['user']['nombre'],
                'rol' => $result['user']['rol']
            ],
            'redirect' => $redirect_url,
            'message' => 'Login exitoso'
        ];
    } else {
        $response = [
            'success' => false,
            'error' => $result['error']
        ];
    }

    // Limpiar buffer y enviar respuesta limpia
    ob_clean();
    echo json_encode($response);
    
} catch (Exception $e) {
    ob_clean();
    $response = [
        'success' => false,
        'error' => 'Error del sistema'
    ];
    echo json_encode($response);
}

exit;
?>
?>