<?php
// Clase para manejo de autenticación y sesiones
class AuthManager {
    private $conn;
    private $session_duration = 8 * 60 * 60; // 8 horas

    public function __construct($db_connection) {
        $this->conn = $db_connection;
        $this->startSession();
    }

    private function startSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login($username_email, $password, $remember_me = false) {
        try {
            // Debug log
            error_log("AuthManager::login - Usuario: $username_email");
            
            // Buscar usuario por username o email
            $sql = "SELECT id, usuario as username, email, password_hash, rol, nombre, activo 
                    FROM usuarios 
                    WHERE (usuario = ? OR email = ?) AND activo = 1";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ss", $username_email, $username_email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            error_log("AuthManager::login - Filas encontradas: " . $result->num_rows);
            
            if ($result->num_rows === 0) {
                error_log("AuthManager::login - Usuario no encontrado: $username_email");
                return ['success' => false, 'error' => 'Usuario no encontrado o inactivo'];
            }
            
            $user = $result->fetch_assoc();
            error_log("AuthManager::login - Usuario encontrado: " . $user['username'] . ", Hash existe: " . (isset($user['password_hash']) ? 'Sí' : 'No'));
            
            // Verificar contraseña
            if (empty($user['password_hash'])) {
                error_log("AuthManager::login - Sin hash de contraseña para: " . $user['username']);
                return ['success' => false, 'error' => 'Error de configuración de usuario'];
            }
            
            if (!password_verify($password, $user['password_hash'])) {
                error_log("AuthManager::login - Contraseña incorrecta para: " . $user['username']);
                return ['success' => false, 'error' => 'Contraseña incorrecta'];
            }
            
            error_log("AuthManager::login - Login exitoso para: " . $user['username']);
            
            // Crear sesión básica (sin tabla por ahora)
            session_regenerate_id(true);
            
            // Configurar variables de sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['rol'] = $user['rol'];
            $_SESSION['nombre_completo'] = $user['nombre'];
            $_SESSION['login_time'] = time();
            
            // Actualizar último acceso
            $this->updateLastAccess($user['id']);
            
            // Cookie "remember me" (opcional)
            if ($remember_me) {
                $remember_token = $this->generateRememberToken($user['id']);
                setcookie('remember_token', $remember_token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
            }
            
            return [
                'success' => true, 
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'rol' => $user['rol'],
                    'nombre' => $user['nombre']
                ]
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error del sistema: ' . $e->getMessage()];
        }
    }

    public function logout() {
        // Destruir sesión
        session_destroy();
        
        // Limpiar cookies
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }
        
        return ['success' => true];
    }

    public function isLoggedIn() {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Verificar expiración de sesión (8 horas)
        if (isset($_SESSION['login_time']) && time() - $_SESSION['login_time'] > $this->session_duration) {
            $this->logout();
            return false;
        }
        
        return true;
    }

    public function requireAuth($required_role = null) {
        if (!$this->isLoggedIn()) {
            header('Location: /INMOBILIARIA_1/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
        
        if ($required_role && $_SESSION['rol'] !== $required_role) {
            if ($_SESSION['rol'] === 'administrador') {
                // Admin puede acceder a todo
                return true;
            }
            
            header('HTTP/1.1 403 Forbidden');
            echo "Acceso denegado. Rol requerido: $required_role";
            exit;
        }
        
        return true;
    }

    public function requireRole($allowed_roles) {
        if (!$this->isLoggedIn()) {
            header('Location: /INMOBILIARIA_1/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
        
        if (!in_array($_SESSION['rol'], $allowed_roles)) {
            // Redirección inteligente según rol
            $this->redirectByRole($_SESSION['rol']);
            exit;
        }
        
        return true;
    }
    
    /**
     * Redirecciona al usuario según su rol
     */
    private function redirectByRole($role) {
        switch($role) {
            case 'administrador':
                header('Location: /INMOBILIARIA_1/admin/dashboard_simple.php');
                break;
            case 'secretaria':
            case 'agente_senior':
            case 'agente_junior':
                header('Location: /INMOBILIARIA_1/index.php');
                break;
            default:
                header('Location: /INMOBILIARIA_1/auth/login.php?error=unauthorized');
        }
    }
    
    /**
     * Verifica permisos específicos según el rol
     */
    public function hasPermission($permission) {
        if (!$this->isLoggedIn()) return false;
        
        $role = $_SESSION['rol'];
        
        switch($permission) {
            case 'manage_users':
                return $role === 'administrador';
                
            case 'create_contracts':
                return in_array($role, ['administrador', 'secretaria', 'agente_senior']);
                
            case 'view_all_properties':
                return in_array($role, ['administrador', 'secretaria', 'agente_senior']);
                
            case 'view_all_clients':
                return in_array($role, ['administrador', 'secretaria', 'agente_senior']);
                
            case 'view_financial':
                return in_array($role, ['administrador', 'agente_senior']);
                
            case 'edit_others_data':
                return in_array($role, ['administrador', 'secretaria']);
                
            default:
                return false;
        }
    }

    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'rol' => $_SESSION['rol'],
            'nombre_completo' => $_SESSION['nombre_completo']
        ];
    }

    public function getRedirectUrl($role) {
        switch ($role) {
            case 'administrador':
                $url = '/INMOBILIARIA_1/admin/dashboard_simple.php';
                break;
            case 'secretaria':
            case 'agente_senior':
            case 'agente_junior':
                $url = '/INMOBILIARIA_1/index.php';
                break;
            default:
                $url = '/INMOBILIARIA_1/auth/login.php';
        }
        
        return $url;
    }

    private function generateSessionId() {
        return bin2hex(random_bytes(32));
    }

    private function createUserSession($user_id, $session_id) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $sql = "INSERT INTO sesiones_usuario (id, usuario_id, ip_address, user_agent) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("siss", $session_id, $user_id, $ip, $user_agent);
        $stmt->execute();
    }

    private function updateSessionActivity($session_id) {
        $sql = "UPDATE sesiones_usuario SET fecha_actividad = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $session_id);
        $stmt->execute();
    }

    private function updateLastAccess($user_id) {
        $sql = "UPDATE usuarios SET fecha_ultimo_acceso = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }

    private function generateRememberToken($user_id) {
        // Implementación simplificada - en producción usar algo más seguro
        return hash('sha256', $user_id . time() . random_bytes(16));
    }

    public function cleanupOldSessions() {
        // Limpiar sesiones inactivas de más de 24 horas
        $sql = "DELETE FROM sesiones_usuario 
                WHERE fecha_actividad < DATE_SUB(NOW(), INTERVAL 24 HOUR) 
                   OR (activa = 0 AND fecha_actividad < DATE_SUB(NOW(), INTERVAL 1 HOUR))";
        $this->conn->query($sql);
    }

    public function changePassword($user_id, $old_password, $new_password) {
        // Verificar contraseña actual
        $sql = "SELECT password_hash FROM usuarios WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'error' => 'Usuario no encontrado'];
        }
        
        $user = $result->fetch_assoc();
        
        if (!password_verify($old_password, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Contraseña actual incorrecta'];
        }
        
        // Validar nueva contraseña
        if (strlen($new_password) < 8) {
            return ['success' => false, 'error' => 'La nueva contraseña debe tener al menos 8 caracteres'];
        }
        
        // Actualizar contraseña
        $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
        $sql = "UPDATE usuarios SET password_hash = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $new_hash, $user_id);
        
        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => 'Error al actualizar contraseña'];
        }
    }
}
?>