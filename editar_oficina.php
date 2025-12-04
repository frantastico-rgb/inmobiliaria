<?php
// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

// Verificar si se recibió el ID de la oficina a editar
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $oficina_id = $_GET['id'];

    // Consulta para obtener la información completa de la oficina (incluyendo nuevos campos)
    $sql_oficina = "SELECT * FROM oficina WHERE Id_ofi = ?";
    $stmt_oficina = $conn->prepare($sql_oficina);
    $stmt_oficina->bind_param("i", $oficina_id);
    $stmt_oficina->execute();
    $resultado_oficina = $stmt_oficina->get_result();

    if ($resultado_oficina->num_rows == 1) {
        $oficina = $resultado_oficina->fetch_assoc();
    } else {
        // Si el ID no es válido o no se encuentra la oficina, redirigir con un mensaje de error
        session_start();
        $_SESSION['mensaje'] = "Oficina no encontrada.";
        header("Location: lista_oficinas.php");
        exit();
    }

} else {
    // Si no se recibió un ID válido, redirigir con un mensaje de error
    session_start();
    $_SESSION['mensaje'] = "ID de oficina inválido.";
    header("Location: lista_oficinas.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Oficina - <?php echo htmlspecialchars($oficina['nom_ofi']); ?></title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="css/leaflet-maps.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
</head>
<body>
    <div class="logo-icono">
        <img src="uploads/logo_casa_meta.png" alt="logo">
    </div>
    <hr>
    <h1>✏️ Editar Oficina: <?php echo htmlspecialchars($oficina['nom_ofi']); ?></h1>

    <form action="guardar_cambios_oficina.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="Id_ofi" value="<?php echo $oficina['Id_ofi']; ?>">

        <!-- INFORMACIÓN BÁSICA -->
        <div class="section">
            <h3>🏢 Información Básica</h3>
            
            <div>
                <label for="nom_ofi">Nombre de la Oficina:</label>
                <input type="text" id="nom_ofi" name="nom_ofi" value="<?php echo htmlspecialchars($oficina['nom_ofi']); ?>" required>
            </div>

            <div>
                <label for="dir_ofi">Dirección:</label>
                <input type="text" id="dir_ofi" name="dir_ofi" value="<?php echo htmlspecialchars($oficina['dir_ofi']); ?>" required>
            </div>

            <div>
                <label for="tel_ofi">Teléfono:</label>
                <input type="tel" id="tel_ofi" name="tel_ofi" value="<?php echo htmlspecialchars($oficina['tel_ofi']); ?>">
            </div>

            <div>
                <label for="email_ofi">Email:</label>
                <input type="email" id="email_ofi" name="email_ofi" value="<?php echo htmlspecialchars($oficina['email_ofi']); ?>">
            </div>
        </div>

        <!-- SECCIÓN DE UBICACIÓN CON MAPA -->
        <div class="coordinates-section">
            <h3>📍 Ubicación de la Oficina</h3>
            
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
                </select>
                <button type="button" onclick="searchCurrentAddress('dir_ofi', 'latitud', 'longitud', 'map-oficina')">🔍 Buscar Dirección</button>
                <button type="button" onclick="searchByCoordinates('latitud', 'longitud', 'map-oficina')">🎯 Ir a Coordenadas</button>
            </div>
            
            <div class="coordinates-row">
                <div>
                    <label for="ciudad_ofi">Ciudad:</label>
                    <input type="text" id="ciudad_ofi" name="ciudad_ofi" 
                           value="<?php echo htmlspecialchars($oficina['ciudad_ofi'] ?? ''); ?>" 
                           placeholder="Ej: Bogotá" required>
                </div>
                <div>
                    <label for="pais_ofi">País:</label>
                    <input type="text" id="pais_ofi" name="pais_ofi" 
                           value="<?php echo htmlspecialchars($oficina['pais_ofi'] ?? 'Colombia'); ?>" 
                           placeholder="Ej: Colombia">
                </div>
            </div>
            
            <div class="coordinates-row">
                <div>
                    <label for="latitud">Latitud:</label>
                    <input type="number" id="latitud" name="latitud" step="any" 
                           value="<?php echo $oficina['latitud']; ?>" placeholder="Ej: 4.6097">
                </div>
                <div>
                    <label for="longitud">Longitud:</label>
                    <input type="number" id="longitud" name="longitud" step="any" 
                           value="<?php echo $oficina['longitud']; ?>" placeholder="Ej: -74.0817">
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
                
                <?php if (!empty($oficina['foto_ofi']) && file_exists($oficina['foto_ofi'])): ?>
                    <div class="current-media">
                        📸 <strong>Foto actual:</strong><br>
                        <img src="<?php echo $oficina['foto_ofi']; ?>" alt="Foto Principal" style="max-width: 200px; margin-top: 10px; border-radius: 8px;">
                    </div>
                    <input type="hidden" name="foto_actual" value="<?php echo $oficina['foto_ofi']; ?>">
                <?php endif; ?>
            </div>
            
            <!-- Foto Secundaria -->
            <div class="photo-upload">
                <label for="foto_secundaria_ofi">📷 Foto Secundaria:</label>
                <input type="file" id="foto_secundaria_ofi" name="foto_secundaria_ofi" accept=".jpg,.jpeg,.png,.gif" onchange="validateFile(this, 'image', 5)">
                <div class="file-info">📷 Foto adicional de la oficina | Tamaño máximo: 5MB</div>
                
                <?php if (!empty($oficina['foto_secundaria_ofi']) && file_exists($oficina['foto_secundaria_ofi'])): ?>
                    <div class="current-media">
                        📸 <strong>Foto secundaria actual:</strong><br>
                        <img src="<?php echo $oficina['foto_secundaria_ofi']; ?>" alt="Foto Secundaria" style="max-width: 200px; margin-top: 10px; border-radius: 8px;">
                    </div>
                    <input type="hidden" name="foto_secundaria_actual" value="<?php echo $oficina['foto_secundaria_ofi']; ?>">
                <?php endif; ?>
            </div>
            
            <!-- Video Local -->
            <div class="video-upload">
                <label for="video_ofi">🎬 Video Local de la Oficina:</label>
                <input type="file" id="video_ofi" name="video_ofi" accept=".mp4,.mov,.avi" onchange="validateFile(this, 'video', 50)">
                <div class="file-info">🎥 Video subido al servidor | Máximo: 50MB, 2 minutos recomendado</div>
                
                <?php if (!empty($oficina['video_ofi']) && file_exists($oficina['video_ofi'])): ?>
                    <div class="current-media">
                        🎥 <strong>Video local actual:</strong><br>
                        <video controls style="max-width: 300px; margin-top: 10px;">
                            <source src="<?php echo $oficina['video_ofi']; ?>" type="video/mp4">
                            Tu navegador no soporta el elemento video.
                        </video>
                    </div>
                    <input type="hidden" name="video_actual" value="<?php echo $oficina['video_ofi']; ?>">
                <?php endif; ?>
            </div>
            
            <!-- Video Externo -->
            <div class="video-external">
                <label for="video_url_ofi">🔗 Enlace a Video Externo:</label>
                <input type="url" id="video_url_ofi" name="video_url_ofi" 
                       value="<?php echo htmlspecialchars($oficina['video_url_ofi'] ?? ''); ?>" 
                       placeholder="https://www.youtube.com/watch?v=...">
                <div class="file-info">🔗 YouTube, Instagram, Vimeo, TikTok | Sin límites de duración o tamaño | Opcional</div>
            </div>
        </div>
        
        <!-- Enlaces Web -->
        <div class="web-links">
            <h3>🌐 Enlaces Web</h3>
            
            <div>
                <label for="web_p1_ofi">🔗 Enlace Web Página 1:</label>
                <input type="url" id="web_p1_ofi" name="web_p1_ofi" 
                       value="<?php echo htmlspecialchars($oficina['web_p1_ofi'] ?? ''); ?>" 
                       placeholder="https://www.ejemplo.com">
            </div>
            
            <div>
                <label for="web_p2_ofi">🔗 Enlace Web Página 2:</label>
                <input type="url" id="web_p2_ofi" name="web_p2_ofi" 
                       value="<?php echo htmlspecialchars($oficina['web_p2_ofi'] ?? ''); ?>" 
                       placeholder="https://www.redes-sociales.com">
            </div>
        </div>

        <div class="form-buttons">
            <button type="submit">💾 Guardar Cambios</button>
            <a href="lista_oficinas.php" class="btn-cancel">❌ Cancelar</a>
        </div>
    </form>

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    
    <script>
        // Variables globales para el mapa
        let mapaOficina, marcadorOficina;
        
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
        
        // Función para buscar dirección
        async function searchCurrentAddress(addressField, latField, lngField, mapId) {
            const direccion = document.getElementById(addressField).value;
            if (!direccion.trim()) {
                alert('Por favor ingrese una dirección para buscar');
                return;
            }
            
            // Mostrar mensaje de carga
            alert('🔍 Buscando dirección... Por favor espere.');
            
            // Construir consulta simple pero efectiva
            let consultaCompleta = direccion + ', Bogotá, Colombia';
            
            console.log('Buscando dirección:', consultaCompleta);
            
            try {
                // URL más simple y confiable
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(consultaCompleta)}&limit=1&countrycodes=co`;
                console.log('URL de búsqueda:', url);
                
                const response = await fetch(url);
                
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Resultados de búsqueda:', data);
                
                if (data && data.length > 0) {
                    const resultado = data[0];
                    const lat = parseFloat(resultado.lat);
                    const lng = parseFloat(resultado.lon);
                    
                    if (!isNaN(lat) && !isNaN(lng)) {
                        // Actualizar campos de coordenadas
                        document.getElementById(latField).value = lat.toFixed(6);
                        document.getElementById(lngField).value = lng.toFixed(6);
                        
                        // Actualizar mapa
                        if (mapaOficina) {
                            mapaOficina.setView([lat, lng], 16);
                            
                            // Eliminar marcador anterior si existe
                            if (marcadorOficina) {
                                mapaOficina.removeLayer(marcadorOficina);
                            }
                            
                            // Crear nuevo marcador
                            marcadorOficina = L.marker([lat, lng], { draggable: true })
                                .addTo(mapaOficina)
                                .bindPopup(`📍 ${resultado.display_name || 'Ubicación encontrada'}`)
                                .on('dragend', function() {
                                    const pos = this.getLatLng();
                                    document.getElementById(latField).value = pos.lat.toFixed(6);
                                    document.getElementById(lngField).value = pos.lng.toFixed(6);
                                });
                        }
                        
                        alert(`✅ Ubicación encontrada exitosamente!\nCoordenadas: ${lat.toFixed(6)}, ${lng.toFixed(6)}`);
                    } else {
                        throw new Error('Coordenadas inválidas recibidas');
                    }
                } else {
                    alert('❌ No se encontró la dirección.\n\nSugerencias:\n• Usar formato: "Calle 100 17A"\n• Incluir número de casa/oficina\n• Verificar ortografía\n• Probar con dirección más específica');
                }
            } catch (error) {
                console.error('Error en búsqueda:', error);
                alert(`❌ Error al buscar la dirección:\n${error.message}\n\n• Verifique su conexión a internet\n• Intente nuevamente en unos segundos`);
            }
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
            // Obtener coordenadas actuales
            const latActual = <?php echo $oficina['latitud'] ?: '4.7110'; ?>;
            const lngActual = <?php echo $oficina['longitud'] ?: '-74.0721'; ?>;
            
            // Inicializar mapa centrado en la ubicación actual o Bogotá por defecto
            mapaOficina = L.map('map-oficina').setView([latActual, lngActual], latActual && lngActual ? 16 : 11);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(mapaOficina);
            
            // Agregar marcador si hay coordenadas
            if (latActual && lngActual) {
                marcadorOficina = L.marker([latActual, lngActual], { draggable: true })
                    .addTo(mapaOficina)
                    .bindPopup('📍 <?php echo htmlspecialchars($oficina['nom_ofi']); ?>')
                    .on('dragend', function() {
                        const pos = this.getLatLng();
                        document.getElementById('latitud').value = pos.lat.toFixed(6);
                        document.getElementById('longitud').value = pos.lng.toFixed(6);
                    });
            }
            
            // Click en el mapa para agregar/mover marcador
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