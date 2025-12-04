<?php
require_once '../conexion.php';
require_once '../auth/AuthManager.php';

$auth = new AuthManager($conn);
$auth->requireRole(['agente', 'administrador']);

$current_user = $auth->getCurrentUser();

// Estadísticas para agente (simplificadas y seguras)
function getAgentStats($conn, $user_id = null) {
    $stats = [
        'total_inmuebles' => 0,
        'leads_mes' => 0,
        'leads_pendientes' => 0,
        'leads_contactados' => 0
    ];
    
    try {
        // Total inmuebles
        $result = $conn->query("SELECT COUNT(*) as total FROM inmuebles");
        if ($result) {
            $stats['total_inmuebles'] = $result->fetch_assoc()['total'];
        }
        
        // Total leads (básico sin filtros complejos)
        $result = $conn->query("SELECT COUNT(*) as total FROM leads");
        if ($result) {
            $stats['leads_mes'] = $result->fetch_assoc()['total'];
        }
        
        // Leads por estado
        $result = $conn->query("SELECT COUNT(*) as total FROM leads WHERE estado = 'nuevo'");
        if ($result) {
            $stats['leads_pendientes'] = $result->fetch_assoc()['total'];
        }
        
        $result = $conn->query("SELECT COUNT(*) as total FROM leads WHERE estado = 'contactado'");
        if ($result) {
            $stats['leads_contactados'] = $result->fetch_assoc()['total'];
        }
    } catch (Exception $e) {
        error_log("Error en getAgentStats: " . $e->getMessage());
    }
    
    return $stats;
}

$stats = getAgentStats($conn, $current_user['id']);

// Obtener leads (simplificado)
$my_leads_query = "SELECT l.*, i.titulo, i.precio FROM leads l 
                   LEFT JOIN inmuebles i ON l.inmueble_id = i.id 
                   ORDER BY l.id DESC LIMIT 15";

$my_leads = $conn->query($my_leads_query);

// Inmuebles destacados (simplificado)
$featured_properties = $conn->query("SELECT i.*, p.nombre as propietario_nombre 
                                   FROM inmuebles i 
                                   LEFT JOIN propietarios p ON i.cod_prop = p.cod_prop 
                                   ORDER BY i.id DESC 
                                   LIMIT 8");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Agente - Inmobiliaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .navbar-brand {
            font-weight: 600;
        }
        
        .sidebar {
            min-height: calc(100vh - 76px);
            background: linear-gradient(180deg, #28a745 0%, #20c997 100%);
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
        
        .lead-card {
            background: white;
            border-radius: 10px;
            border-left: 4px solid #28a745;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .lead-card:hover {
            transform: translateY(-3px);
        }
        
        .property-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .property-card:hover {
            transform: translateY(-3px);
        }
        
        .property-image {
            height: 200px;
            background: linear-gradient(45deg, #28a745, #20c997);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }
        
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .btn-action {
            padding: 6px 12px;
            font-size: 0.85rem;
            border-radius: 6px;
        }
        
        .urgent-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-user-tie me-2"></i>
                Panel de Agente
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
                        <a class="nav-link" href="mis_leads.php">
                            <i class="fas fa-bullhorn me-2"></i>Mis Leads
                        </a>
                        <a class="nav-link" href="properties.php">
                            <i class="fas fa-home me-2"></i>Inmuebles
                        </a>
                        <?php if ($current_user['rol'] === 'administrador'): ?>
                        <a class="nav-link" href="../propietarios.php">
                            <i class="fas fa-user-tie me-2"></i>Propietarios
                        </a>
                        <a class="nav-link" href="../empleados.php">
                            <i class="fas fa-id-badge me-2"></i>Empleados
                        </a>
                        <a class="nav-link" href="../admin/dashboard.php">
                            <i class="fas fa-user-shield me-2"></i>Panel Admin
                        </a>
                        <?php endif; ?>
                        <hr class="my-3" style="border-color: rgba(255,255,255,0.3);">
                        <a class="nav-link" href="../public/index.php">
                            <i class="fas fa-globe me-2"></i>Ver Sitio Público
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">Dashboard de Agente</h1>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newLeadModal">
                            <i class="fas fa-plus me-2"></i>Nuevo Lead
                        </button>
                        <a href="../public/index.php" class="btn btn-outline-primary">
                            <i class="fas fa-external-link-alt me-2"></i>Ver Catálogo
                        </a>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon" style="background: linear-gradient(45deg, #28a745, #20c997);">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="ms-3">
                                    <div class="stat-value"><?= $stats['total_inmuebles'] ?></div>
                                    <div class="text-muted">Inmuebles Disponibles</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon" style="background: linear-gradient(45deg, #17a2b8, #20c997);">
                                    <i class="fas fa-bullhorn"></i>
                                </div>
                                <div class="ms-3">
                                    <div class="stat-value"><?= $stats['leads_mes'] ?></div>
                                    <div class="text-muted">Leads este Mes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon" style="background: linear-gradient(45deg, #ffc107, #fd7e14);">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="ms-3">
                                    <div class="stat-value"><?= $stats['leads_pendientes'] ?></div>
                                    <div class="text-muted">Pendientes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon" style="background: linear-gradient(45deg, #007bff, #6f42c1);">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="ms-3">
                                    <div class="stat-value"><?= $stats['leads_contactados'] ?></div>
                                    <div class="text-muted">Contactados</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- My Leads Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="mb-3">
                            <i class="fas fa-bullhorn me-2 text-success"></i>
                            <?= $current_user['rol'] === 'agente' ? 'Mis Leads' : 'Leads Recientes' ?>
                        </h4>
                        
                        <?php if ($my_leads->num_rows > 0): ?>
                        <div class="table-container">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Cliente</th>
                                            <th>Contacto</th>
                                            <th>Inmueble</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($lead = $my_leads->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <?= date('d/m H:i', strtotime($lead['fecha_creacion'])) ?>
                                                <?php if (strtotime($lead['fecha_creacion']) > strtotime('-24 hours')): ?>
                                                    <span class="badge bg-danger ms-1">Nuevo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($lead['nombre']) ?></strong>
                                            </td>
                                            <td>
                                                <small>
                                                    <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($lead['email']) ?><br>
                                                    <i class="fas fa-phone me-1"></i><?= htmlspecialchars($lead['telefono']) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($lead['titulo']): ?>
                                                    <small><?= htmlspecialchars($lead['titulo']) ?></small>
                                                    <?php if ($lead['precio']): ?>
                                                        <br><strong class="text-success">$<?= number_format($lead['precio']) ?></strong>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Consulta general</span>
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
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-outline-primary btn-action" onclick="viewLead(<?= $lead['id'] ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="tel:<?= $lead['telefono'] ?>" class="btn btn-outline-success btn-action">
                                                        <i class="fas fa-phone"></i>
                                                    </a>
                                                    <a href="https://wa.me/57<?= preg_replace('/[^0-9]/', '', $lead['telefono']) ?>?text=Hola <?= $lead['nombre'] ?>, te contacto desde la inmobiliaria..." 
                                                       class="btn btn-outline-success btn-action" target="_blank">
                                                        <i class="fab fa-whatsapp"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-bullhorn text-muted" style="font-size: 4rem;"></i>
                            <h5 class="text-muted mt-3">No tienes leads asignados</h5>
                            <p class="text-muted">Los nuevos leads aparecerán aquí</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Featured Properties -->
                <div class="row">
                    <div class="col-12">
                        <h4 class="mb-3">
                            <i class="fas fa-star me-2 text-warning"></i>
                            Propiedades Destacadas
                        </h4>
                    </div>
                    
                    <?php while ($property = $featured_properties->fetch_assoc()): ?>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="property-card">
                            <div class="property-image">
                                <?php if (!empty($property['foto']) && file_exists('../' . $property['foto'])): ?>
                                    <img src="../<?= htmlspecialchars($property['foto']) ?>" 
                                         alt="<?= htmlspecialchars($property['dir_inm']) ?>"
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <div style="background: linear-gradient(45deg, #28a745, #20c997); color: white; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                                        <i class="fas fa-home"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-3">
                                <h6 class="mb-2"><?= htmlspecialchars($property['dir_inm']) ?></h6>
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?= htmlspecialchars($property['barrio_inm']) ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="text-success">$<?= number_format($property['precio_alq']) ?></strong>
                                    <span class="badge bg-primary">Inmueble</span>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <?= $property['num_hab'] ?> hab • Área: <?= $property['area_inm'] ?>m²
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewLead(leadId) {
            // Función para ver detalles del lead
            // Por ahora solo mostramos alert, luego se puede implementar modal
            alert('Ver detalles del lead #' + leadId);
        }
        
        // Actualizar cada 30 segundos para nuevos leads
        setInterval(() => {
            const badges = document.querySelectorAll('.badge:contains("Nuevo")');
            // Lógica para actualizar leads en tiempo real
        }, 30000);
    </script>
</body>
</html>