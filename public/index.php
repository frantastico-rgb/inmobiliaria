<?php
// Portal Público - Catálogo de Inmuebles
require_once '../conexion.php';

// Obtener filtros de búsqueda
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$filtro_ciudad = isset($_GET['ciudad']) ? $_GET['ciudad'] : '';
$filtro_precio_min = isset($_GET['precio_min']) ? $_GET['precio_min'] : '';
$filtro_precio_max = isset($_GET['precio_max']) ? $_GET['precio_max'] : '';
$filtro_habitaciones = isset($_GET['habitaciones']) ? $_GET['habitaciones'] : '';

// Construir consulta SQL con filtros
$sql = "SELECT i.*, t.nom_tipoinm, p.nom_prop, p.tel_prop, o.nom_ofi, o.tel_ofi, o.email_ofi 
        FROM inmuebles i 
        LEFT JOIN tipo_inmueble t ON i.cod_tipoinm = t.cod_tipoinm 
        LEFT JOIN propietarios p ON i.cod_prop = p.cod_prop 
        LEFT JOIN oficina o ON p.cod_prop = o.Id_ofi 
        WHERE 1=1";

$params = [];
$types = "";

// Aplicar filtros
if (!empty($filtro_tipo)) {
    $sql .= " AND i.cod_tipoinm = ?";
    $params[] = $filtro_tipo;
    $types .= "i";
}

if (!empty($filtro_ciudad)) {
    $sql .= " AND i.ciudad_inm LIKE ?";
    $params[] = "%" . $filtro_ciudad . "%";
    $types .= "s";
}

if (!empty($filtro_precio_min)) {
    $sql .= " AND i.precio_alq >= ?";
    $params[] = $filtro_precio_min;
    $types .= "d";
}

if (!empty($filtro_precio_max)) {
    $sql .= " AND i.precio_alq <= ?";
    $params[] = $filtro_precio_max;
    $types .= "d";
}

if (!empty($filtro_habitaciones)) {
    $sql .= " AND i.num_hab = ?";
    $params[] = $filtro_habitaciones;
    $types .= "i";
}

$sql .= " ORDER BY i.cod_inm DESC";

// Ejecutar consulta
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resultado = $stmt->get_result();

// Obtener tipos de inmueble para filtros
$sql_tipos = "SELECT * FROM tipo_inmueble ORDER BY nom_tipoinm";
$tipos_resultado = $conn->query($sql_tipos);

// Obtener ciudades disponibles para filtros
$sql_ciudades = "SELECT DISTINCT ciudad_inm FROM inmuebles WHERE ciudad_inm IS NOT NULL AND ciudad_inm != '' ORDER BY ciudad_inm";
$ciudades_resultado = $conn->query($sql_ciudades);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Casa Meta - Catálogo de Inmuebles</title>
    <link rel="stylesheet" href="css/catalogo.css">
    <link rel="stylesheet" href="css/compare-widget.css">
    <link rel="stylesheet" href="css/leads-system.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="description" content="Encuentra tu inmueble ideal - Catálogo completo de propiedades en venta y alquiler">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <h1><i class="fas fa-home"></i> Casa Meta</h1>
                <p>Conectamos Sueños, con espacio perfecto</p>
            </div>
            <nav>
                <ul class="nav-links">
                    <li><a href="#inicio"><i class="fas fa-home"></i> Inicio</a></li>
                    <li><a href="#inmuebles"><i class="fas fa-building"></i> Inmuebles</a></li>
                    <li><a href="mapa.php"><i class="fas fa-map"></i> Mapa</a></li>
                    <li><a href="favoritos.php"><i class="fas fa-heart"></i> Favoritos (<span id="favoritesCountNav">0</span>)</a></li>
                    <li><a href="#contacto"><i class="fas fa-envelope"></i> Contacto</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <!-- Filtros de búsqueda -->
        <section class="search-filters" id="inmuebles">
            <h2><i class="fas fa-search"></i> Buscar Inmuebles</h2>
            <form method="GET" action="">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="tipo">Tipo de Inmueble:</label>
                        <select name="tipo" id="tipo">
                            <option value="">Todos los tipos</option>
                            <?php while ($tipo = $tipos_resultado->fetch_assoc()): ?>
                                <option value="<?php echo $tipo['cod_tipoinm']; ?>" 
                                        <?php echo ($filtro_tipo == $tipo['cod_tipoinm']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(ucfirst($tipo['nom_tipoinm'])); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="ciudad">Ciudad:</label>
                        <select name="ciudad" id="ciudad">
                            <option value="">Todas las ciudades</option>
                            <?php while ($ciudad = $ciudades_resultado->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($ciudad['ciudad_inm']); ?>" 
                                        <?php echo ($filtro_ciudad == $ciudad['ciudad_inm']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ciudad['ciudad_inm']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="habitaciones">Habitaciones:</label>
                        <select name="habitaciones" id="habitaciones">
                            <option value="">Cualquier cantidad</option>
                            <option value="1" <?php echo ($filtro_habitaciones == '1') ? 'selected' : ''; ?>>1 habitación</option>
                            <option value="2" <?php echo ($filtro_habitaciones == '2') ? 'selected' : ''; ?>>2 habitaciones</option>
                            <option value="3" <?php echo ($filtro_habitaciones == '3') ? 'selected' : ''; ?>>3 habitaciones</option>
                            <option value="4" <?php echo ($filtro_habitaciones == '4') ? 'selected' : ''; ?>>4+ habitaciones</option>
                        </select>
                    </div>
                </div>

                <div class="filter-row">
                    <div class="filter-group">
                        <label for="precio_min">Precio mínimo:</label>
                        <input type="number" name="precio_min" id="precio_min" 
                               value="<?php echo htmlspecialchars($filtro_precio_min); ?>" 
                               placeholder="Ej: 500000">
                    </div>

                    <div class="filter-group">
                        <label for="precio_max">Precio máximo:</label>
                        <input type="number" name="precio_max" id="precio_max" 
                               value="<?php echo htmlspecialchars($filtro_precio_max); ?>" 
                               placeholder="Ej: 2000000">
                    </div>

                    <div class="filter-group">
                        <button type="submit" class="btn-search">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>

                    <div class="filter-group">
                        <a href="mapa.php" class="btn-map">
                            <i class="fas fa-map"></i> Ver en Mapa
                        </a>
                    </div>
                </div>
            </form>
        </section>

        <!-- Resultados -->
        <section class="results">
            <?php if ($resultado->num_rows > 0): ?>
                <h3>Se encontraron <?php echo $resultado->num_rows; ?> inmuebles</h3>
                
                <div class="properties-grid">
                    <?php while ($inmueble = $resultado->fetch_assoc()): ?>
                        <div class="property-card" data-id="<?php echo $inmueble['cod_inm']; ?>">
                            <!-- Checkbox para comparar -->
                            <div class="compare-checkbox-wrapper">
                                <input type="checkbox" class="compare-checkbox-input" 
                                       data-property-id="<?php echo $inmueble['cod_inm']; ?>" 
                                       title="Seleccionar para comparar">
                            </div>
                            
                            <!-- Imagen principal -->
                            <div class="property-image">
                                <?php if (!empty($inmueble['foto']) && file_exists('../' . $inmueble['foto'])): ?>
                                    <img src="../<?php echo htmlspecialchars($inmueble['foto']); ?>" 
                                         alt="<?php echo htmlspecialchars($inmueble['dir_inm']); ?>">
                                <?php else: ?>
                                    <div class="no-image-placeholder">
                                        <i class="fas fa-image"></i>
                                        <span>Sin Imagen</span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Precio -->
                                <div class="property-price">
                                    $<?php echo number_format($inmueble['precio_alq'], 0, ',', '.'); ?>
                                </div>

                                <!-- Tipo de inmueble -->
                                <div class="property-type">
                                    <?php echo htmlspecialchars(ucfirst($inmueble['nom_tipoinm'] ?? 'Inmueble')); ?>
                                </div>

                                <!-- Botón de favoritos -->
                                <button class="btn-favorite" onclick="toggleFavorite(<?php echo $inmueble['cod_inm']; ?>)">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>

                            <!-- Contenido -->
                            <div class="property-content">
                                <h3 class="property-title">
                                    <?php echo htmlspecialchars($inmueble['dir_inm']); ?>
                                </h3>

                                <div class="property-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($inmueble['barrio_inm'] . ', ' . $inmueble['ciudad_inm']); ?>
                                </div>

                                <div class="property-features">
                                    <div class="feature">
                                        <i class="fas fa-bed"></i>
                                        <?php echo $inmueble['num_hab']; ?> hab.
                                    </div>
                                    <?php if ($inmueble['latitude'] && $inmueble['longitud']): ?>
                                        <div class="feature">
                                            <i class="fas fa-map"></i>
                                            Ubicación exacta
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($inmueble['video']) || !empty($inmueble['video_url'])): ?>
                                        <div class="feature">
                                            <i class="fas fa-video"></i>
                                            Video disponible
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($inmueble['caract_inm'])): ?>
                                    <div class="property-description">
                                        <?php echo htmlspecialchars($inmueble['caract_inm']); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="property-actions">
                                    <button class="btn-favorite" onclick="toggleFavorite(<?php echo $inmueble['cod_inm']; ?>)" title="Agregar a favoritos">
                                        <i class="far fa-heart"></i>
                                    </button>
                                    
                                    <button class="btn-cta-card" onclick="openContactModal(<?php echo $inmueble['cod_inm']; ?>, {
                                        direccion: '<?php echo addslashes($inmueble['dir_inm']); ?>',
                                        ciudad: '<?php echo addslashes($inmueble['ciudad_inm']); ?>',
                                        precio: '<?php echo number_format($inmueble['precio_alq'], 0, ',', '.'); ?>',
                                        tipo: '<?php echo addslashes($inmueble['nom_tipoinm'] ?? 'Inmueble'); ?>'
                                    })" title="Contactar sobre esta propiedad">
                                        <i class="fas fa-envelope"></i> Consultar
                                    </button>
                                    
                                    <a href="inmueble.php?id=<?php echo $inmueble['cod_inm']; ?>" class="btn-details">
                                        <i class="fas fa-eye"></i> Ver detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

            <?php else: ?>
                <div class="no-results">
                    <h3><i class="fas fa-search-minus"></i> No se encontraron inmuebles</h3>
                    <p>Intenta ajustar los filtros de búsqueda para obtener más resultados.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- Sección de Contacto -->
    <section class="contact-section">
        <div class="contact-section-content">
            <h2><i class="fas fa-handshake"></i> ¿Necesitas ayuda?</h2>
            <p>Nuestros asesores expertos están listos para ayudarte a encontrar la propiedad perfecta. ¡Contáctanos sin compromiso!</p>
            
            <div class="contact-options">
                <div class="contact-option" onclick="openGeneralContact()">
                    <i class="fas fa-comments"></i>
                    <h4>Consulta General</h4>
                    <p>Qué tipo de propiedad buscas</p>
                </div>
                
                <div class="contact-option" onclick="window.open('https://wa.me/573001234567?text=Hola! Me interesa conocer más sobre sus propiedades disponibles.', '_blank')">
                    <i class="fab fa-whatsapp"></i>
                    <h4>WhatsApp Directo</h4>
                    <p>Respuesta inmediata</p>
                </div>
                
                <div class="contact-option" onclick="window.location.href='tel:+573001234567'">
                    <i class="fas fa-phone"></i>
                    <h4>Llamada Telefónica</h4>
                    <p>Habla con un asesor</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Casa Meta. Todos los derechos reservados.</p>
            <p><i class="fas fa-heart"></i> Conectamos Sueños, con espacio perfecto</p>
        </div>
    </footer>

    <script>
        // Sistema de favoritos simple
        let favoritos = JSON.parse(localStorage.getItem('favoriteProperties') || '[]');

        function updateFavoritesCount() {
            const countElement = document.getElementById('favoritesCountNav');
            if (countElement) {
                countElement.textContent = favoritos.length;
            }
        }

        function toggleFavorite(inmuebleId) {
            const index = favoritos.indexOf(inmuebleId);
            const btn = document.querySelector(`[data-id="${inmuebleId}"] .btn-favorite`);
            const icon = btn.querySelector('i');

            if (index === -1) {
                // Agregar a favoritos
                favoritos.push(inmuebleId);
                btn.classList.add('active');
                icon.className = 'fas fa-heart';
                showToast('❤️ Agregado a favoritos');
            } else {
                // Quitar de favoritos
                favoritos.splice(index, 1);
                btn.classList.remove('active');
                icon.className = 'far fa-heart';
                showToast('💔 Removido de favoritos');
            }

            localStorage.setItem('favoriteProperties', JSON.stringify(favoritos));
            updateFavoritesCount();
        }

        // Cargar favoritos al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            favoritos.forEach(id => {
                const btn = document.querySelector(`[data-id="${id}"] .btn-favorite`);
                if (btn) {
                    btn.classList.add('active');
                    btn.querySelector('i').className = 'fas fa-heart';
                }
            });
            updateFavoritesCount();
        });

        function contactarInmueble(inmuebleId) {
            // Aquí puedes implementar un modal de contacto o redireccionar
            alert(`Contactar sobre inmueble #${inmuebleId}\n\n📞 Próximamente: formulario de contacto directo`);
        }

        function showToast(message) {
            // Toast notification simple
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #2c3e50;
                color: white;
                padding: 15px 20px;
                border-radius: 5px;
                z-index: 1000;
                animation: slideIn 0.3s ease-out;
            `;
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease-out forwards';
                setTimeout(() => document.body.removeChild(toast), 300);
            }, 2000);
        }

        // Agregar estilos para animaciones de toast
        const styleSheet = document.createElement('style');
        styleSheet.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); }
                to { transform: translateX(0); }
            }
            @keyframes slideOut {
                from { transform: translateX(0); }
                to { transform: translateX(100%); }
            }
        `;
        document.head.appendChild(styleSheet);
    </script>
    
    <script src="js/compare-system.js"></script>
    <script src="js/leads-system.js"></script>
</body>
</html>

<?php
$conn->close();
?>