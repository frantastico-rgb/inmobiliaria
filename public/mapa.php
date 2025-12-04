<?php
// Portal Público - Mapa General de Inmuebles
require_once '../conexion.php';

// Obtener todos los inmuebles con coordenadas
$sql = "SELECT i.cod_inm, i.dir_inm, i.barrio_inm, i.ciudad_inm, i.precio_alq, i.num_hab, 
               i.latitude, i.longitud, i.foto, t.nom_tipoinm
        FROM inmuebles i 
        LEFT JOIN tipo_inmueble t ON i.cod_tipoinm = t.cod_tipoinm 
        WHERE i.latitude IS NOT NULL AND i.longitud IS NOT NULL 
        AND i.latitude != 0 AND i.longitud != 0
        ORDER BY i.cod_inm DESC";

$resultado = $conn->query($sql);
$inmuebles = [];

while ($inmueble = $resultado->fetch_assoc()) {
    $inmuebles[] = $inmueble;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa de Inmuebles - Casa Meta</title>
    <link rel="stylesheet" href="css/catalogo.css">
    <link rel="stylesheet" href="css/compare-widget.css">
    <link rel="stylesheet" href="css/leads-system.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        /* Estilos específicos para el mapa */
        .map-container {
            height: calc(100vh - 160px);
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: relative;
        }

        .map-controls {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 1000;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 280px;
        }

        .map-controls h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 16px;
        }

        .control-group {
            margin-bottom: 12px;
        }

        .control-group label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 4px;
        }

        .control-group select {
            width: 100%;
            padding: 6px 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
        }

        .btn-reset {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            width: 100%;
            margin-top: 8px;
        }

        .btn-reset:hover {
            background: #7f8c8d;
        }

        .map-stats {
            position: absolute;
            bottom: 20px;
            left: 20px;
            z-index: 1000;
            background: rgba(44, 62, 80, 0.9);
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 14px;
        }

        /* Popup personalizado */
        .leaflet-popup-content {
            margin: 8px 12px;
            line-height: 1.4;
        }

        .popup-content {
            min-width: 200px;
        }

        .popup-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .popup-price {
            color: #e74c3c;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 8px;
        }

        .popup-details {
            color: #666;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .popup-image {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 8px;
        }

        .popup-actions {
            text-align: center;
        }

        .btn-popup {
            background: #3498db;
            color: white;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            display: inline-block;
            margin-right: 5px;
        }

        .btn-popup:hover {
            background: #2980b9;
        }

        .btn-popup-contact {
            background: #27ae60;
        }

        .btn-popup-contact:hover {
            background: #229954;
        }

        /* Legend/Leyenda */
        .map-legend {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: white;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            font-size: 12px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 6px;
        }

        .legend-marker {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 8px;
            border: 2px solid white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }

        .marker-apartamento { background: #3498db; }
        .marker-casa { background: #27ae60; }
        .marker-lote { background: #f39c12; }
        .marker-oficina { background: #9b59b6; }

        @media (max-width: 768px) {
            .map-controls {
                position: relative;
                max-width: none;
                margin-bottom: 20px;
            }

            .map-legend {
                position: relative;
                margin-bottom: 20px;
            }

            .map-container {
                height: 60vh;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <h1><i class="fas fa-home"></i> Casa Meta</h1>
                <p>Mapa de Propiedades</p>
            </div>
            <nav>
                <ul class="nav-links">
                    <li><a href="index.php"><i class="fas fa-th-large"></i> Catálogo</a></li>
                    <li><a href="favoritos.php"><i class="fas fa-heart"></i> Favoritos</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <!-- Mapa principal -->
        <div class="map-container">
            <div id="map" style="height: 100%; width: 100%;"></div>
            
            <!-- Controles del mapa -->
            <div class="map-controls">
                <h3><i class="fas fa-filter"></i> Filtrar en Mapa</h3>
                
                <div class="control-group">
                    <label>Tipo de Inmueble:</label>
                    <select id="filterTipo" onchange="filterMap()">
                        <option value="">Todos</option>
                        <option value="apartamento">Apartamentos</option>
                        <option value="casa">Casas</option>
                        <option value="lote">Lotes</option>
                        <option value="oficina">Oficinas</option>
                    </select>
                </div>

                <div class="control-group">
                    <label>Ciudad:</label>
                    <select id="filterCiudad" onchange="filterMap()">
                        <option value="">Todas</option>
                    </select>
                </div>

                <button class="btn-reset" onclick="resetFilters()">
                    <i class="fas fa-undo"></i> Mostrar Todos
                </button>
            </div>

            <!-- Leyenda -->
            <div class="map-legend">
                <div class="legend-item">
                    <div class="legend-marker marker-apartamento"></div>
                    <span>Apartamentos</span>
                </div>
                <div class="legend-item">
                    <div class="legend-marker marker-casa"></div>
                    <span>Casas</span>
                </div>
                <div class="legend-item">
                    <div class="legend-marker marker-lote"></div>
                    <span>Lotes</span>
                </div>
                <div class="legend-item">
                    <div class="legend-marker marker-oficina"></div>
                    <span>Oficinas</span>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="map-stats" id="mapStats">
                <i class="fas fa-home"></i> <span id="statsText">Cargando propiedades...</span>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Datos de inmuebles desde PHP
        const inmuebles = <?php echo json_encode($inmuebles); ?>;
        
        // Variables del mapa
        let map;
        let markersLayer;
        let allMarkers = [];

        // Colores por tipo
        const tipoColors = {
            'apartamento': '#3498db',
            'casa': '#27ae60', 
            'lote': '#f39c12',
            'oficina': '#9b59b6'
        };

        // Inicializar mapa
        function initMap() {
            // Mapa centrado en Bogotá por defecto
            map = L.map('map').setView([4.6097, -74.0817], 11);
            
            // Tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 18
            }).addTo(map);

            // Capa de marcadores
            markersLayer = L.layerGroup().addTo(map);
            
            // Agregar marcadores
            addMarkers();
            
            // Poblar filtros
            populateFilters();
            
            // Actualizar estadísticas
            updateStats();
        }

        // Agregar marcadores al mapa
        function addMarkers() {
            allMarkers = [];
            
            inmuebles.forEach(inmueble => {
                const lat = parseFloat(inmueble.latitude);
                const lng = parseFloat(inmueble.longitud);
                
                if (!isNaN(lat) && !isNaN(lng)) {
                    const tipo = (inmueble.nom_tipoinm || 'apartamento').toLowerCase();
                    const color = tipoColors[tipo] || '#3498db';
                    
                    // Crear icono personalizado
                    const icon = L.divIcon({
                        className: 'custom-marker',
                        html: `<div style="
                            background: ${color};
                            width: 20px;
                            height: 20px;
                            border-radius: 50%;
                            border: 2px solid white;
                            box-shadow: 0 1px 3px rgba(0,0,0,0.3);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: white;
                            font-size: 10px;
                            font-weight: bold;
                        ">$</div>`,
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    });

                    // Crear popup
                    const popupContent = createPopupContent(inmueble);
                    
                    // Crear marcador
                    const marker = L.marker([lat, lng], { icon })
                        .bindPopup(popupContent, {
                            maxWidth: 250,
                            className: 'custom-popup'
                        });

                    // Agregar datos al marcador para filtros
                    marker.inmuebleData = inmueble;
                    
                    allMarkers.push(marker);
                    markersLayer.addLayer(marker);
                }
            });

            // Ajustar vista si hay marcadores
            if (allMarkers.length > 0) {
                const group = new L.featureGroup(allMarkers);
                map.fitBounds(group.getBounds().pad(0.1));
            }
        }

        // Crear contenido del popup
        function createPopupContent(inmueble) {
            const imageUrl = inmueble.foto ? 
                `../${inmueble.foto}` : 
                'https://via.placeholder.com/200x100/e0e0e0/666666?text=Sin+Imagen';
            
            return `
                <div class="popup-content">
                    <img src="${imageUrl}" alt="Inmueble" class="popup-image" onerror="this.src='https://via.placeholder.com/200x100/e0e0e0/666666?text=Sin+Imagen'">
                    
                    <div class="popup-title">${inmueble.dir_inm}</div>
                    
                    <div class="popup-price">$${new Intl.NumberFormat('es-CO').format(inmueble.precio_alq)}</div>
                    
                    <div class="popup-details">
                        <i class="fas fa-map-marker-alt"></i> ${inmueble.barrio_inm}, ${inmueble.ciudad_inm}<br>
                        <i class="fas fa-home"></i> ${inmueble.nom_tipoinm || 'Inmueble'}<br>
                        <i class="fas fa-bed"></i> ${inmueble.num_hab} habitaciones
                    </div>
                    
                    <div class="popup-actions">
                        <a href="inmueble.php?id=${inmueble.cod_inm}" class="btn-popup">
                            <i class="fas fa-eye"></i> Ver detalles
                        </a>
                        <a href="#" onclick="contactarDesdePopup(${inmueble.cod_inm})" class="btn-popup btn-popup-contact">
                            <i class="fas fa-phone"></i> Contactar
                        </a>
                    </div>
                </div>
            `;
        }

        // Poblar filtros dinámicamente
        function populateFilters() {
            const ciudades = [...new Set(inmuebles.map(i => i.ciudad_inm))].sort();
            const selectCiudad = document.getElementById('filterCiudad');
            
            ciudades.forEach(ciudad => {
                const option = document.createElement('option');
                option.value = ciudad;
                option.textContent = ciudad;
                selectCiudad.appendChild(option);
            });
        }

        // Filtrar marcadores
        function filterMap() {
            const tipoFilter = document.getElementById('filterTipo').value.toLowerCase();
            const ciudadFilter = document.getElementById('filterCiudad').value;
            
            markersLayer.clearLayers();
            const filteredMarkers = [];
            
            allMarkers.forEach(marker => {
                const inmueble = marker.inmuebleData;
                const tipo = (inmueble.nom_tipoinm || '').toLowerCase();
                
                const matchTipo = !tipoFilter || tipo.includes(tipoFilter);
                const matchCiudad = !ciudadFilter || inmueble.ciudad_inm === ciudadFilter;
                
                if (matchTipo && matchCiudad) {
                    markersLayer.addLayer(marker);
                    filteredMarkers.push(marker);
                }
            });
            
            updateStats(filteredMarkers.length);
        }

        // Resetear filtros
        function resetFilters() {
            document.getElementById('filterTipo').value = '';
            document.getElementById('filterCiudad').value = '';
            
            markersLayer.clearLayers();
            allMarkers.forEach(marker => markersLayer.addLayer(marker));
            
            updateStats();
        }

        // Actualizar estadísticas
        function updateStats(filtered = null) {
            const count = filtered !== null ? filtered : allMarkers.length;
            const total = allMarkers.length;
            
            document.getElementById('statsText').textContent = 
                filtered !== null ? 
                `${count} de ${total} propiedades mostradas` :
                `${total} propiedades disponibles`;
        }

        // Contactar desde popup
        function contactarDesdePopup(inmuebleId) {
            const inmueble = inmuebles.find(i => i.cod_inm == inmuebleId);
            if (inmueble) {
                alert(`Contactar sobre:\n${inmueble.dir_inm}\n\n📞 Función de contacto próximamente...`);
            }
        }

        // Inicializar cuando carga la página
        document.addEventListener('DOMContentLoaded', initMap);
    </script>
    
    <script src="js/compare-system.js"></script>
    <script src="js/leads-system.js"></script>
</body>
</html>

<?php
$conn->close();
?>