<?php
require_once '../conexion.php';
require_once '../auth/AuthManager.php';

$auth = new AuthManager($conn);
$auth->requireRole(['administrador']);

$current_user = $auth->getCurrentUser();

// Estadísticas generales
function getGeneralStats($conn) {
    $stats = [];
    
    // Total inmuebles
    $result = $conn->query("SELECT COUNT(*) as total FROM inmuebles");
    $stats['total_inmuebles'] = $result->fetch_assoc()['total'];
    
    // Total propietarios
    $result = $conn->query("SELECT COUNT(*) as total FROM propietarios");
    $stats['total_propietarios'] = $result->fetch_assoc()['total'];
    
    // Total leads (últimos 30 días) - usar timestamp o fecha disponible
    $leads_query = "SELECT COUNT(*) as total FROM leads WHERE 1=1";
    
    // Verificar si existe una columna de fecha
    $columns_check = $conn->query("SHOW COLUMNS FROM leads LIKE '%fecha%'");
    if ($columns_check && $columns_check->num_rows > 0) {
        $date_column = $columns_check->fetch_assoc()['Field'];
        $leads_query = "SELECT COUNT(*) as total FROM leads WHERE $date_column >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    }
    
    $result = $conn->query($leads_query);
    $stats['leads_mes'] = $result->fetch_assoc()['total'];
    
    // Leads pendientes
    $result = $conn->query("SELECT COUNT(*) as total FROM leads WHERE estado = 'nuevo'");
    $stats['leads_pendientes'] = $result->fetch_assoc()['total'];
    
    // Inmuebles por tipo - consulta adaptativa
    $tipo_query = "SELECT 
        COALESCE(ti.tipo, ti.nom_tipoinm, 'Sin tipo') as tipo_nombre,
        COUNT(i.id) as cantidad 
        FROM tipo_inmueble ti 
        LEFT JOIN inmuebles i ON (ti.id = i.tipo_inmueble_id OR ti.cod_tipoinm = i.cod_tipoinm)
        GROUP BY ti.id
        ORDER BY cantidad DESC";
    
    $result = $conn->query($tipo_query);
    $stats['inmuebles_por_tipo'] = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $stats['inmuebles_por_tipo'][] = [
                'tipo' => $row['tipo_nombre'],
                'cantidad' => $row['cantidad']
            ];
        }
    }
    
    // Leads por estado
    $result = $conn->query("SELECT estado, COUNT(*) as cantidad FROM leads GROUP BY estado");
    $stats['leads_por_estado'] = [];
    while ($row = $result->fetch_assoc()) {
        $stats['leads_por_estado'][] = $row;
    }
    
    return $stats;
}

$stats = getGeneralStats($conn);

// Obtener leads recientes
$recent_leads_query = "SELECT l.*, i.titulo, i.precio FROM leads l 
                       LEFT JOIN inmuebles i ON l.inmueble_id = i.id 
                       ORDER BY l.id DESC LIMIT 10";

// Intentar ordenar por fecha si existe
$date_column_check = $conn->query("SHOW COLUMNS FROM leads LIKE '%fecha%'");
if ($date_column_check && $date_column_check->num_rows > 0) {
    $date_col = $date_column_check->fetch_assoc()['Field'];
    $recent_leads_query = "SELECT l.*, i.titulo, i.precio FROM leads l 
                           LEFT JOIN inmuebles i ON l.inmueble_id = i.id 
                           ORDER BY l.$date_col DESC LIMIT 10";
}

$recent_leads = $conn->query($recent_leads_query);

// Obtener usuarios del sistema
$users = $conn->query("SELECT id, usuario as username, email, rol, nombre, activo, 
                       fecha_ultimo_acceso 
                       FROM usuarios 
                       ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Inmobiliaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .navbar-brand {
            font-weight: 600;
        }
        
        .sidebar {
            min-height: calc(100vh - 76px);
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.9);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 4px 8px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
        }
        
        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .user-status-badge {
            font-size: 0.8rem;
            padding: 4px 8px;
        }
        
        .btn-action {
            padding: 6px 12px;
            font-size: 0.85rem;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-building me-2"></i>
                Panel de Administración
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-2"></i>
                        <?= htmlspecialchars($current_user['nombre_completo']) ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Perfil</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Configuración</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0">
                <div class="sidebar">
                    <nav class="nav flex-column py-3">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users me-2"></i>Usuarios
                        </a>
                        <a class="nav-link" href="leads.php">
                            <i class="fas fa-bullhorn me-2"></i>Leads
                        </a>
                        <a class="nav-link" href="../inmuebles.php">
                            <i class="fas fa-home me-2"></i>Inmuebles
                        </a>
                        <a class="nav-link" href="../propietarios.php">
                            <i class="fas fa-user-tie me-2"></i>Propietarios
                        </a>
                        <a class="nav-link" href="../empleados.php">
                            <i class="fas fa-id-badge me-2"></i>Empleados
                        </a>
                        <a class="nav-link" href="../oficinas.php">
                            <i class="fas fa-building me-2"></i>Oficinas
                        </a>
                        <hr class="my-3" style="border-color: rgba(255,255,255,0.3);">
                        <a class="nav-link" href="../public/index.php">
                            <i class="fas fa-globe me-2"></i>Ver Sitio Público
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 py-4">
                <h1 class="h3 mb-4">Dashboard Principal</h1>
                
                <!-- Statistics Cards -->
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
                                    <div class="stat-value"><?= $stats['leads_mes'] ?></div>
                                    <div class="text-muted">Leads este mes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon" style="background: linear-gradient(45deg, #fa709a, #fee140);">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="ms-3">
                                    <div class="stat-value"><?= $stats['leads_pendientes'] ?></div>
                                    <div class="text-muted">Pendientes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-lg-6 mb-3">
                        <div class="chart-container">
                            <h5 class="mb-3">Inmuebles por Tipo</h5>
                            <canvas id="inmueblesPorTipo" style="max-height: 300px;"></canvas>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 mb-3">
                        <div class="chart-container">
                            <h5 class="mb-3">Estado de Leads</h5>
                            <canvas id="leadsPorEstado" style="max-height: 300px;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Leads Table -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="table-container">
                            <div class="p-3 border-bottom">
                                <h5 class="mb-0">
                                    <i class="fas fa-bullhorn me-2 text-primary"></i>
                                    Leads Recientes
                                </h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Cliente</th>
                                            <th>Email</th>
                                            <th>Inmueble</th>
                                            <th>Precio</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($lead = $recent_leads->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <?php
                                                // Buscar cualquier columna de fecha disponible
                                                $fecha_mostrar = 'N/A';
                                                if (isset($lead['fecha_creacion'])) {
                                                    $fecha_mostrar = date('d/m/Y H:i', strtotime($lead['fecha_creacion']));
                                                } elseif (isset($lead['timestamp'])) {
                                                    $fecha_mostrar = date('d/m/Y H:i', strtotime($lead['timestamp']));
                                                } elseif (isset($lead['created_at'])) {
                                                    $fecha_mostrar = date('d/m/Y H:i', strtotime($lead['created_at']));
                                                }
                                                echo $fecha_mostrar;
                                                ?>
                                            </td>
                                            <td><?= htmlspecialchars($lead['nombre']) ?></td>
                                            <td><?= htmlspecialchars($lead['email']) ?></td>
                                            <td>
                                                <?php if ($lead['titulo']): ?>
                                                    <small><?= htmlspecialchars($lead['titulo']) ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">Sin inmueble específico</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($lead['precio']): ?>
                                                    <strong>$<?= number_format($lead['precio']) ?></strong>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $estado_colors = [
                                                    'nuevo' => 'warning',
                                                    'contactado' => 'info', 
                                                    'interesado' => 'primary',
                                                    'cerrado' => 'success',
                                                    'perdido' => 'danger'
                                                ];
                                                $color = $estado_colors[$lead['estado']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?= $color ?>"><?= ucfirst($lead['estado']) ?></span>
                                            </td>
                                            <td>
                                                <a href="leads.php?view=<?= $lead['id'] ?>" class="btn btn-outline-primary btn-action">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="tel:<?= $lead['telefono'] ?>" class="btn btn-outline-success btn-action">
                                                    <i class="fas fa-phone"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Management -->
                <div class="row">
                    <div class="col-12">
                        <div class="table-container">
                            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-users me-2 text-primary"></i>
                                    Usuarios del Sistema
                                </h5>
                                <a href="users.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-2"></i>Agregar Usuario
                                </a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Usuario</th>
                                            <th>Email</th>
                                            <th>Rol</th>
                                            <th>Estado</th>
                                            <th>Último Acceso</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($user = $users->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($user['username']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($user['nombre'] ?? 'Sin nombre') ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($user['email'] ?? 'Sin email') ?></td>
                                            <td>
                                                <span class="badge bg-<?= $user['rol'] === 'administrador' ? 'danger' : 'info' ?>">
                                                    <?= ucfirst($user['rol'] ?? 'usuario') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge user-status-badge bg-<?= $user['activo'] ? 'success' : 'secondary' ?>">
                                                    <?= $user['activo'] ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($user['fecha_ultimo_acceso'])): ?>
                                                    <?= date('d/m/Y H:i', strtotime($user['fecha_ultimo_acceso'])) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Nunca</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="users.php?edit=<?= $user['id'] ?>" class="btn btn-outline-primary btn-action">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.min.js"></script>
    <script>
        // Configurar gráficos
        const inmueblesPorTipoData = <?= json_encode($stats['inmuebles_por_tipo']) ?>;
        const leadsPorEstadoData = <?= json_encode($stats['leads_por_estado']) ?>;
        
        // Gráfico de inmuebles por tipo
        new Chart(document.getElementById('inmueblesPorTipo'), {
            type: 'doughnut',
            data: {
                labels: inmueblesPorTipoData.map(item => item.tipo),
                datasets: [{
                    data: inmueblesPorTipoData.map(item => item.cantidad),
                    backgroundColor: [
                        '#667eea',
                        '#764ba2', 
                        '#f093fb',
                        '#f5576c',
                        '#4facfe',
                        '#00f2fe'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
        
        // Gráfico de leads por estado
        new Chart(document.getElementById('leadsPorEstado'), {
            type: 'bar',
            data: {
                labels: leadsPorEstadoData.map(item => item.estado),
                datasets: [{
                    label: 'Cantidad',
                    data: leadsPorEstadoData.map(item => item.cantidad),
                    backgroundColor: [
                        '#ffc107',
                        '#17a2b8',
                        '#007bff', 
                        '#28a745',
                        '#dc3545'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>