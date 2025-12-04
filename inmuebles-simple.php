<?php
require_once 'conexion.php';

$sql_tipos = "SELECT cod_tipoinm, nom_tipoinm FROM tipo_inmueble";
$resultado_tipos = $conn->query($sql_tipos);
$tipos_inmueble = [];
if ($resultado_tipos->num_rows > 0) {
    while ($fila = $resultado_tipos->fetch_assoc()) {
        $tipos_inmueble[$fila['cod_tipoinm']] = $fila['nom_tipoinm'];
    }
}

$sql_propietarios = "SELECT cod_prop, nom_prop FROM propietarios";
$resultado_propietarios = $conn->query($sql_propietarios);
$propietarios = [];
if ($resultado_propietarios->num_rows > 0) {
    while ($fila = $resultado_propietarios->fetch_assoc()) {
        $propietarios[$fila['cod_prop']] = $fila['nom_prop'];
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Nuevo Inmueble</title>
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
    <h1>Agregar Nuevo Inmueble</h1>

    <form action="guardar_inmueble.php" method="post" enctype="multipart/form-data">
        <div>
            <label for="dir_inm">Dirección:</label>
            <input type="text" id="dir_inm" name="dir_inm" placeholder="Ej: Calle 26 #13-19">
        </div>

        <div>
            <label for="barrio_inm">Barrio:</label>
            <input type="text" id="barrio_inm" name="barrio_inm" placeholder="Ej: Chapinero">
        </div>

        <div>
            <label for="ciudad_inm">Ciudad:</label>
            <input type="text" id="ciudad_inm" name="ciudad_inm" placeholder="Ej: Bogotá">
        </div>

        <div>
            <label for="pais_inm">País:</label>
            <input type="text" id="pais_inm" name="pais_inm" value="Colombia">
        </div>

        <div class="coordinates-section">
            <h3>📍 Ubicación en Mapa</h3>
            
            <div class="coordinates-row">
                <div>
                    <label for="latitude">Latitud:</label>
                    <input type="number" id="latitude" name="latitude" step="any" placeholder="4.6097">
                </div>
                <div>
                    <label for="longitud">Longitud:</label>
                    <input type="number" id="longitud" name="longitud" step="any" placeholder="-74.0817">
                </div>
            </div>
            
            <div style="margin-bottom: 10px;">
                <button type="button" id="search-full-address" class="btn-search-address">
                    🔍 Buscar Dirección
                </button>
                <button type="button" id="search-city-only" class="btn-search-address" style="background-color: #17a2b8;">
                    🏙️ Centrar en Ciudad
                </button>
            </div>
            
            <div id="map-inmueble"></div>
            
            <small style="color: #666;">
                💡 Haz clic en el mapa para seleccionar la ubicación exacta del inmueble.
            </small>
        </div>

        <div>
            <label for="foto">Foto:</label>
            <input type="file" id="foto" name="foto">
        </div>

        <div>
            <label for="web_p1">Enlace Web Página 1:</label>
            <input type="url" id="web_p1" name="web_p1">
        </div>

        <div>
            <label for="web_p2">Enlace Web Página 2:</label>
            <input type="url" id="web_p2" name="web_p2">
        </div>

        <div>
            <label for="cod_tipoinm">Tipo de Inmueble:</label>
            <select id="cod_tipoinm" name="cod_tipoinm">
                <option value="">Seleccionar Tipo</option>
                <?php foreach ($tipos_inmueble as $cod => $nombre): ?>
                    <option value="<?php echo $cod; ?>"><?php echo $nombre; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="num_hab">Número de Habitaciones:</label>
            <input type="number" id="num_hab" name="num_hab">
        </div>

        <div>
            <label for="precio_alq">Precio de Alquiler:</label>
            <input type="number" step="0.01" id="precio_alq" name="precio_alq">
        </div>

        <div>
            <label for="cod_prop">Propietario:</label>
            <select id="cod_prop" name="cod_prop">
                <option value="">Seleccionar Propietario</option>
                <?php foreach ($propietarios as $cod => $nombre): ?>
                    <option value="<?php echo $cod; ?>"><?php echo $nombre; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="caract_inm">Característica:</label>
            <select id="caract_inm" name="caract_inm">
                <option value="">Seleccionar Característica</option>
                <option value="Conjunto">Conjunto</option>
                <option value="Urb">Urb</option>
            </select>
        </div>

        <div>
            <label for="notas_inm">Notas Adicionales:</label>
            <textarea id="notas_inm" name="notas_inm"></textarea>
        </div>

        <div>
            <button type="submit">Guardar Inmueble</button>
            <a href="lista_inmuebles.php">Cancelar</a>
        </div>
    </form>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map;
        let marker;
        
        // Ciudades colombianas
        const cities = {
            'bogotá': [4.6097, -74.0817], 'bogota': [4.6097, -74.0817],
            'medellín': [6.2442, -75.5812], 'medellin': [6.2442, -75.5812],
            'cali': [3.4516, -76.5320], 'cartagena': [10.3910, -75.4794],
            'villavicencio': [4.1420, -73.6266], 'girardot': [4.3017, -74.8022],
            'sincelejo': [9.3047, -75.3978], 'bucaramanga': [7.1254, -73.1198],
            'pereira': [4.8133, -75.6961], 'neiva': [2.9273, -75.2819],
            'ibagué': [4.4389, -75.2322], 'ibague': [4.4389, -75.2322]
        };
        
        document.addEventListener('DOMContentLoaded', function() {
            try {
                // Crear mapa
                map = L.map('map-inmueble').setView([4.6097, -74.0817], 6);
                
                // Agregar tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);
                
                // Click en mapa
                map.on('click', function(e) {
                    placeMarker(e.latlng.lat, e.latlng.lng);
                });
                
                setupButtons();
                
            } catch (error) {
                document.getElementById('map-inmueble').innerHTML = 
                    '<div style="padding:20px;text-align:center;color:red;">Error: ' + error.message + '</div>';
            }
        });
        
        function placeMarker(lat, lng) {
            if (marker) marker.remove();
            
            marker = L.marker([lat, lng], {draggable: true}).addTo(map)
                .bindPopup('Ubicación seleccionada').openPopup();
            
            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitud').value = lng.toFixed(6);
            
            marker.on('dragend', function(e) {
                const pos = e.target.getLatLng();
                document.getElementById('latitude').value = pos.lat.toFixed(6);
                document.getElementById('longitud').value = pos.lng.toFixed(6);
            });
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
                query += ', Colombia';
                
                this.innerHTML = '🔄 Buscando...';
                this.disabled = true;
                
                const button = this;
                
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=co&limit=1`)
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
                            alert('❌ No se encontró la dirección. Prueba con "Centrar en Ciudad" y selecciona manualmente.');
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
                        
                        // Limpiar marcador y coordenadas
                        if (marker) marker.remove();
                        document.getElementById('latitude').value = '';
                        document.getElementById('longitud').value = '';
                        
                        alert(`✅ Mapa centrado en ${ciudad}.\n\n💡 Ahora haz clic en el mapa para seleccionar la ubicación exacta del inmueble.`);
                    } else {
                        alert(`❌ Ciudad "${ciudad}" no encontrada.\n\n🏙️ Ciudades disponibles: bogotá, medellín, cali, cartagena, villavicencio, girardot, sincelejo, bucaramanga, pereira, neiva, ibagué`);
                    }
                }, 300);
            };
        }
    </script>
</body>
</html>