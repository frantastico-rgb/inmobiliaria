<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Nueva Oficina</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="css/leaflet-maps.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
</head>
<body>
    <div class=logo-icono>
        <img src="uploads/logo_casa_meta.png" alt="logo">
    </div>
    <hr>
    <h1>Agregar Nueva Oficina</h1>

    <form action="guardar_oficina.php" method="post" enctype="multipart/form-data">
        <div>
            <label for="nom_ofi">Nombre de la Oficina:</label>
            <input type="text" id="nom_ofi" name="nom_ofi" required>
        </div>

        <div>
            <label for="dir_ofi">Dirección:</label>
            <input type="text" id="dir_ofi" name="dir_ofi" required>
        </div>

        <div>
            <label for="tel_ofi">Teléfono (opcional):</label>
            <input type="tel" id="tel_ofi" name="tel_ofi">
        </div>

        <div>
            <label for="email_ofi">Email (opcional):</label>
            <input type="email" id="email_ofi" name="email_ofi">
        </div>

        <!-- SECCIÓN DE UBICACIÓN CON MAPA -->
        <div class="coordinates-section">
            <h3>🏢 Ubicación de la Oficina</h3>
            
            <!-- Selector de Ciudad Rápida -->
            <div class="city-selector">
                <label for="ciudad_rapida">🏙️ Selección Rápida de Ciudad:</label>
                <select id="ciudad_rapida" onchange="selectCity(this.value, 'latitud', 'longitud', 'map-oficina')">
                    <option value="">Seleccione una ciudad...</option>
                    <option value="4.7110,-74.0721">Bogotá</option>
                    <option value="6.2442,-75.5812">Medellín</option>
                    <option value="3.4516,-76.5320">Cali</option>
                    <option value="11.0041,-74.8070">Barranquilla</option>
                    <option value="7.8890,-72.4966">Bucaramanga</option>
                    <option value="4.5389,-75.6578">Manizales</option>
                    <option value="4.8143,-75.6946">Pereira</option>
                    <option value="1.2136,-77.2811">Pasto</option>
                    <option value="2.4448,-76.6147">Popayán</option>
                    <option value="5.0700,-75.5138">Armenia</option>
                    <option value="8.7500,-75.8814">Montería</option>
                    <option value="9.3077,-75.3976">Sincelejo</option>
                    <option value="10.3910,-75.4794">Cartagena</option>
                    <option value="4.2421,-73.6127">Villavicencio</option>
                    <option value="4.3079,-74.8066">Girardot</option>
                    <option value="5.5353,-73.3678">Tunja</option>
                    <option value="4.4389,-75.2322">Ibagué</option>
                    <option value="2.9273,-75.2819">Neiva</option>
                    <option value="7.8939,-72.5078">Cúcuta</option>
                    <option value="10.4631,-73.2532">Valledupar</option>
                    <option value="11.2408,-74.2099">Santa Marta</option>
                    <option value="11.5444,-72.9059">Riohacha</option>
                    <option value="5.3478,-72.3936">Yopal</option>
                    <option value="4.5433,-75.6811">Dosquebradas</option>
                    <option value="6.1644,-75.6062">Bello</option>
                    <option value="6.2308,-75.5906">Envigado</option>
                    <option value="3.8542,-77.0297">Tumaco</option>
                    <option value="5.0344,-75.9138">Cartago</option>
                    <option value="8.3114,-62.7175">Puerto Ordaz (Venezuela)</option>
                    <option value="10.4806,-66.9036">Caracas (Venezuela)</option>
                </select>
                <button type="button" onclick="searchCurrentAddress('dir_ofi', 'latitud', 'longitud', 'map-oficina')">🔍 Buscar Dirección</button>
                <button type="button" onclick="searchByCoordinates('latitud', 'longitud', 'map-oficina')">🎯 Ir a Coordenadas</button>
            </div>
            
            <div class="coordinates-row">
                <div>
                    <label for="ciudad_ofi">Ciudad:</label>
                    <input type="text" id="ciudad_ofi" name="ciudad_ofi" placeholder="Ej: Bogotá" required>
                </div>
                <div>
                    <label for="pais_ofi">País:</label>
                    <input type="text" id="pais_ofi" name="pais_ofi" value="Colombia" placeholder="Ej: Colombia">
                </div>
            </div>
            
            <div class="coordinates-row">
                <div>
                    <label for="latitud">Latitud:</label>
                    <input type="number" id="latitud" name="latitud" step="any" placeholder="Ej: 4.6097">
                </div>
                <div>
                    <label for="longitud">Longitud:</label>
                    <input type="number" id="longitud" name="longitud" step="any" placeholder="Ej: -74.0817">
                </div>
            </div>
            
            <div class="map-instructions">
                <strong>💡 Instrucciones:</strong>
                <ul>
                    <li>Haz clic en "🔍 Buscar en Mapa" para encontrar la dirección automáticamente</li>
                    <li>O haz clic directamente en el mapa para seleccionar la ubicación</li>
                    <li>Puedes arrastrar el marcador rojo para ajustar la posición</li>
                </ul>
            </div>
            
            <!-- MAPA INTERACTIVO -->
            <div id="map-oficina" class="map-container form-map"></div>
        </div>

        <!-- SECCIÓN MULTIMEDIA -->
        <div class="multimedia-section">
            <h3>📸 Multimedia de la Oficina</h3>
            
            <!-- Foto Principal -->
            <div class="photo-upload">
                <label for="foto_ofi">📷 Foto Principal:</label>
                <input type="file" id="foto_ofi" name="foto_ofi" accept=".jpg,.jpeg,.png,.gif" onchange="validateFile(this, 'image', 5)">
                <div class="file-info">📌 Formatos: JPG, PNG, GIF | Tamaño máximo: 5MB</div>
            </div>
            
            <!-- Foto Secundaria -->
            <div class="photo-upload">
                <label for="foto_secundaria_ofi">📷 Foto Secundaria:</label>
                <input type="file" id="foto_secundaria_ofi" name="foto_secundaria_ofi" accept=".jpg,.jpeg,.png,.gif" onchange="validateFile(this, 'image', 5)">
                <div class="file-info">📷 Foto adicional de la oficina | Tamaño máximo: 5MB</div>
            </div>
            
            <!-- Video Local -->
            <div class="video-upload">
                <label for="video_ofi">🎬 Video Local de la Oficina:</label>
                <input type="file" id="video_ofi" name="video_ofi" accept=".mp4,.mov,.avi" onchange="validateFile(this, 'video', 50)">
                <div class="file-info">🎥 Video subido al servidor | Máximo: 50MB, 2 minutos recomendado</div>
            </div>
            
            <!-- Video Externo -->
            <div class="video-external">
                <label for="video_url_ofi">🔗 Enlace a Video Externo:</label>
                <input type="url" id="video_url_ofi" name="video_url_ofi" placeholder="https://www.youtube.com/watch?v=...">
                <div class="file-info">🔗 YouTube, Instagram, Vimeo, TikTok | Sin límites de duración o tamaño | Opcional</div>
            </div>
        </div>
        
        <!-- Enlaces Web -->
        <div class="web-links">
            <h3>🌐 Enlaces Web</h3>
            
            <div>
                <label for="web_p1_ofi">🔗 Enlace Web Página 1:</label>
                <input type="url" id="web_p1_ofi" name="web_p1_ofi" placeholder="https://www.ejemplo.com">
            </div>
            
            <div>
                <label for="web_p2_ofi">🔗 Enlace Web Página 2:</label>
                <input type="url" id="web_p2_ofi" name="web_p2_ofi" placeholder="https://www.redes-sociales.com">
            </div>
        </div>

        <div>
            <button type="submit">Guardar Oficina</button>
            <a href="lista_oficinas.php">Cancelar</a>
        </div>
    </form>

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    
    <!-- Script personalizado para mapas -->
    <script src="js/leaflet-maps.js"></script>
    
    <script>
        // Variables globales para el mapa
        let mapaOficina, marcadorOficina;
        
        // Ciudades principales de Colombia con coordenadas
        const ciudadesColombiaMapa = {
            'Bogotá': { lat: 4.7110, lng: -74.0721 },
            'Medellín': { lat: 6.2442, lng: -75.5812 },
            'Cali': { lat: 3.4516, lng: -76.5320 },
            'Barranquilla': { lat: 11.0041, lng: -74.8070 },
            'Bucaramanga': { lat: 7.8890, lng: -72.4966 },
            'Manizales': { lat: 4.5389, lng: -75.6578 },
            'Pereira': { lat: 4.8143, lng: -75.6946 },
            'Pasto': { lat: 1.2136, lng: -77.2811 },
            'Popayán': { lat: 2.4448, lng: -76.6147 },
            'Armenia': { lat: 5.0700, lng: -75.5138 },
            'Montería': { lat: 8.7500, lng: -75.8814 },
            'Sincelejo': { lat: 9.3077, lng: -75.3976 },
            'Cartagena': { lat: 10.3910, lng: -75.4794 },
            'Villavicencio': { lat: 4.2421, lng: -73.6127 },
            'Girardot': { lat: 4.3079, lng: -74.8066 },
            'Tunja': { lat: 5.5353, lng: -73.3678 },
            'Ibagué': { lat: 4.4389, lng: -75.2322 },
            'Neiva': { lat: 2.9273, lng: -75.2819 },
            'Cúcuta': { lat: 7.8939, lng: -72.5078 },
            'Valledupar': { lat: 10.4631, lng: -73.2532 },
            'Santa Marta': { lat: 11.2408, lng: -74.2099 },
            'Riohacha': { lat: 11.5444, lng: -72.9059 },
            'Yopal': { lat: 5.3478, lng: -72.3936 }
        };
        
        // Función para seleccionar ciudad rápidamente
        function selectCity(coordenadas, latField, lngField, mapId) {
            if (!coordenadas) return;
            
            const [lat, lng] = coordenadas.split(',').map(Number);
            
            // Actualizar campos
            document.getElementById(latField).value = lat;
            document.getElementById(lngField).value = lng;
            
            // Actualizar mapa
            if (mapaOficina) {
                mapaOficina.setView([lat, lng], 14);
                
                if (marcadorOficina) {
                    marcadorOficina.setLatLng([lat, lng]);
                } else {
                    marcadorOficina = L.marker([lat, lng], { draggable: true })
                        .addTo(mapaOficina)
                        .on('dragend', function() {
                            const pos = this.getLatLng();
                            document.getElementById(latField).value = pos.lat.toFixed(6);
                            document.getElementById(lngField).value = pos.lng.toFixed(6);
                        });
                }
            }
        }
        
        // Función para buscar dirección con solución alternativa
        async function searchCurrentAddress(addressField, latField, lngField, mapId) {
            const direccion = document.getElementById(addressField).value;
            if (!direccion.trim()) {
                alert('Por favor ingrese una dirección para buscar');
                return;
            }
            
            // Buscar primero en base de datos local de calles conocidas de Bogotá
            const resultado = buscarDireccionLocal(direccion);
            
            if (resultado) {
                // Encontrado en base local
                document.getElementById(latField).value = resultado.lat.toFixed(6);
                document.getElementById(lngField).value = resultado.lng.toFixed(6);
                
                if (mapaOficina) {
                    mapaOficina.setView([resultado.lat, resultado.lng], 16);
                    
                    if (marcadorOficina) {
                        mapaOficina.removeLayer(marcadorOficina);
                    }
                    
                    marcadorOficina = L.marker([resultado.lat, resultado.lng], { draggable: true })
                        .addTo(mapaOficina)
                        .bindPopup(`📍 ${resultado.nombre}`)
                        .on('dragend', function() {
                            const pos = this.getLatLng();
                            document.getElementById(latField).value = pos.lat.toFixed(6);
                            document.getElementById(lngField).value = pos.lng.toFixed(6);
                        });
                }
                
                alert(`✅ Ubicación encontrada: ${resultado.nombre}\nCoordenadas: ${resultado.lat.toFixed(6)}, ${resultado.lng.toFixed(6)}`);
                return;
            }
            
            // Si no se encuentra localmente, usar coordenadas estimadas
            const coordenadasEstimadas = estimarCoordenadas(direccion);
            
            document.getElementById(latField).value = coordenadasEstimadas.lat.toFixed(6);
            document.getElementById(lngField).value = coordenadasEstimadas.lng.toFixed(6);
            
            if (mapaOficina) {
                mapaOficina.setView([coordenadasEstimadas.lat, coordenadasEstimadas.lng], 15);
                
                if (marcadorOficina) {
                    mapaOficina.removeLayer(marcadorOficina);
                }
                
                marcadorOficina = L.marker([coordenadasEstimadas.lat, coordenadasEstimadas.lng], { draggable: true })
                    .addTo(mapaOficina)
                    .bindPopup(`📍 Ubicación aproximada: ${direccion}`)
                    .on('dragend', function() {
                        const pos = this.getLatLng();
                        document.getElementById(latField).value = pos.lat.toFixed(6);
                        document.getElementById(lngField).value = pos.lng.toFixed(6);
                    });
            }
            
            alert(`📍 Ubicación aproximada encontrada para: ${direccion}\nCoordenadas: ${coordenadasEstimadas.lat.toFixed(6)}, ${coordenadasEstimadas.lng.toFixed(6)}\n\n📝 Puede arrastrar el marcador para ajustar la posición exacta.`);
        }
        
        // Base de datos local de direcciones conocidas de Bogotá
        function buscarDireccionLocal(direccion) {
            const direccionLimpia = direccion.toLowerCase().trim();
            
            // Calles principales conocidas
            const callesConocidas = {
                'calle 100': { lat: 4.6781, lng: -74.0478, nombre: 'Calle 100 - Zona Rosa Norte' },
                'calle 93': { lat: 4.6756, lng: -74.0525, nombre: 'Calle 93 - Zona Rosa' },
                'calle 72': { lat: 4.6567, lng: -74.0606, nombre: 'Calle 72 - Zona T' },
                'carrera 7': { lat: 4.6350, lng: -74.0645, nombre: 'Carrera 7ª - Centro Histórico' },
                'carrera 15': { lat: 4.6289, lng: -74.0645, nombre: 'Carrera 15 - La Candelaria' },
                'avenida 68': { lat: 4.6486, lng: -74.1103, nombre: 'Avenida 68 - Zona Industrial' },
                'calle 26': { lat: 4.6286, lng: -74.0776, nombre: 'Calle 26 - Avenida El Dorado' },
                'autopista norte': { lat: 4.7200, lng: -74.0350, nombre: 'Autopista Norte' },
                'calle 63': { lat: 4.6519, lng: -74.0598, nombre: 'Calle 63 - Chapinero' },
                'calle 80': { lat: 4.6692, lng: -74.0567, nombre: 'Calle 80' }
            };
            
            for (const calle in callesConocidas) {
                if (direccionLimpia.includes(calle)) {
                    return callesConocidas[calle];
                }
            }
            
            return null;
        }
        
        // Estimación de coordenadas basada en patrones
        function estimarCoordenadas(direccion) {
            const direccionLimpia = direccion.toLowerCase();
            
            // Coordenadas base de Bogotá centro
            let lat = 4.6350;
            let lng = -74.0645;
            
            // Ajustes basados en números de calle (sistema de Bogotá)
            const numeroCalle = direccionLimpia.match(/calle\s*(\d+)/);
            if (numeroCalle) {
                const num = parseInt(numeroCalle[1]);
                // Calles más altas están al norte
                lat = 4.6350 + (num - 50) * 0.0008; // Aproximación
            }
            
            const numeroCarrera = direccionLimpia.match(/carrera\s*(\d+)/);
            if (numeroCarrera) {
                const num = parseInt(numeroCarrera[1]);
                // Carreras más altas están al oeste
                lng = -74.0645 - (num - 10) * 0.0015; // Aproximación
            }
            
            return { lat, lng };
        }
        
        // Función para ir a coordenadas específicas
        function searchByCoordinates(latField, lngField, mapId) {
            const lat = parseFloat(document.getElementById(latField).value);
            const lng = parseFloat(document.getElementById(lngField).value);
            
            if (isNaN(lat) || isNaN(lng)) {
                alert('❌ Por favor ingrese coordenadas válidas (latitud y longitud)');
                return;
            }
            
            if (lat < -90 || lat > 90) {
                alert('❌ Latitud debe estar entre -90 y 90 grados');
                return;
            }
            
            if (lng < -180 || lng > 180) {
                alert('❌ Longitud debe estar entre -180 y 180 grados');
                return;
            }
            
            if (mapaOficina) {
                mapaOficina.setView([lat, lng], 16);
                
                if (marcadorOficina) {
                    marcadorOficina.setLatLng([lat, lng]);
                } else {
                    marcadorOficina = L.marker([lat, lng], { draggable: true })
                        .addTo(mapaOficina)
                        .bindPopup('📍 Ubicación de la oficina')
                        .on('dragend', function() {
                            const pos = this.getLatLng();
                            document.getElementById(latField).value = pos.lat.toFixed(6);
                            document.getElementById(lngField).value = pos.lng.toFixed(6);
                        });
                }
                
                alert(`✅ Mapa centrado en: ${lat.toFixed(6)}, ${lng.toFixed(6)}`);
            }
        }
        
        // Función para validar archivos
        function validateFile(input, type, maxSizeMB) {
            const file = input.files[0];
            if (!file) return;
            
            const maxSize = maxSizeMB * 1024 * 1024;
            
            if (file.size > maxSize) {
                alert(`❌ El archivo es muy grande. Máximo permitido: ${maxSizeMB}MB`);
                input.value = '';
                return false;
            }
            
            if (type === 'image') {
                const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    alert('❌ Formato de imagen no válido. Use JPG, PNG o GIF.');
                    input.value = '';
                    return false;
                }
            } else if (type === 'video') {
                const validTypes = ['video/mp4', 'video/mov', 'video/quicktime', 'video/avi', 'video/x-msvideo'];
                if (!validTypes.includes(file.type)) {
                    alert('❌ Formato de video no válido. Use MP4, MOV o AVI.');
                    input.value = '';
                    return false;
                }
            }
            
            return true;
        }
        
        // Inicializar cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar mapa centrado en Bogotá
            mapaOficina = L.map('map-oficina').setView([4.7110, -74.0721], 11);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(mapaOficina);
            
            // Click en el mapa para agregar marcador
            mapaOficina.on('click', function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;
                
                document.getElementById('latitud').value = lat.toFixed(6);
                document.getElementById('longitud').value = lng.toFixed(6);
                
                if (marcadorOficina) {
                    marcadorOficina.setLatLng([lat, lng]);
                } else {
                    marcadorOficina = L.marker([lat, lng], { draggable: true })
                        .addTo(mapaOficina)
                        .on('dragend', function() {
                            const pos = this.getLatLng();
                            document.getElementById('latitud').value = pos.lat.toFixed(6);
                            document.getElementById('longitud').value = pos.lng.toFixed(6);
                        });
                }
            });
        });
    </script>
</body>
</html>