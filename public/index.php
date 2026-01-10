<?php
// Portal Público - Catálogo de Inmuebles
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/foto_utils.php';

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
    
    <!-- Etiquetas para vista previa en WhatsApp y Redes Sociales -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Casa Meta - Catálogo de Inmuebles">
    <meta property="og:description" content="Conectamos Sueños con el espacio perfecto. Explora nuestras propiedades destacadas en venta y alquiler.">
    <meta property="og:image" content="https://res.cloudinary.com/drbeqchej/image/upload/logo_casa_meta.png">
    <meta property="og:url" content="https://casameta.onrender.com">

    <!-- Meta Pixel Code (Facebook) -->
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', 'TU_PIXEL_ID'); // <-- REEMPLAZA ESTO CON TU ID DE PIXEL REAL
    fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=TU_PIXEL_ID&ev=PageView&noscript=1"/></noscript>
    <!-- End Meta Pixel Code -->

    <title>Casa Meta - Catálogo de Inmuebles</title>
    <link rel="stylesheet" href="css/catalogo.css">
    <link rel="stylesheet" href="css/compare-widget.css">
    <link rel="stylesheet" href="css/leads-system.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <h1><i class="fas fa-home"></i> Casa Meta</h1>
                <p>Conectamos Sueños, con espacio perfecto</p>
            </div>
            <nav>
                <ul class="nav-links">
                    <li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
                    <li><a href="#inmuebles"><i class="fas fa-building"></i> Inmuebles</a></li>
                    <li><a href="mapa.php"><i class="fas fa-map"></i> Mapa</a></li>
                    <li><a href="favoritos.php"><i class="fas fa-heart"></i> Favoritos (<span id="favoritesCountNav">0</span>)</a></li>
                    <li><a href="acceso.php"><i class="fas fa-user-tie"></i> Gestión</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <section class="search-filters" id="inmuebles">
            <h2><i class="fas fa-search"></i> Buscar Inmuebles</h2>
            <form method="GET" action="">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Tipo:</label>
                        <select name="tipo">
                            <option value="">Todos</option>
                            <?php while ($tipo = $tipos_resultado->fetch_assoc()): ?>
                                <option value="<?= $tipo['cod_tipoinm']; ?>" <?= ($filtro_tipo == $tipo['cod_tipoinm']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars(ucfirst($tipo['nom_tipoinm'])); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Ciudad:</label>
                        <select name="ciudad">
                            <option value="">Todas</option>
                            <?php while ($ciudad = $ciudades_resultado->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($ciudad['ciudad_inm']); ?>" <?= ($filtro_ciudad == $ciudad['ciudad_inm']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($ciudad['ciudad_inm']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn-search"><i class="fas fa-search"></i> Filtrar</button>
                    </div>
                </div>
            </form>
        </section>

        <section class="results">
            <?php if ($resultado->num_rows > 0): ?>
                <div class="properties-grid">
                    <?php while ($inmueble = $resultado->fetch_assoc()): ?>
                        <div class="property-card" data-id="<?= $inmueble['cod_inm']; ?>">
                            <div class="property-image">
                                <img src="<?= get_foto_url($inmueble['foto']); ?>" 
                                     alt="<?= htmlspecialchars($inmueble['dir_inm']); ?>"
                                     style="height: 250px; width: 100%; object-fit: cover;">
                                
                                <div class="property-price">
                                    $<?= number_format($inmueble['precio_alq'], 0, ',', '.'); ?>
                                </div>
                            </div>

                            <div class="property-content">
                                <h3><?= htmlspecialchars($inmueble['dir_inm']); ?></h3>
                                <p class="location"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($inmueble['barrio_inm'] . ', ' . $inmueble['ciudad_inm']); ?></p>
                                
                                <div class="property-features">
                                    <span><i class="fas fa-bed"></i> <?= $inmueble['num_hab']; ?> hab.</span>
                                </div>

                                <div class="property-actions">
                                    <a href="inmueble.php?id=<?= $inmueble['cod_inm']; ?>" class="btn-details" onclick="trackViewContent('<?= $inmueble['cod_inm'] ?>', '<?= addslashes($inmueble['dir_inm']) ?>', <?= floatval($inmueble['precio_alq']) ?>)">Ver detalles</a>
                                    <button class="btn-favorite" onclick="toggleFavorite(<?= $inmueble['cod_inm']; ?>)">
                                        <i class="far fa-heart"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <p>No se encontraron inmuebles con esos criterios.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Casa Meta. Conectamos sueños.</p>
    </footer>

    <script>
        let favoritos = JSON.parse(localStorage.getItem('favoriteProperties') || '[]');
        function updateFavoritesCount() {
            const el = document.getElementById('favoritesCountNav');
            if(el) el.textContent = favoritos.length;
        }
        function toggleFavorite(id) {
            const index = favoritos.indexOf(id);
            if (index === -1) {
                favoritos.push(id);
                alert("Agregado a favoritos");
            } else {
                favoritos.splice(index, 1);
            }
            localStorage.setItem('favoriteProperties', JSON.stringify(favoritos));
            updateFavoritesCount();
            location.reload(); // Para actualizar iconos
        }
        document.addEventListener('DOMContentLoaded', updateFavoritesCount);

        /**
         * Rastrea el evento 'ViewContent' del Pixel de Facebook cuando un usuario ve los detalles de una propiedad.
         * @param {string} propertyId - El ID único del inmueble.
         * @param {string} propertyName - El nombre o dirección del inmueble.
         * @param {number} propertyValue - El precio del inmueble.
         */
        function trackViewContent(propertyId, propertyName, propertyValue) {
            // Verificar si la función del Pixel de Facebook (fbq) está disponible
            if (typeof fbq === 'function') {
                fbq('track', 'ViewContent', {
                    content_type: 'product', // 'product' es un tipo estándar para catálogos
                    content_ids: [String(propertyId)], // Debe ser un array de strings
                    content_name: propertyName,
                    value: parseFloat(propertyValue),
                    currency: 'COP' // Peso Colombiano
                });
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>