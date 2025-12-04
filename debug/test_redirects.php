<?php
require_once '../conexion.php';
require_once '../auth/AuthManager.php';

$auth = new AuthManager($conn);

echo "<h2>Test de Redirecciones por Rol</h2>";
echo "<pre>";

$roles = ['administrador', 'secretaria', 'agente_senior', 'agente_junior'];

foreach ($roles as $role) {
    $url = $auth->getRedirectUrl($role);
    echo "Rol: $role -> URL: $url\n";
    
    // Verificar si la URL existe
    $local_path = str_replace('/INMOBILIARIA_1', '', $url);
    if ($local_path === '/auth/login.php') {
        echo "  -> Login page (default)\n";
    } else {
        $file_path = $_SERVER['DOCUMENT_ROOT'] . '/INMOBILIARIA_1' . $local_path;
        if (file_exists($file_path)) {
            echo "  -> ✅ Archivo existe: $file_path\n";
        } else {
            echo "  -> ❌ Archivo NO existe: $file_path\n";
        }
    }
    echo "\n";
}

echo "=== Verificación de archivos ===\n";
$files_to_check = [
    '/INMOBILIARIA_1/index.php',
    '/INMOBILIARIA_1/admin/dashboard_simple.php'
];

foreach ($files_to_check as $file) {
    $full_path = $_SERVER['DOCUMENT_ROOT'] . $file;
    if (file_exists($full_path)) {
        echo "✅ $file -> Existe\n";
    } else {
        echo "❌ $file -> NO existe\n";
    }
}

echo "</pre>";
?>