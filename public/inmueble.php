<?php
// Portal Público - Vista Detallada de Inmueble
require_once __DIR__ . '/../conexion.php';
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

// --- Preparación de Variables para Open Graph ---
$og_title = htmlspecialchars($inmueble['dir_inm'] . " - $" . number_format($inmueble['precio_alq']));
$og_description = htmlspecialchars(substr(strip_tags($inmueble['caract_inm']), 0, 160) . '...');
// Asegúrate de que esta sea tu URL de producción final
$og_url = 'https://casameta.onrender.com/public/inmueble.php?id=' . $inmueble['cod_inm'];
// Usamos la misma lógica que ya tienes para obtener la URL de la foto
$og_image = get_foto_url($inmueble['foto']);
// --- Fin de la preparación ---


// Pre-procesar URLs de fotos para la galería
$foto_principal = get_foto_url($inmueble['foto']);
$foto_1 = !empty($inmueble['foto_1']) ? get_foto_url($inmueble['foto_1']) : null;
$foto_2 = !empty($inmueble['foto_2']) ? get_foto_url($inmueble['foto_2']) : null;

// Lógica para Videos (Local/Cloudinary y Externo)
$video_local = null;
if (!empty($inmueble['video'])) {
    $vid = trim(str_replace('"', '', $inmueble['video']));
    // Si ya es URL completa (Cloudinary)
    if (stripos($vid, 'http') === 0) {
        // Corregir posible duplicidad de ruta upload/uploads si existe
        $video_local = str_replace('/upload/uploads/', '/upload/', $vid);
    } else {
        // Si es nombre de archivo local, construir URL de Cloudinary (Video)
        $video_local = "https://res.cloudinary.com/drbeqchej/video/upload/" . str_replace(' ', '_', basename(str_replace('uploads/', '', $vid)));
    }
}

$video_externo = null;
if (!empty($inmueble['video_url'])) {
    $url = $inmueble['video_url'];
    // Detectar y convertir enlaces de YouTube a formato embed
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $matches)) {
        // Se agrega autoplay=1 y mute=1 para asegurar reproducción automática en la mayoría de navegadores
        $video_externo = "https://www.youtube.com/embed/" . $matches[1] . "?autoplay=1&mute=1&rel=0";
    } else {
        $video_externo = $url; // Otros proveedores o enlace directo
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($inmueble['dir_inm']); ?> - Casa Meta</title>

    <!-- Open Graph Dinámico para este Inmueble -->
    <meta property="og:title" content="<?php echo $og_title; ?>" />
    <meta property="og:type" content="article" />
    <meta property="og:url" content="<?php echo $og_url; ?>" />
    <meta property="og:image" content="<?php echo $og_image; ?>" />
    <meta property="og:description" content="<?php echo $og_description; ?>" />
    <meta property="og:site_name" content="Casa Meta" />

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
        .gallery-container { position: relative; height: 500px; background: #222; overflow: hidden; }
        .main-image { width: 100%; height: 100%; object-fit: contain; transition: transform 0.3s ease; cursor: zoom-in; }
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

                        <?php if ($video_local || $video_externo): ?>
                            <div class="video-section" style="margin-top: 30px; background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
                                <h3 style="margin-bottom: 20px; color: #2c3e50;"><i class="fas fa-video"></i> Video Recorrido</h3>
                                
                                <?php if ($video_local): ?>
                                    <div class="video-container" style="margin-bottom: 20px; border-radius: 8px; overflow: hidden; background: #000;">
                                        <video controls style="width: 100%; max-height: 450px; display: block;">
                                            <source src="<?php echo $video_local; ?>" type="video/mp4">
                                            Tu navegador no soporta la reproducción de video.
                                        </video>
                                        <p style="padding: 10px; font-size: 14px; color: #fff; background: #333; margin: 0;"><i class="fas fa-film"></i> Video del inmueble</p>
                                    </div>
                                <?php endif; ?>

                                <?php if ($video_externo): ?>
                                    <div class="video-container" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px; background: #000;">
                                        <iframe src="<?php echo $video_externo; ?>" 
                                                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" 
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                                allowfullscreen>
                                        </iframe>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

<?php if (!empty($inmueble['latitude']) && !empty($inmueble['longitud'])): ?>
    <div class="map-section">
        <h3 style="margin-top: 25px;"><i class="fas fa-map"></i> Ubicación</h3>
        <div id="map" class="map-container" style="height: 400px; width: 100%; border-radius: 10px; border: 1px solid #ddd;"></div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Extraemos los datos de PHP usando tus nombres de columna exactos
        const lat = <?php echo floatval($inmueble['latitude']); ?>;
        const lng = <?php echo floatval($inmueble['longitud']); ?>;

        // Inicializamos el mapa centrado en las coordenadas del inmueble
        const map = L.map('map').setView([lat, lng], 16);

        // Cargamos la capa de OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Colocamos el marcador (Pin)
        L.marker([lat, lng]).addTo(map)
            .bindPopup('<?php echo addslashes($inmueble['barrio_inm']); ?>')
            .openPopup();

        // Solución para evitar que el mapa cargue "gris" o incompleto
        setTimeout(function() {
            map.invalidateSize();
        }, 500);
    });
    </script>
<?php else: ?>
    <div class="alert alert-warning mt-4">
        <i class="fas fa-exclamation-triangle"></i> Coordenadas no disponibles para este inmueble.
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
        // Función para cambiar imagen en galería
        function changeImage(src, element) {
            document.getElementById('mainImage').src = src;
            const thumbs = document.querySelectorAll('.gallery-thumb');
            thumbs.forEach(thumb => thumb.classList.remove('active'));
            element.classList.add('active');
        }

        // Lógica de Favoritos (Faltaba esta función)
        let favoritos = JSON.parse(localStorage.getItem('favoriteProperties') || '[]');
        
        function updateFavoritesCount() {
            const el = document.getElementById('favoritesCountNav');
            if(el) el.textContent = favoritos.length;
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateFavoritesCount();
        });

        // Función unificada y segura para WhatsApp
        function contactarWhatsApp() {
            // Datos seguros desde PHP
            const direccion = <?php echo json_encode($inmueble['dir_inm']); ?>;
            const ciudad = <?php echo json_encode($inmueble['ciudad_inm']); ?>;
            const precio = "<?php echo number_format($inmueble['precio_alq'], 0, ',', '.'); ?>";
            const id = "<?php echo $inmueble['cod_inm']; ?>";
            const telefono = "<?php echo !empty($inmueble['tel_ofi']) ? preg_replace('/[^0-9]/', '', $inmueble['tel_ofi']) : '573246611306'; ?>";
            
            const mensaje = `Hola Casa Meta, estoy interesado en:\n` +
                            `📍 ${direccion}, ${ciudad}\n` +
                            `💰 $${precio}\n` +
                            `🆔 Ref: ${id}\n` +
                            `🔗 ${window.location.href}`;

            const url = `https://wa.me/${telefono}?text=${encodeURIComponent(mensaje)}`;
            window.open(url, '_blank');
        }
    </script>
</body>
</html>
?><?php
