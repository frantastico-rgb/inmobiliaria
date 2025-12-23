<?php
require_once 'conexion.php';
require_once 'auth/AuthManager.php';

// Verificar autenticación - solo usuarios operativos (no admin)
$auth = new AuthManager($conn);
$auth->requireRole(['secretaria', 'agente_senior', 'agente_junior']);

$current_user = $auth->getCurrentUser();
$user_role = $current_user['rol'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Inmobiliario - Panel Operativo</title>
    <link rel="stylesheet" href="estilos.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .menu-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .user-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .role-badge {
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 0.9em;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .menu-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-left: 4px solid #2196F3;
            transition: transform 0.3s ease;
        }
        .menu-card:hover {
            transform: translateY(-5px);
        }
        .menu-card h3 {
            color: #1976D2;
            margin-bottom: 15px;
        }
        .menu-card ul {
            list-style: none;
            padding: 0;
        }
        .menu-card li {
            margin-bottom: 8px;
        }
        .menu-card a {
            color: #333;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .menu-card a:hover {
            background-color: #E3F2FD;
            color: #1976D2;
        }
        .welcome-section {
            text-align: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="logo-icono">
        <img src="uploads/logo_casa_meta.png" alt="logo">
    </div>
    
    <div class="menu-container">
        <!-- Información del usuario -->
        <div class="user-info">
            <div>
                <h3><i class="fas fa-user-circle me-2"></i>
                    <?= htmlspecialchars($current_user['nombre_completo'] ?? $current_user['username']) ?>
                </h3>
                <p class="mb-0">Panel Operativo - Sistema Inmobiliario</p>
            </div>
            <div class="d-flex align-items-center">
                <span class="role-badge me-3">
                    <?php 
                        switch($user_role) {
                            case 'secretaria': echo '👩‍💼 Secretaria'; break;
                            case 'agente_senior': echo '🏠 Agente Senior'; break;
                            case 'agente_junior': echo '🏘️ Agente Junior'; break;
                            default: echo ucfirst($user_role);
                        }
                    ?>
                </span>
                <a href="auth/logout.php" class="btn" style="color: white; text-decoration: none;">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </a>
            </div>
        </div>

        <div class="welcome-section">
            <h1>🏠 Sistema de Gestión Inmobiliaria</h1>
            <p>Panel operativo para gestión diaria de propiedades, clientes, contratos y más.</p>
        </div>

        <div class="menu-grid">
            <!-- Gestión de Propietarios -->
            <?php if ($auth->hasPermission('view_all_properties') || $user_role === 'agente_junior'): ?>
            <div class="menu-card">
                <h3>👥 Propietarios</h3>
                <ul>
                    <li><a href="propietarios.php">➕ Agregar Propietario</a></li>
                    <li><a href="lista_propietarios.php">📋 Ver Lista de Propietarios</a></li>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Gestión de Inmuebles -->
            <div class="menu-card">
                <h3>🏘️ Inmuebles</h3>
                <ul>
                    <?php if ($auth->hasPermission('view_all_properties') || $user_role === 'agente_junior'): ?>
                    <li><a href="inmuebles.php">➕ Agregar Inmueble</a></li>
                    <?php endif; ?>
                    <li><a href="lista_inmuebles.php">📋 Ver <?= $user_role === 'agente_junior' ? 'Mis' : '' ?> Inmuebles</a></li>
                </ul>
            </div>

            <!-- Gestión de Clientes -->
            <div class="menu-card">
                <h3>👤 Clientes</h3>
                <ul>
                    <li><a href="clientes.php">➕ Agregar Cliente</a></li>
                    <li><a href="lista_clientes.php">📋 Ver <?= $user_role === 'agente_junior' ? 'Mis' : '' ?> Clientes</a></li>
                </ul>
            </div>

            <!-- Gestión de Empleados - Solo secretaria y admin -->
            <?php if ($auth->hasPermission('edit_others_data')): ?>
            <div class="menu-card">
                <h3>👔 Empleados</h3>
                <ul>
                    <li><a href="empleados.php">➕ Agregar Empleado</a></li>
                    <li><a href="lista_empleados.php">📋 Ver Lista de Empleados</a></li>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Gestión de Oficinas - Solo secretaria y admin -->
            <?php if ($auth->hasPermission('edit_others_data')): ?>
            <div class="menu-card">
                <h3>🏢 Oficinas</h3>
                <ul>
                    <li><a href="oficinas.php">➕ Agregar Oficina</a></li>
                    <li><a href="lista_oficinas.php">📋 Ver Lista de Oficinas</a></li>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Gestión de Contratos -->
            <div class="menu-card">
                <h3>📄 Contratos</h3>
                <ul>
                    <?php if ($auth->hasPermission('create_contracts')): ?>
                    <li><a href="contratos.php">➕ Nuevo Contrato</a></li>
                    <?php endif; ?>
                    <li><a href="lista_contratos.php">📋 Ver <?= $user_role === 'agente_junior' ? 'Mis' : '' ?> Contratos</a></li>
                </ul>
            </div>

            <!-- Gestión de Visitas -->
            <div class="menu-card">
                <h3>🏠 Visitas</h3>
                <ul>
                    <li><a href="visitas/crear_visita.php">➕ Programar Visita</a></li>
                    <li><a href="visitas/lista_visitas.php">📋 Ver <?= $user_role === 'agente_junior' ? 'Mis' : '' ?> Visitas</a></li>
                </ul>
            </div>

            <!-- Gestión de Inspecciones -->
            <div class="menu-card">
                <h3>🔍 Inspecciones</h3>
                <ul>
                    <li><a href="inspeccion.php">➕ Nueva Inspección</a></li>
                    <li><a href="lista_inspecciones.php">📋 Ver <?= $user_role === 'agente_junior' ? 'Mis' : '' ?> Inspecciones</a></li>
                </ul>
            </div>
                </ul>
            </div>
            
            <!-- Enlace especial para administradores -->
            <?php if ($user_role === 'administrador'): ?>
            <div class="menu-card" style="background: linear-gradient(135deg, #ff6b6b, #ee5a52); color: white;">
                <h3 style="color: white;">⚙️ Panel Administrador</h3>
                <ul>
                    <li><a href="admin/dashboard_simple.php" style="color: white; font-weight: bold;">🎯 Dashboard Completo</a></li>
                    <li><a href="admin/users_manage.php" style="color: white;">👥 Gestionar Usuarios</a></li>
                </ul>
            </div>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 40px; padding: 20px; background: #f5f5f5; border-radius: 10px;">
            <h3>🛠️ Estado del Sistema</h3>
            <p><strong>Base de Datos:</strong> inmobil | <strong>Servidor:</strong> localhost | <strong>Puerto:</strong> 3306</p>
            <p><strong>Usuario:</strong> <?= htmlspecialchars($current_user['nombre_completo']) ?> | 
               <strong>Rol:</strong> <?= ucfirst($user_role) ?> |
               <strong>Sesión:</strong> <?= date('d/m/Y H:i') ?>
            </p>
            <?php
            require_once 'conexion.php';
            echo '<p style="color: green;">✅ Conexión a la base de datos exitosa</p>';
            $conn->close();
            ?>
        </div>
        </div>
    </div>
</body>
</html>