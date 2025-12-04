<?php
// Portal Público - Vista Detallada de Inmueble
require_once '../conexion.php';

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
        .inmueble-detail {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .gallery-container {
            position: relative;
            height: 400px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .main-image {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            transition: transform 0.3s ease;
            cursor: zoom-in;
        }

        .main-image:hover {
            transform: scale(1.02);
        }

        .main-image.zoomed {
            transform: scale(1.5);
            cursor: zoom-out;
        }

        .gallery-nav {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            background: rgba(0,0,0,0.7);
            padding: 10px;
            border-radius: 10px;
        }

        .gallery-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.3s;
            opacity: 0.8;
        }

        .gallery-thumb:hover {
            opacity: 1;
            transform: scale(1.1);
        }

        .gallery-thumb.active {
            border-color: #3498db;
            opacity: 1;
        }

        .image-fullscreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 10000;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .fullscreen-image {
            max-width: 95%;
            max-height: 95%;
            object-fit: contain;
        }

        .close-fullscreen {
            position: absolute;
            top: 20px;
            right: 20px;
            background: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 20px;
            cursor: pointer;
            z-index: 10001;
        }

        .detail-content {
            padding: 30px;
        }

        .property-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .property-title-section h1 {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .property-location {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #7f8c8d;
            font-size: 16px;
        }

        .property-price-section {
            text-align: right;
        }

        .price-main {
            font-size: 32px;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 5px;
        }

        .price-label {
            color: #7f8c8d;
            font-size: 14px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-top: 30px;
        }

        .main-details, .sidebar-details {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
        }

        .detail-section {
            margin-bottom: 30px;
        }

        .detail-section h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px;
            background: white;
            border-radius: 8px;
        }

        .map-container {
            height: 300px;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 15px;
        }

        .video-section {
            margin-top: 15px;
        }

        .video-player {
            width: 100%;
            max-width: 500px;
            border-radius: 10px;
        }

        .video-external {
            background: #3498db;
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }

        .video-external a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .contact-card {
            background: #2c3e50;
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
        }

        .contact-info {
            margin-bottom: 20px;
        }

        .contact-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn-contact-main {
            background: #e74c3c;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            flex: 2;
            transition: background 0.3s;
        }

        .btn-contact-main:hover {
            background: #c0392b;
        }

        .btn-favorite {
            background: #ecf0f1;
            color: #7f8c8d;
            padding: 12px 20px;
            border: 2px solid #bdc3c7;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            flex: 1;
            transition: all 0.3s;
            min-width: 120px;
        }

        .btn-favorite:hover {
            background: #e74c3c;
            color: white;
            border-color: #e74c3c;
        }

        .btn-favorite.active {
            background: #e74c3c;
            color: white;
            border-color: #e74c3c;
        }

        .btn-compare-single {
            background: #ecf0f1;
            color: #7f8c8d;
            padding: 12px 20px;
            border: 2px solid #bdc3c7;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            flex: 1;
            transition: all 0.3s;
            min-width: 120px;
        }

        .btn-compare-single:hover {
            background: #f39c12;
            color: white;
            border-color: #f39c12;
        }

        .btn-compare-single.active {
            background: #f39c12;
            color: white;
            border-color: #f39c12;
        }

        @media (max-width: 768px) {
            .contact-actions {
                flex-direction: column;
            }
            
            .btn-contact-main, 
            .btn-favorite,
            .btn-compare-single {
                flex: 1;
                width: 100%;
            }
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #95a5a6;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            transition: background 0.3s;
        }

        .btn-back:hover {
            background: #7f8c8d;
        }

        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }

            .property-header {
                flex-direction: column;
                text-align: center;
            }

            .property-price-section {
                text-align: center;
            }

            .gallery-container {
                height: 250px;
            }
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <!-- Header simplificado -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <h1><i class="fas fa-home"></i> Casa Meta</h1>
            </div>
            <nav>
                <ul class="nav-links">
                    <li><a href="index.php"><i class="fas fa-home"></i> Catálogo</a></li>
                    <li><a href="mapa.php"><i class="fas fa-map"></i> Mapa</a></li>
                    <li><a href="favoritos.php"><i class="fas fa-heart"></i> Favoritos (<span id="favoritesCountNav">0</span>)</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <a href="index.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Volver al catálogo
        </a>

        <div class="inmueble-detail">
            <!-- Galería de fotos -->
            <div class="gallery-container">
                <img id="mainImage" class="main-image" 
                     src="../<?php echo !empty($inmueble['foto']) && file_exists('../' . $inmueble['foto']) 
                                    ? htmlspecialchars($inmueble['foto']) 
                                    : 'https://via.placeholder.com/800x400/e0e0e0/666666?text=Sin+Imagen'; ?>" 
                     alt="<?php echo htmlspecialchars($inmueble['dir_inm']); ?>"
                     onclick="toggleImageZoom(this)">
                
                <!-- Navegación de galería -->
                <div class="gallery-nav">
                    <?php if (!empty($inmueble['foto']) && file_exists('../' . $inmueble['foto'])): ?>
                        <img class="gallery-thumb active" 
                             src="../<?php echo htmlspecialchars($inmueble['foto']); ?>" 
                             onclick="changeImage(this.src)" alt="Foto principal">
                    <?php endif; ?>
                    
                    <?php if (!empty($inmueble['foto_2']) && file_exists('../' . $inmueble['foto_2'])): ?>
                        <img class="gallery-thumb" 
                             src="../<?php echo htmlspecialchars($inmueble['foto_2']); ?>" 
                             onclick="changeImage(this.src)" alt="Foto secundaria">
                    <?php endif; ?>
                    
                    <!-- Botón para pantalla completa -->
                    <button onclick="openFullscreen()" style="background: #3498db; border: none; border-radius: 8px; color: white; width: 60px; height: 60px; cursor: pointer;">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>

            <!-- Modal para pantalla completa -->
            <div id="imageFullscreen" class="image-fullscreen" onclick="closeFullscreen()">
                <button class="close-fullscreen" onclick="closeFullscreen()">
                    <i class="fas fa-times"></i>
                </button>
                <img id="fullscreenImage" class="fullscreen-image" src="" alt="">
            </div>

            <!-- Contenido principal -->
            <div class="detail-content">
                <!-- Header del inmueble -->
                <div class="property-header">
                    <div class="property-title-section">
                        <h1><?php echo htmlspecialchars($inmueble['dir_inm']); ?></h1>
                        <div class="property-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($inmueble['barrio_inm'] . ', ' . $inmueble['ciudad_inm']); ?>
                            <?php if (!empty($inmueble['pais_inm'])): ?>
                                , <?php echo htmlspecialchars($inmueble['pais_inm']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="property-price-section">
                        <div class="price-main">$<?php echo number_format($inmueble['precio_alq'], 0, ',', '.'); ?></div>
                        <div class="price-label">Precio de alquiler</div>
                    </div>
                </div>

                <!-- Grid de detalles -->
                <div class="detail-grid">
                    <!-- Detalles principales -->
                    <div class="main-details">
                        <!-- Características básicas -->
                        <div class="detail-section">
                            <h3><i class="fas fa-info-circle"></i> Características principales</h3>
                            <div class="features-grid">
                                <div class="feature-item">
                                    <i class="fas fa-home"></i>
                                    <span><?php echo htmlspecialchars(ucfirst($inmueble['nom_tipoinm'] ?? 'Inmueble')); ?></span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-bed"></i>
                                    <span><?php echo $inmueble['num_hab']; ?> habitaciones</span>
                                </div>
                                <?php if ($inmueble['latitude'] && $inmueble['longitud']): ?>
                                <div class="feature-item">
                                    <i class="fas fa-map"></i>
                                    <span>Ubicación exacta</span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($inmueble['video']) || !empty($inmueble['video_url'])): ?>
                                <div class="feature-item">
                                    <i class="fas fa-video"></i>
                                    <span>Video disponible</span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <?php if (!empty($inmueble['caract_inm'])): ?>
                        <div class="detail-section">
                            <h3><i class="fas fa-align-left"></i> Descripción</h3>
                            <p><?php echo nl2br(htmlspecialchars($inmueble['caract_inm'])); ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Notas adicionales -->
                        <?php if (!empty($inmueble['notas_inm'])): ?>
                        <div class="detail-section">
                            <h3><i class="fas fa-sticky-note"></i> Notas adicionales</h3>
                            <p><?php echo nl2br(htmlspecialchars($inmueble['notas_inm'])); ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Video -->
                        <?php if (!empty($inmueble['video']) || !empty($inmueble['video_url'])): ?>
                        <div class="detail-section">
                            <h3><i class="fas fa-video"></i> Video del inmueble</h3>
                            <div class="video-section">
                                <?php if (!empty($inmueble['video']) && file_exists('../' . $inmueble['video'])): ?>
                                    <video controls class="video-player">
                                        <source src="../<?php echo htmlspecialchars($inmueble['video']); ?>" type="video/mp4">
                                        Tu navegador no soporta el elemento video.
                                    </video>
                                <?php endif; ?>
                                
                                <?php if (!empty($inmueble['video_url'])): ?>
                                    <div class="video-external">
                                        <i class="fas fa-external-link-alt"></i>
                                        <p>Video externo disponible:</p>
                                        <a href="<?php echo htmlspecialchars($inmueble['video_url']); ?>" target="_blank">
                                            Ver video en plataforma externa
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Mapa -->
                        <?php if ($inmueble['latitude'] && $inmueble['longitud']): ?>
                        <div class="detail-section">
                            <h3><i class="fas fa-map-marker-alt"></i> Ubicación</h3>
                            <div id="map" class="map-container"></div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Sidebar -->
                    <div class="sidebar-details">
                        <!-- Información de contacto -->
                        <div class="contact-card">
                            <h3><i class="fas fa-phone"></i> Contactar</h3>
                            
                            <?php if (!empty($inmueble['nom_prop'])): ?>
                            <div class="contact-info">
                                <strong>Propietario:</strong><br>
                                <?php echo htmlspecialchars($inmueble['nom_prop']); ?>
                                <?php if (!empty($inmueble['tel_prop'])): ?>
                                    <br><i class="fas fa-phone"></i> <?php echo htmlspecialchars($inmueble['tel_prop']); ?>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($inmueble['nom_ofi'])): ?>
                            <div class="contact-info">
                                <strong>Oficina:</strong><br>
                                <?php echo htmlspecialchars($inmueble['nom_ofi']); ?>
                                <?php if (!empty($inmueble['tel_ofi'])): ?>
                                    <br><i class="fas fa-phone"></i> <?php echo htmlspecialchars($inmueble['tel_ofi']); ?>
                                <?php endif; ?>
                                <?php if (!empty($inmueble['email_ofi'])): ?>
                                    <br><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($inmueble['email_ofi']); ?>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <div class="contact-actions">
                                <button class="btn-contact-main" onclick="openContactModal(<?php echo $inmueble['cod_inm']; ?>, {
                                    direccion: '<?php echo addslashes($inmueble['dir_inm']); ?>',
                                    ciudad: '<?php echo addslashes($inmueble['ciudad_inm']); ?>',
                                    precio: '<?php echo number_format($inmueble['precio_alq'], 0, ',', '.'); ?>',
                                    tipo: '<?php echo addslashes($inmueble['nom_tipoinm'] ?? 'Inmueble'); ?>'
                                })">
                                    <i class="fas fa-envelope"></i> Contactar ahora
                                </button>
                                
                                <button class="btn-favorite" onclick="toggleFavorite()" title="Agregar a favoritos">
                                    <i class="far fa-heart"></i> Favoritos
                                </button>
                                
                                <button class="btn-compare-single" onclick="toggleCompareFromDetail()" title="Agregar para comparar">
                                    <i class="fas fa-balance-scale"></i> Comparar
                                </button>
                            </div>
                        </div>

                        <!-- Enlaces web -->
                        <?php if (!empty($inmueble['web_p1']) || !empty($inmueble['web_p2'])): ?>
                        <div class="detail-section">
                            <h3><i class="fas fa-link"></i> Enlaces</h3>
                            <?php if (!empty($inmueble['web_p1'])): ?>
                                <p><a href="<?php echo htmlspecialchars($inmueble['web_p1']); ?>" target="_blank" class="btn-details">
                                    <i class="fas fa-external-link-alt"></i> Enlace 1
                                </a></p>
                            <?php endif; ?>
                            <?php if (!empty($inmueble['web_p2'])): ?>
                                <p><a href="<?php echo htmlspecialchars($inmueble['web_p2']); ?>" target="_blank" class="btn-details">
                                    <i class="fas fa-external-link-alt"></i> Enlace 2
                                </a></p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Casa Meta. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Sistema de favoritos
        let favoritos = JSON.parse(localStorage.getItem('favoriteProperties') || '[]');
        const inmuebleId = <?php echo $inmueble['cod_inm']; ?>;

        function updateFavoritesCount() {
            const countElement = document.getElementById('favoritesCountNav');
            if (countElement) {
                countElement.textContent = favoritos.length;
            }
        }

        function toggleFavorite() {
            const btn = document.querySelector('.btn-favorite');
            const icon = btn.querySelector('i');
            
            if (favoritos.includes(inmuebleId)) {
                // Remover de favoritos
                favoritos = favoritos.filter(id => id !== inmuebleId);
                btn.classList.remove('active');
                icon.className = 'far fa-heart';
                showToast('💔 Removido de favoritos');
            } else {
                // Agregar a favoritos
                favoritos.push(inmuebleId);
                btn.classList.add('active');
                icon.className = 'fas fa-heart';
                showToast('❤️ Agregado a favoritos');
            }

            localStorage.setItem('favoriteProperties', JSON.stringify(favoritos));
            updateFavoritesCount();
        }

        function showToast(message) {
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #2c3e50;
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                z-index: 10000;
                animation: slideIn 0.3s ease;
            `;
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // Inicializar favoritos y contador al cargar
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.querySelector('.btn-favorite');
            if (btn && favoritos.includes(inmuebleId)) {
                btn.classList.add('active');
                btn.querySelector('i').className = 'fas fa-heart';
            }
            updateFavoritesCount();
            updateCompareButton();
        });

        // Función para el botón de comparar desde detalles
        function toggleCompareFromDetail() {
            if (compareSystem) {
                const success = compareSystem.toggleItem(inmuebleId);
                if (success) {
                    updateCompareButton();
                }
            }
        }

        function updateCompareButton() {
            const btn = document.querySelector('.btn-compare-single');
            if (btn && compareSystem) {
                if (compareSystem.isSelected(inmuebleId)) {
                    btn.classList.add('active');
                    btn.innerHTML = '<i class="fas fa-check"></i> En comparación';
                } else {
                    btn.classList.remove('active');
                    btn.innerHTML = '<i class="fas fa-balance-scale"></i> Comparar';
                }
            }
        }

        // Galería de imágenes mejorada
        function changeImage(src) {
            const mainImage = document.getElementById('mainImage');
            mainImage.src = src;
            mainImage.classList.remove('zoomed');
            
            // Actualizar thumbnail activo
            document.querySelectorAll('.gallery-thumb').forEach(thumb => {
                thumb.classList.remove('active');
            });
            
            event.target.classList.add('active');
        }

        // Zoom de imagen
        function toggleImageZoom(img) {
            img.classList.toggle('zoomed');
        }

        // Pantalla completa
        function openFullscreen() {
            const mainImage = document.getElementById('mainImage');
            const fullscreenImage = document.getElementById('fullscreenImage');
            const fullscreenModal = document.getElementById('imageFullscreen');
            
            fullscreenImage.src = mainImage.src;
            fullscreenModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeFullscreen() {
            const fullscreenModal = document.getElementById('imageFullscreen');
            fullscreenModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Cerrar con Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeFullscreen();
            }
        });

        // Mapa
        <?php if ($inmueble['latitude'] && $inmueble['longitud']): ?>
        const lat = <?php echo $inmueble['latitude']; ?>;
        const lng = <?php echo $inmueble['longitud']; ?>;
        
        const map = L.map('map').setView([lat, lng], 16);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        L.marker([lat, lng])
            .addTo(map)
            .bindPopup('<strong><?php echo htmlspecialchars($inmueble['dir_inm']); ?></strong><br><?php echo htmlspecialchars($inmueble['barrio_inm'] . ', ' . $inmueble['ciudad_inm']); ?>')
            .openPopup();
        <?php endif; ?>

        // Función de contacto
        function contactarInmueble() {
            const mensaje = `Hola! Estoy interesado en el inmueble:\n\n` +
                           `📍 Dirección: <?php echo htmlspecialchars($inmueble['dir_inm']); ?>\n` +
                           `🏠 Tipo: <?php echo htmlspecialchars(ucfirst($inmueble['nom_tipoinm'] ?? 'Inmueble')); ?>\n` +
                           `💰 Precio: $<?php echo number_format($inmueble['precio_alq'], 0, ',', '.'); ?>\n` +
                           `🛏️ Habitaciones: <?php echo $inmueble['num_hab']; ?>\n\n` +
                           `¿Podrían proporcionarme más información?`;
            
            <?php if (!empty($inmueble['tel_prop'])): ?>
                const telefono = '<?php echo preg_replace('/[^0-9]/', '', $inmueble['tel_prop']); ?>';
                const whatsappUrl = `https://wa.me/${telefono}?text=${encodeURIComponent(mensaje)}`;
                window.open(whatsappUrl, '_blank');
            <?php else: ?>
                alert('📞 Información de contacto:\n\n' + mensaje);
            <?php endif; ?>
        }
    </script>
    
    <script src="js/compare-system.js"></script>
    <script src="js/leads-system.js"></script>
</body>
</html>

<?php
$conn->close();
?>