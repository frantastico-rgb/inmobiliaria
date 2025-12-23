<?php
// Portal Público - Vista Detallada de Inmueble
require_once __DIR__ . '/../src/conexion.php';
require_once __DIR__ . '/foto_utils.php';

// Verificar que se proporcionó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$inmueble_id = $_GET['id'];

// Obtener información completa del inmueble
$sql = "SELECT i.*, t.nom_tipoinm, p.nom_prop, p.tel_prop, o.nom_ofi, o.tel_ofi, o.email_ofi 
        FROM inmuebles i 
        LEFT JOIN tipo_inmueble t ON i.cod_tipoinm = t.cod_tipoinm 
        LEFT JOIN propietarios p ON i.cod_prop = p.cod_prop 
        LEFT JOIN oficina o ON p.cod_prop = o.Id_ofi 
        WHERE i.cod_inm = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $inmueble_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header('Location: index.php');
    exit();
}

$inmueble = $resultado->fetch_assoc();

// Pre-procesar URLs de fotos para la galería
$foto_principal = get_foto_url($inmueble['foto']);
$foto_1 = !empty($inmueble['foto_1']) ? get_foto_url($inmueble['foto_1']) : null;
$foto_2 = !empty($inmueble['foto_2']) ? get_foto_url($inmueble['foto_2']) : null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($inmueble['dir_inm']); ?> - Casa Meta</title>
    <link rel="stylesheet" href="css/catalogo.css">
    <link rel="stylesheet" href="css/compare-widget.css">
    <link rel="stylesheet" href="css/leads-system.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        /* Estilos optimizados para la galería */
        .inmueble-detail { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .gallery-container { position: relative; height: 500px; background: #222; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .main-image { max-width: 100%; max-height: 100%; object-fit: contain; transition: transform 0.3s ease; cursor: zoom-in; }
        .gallery-nav { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; gap: 10px; background: rgba(0,0,0,0.6); padding: 10px; border-radius: 12px; backdrop-filter: blur(5px); }
        .gallery-thumb { width: 70px; height: 70px; object-fit: cover; border-radius: 6px; cursor: pointer; border: 2px solid transparent; transition: 0.3s; }
        .gallery-thumb.active { border-color: #3498db; transform: scale(1.1); }
        .detail-content { padding: 30px; }
        .property-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px; }
        .price-main { font-size: 32px; font-weight: 800; color: #e74c3c; }
        .detail-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
        .map-container { height: 350px; border-radius: 12px; margin-top: 15px; border: 1px solid #ddd; }
        .contact-card { background: #2c3e50; color: white; padding: 25px; border-radius: 12px; position: sticky; top: 20px; }
        @media (max-width: 768px) { .detail-grid { grid-template-columns: 1fr; } .gallery-container { height: 300px; } }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo"><h1><i class="fas fa-home"></i> Casa Meta</h1></div>
            <nav>
                <ul class="nav-links">
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="favoritos.php">Favoritos (<span id="favoritesCountNav">0</span>)</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <a href="index.php" class="btn-back" style="margin: 20px 0; display: inline-block; text-decoration: none; color: #666;">
            <i class="fas fa-arrow-left"></i> Volver al catálogo
        </a>

        <div class="inmueble-detail">
            <div class="gallery-container">
                <img id="mainImage" class="main-image" src="<?php echo $foto_principal; ?>" alt="Foto Inmueble">
                
                <div class="gallery-nav">
                    <img class="gallery-thumb active" src="<?php echo $foto_principal; ?>" onclick="changeImage(this.src, this)">
                    
                    <?php if ($foto_1): ?>
                        <img class="gallery-thumb" src="<?php echo $foto_1; ?>" onclick="changeImage(this.src, this)">
                    <?php endif; ?>
                    
                    <?php if ($foto_2): ?>
                        <img class="gallery-thumb" src="<?php echo $foto_2; ?>" onclick="changeImage(this.src, this)">
                    <?php endif; ?>
                </div>
            </div>

            <div class="detail-content">
                <div class="property-header">
                    <div class="title-area">
                        <h1><?php echo htmlspecialchars($inmueble['dir_inm']); ?></h1>
                        <p style="color: #7f8c8d;"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($inmueble['barrio_inm'] . ', ' . $inmueble['ciudad_inm']); ?></p>
                    </div>
                    <div class="price-area">
                        <div class="price-main">$<?php echo number_format($inmueble['precio_alq'], 0, ',', '.'); ?></div>
                        <span style="font-size: 14px; color: #95a5a6;">Alquiler mensual</span>
                    </div>
                </div>

                <div class="detail-grid">
                    <div class="main-column">
                        <div class="info-box" style="background: #fdfdfd; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
                            <h3>Detalles de la propiedad</h3>
                            <div style="display: flex; gap: 20px; margin-top: 15px;">
                                <span><i class="fas fa-bed"></i> <?php echo $inmueble['num_hab']; ?> Hab.</span>
                                <span><i class="fas fa-building"></i> <?php echo htmlspecialchars($inmueble['nom_tipoinm']); ?></span>
                            </div>
                            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">
                            <p><?php echo nl2br(htmlspecialchars($inmueble['caract_inm'])); ?></p>
                        </div>

                        <?php if ($inmueble['latitude'] && $inmueble['longitud']): ?>
                        <div class="map-section">
                            <h3 style="margin-top: 25px;"><i class="fas fa-map"></i> Ubicación</h3>
                            <div id="map" class="map-container"></div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="sidebar">
                        <div class="contact-card">
                            <h3>¿Te interesa?</h3>
                            <p style="margin: 15px 0; font-size: 14px; opacity: 0.9;">Contacta con nosotros para agendar una visita.</p>
                            
                            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                                <small>Atendido por:</small><br>
                                <strong><?php echo htmlspecialchars($inmueble['nom_ofi'] ?? 'Oficina Central'); ?></strong>
                            </div>

                            <button class="btn-contact-main" onclick="contactarWhatsApp()" style="width: 100%; background: #27ae60; border: none; padding: 15px; color: white; border-radius: 8px; cursor: pointer; font-weight: bold;">
                                <i class="fab fa-whatsapp"></i> Contactar por WhatsApp
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Cambiar imagen de