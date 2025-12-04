<?php
// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

// Verificar si se recibió el ID del inmueble a editar
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $inmueble_id = $_GET['id'];

    // Consulta para obtener la información del inmueble
    $sql_inmueble = "SELECT * FROM inmuebles WHERE cod_inm = ?";
    $stmt_inmueble = $conn->prepare($sql_inmueble);
    $stmt_inmueble->bind_param("i", $inmueble_id);
    $stmt_inmueble->execute();
    $resultado_inmueble = $stmt_inmueble->get_result();

    if ($resultado_inmueble->num_rows == 1) {
        $inmueble = $resultado_inmueble->fetch_assoc();

        // Consulta para obtener los tipos de inmueble para el desplegable
        $sql_tipos = "SELECT cod_tipoinm, nom_tipoinm FROM tipo_inmueble";
        $resultado_tipos = $conn->query($sql_tipos);
        $tipos_inmueble = [];
        if ($resultado_tipos->num_rows > 0) {
            while ($fila = $resultado_tipos->fetch_assoc()) {
                $tipos_inmueble[$fila['cod_tipoinm']] = $fila['nom_tipoinm'];
            }
        }

        // Consulta para obtener los propietarios para el desplegable
        $sql_propietarios = "SELECT cod_prop, nom_prop FROM propietarios";
        $resultado_propietarios = $conn->query($sql_propietarios);
        $propietarios = [];
        if ($resultado_propietarios->num_rows > 0) {
            while ($fila = $resultado_propietarios->fetch_assoc()) {
                $propietarios[$fila['cod_prop']] = $fila['nom_prop'];
            }
        }

    } else {
        // Si el ID no es válido o no se encuentra el inmueble, redirigir con un mensaje de error
        $_SESSION['mensaje'] = "Inmueble no encontrado.";
        header("Location: lista_inmuebles.php");
        exit();
    }

} else {
    // Si no se recibió un ID válido, redirigir con un mensaje de error
    $_SESSION['mensaje'] = "ID de inmueble inválido.";
    header("Location: lista_inmuebles.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Inmueble</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .coordinates-section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
        }
        .coordinates-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        .coordinates-row > div {
            flex: 1;
        }
        #map-inmueble {
            height: 300px;
            width: 100%;
            border: 2px solid #007bff;
            border-radius: 8px;
            margin: 15px 0;
        }
        .btn-search-address {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            font-size: 14px;
        }
        .btn-search-address:hover {
            background-color: #218838;
        }
        .btn-search-address:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class=logo-icono>
        <img src="uploads/logo_casa_meta.png" alt="logo">
    </div>
    <hr>
    <h1>Editar Inmueble</h1>

    <form action="guardarCambios_inmueble.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="cod_inm" value="<?php echo $inmueble['cod_inm']; ?>">

        <div>
            <label for="dir_inm">Dirección:</label>
            <input type="text" id="dir_inm" name="dir_inm" value="<?php echo $inmueble['dir_inm']; ?>">
        </div>

        <div>
            <label for="barrio_inm">Barrio:</label>
            <input type="text" id="barrio_inm" name="barrio_inm" value="<?php echo $inmueble['barrio_inm']; ?>">
        </div>

        <div>
            <label for="ciudad_inm">Ciudad:</label>
            <input type="text" id="ciudad_inm" name="ciudad_inm" value="<?php echo $inmueble['ciudad_inm']; ?>" placeholder="Ej: Bogotá">
        </div>

        <div>
            <label for="pais_inm">País:</label>
            <input type="text" id="pais_inm" name="pais_inm" value="<?php echo $inmueble['pais_inm'] ?? 'Colombia'; ?>">
        </div>

        <div class="coordinates-section">
            <h3>📍 Ubicación en Mapa</h3>
            
            <div class="coordinates-row">
                <div>
                    <label for="latitude">Latitud:</label>
                    <input type="number" id="latitude" name="latitude" step="any" value="<?php echo $inmueble['latitude']; ?>" placeholder="4.6097">
                </div>
                <div>
                    <label for="longitud">Longitud:</label>
                    <input type="number" id="longitud" name="longitud" step="any" value="<?php echo $inmueble['longitud']; ?>" placeholder="-74.0817">
                </div>
            </div>
            
            <div style="margin-bottom: 10px;">
                <button type="button" id="search-full-address" class="btn-search-address">
                    🔍 Buscar Dirección
                </button>
                <button type="button" id="search-city-only" class="btn-search-address" style="background-color: #17a2b8;">
                    🏙️ Centrar en Ciudad
                </button>
                <button type="button" id="search-coordinates" class="btn-search-address" style="background-color: #6f42c1;">
                    📐 Ir a Coordenadas
                </button>
            </div>
            
            <div id="map-inmueble"></div>
            
            <small style="color: #666;">
                💡 Haz clic en el mapa para actualizar la ubicación del inmueble.
            </small>
        </div>

        <div>
            <label for="foto">Foto Principal:</label>
            <input type="file" id="foto" name="foto" accept="image/*">
            <?php if ($inmueble['foto']): ?>
                <div style="margin-top: 10px;">
                    <p>📷 <strong>Foto actual:</strong></p>
                    <img src="<?php echo $inmueble['foto']; ?>" alt="Foto Principal" style="max-width: 200px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <input type="hidden" name="foto_actual" value="<?php echo $inmueble['foto']; ?>">
                </div>
            <?php endif; ?>
            <small style="color: #666; display: block; margin-top: 5px;">
                📷 Formatos: JPG, PNG, GIF | Tamaño máximo: 5MB
            </small>
        </div>

        <div>
            <label for="foto_secundaria">Foto Secundaria:</label>
            <input type="file" id="foto_secundaria" name="foto_secundaria" accept="image/*">
            <?php if (!empty($inmueble['foto_2'])): ?>
                <div style="margin-top: 10px;">
                    <p>📸 <strong>Foto secundaria actual:</strong></p>
                    <img src="<?php echo $inmueble['foto_2']; ?>" alt="Foto Secundaria" style="max-width: 200px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <input type="hidden" name="foto_secundaria_actual" value="<?php echo $inmueble['foto_2']; ?>">
                </div>
            <?php endif; ?>
            <small style="color: #666; display: block; margin-top: 5px;">
                📸 Foto adicional del inmueble | Tamaño máximo: 5MB
            </small>
        </div>

        <div>
            <label for="video">Video Local del Inmueble:</label>
            <input type="file" id="video" name="video" accept="video/*">
            <?php if (!empty($inmueble['video'])): ?>
                <div style="margin-top: 10px;">
                    <p>🎥 <strong>Video local actual:</strong></p>
                    <video controls style="max-width: 300px; border-radius: 8px;">
                        <source src="<?php echo $inmueble['video']; ?>" type="video/mp4">
                        Tu navegador no soporta el elemento video.
                    </video>
                    <input type="hidden" name="video_actual" value="<?php echo $inmueble['video']; ?>">
                </div>
            <?php endif; ?>
            <small style="color: #666; display: block; margin-top: 5px;">
                🎥 Video subido al servidor | Máximo: 50MB, 2 minutos recomendado
            </small>
        </div>

        <div>
            <label for="video_url">Enlace a Video Externo:</label>
            <input type="url" id="video_url" name="video_url" value="<?php echo $inmueble['video_url'] ?? ''; ?>" 
                   placeholder="https://www.youtube.com/watch?v=... o https://www.instagram.com/p/...">
            <small style="color: #666; display: block; margin-top: 5px;">
                🔗 YouTube, Instagram, Vimeo, TikTok | Sin límites de duración o tamaño | Opcional
            </small>
        </div>

        <div>
            <label for="web_p1">Enlace Web Página 1:</label>
            <input type="url" id="web_p1" name="web_p1" value="<?php echo $inmueble['web_p1']; ?>">
        </div>

        <div>
            <label for="web_p2">Enlace Web Página 2:</label>
            <input type="url" id="web_p2" name="web_p2" value="<?php echo $inmueble['web_p2']; ?>">
        </div>

        <div>
            <label for="cod_tipoinm">Tipo de Inmueble:</label>
            <select id="cod_tipoinm" name="cod_tipoinm">
                <option value="">Seleccionar Tipo</option>
                <?php foreach ($tipos_inmueble as $cod => $nombre): ?>
                    <option value="<?php echo $cod; ?>" <?php if ($inmueble['cod_tipoinm'] == $cod) echo 'selected'; ?>><?php echo $nombre; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="num_hab">Número de Habitaciones:</label>
            <input type="number" id="num_hab" name="num_hab" value="<?php echo $inmueble['num_hab']; ?>">
        </div>

        <div>
            <label for="precio_alq">Precio de Alquiler:</label>
            <input type="number" step="0.01" id="precio_alq" name="precio_alq" value="<?php echo $inmueble['precio_alq']; ?>">
        </div>

        <div>
            <label for="cod_prop">Propietario:</label>
            <select id="cod_prop" name="cod_prop">
                <option value="">Seleccionar Propietario</option>
                <?php foreach ($propietarios as $cod => $nombre): ?>
                    <option value="<?php echo $cod; ?>" <?php if ($inmueble['cod_prop'] == $cod) echo 'selected'; ?>><?php echo $nombre; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="caract_inm">Característica:</label>
            <select id="caract_inm" name="caract_inm">
                <option value="">Seleccionar Característica</option>
                <option value="Conjunto" <?php if ($inmueble['caract_inm'] == 'Conjunto') echo 'selected'; ?>>Conjunto</option>
                <option value="Urb" <?php if ($inmueble['caract_inm'] == 'Urb') echo 'selected'; ?>>Urb</option>
            </select>
        </div>

        <div>
            <label for="notas_inm">Notas Adicionales:</label>
            <textarea id="notas_inm" name="notas_inm"><?php echo $inmueble['notas_inm']; ?></textarea>
        </div>

        <div>
            <button type="submit">Guardar Cambios</button>
            <a href="lista_inmuebles.php">Cancelar</a>
        </div>
    </form>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map;
        let marker;
        
        // Ciudades colombianas expandidas
        const cities = {
            // Principales ciudades
            'bogotá': [4.6097, -74.0817], 'bogota': [4.6097, -74.0817],
            'medellín': [6.2442, -75.5812], 'medellin': [6.2442, -75.5812],
            'cali': [3.4516, -76.5320], 'cartagena': [10.3910, -75.4794],
            'barranquilla': [10.9639, -74.7964], 'bucaramanga': [7.1254, -73.1198],
            
            // Ciudades intermedias
            'villavicencio': [4.1420, -73.6266], 'girardot': [4.3017, -74.8022],
            'sincelejo': [9.3047, -75.3978], 'pereira': [4.8133, -75.6961],
            'neiva': [2.9273, -75.2819], 'ibagué': [4.4389, -75.2322], 'ibague': [4.4389, -75.2322],
            'manizales': [5.0670, -75.5174], 'montería': [8.7479, -75.8814], 'monteria': [8.7479, -75.8814],
            'popayán': [2.4448, -76.6147], 'popayn': [2.4448, -76.6147], 'popayan': [2.4448, -76.6147],
            'armenia': [4.5339, -75.6811], 'tunja': [5.5353, -73.3678],
            'pasto': [1.2136, -77.2811], 'valledupar': [10.4631, -73.2532],
            
            // Capitales departamentales
            'florencia': [1.6144, -75.6062], 'yopal': [5.3347, -72.3958],
            'riohacha': [11.5444, -72.9072], 'santa marta': [11.2408, -74.1990], 'santamarta': [11.2408, -74.1990],
            'quibdó': [5.6947, -76.6581], 'quibdo': [5.6947, -76.6581],
            'inírida': [3.8653, -67.9239], 'inirida': [3.8653, -67.9239],
            'leticia': [-4.2151, -69.9406], 'mitú': [1.2581, -70.2336], 'mitu': [1.2581, -70.2336],
            'puerto carreño': [6.1890, -67.4858], 'puertocarreño': [6.1890, -67.4858], 'puerto carreno': [6.1890, -67.4858],
            'san josé del guaviare': [2.5648, -72.6459], 'sanjosedelguaviare': [2.5648, -72.6459],
            
            // Ciudades importantes del Meta
            'acacías': [3.9886, -73.7608], 'acacias': [3.9886, -73.7608],
            'puerto lópez': [4.0890, -72.9667], 'puerto lopez': [4.0890, -72.9667], 'puertolopez': [4.0890, -72.9667]
        };
        
        document.addEventListener('DOMContentLoaded', function() {
            try {
                // Obtener coordenadas actuales del inmueble
                const currentLat = parseFloat(document.getElementById('latitude').value) || 4.6097;
                const currentLng = parseFloat(document.getElementById('longitud').value) || -74.0817;
                
                // Crear mapa centrado en la ubicación actual del inmueble
                map = L.map('map-inmueble').setView([currentLat, currentLng], 15);
                
                // Agregar tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19,
                    minZoom: 1
                }).addTo(map);
                
                // Colocar marcador en la ubicación actual
                if (currentLat !== 4.6097 || currentLng !== -74.0817) {
                    placeMarker(currentLat, currentLng);
                }
                
                // Click en mapa
                map.on('click', function(e) {
                    placeMarker(e.latlng.lat, e.latlng.lng);
                });
                
                setupButtons();
                setupFileValidation();
                
            } catch (error) {
                document.getElementById('map-inmueble').innerHTML = 
                    '<div style="padding:20px;text-align:center;color:red;">Error: ' + error.message + '</div>';
            }
        });
        
        function placeMarker(lat, lng) {
            if (marker) marker.remove();
            
            marker = L.marker([lat, lng], {draggable: true}).addTo(map)
                .bindPopup('🔍 Buscando dirección...').openPopup();
            
            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitud').value = lng.toFixed(6);
            
            // Geocodificación inversa - buscar dirección desde coordenadas
            reverseGeocode(lat, lng);
            
            marker.on('dragend', function(e) {
                const pos = e.target.getLatLng();
                document.getElementById('latitude').value = pos.lat.toFixed(6);
                document.getElementById('longitud').value = pos.lng.toFixed(6);
                
                // Actualizar dirección cuando se arrastra el marcador
                marker.bindPopup('🔍 Actualizando dirección...').openPopup();
                reverseGeocode(pos.lat, pos.lng);
            });
        }
        
        // Nueva función: Geocodificación inversa global
        function reverseGeocode(lat, lng) {
            // Usar API global sin restricción de país
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.display_name) {
                        const address = data.address || {};
                        
                        // Extraer componentes de la dirección (adaptado para cualquier país)
                        const road = address.road || address.pedestrian || address.path || '';
                        const houseNumber = address.house_number || '';
                        const neighbourhood = address.neighbourhood || address.suburb || address.quarter || address.village || '';
                        const city = address.city || address.town || address.village || address.municipality || address.county || '';
                        const state = address.state || address.region || address.province || '';
                        const country = address.country || '';
                        
                        // Construir dirección completa
                        let fullAddress = '';
                        if (road) {
                            fullAddress = road;
                            if (houseNumber) fullAddress = road + ' #' + houseNumber;
                        }
                        
                        // Actualizar campos si están vacíos (no sobrescribir datos existentes)
                        const dirField = document.getElementById('dir_inm');
                        const barrioField = document.getElementById('barrio_inm');
                        const ciudadField = document.getElementById('ciudad_inm');
                        const paisField = document.getElementById('pais_inm');
                        
                        // Solo actualizar si el campo está vacío
                        if (!dirField.value.trim() && fullAddress) {
                            dirField.value = fullAddress;
                        }
                        if (!barrioField.value.trim() && neighbourhood) {
                            barrioField.value = neighbourhood;
                        }
                        if (!ciudadField.value.trim() && city) {
                            ciudadField.value = city;
                        }
                        if (country && (!paisField.value.trim() || paisField.value === 'Colombia')) {
                            paisField.value = country;
                        }
                        
                        // Determinar el emoji del país
                        const countryEmoji = getCountryEmoji(country);
                        
                        // Actualizar popup del marcador
                        const popupContent = `
                            <div style="max-width: 250px;">
                                <strong>${countryEmoji} Ubicación actualizada</strong><br>
                                <small>${data.display_name}</small><br><br>
                                <em>✏️ Editando inmueble</em>
                            </div>
                        `;
                        marker.setPopupContent(popupContent);
                        
                        // Mostrar notificación discreta con país
                        const locationName = city || neighbourhood || state || 'Ubicación';
                        showLocationNotification(`${countryEmoji} Ubicación actualizada: ${locationName}, ${country || 'Mundo'}`, 'success');
                        
                    } else {
                        marker.setPopupContent(`
                            <div>
                                <strong>🌍 Ubicación actualizada</strong><br>
                                <small>Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}</small><br>
                                <em>⚠️ No se pudo obtener la dirección exacta</em>
                            </div>
                        `);
                        showLocationNotification('🌍 Coordenadas actualizadas', 'info');
                    }
                })
                .catch(error => {
                    console.error('Error en geocodificación inversa:', error);
                    marker.setPopupContent(`
                        <div>
                            <strong>🌍 Ubicación actualizada</strong><br>
                            <small>Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}</small>
                        </div>
                    `);
                    showLocationNotification('🌍 Coordenadas actualizadas', 'info');
                });
        }
        
        // Nueva función: Obtener emoji del país
        function getCountryEmoji(country) {
            if (!country) return '🌍';
            
            const countryEmojis = {
                'colombia': '🇨🇴', 'united states': '🇺🇸', 'usa': '🇺🇸', 'estados unidos': '🇺🇸',
                'france': '🇫🇷', 'francia': '🇫🇷', 'spain': '🇪🇸', 'españa': '🇪🇸',
                'brazil': '🇧🇷', 'brasil': '🇧🇷', 'argentina': '🇦🇷', 'chile': '🇨🇱',
                'peru': '🇵🇪', 'perú': '🇵🇪', 'ecuador': '🇪🇨', 'venezuela': '🇻🇪',
                'mexico': '🇲🇽', 'méxico': '🇲🇽', 'canada': '🇨🇦', 'canadá': '🇨🇦',
                'united kingdom': '🇬🇧', 'reino unido': '🇬🇧', 'germany': '🇩🇪', 'alemania': '🇩🇪',
                'italy': '🇮🇹', 'italia': '🇮🇹', 'japan': '🇯🇵', 'japón': '🇯🇵',
                'china': '🇨🇳', 'australia': '🇦🇺', 'india': '🇮🇳'
            };
            
            return countryEmojis[country.toLowerCase()] || '🌍';
        }
        
        function setupButtons() {
            // Búsqueda de dirección
            document.getElementById('search-full-address').onclick = function() {
                const direccion = document.getElementById('dir_inm').value.trim();
                const ciudad = document.getElementById('ciudad_inm').value.trim();
                
                if (!direccion && !ciudad) {
                    alert('Por favor ingrese una dirección o ciudad');
                    return;
                }
                
                let query = '';
                if (direccion) query += direccion;
                if (ciudad) query += (query ? ', ' : '') + ciudad;
                if (!query.toLowerCase().includes('colombia') && !query.includes(',')) {
                    query += ', Colombia'; // Solo agregar Colombia si no hay país especificado
                }
                
                this.innerHTML = '🔄 Buscando...';
                this.disabled = true;
                
                const button = this;
                
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`)
                    .then(response => response.json())
                    .then(data => {
                        button.innerHTML = '🔍 Buscar Dirección';
                        button.disabled = false;
                        
                        if (data && data.length > 0) {
                            const lat = parseFloat(data[0].lat);
                            const lng = parseFloat(data[0].lon);
                            
                            map.setView([lat, lng], 16);
                            placeMarker(lat, lng);
                            
                            alert('✅ Ubicación encontrada: ' + data[0].display_name);
                        } else {
                            alert('❌ No se encontró la dirección. Prueba con:\\n• "Centrar en Ciudad" para ciudades colombianas\\n• Direcciones más específicas (ej: "Times Square, New York")\\n• Verificar ortografía');
                        }
                    })
                    .catch(error => {
                        button.innerHTML = '🔍 Buscar Dirección';
                        button.disabled = false;
                        alert('Error de búsqueda: ' + error.message);
                    });
            };
            
            // Centrar en ciudad
            document.getElementById('search-city-only').onclick = function() {
                const ciudad = document.getElementById('ciudad_inm').value.trim().toLowerCase();
                
                if (!ciudad) {
                    alert('Por favor ingrese la ciudad');
                    return;
                }
                
                this.innerHTML = '🔄 Centrando...';
                this.disabled = true;
                
                const button = this;
                
                setTimeout(function() {
                    button.innerHTML = '🏙️ Centrar en Ciudad';
                    button.disabled = false;
                    
                    if (cities[ciudad]) {
                        map.setView(cities[ciudad], 12);
                        
                        alert(`✅ Mapa centrado en ${ciudad}.\\n\\n💡 Ahora haz clic en el mapa para actualizar la ubicación del inmueble.`);
                    } else {
                        alert(`❌ Ciudad "${ciudad}" no encontrada.\\n\\n🏙️ Ciudades disponibles:\\n• Principales: bogotá, medellín, cali, barranquilla, cartagena\\n• Intermedias: bucaramanga, pereira, villavicencio, girardot, sincelejo\\n• Capitales: manizales, armenia, popayán, pasto, neiva, ibagué\\n• Meta: acacías, puerto lópez`);
                    }
                }, 300);
            };
            
            // Búsqueda por coordenadas
            document.getElementById('search-coordinates').onclick = function() {
                const lat = document.getElementById('latitude').value.trim();
                const lng = document.getElementById('longitud').value.trim();
                
                if (!lat || !lng) {
                    alert('Por favor ingrese latitud y longitud para ir a esa ubicación');
                    return;
                }
                
                const latitude = parseFloat(lat);
                const longitude = parseFloat(lng);
                
                if (isNaN(latitude) || isNaN(longitude)) {
                    alert('Por favor ingrese coordenadas válidas\\n\\nEjemplos:\\n• Bogotá: 4.6097, -74.0817\\n• París: 48.8566, 2.3522\\n• Nueva York: 40.7128, -74.0060\\n• Tokio: 35.6762, 139.6503');
                    return;
                }
                
                // Validación básica de rangos mundiales
                if (latitude < -90 || latitude > 90 || longitude < -180 || longitude > 180) {
                    alert('⚠️ Coordenadas fuera de rango válido.\\n\\n🌍 Rangos globales:\\n• Latitud: -90 a 90\\n• Longitud: -180 a 180');
                    return;
                }
                
                this.innerHTML = '🔄 Navegando...';
                this.disabled = true;
                
                const button = this;
                
                setTimeout(function() {
                    button.innerHTML = '📐 Ir a Coordenadas';
                    button.disabled = false;
                    
                    // Determinar zoom apropiado basado en la distancia a Colombia
                    const colombiaLat = 4.6097;
                    const colombiaLng = -74.0817;
                    
                    // Calcular distancia aproximada
                    const latDiff = Math.abs(latitude - colombiaLat);
                    const lngDiff = Math.abs(longitude - colombiaLng);
                    const maxDiff = Math.max(latDiff, lngDiff);
                    
                    let zoomLevel = 16; // Zoom por defecto para ubicaciones locales
                    
                    if (maxDiff > 100) {
                        // Muy lejos (otro continente)
                        zoomLevel = 3;
                        // Primero mostrar vista global, luego hacer zoom
                        map.setView([0, 0], 2);
                        setTimeout(() => {
                            map.setView([latitude, longitude], 12);
                        }, 1000);
                    } else if (maxDiff > 50) {
                        // Lejos (otro país)
                        zoomLevel = 6;
                        map.setView([latitude, longitude], zoomLevel);
                    } else if (maxDiff > 10) {
                        // Moderadamente lejos (mismo continente)
                        zoomLevel = 10;
                        map.setView([latitude, longitude], zoomLevel);
                    } else {
                        // Cerca (mismo país o región)
                        map.setView([latitude, longitude], zoomLevel);
                    }
                    
                    // Si no es la animación global, ir directamente
                    if (maxDiff <= 100) {
                        map.setView([latitude, longitude], zoomLevel);
                    }
                    
                    placeMarker(latitude, longitude);
                    
                    // Notificación con información de ubicación
                    const isInColombia = (latitude >= -4.5 && latitude <= 12.5 && 
                                         longitude >= -79 && longitude <= -66);
                    const locationInfo = isInColombia ? 
                        `📐 Colombia: ${latitude.toFixed(4)}, ${longitude.toFixed(4)}` :
                        `🌍 Global: ${latitude.toFixed(4)}, ${longitude.toFixed(4)}`;
                    
                    showLocationNotification(locationInfo, 'info');
                }, 500);
            };
        }
        
        // Nueva función: Notificaciones discretas
        function showLocationNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 12px 20px;
                border-radius: 6px;
                color: white;
                font-size: 14px;
                z-index: 10000;
                max-width: 300px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                transition: all 0.3s ease;
                background-color: ${type === 'success' ? '#28a745' : type === 'warning' ? '#ffc107' : '#17a2b8'};
            `;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Remover después de 3 segundos
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.parentNode.removeChild(notification);
                        }
                    }, 300);
                }
            }, 3000);
        }
        
        // Nueva función: Validación de archivos
        function setupFileValidation() {
            // Validación de fotos
            document.getElementById('foto').addEventListener('change', function(e) {
                validateImage(e.target, 'Foto Principal');
            });
            
            document.getElementById('foto_secundaria').addEventListener('change', function(e) {
                validateImage(e.target, 'Foto Secundaria');
            });
            
            // Validación de video
            document.getElementById('video').addEventListener('change', function(e) {
                validateVideo(e.target);
            });
        }
        
        function validateImage(input, label) {
            const file = input.files[0];
            if (!file) return;
            
            const maxSize = 5 * 1024 * 1024; // 5MB
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            
            if (!allowedTypes.includes(file.type)) {
                alert(`❌ ${label}: Formato no válido.\\n\\n✅ Formatos permitidos: JPG, PNG, GIF`);
                input.value = '';
                return;
            }
            
            if (file.size > maxSize) {
                alert(`❌ ${label}: Archivo muy grande.\\n\\n📏 Tamaño actual: ${(file.size / 1024 / 1024).toFixed(1)}MB\\n📏 Máximo permitido: 5MB`);
                input.value = '';
                return;
            }
            
            showLocationNotification(`✅ ${label} cargada: ${file.name} (${(file.size / 1024 / 1024).toFixed(1)}MB)`, 'success');
        }
        
        function validateVideo(input) {
            const file = input.files[0];
            if (!file) return;
            
            const maxSize = 50 * 1024 * 1024; // 50MB
            const allowedTypes = ['video/mp4', 'video/mov', 'video/quicktime', 'video/avi', 'video/x-msvideo'];
            
            if (!allowedTypes.includes(file.type)) {
                alert(`❌ Video: Formato no válido.\\n\\n✅ Formatos permitidos: MP4, MOV, AVI`);
                input.value = '';
                return;
            }
            
            if (file.size > maxSize) {
                alert(`❌ Video: Archivo muy grande.\\n\\n📏 Tamaño actual: ${(file.size / 1024 / 1024).toFixed(1)}MB\\n📏 Máximo permitido: 50MB\\n\\n💡 Sugerencia: Comprimir el video o reducir la calidad`);
                input.value = '';
                return;
            }
            
            showLocationNotification(`✅ Video cargado: ${file.name} (${(file.size / 1024 / 1024).toFixed(1)}MB)`, 'success');
            
            // Mostrar información adicional
            setTimeout(() => {
                showLocationNotification(`🎥 Video listo para subir. Recuerda que la duración máxima recomendada es 2 minutos.`, 'info');
            }, 2000);
        }
    </script>
</body>
</html>