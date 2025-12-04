<?php
require_once '../conexion.php';
require_once '../auth/AuthManager.php';

$auth = new AuthManager($conn);
$auth->requireRole(['administrador']);

$current_user = $auth->getCurrentUser();
$message = '';
$error = '';

// Manejar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_user':
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $nombre = trim($_POST['nombre'] ?? '');
            $rol = $_POST['rol'] ?? 'usuario';
            
            if (empty($username) || empty($password) || empty($nombre)) {
                $error = "Todos los campos son requeridos";
            } else {
                // Verificar si el usuario ya existe
                $check_sql = "SELECT id FROM usuarios WHERE usuario = ? OR email = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("ss", $username, $email);
                $check_stmt->execute();
                
                if ($check_stmt->get_result()->num_rows > 0) {
                    $error = "El usuario o email ya existe";
                } else {
                    // Crear usuario
                    $password_hash = password_hash($password, PASSWORD_BCRYPT);
                    $insert_sql = "INSERT INTO usuarios (usuario, password_hash, nombre, email, rol, activo) VALUES (?, ?, ?, ?, ?, 1)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->bind_param("sssss", $username, $password_hash, $nombre, $email, $rol);
                    
                    if ($insert_stmt->execute()) {
                        $message = "Usuario '$username' creado exitosamente";
                    } else {
                        $error = "Error al crear usuario: " . $conn->error;
                    }
                }
            }
            break;
            
        case 'toggle_status':
            $user_id = $_POST['user_id'] ?? 0;
            $new_status = $_POST['new_status'] ?? 0;
            
            $update_sql = "UPDATE usuarios SET activo = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $new_status, $user_id);
            
            if ($update_stmt->execute()) {
                $message = "Estado de usuario actualizado";
            } else {
                $error = "Error actualizando estado";
            }
            break;
    }
}

// Obtener todos los usuarios
$users_sql = "SELECT id, usuario, email, nombre, rol, activo, fecha_ultimo_acceso 
              FROM usuarios 
              ORDER BY fecha_ultimo_acceso DESC, id DESC";
$users_result = $conn->query($users_sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: 600; }
        .main-content { margin-top: 20px; }
        .user-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .status-badge {
            font-size: 0.85rem;
            padding: 5px 10px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard_simple.php">
                <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-2"></i>
                        <?= htmlspecialchars($current_user['nombre_completo'] ?? $current_user['username']) ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid main-content">
        <div class="row">
            <div class="col-12">
                <h1 class="h3 mb-4">
                    <i class="fas fa-users-cog me-2"></i>Gestión de Usuarios del Sistema
                </h1>

                <!-- Mensajes -->
                <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Formulario para crear usuario -->
                <div class="user-card">
                    <h4 class="mb-3">
                        <i class="fas fa-user-plus me-2 text-primary"></i>Crear Nuevo Usuario
                    </h4>
                    
                    <form method="POST" class="row g-3">
                        <input type="hidden" name="action" value="create_user">
                        
                        <div class="col-md-3">
                            <label for="username" class="form-label">Usuario *</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="password" class="form-label">Contraseña *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="nombre" class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        
                        <div class="col-md-2">
                            <label for="rol" class="form-label">Rol</label>
                            <select class="form-select" id="rol" name="rol">
                                <option value="usuario">Usuario</option>
                                <option value="agente_junior">Agente Junior</option>
                                <option value="agente_senior">Agente Senior</option>
                                <option value="secretaria">Secretaria</option>
                                <option value="administrador">Administrador</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-success d-block w-100">
                                <i class="fas fa-plus me-2"></i>Crear Usuario
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Lista de usuarios -->
                <div class="user-card">
                    <h4 class="mb-3">
                        <i class="fas fa-users me-2 text-info"></i>Usuarios Existentes
                    </h4>
                    
                    <?php if ($users_result && $users_result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Último Acceso</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $users_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($user['usuario']) ?></strong>
                                        <?php if ($user['id'] == $current_user['id']): ?>
                                            <small class="text-muted">(Tú)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($user['nombre'] ?? 'Sin nombre') ?></td>
                                    <td><?= htmlspecialchars($user['email'] ?? 'Sin email') ?></td>
                                    <td>
                                        <span class="badge bg-<?php
                                            switch($user['rol']) {
                                                case 'administrador': echo 'danger'; break;
                                                case 'secretaria': echo 'warning'; break;
                                                case 'agente_senior': echo 'info'; break;
                                                case 'agente_junior': echo 'primary'; break;
                                                default: echo 'secondary';
                                            }
                                        ?>">
                                            <?php 
                                                switch($user['rol']) {
                                                    case 'agente_junior': echo 'Agente Junior'; break;
                                                    case 'agente_senior': echo 'Agente Senior'; break;
                                                    case 'secretaria': echo 'Secretaria'; break;
                                                    case 'administrador': echo 'Administrador'; break;
                                                    default: echo ucfirst($user['rol']);
                                                }
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge status-badge bg-<?= $user['activo'] ? 'success' : 'secondary' ?>">
                                            <?= $user['activo'] ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['fecha_ultimo_acceso']): ?>
                                            <?= date('d/m/Y H:i', strtotime($user['fecha_ultimo_acceso'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Nunca</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['id'] != $current_user['id']): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <input type="hidden" name="new_status" value="<?= $user['activo'] ? 0 : 1 ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-<?= $user['activo'] ? 'warning' : 'success' ?>" 
                                                    onclick="return confirm('¿Cambiar estado del usuario?')">
                                                <i class="fas fa-<?= $user['activo'] ? 'pause' : 'play' ?>"></i>
                                                <?= $user['activo'] ? 'Desactivar' : 'Activar' ?>
                                            </button>
                                        </form>
                                        <?php else: ?>
                                        <span class="text-muted">Tu cuenta</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>No hay usuarios registrados.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>