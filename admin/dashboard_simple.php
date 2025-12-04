<?php
require_once '../conexion.php';
require_once '../auth/AuthManager.php';

$auth = new AuthManager($conn);
$auth->requireRole(['administrador']);

$current_user = $auth->getCurrentUser();

// Estadísticas básicas y seguras
$stats = [
    'total_inmuebles' => 0,
    'total_propietarios' => 0,
    'total_leads' => 0,
    'total_usuarios' => 0,
    'total_contratos' => 0,
    'total_visitas' => 0,
    'total_inspecciones' => 0,
    'total_oficinas' => 0
];

try {
    // Contar inmuebles
    $result = $conn->query("SELECT COUNT(*) as total FROM inmuebles");
    if ($result) {
        $stats['total_inmuebles'] = $result->fetch_assoc()['total'];
    }
    
    // Contar propietarios
    $result = $conn->query("SELECT COUNT(*) as total FROM propietarios");
    if ($result) {
        $stats['total_propietarios'] = $result->fetch_assoc()['total'];
    }
    
    // Contar leads
    $result = $conn->query("SELECT COUNT(*) as total FROM leads");
    if ($result) {
        $stats['total_leads'] = $result->fetch_assoc()['total'];
    }
    
    // Contar usuarios
    $result = $conn->query("SELECT COUNT(*) as total FROM usuarios");
    if ($result) {
        $stats['total_usuarios'] = $result->fetch_assoc()['total'];
    }
    
    // Contar contratos
    $result = $conn->query("SELECT COUNT(*) as total FROM contratos");
    if ($result && $result->num_rows > 0) {
        $stats['total_contratos'] = $result->fetch_assoc()['total'];
    }
    
    // Contar visitas
    $result = $conn->query("SELECT COUNT(*) as total FROM visitas");
    if ($result && $result->num_rows > 0) {
        $stats['total_visitas'] = $result->fetch_assoc()['total'];
    }
    
    // Contar inspecciones (tabla: inspeccion)
    $result = $conn->query("SELECT COUNT(*) as total FROM inspeccion");
    if ($result && $result->num_rows > 0) {
        $stats['total_inspecciones'] = $result->fetch_assoc()['total'];
    }
    
    // Contar oficinas (tabla: oficina)
    $result = $conn->query("SELECT COUNT(*) as total FROM oficina");
    if ($result && $result->num_rows > 0) {
        $stats['total_oficinas'] = $result->fetch_assoc()['total'];
    }
} catch (Exception $e) {
    error_log("Error en estadísticas: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Inmobiliaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar {
            min-height: calc(100vh - 76px);
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.9);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 4px 8px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover { background: rgba(255,255,255,0.1); color: white; }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-icon {
            width: 60px; height: 60px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; color: white;
        }
        .stat-value { font-size: 2.5rem; font-weight: 700; color: #2d3748; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="fas fa-building me-2"></i>Panel de Administración</a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-2"></i><?= htmlspecialchars($current_user['nombre_completo']) ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 p-0">
                <div class="sidebar">
                    <nav class="nav flex-column py-3">
                        <a class="nav-link active" href="dashboard_simple.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                        <a class="nav-link" href="../lista_inmuebles.php"><i class="fas fa-list me-2"></i>Ver Inmuebles</a>
                        <a class="nav-link" href="../inmuebles_nuevo.php"><i class="fas fa-plus me-2"></i>Nuevo Inmueble</a>
                        <a class="nav-link" href="../lista_propietarios.php"><i class="fas fa-list me-2"></i>Ver Propietarios</a>
                        <a class="nav-link" href="../propietarios.php"><i class="fas fa-plus me-2"></i>Nuevo Propietario</a>
                        <a class="nav-link" href="../lista_empleados.php"><i class="fas fa-list me-2"></i>Ver Empleados</a>
                        <a class="nav-link" href="../empleados.php"><i class="fas fa-plus me-2"></i>Nuevo Empleado</a>
                        <hr class="my-2" style="border-color: rgba(255,255,255,0.3);">
                        <a class="nav-link" href="../lista_oficinas.php"><i class="fas fa-building me-2"></i>Ver Oficinas</a>
                        <a class="nav-link" href="../oficinas.php"><i class="fas fa-plus me-2"></i>Nueva Oficina</a>
                        <a class="nav-link" href="../lista_contratos.php"><i class="fas fa-file-contract me-2"></i>Ver Contratos</a>
                        <a class="nav-link" href="../contratos.php"><i class="fas fa-plus me-2"></i>Nuevo Contrato</a>
                        <a class="nav-link" href="../lista_visitas.php"><i class="fas fa-calendar-check me-2"></i>Ver Visitas</a>
                        <a class="nav-link" href="../visitas.php"><i class="fas fa-plus me-2"></i>Programar Visita</a>
                        <a class="nav-link" href="../lista_inspecciones.php"><i class="fas fa-search me-2"></i>Ver Inspecciones</a>
                        <a class="nav-link" href="../inspeccion.php"><i class="fas fa-plus me-2"></i>Nueva Inspección</a>
                        <hr class="my-2" style="border-color: rgba(255,255,255,0.3);">
                        <a class="nav-link" href="../lista_clientes.php"><i class="fas fa-users me-2"></i>Ver Clientes/Leads</a>
                        <a class="nav-link" href="../clientes.php"><i class="fas fa-user-plus me-2"></i>Nuevo Cliente</a>
                        <a class="nav-link" href="users_manage.php"><i class="fas fa-users-cog me-2"></i>Gestionar Usuarios</a>
                        <hr class="my-3" style="border-color: rgba(255,255,255,0.3);">
                        <a class="nav-link" href="../public/index.php"><i class="fas fa-globe me-2"></i>Ver Sitio Público</a>
                    </nav>
                </div>
            </div>

            <div class="col-md-10 py-4">
                <h1 class="h3 mb-4">Dashboard Principal</h1>
                
                <?php if ($current_user['rol'] === 'administrador'): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>¡Bienvenido Administrador!</strong> Tienes acceso completo al sistema.
                </div>
                <?php endif; ?>

                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon" style="background: linear-gradient(45deg, #667eea, #764ba2);">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="ms-3">
                                    <div class="stat-value"><?= $stats['total_inmuebles'] ?></div>
                                    <div class="text-muted">Inmuebles</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon" style="background: linear-gradient(45deg, #f093fb, #f5576c);">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div class="ms-3">
                                    <div class="stat-value"><?= $stats['total_propietarios'] ?></div>
                                    <div class="text-muted">Propietarios</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon" style="background: linear-gradient(45deg, #4facfe, #00f2fe);">
                                    <i class="fas fa-bullhorn"></i>
                                </div>
                                <div class="ms-3">
                                    <div class="stat-value"><?= $stats['total_leads'] ?></div>
                                    <div class="text-muted">Total Leads</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon" style="background: linear-gradient(45deg, #fa709a, #fee140);">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="ms-3">
                                    <div class="stat-value"><?= $stats['total_usuarios'] ?></div>
                                    <div class="text-muted">Usuarios</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Segunda fila de estadísticas -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon" style="background: linear-gradient(45deg, #a8edea, #fed6e3);">
                                    <i class="fas fa-file-contract"></i>
                                </div>
                                <div class="ms-3">
                                    <div class="stat-value"><?= $stats['total_contratos'] ?></div>
                                    <div class="text-muted">Contratos</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon" style="background: linear-gradient(45deg, #fbc2eb, #a6c1ee);">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="ms-3">
                                    <div class="stat-value"><?= $stats['total_visitas'] ?></div>
                                    <div class="text-muted">Visitas</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon" style="background: linear-gradient(45deg, #84fab0, #8fd3f4);">
                                    <i class="fas fa-search"></i>
                                </div>
                                <div class="ms-3">
                                    <div class="stat-value"><?= $stats['total_inspecciones'] ?></div>
                                    <div class="text-muted">Inspecciones</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon" style="background: linear-gradient(45deg, #ffecd2, #fcb69f);">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="ms-3">
                                    <div class="stat-value"><?= $stats['total_oficinas'] ?></div>
                                    <div class="text-muted">Oficinas</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-list me-2"></i>Acciones Rápidas</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="../inmuebles.php" class="btn btn-outline-primary">
                                        <i class="fas fa-home me-2"></i>Gestionar Inmuebles
                                    </a>
                                    <a href="../propietarios.php" class="btn btn-outline-success">
                                        <i class="fas fa-user-tie me-2"></i>Gestionar Propietarios
                                    </a>
                                    <a href="../public/index.php" class="btn btn-outline-info">
                                        <i class="fas fa-globe me-2"></i>Ver Portal Público
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-info-circle me-2"></i>Información del Sistema</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Usuario:</strong> <?= htmlspecialchars($current_user['username']) ?></p>
                                <p><strong>Rol:</strong> <?= htmlspecialchars($current_user['rol']) ?></p>
                                <p><strong>Sistema:</strong> Funcionando correctamente</p>
                                <div class="mt-3">
                                    <a href="../auth/logout.php" class="btn btn-outline-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>