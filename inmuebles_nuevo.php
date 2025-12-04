<?php
// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

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

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Nuevo Inmueble</title>
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
            <input type="text" id="ciudad_inm" name="ciudad_inm" placeholder="Ej: Bogotá, Medellín, Cali">
        </div>

        <div>
            <label for="pais_inm">País:</label>
            <input type="text" id="pais_inm" name="pais_inm" value="Colombia" placeholder="Colombia">
        </div>

        <!-- SECCIÓN DE UBICACIÓN CON MAPA -->
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
            
            <div class="map-instructions">
                <strong>💡 Instrucciones:</strong>
                <ul>
                    <li><strong>Para buscar dirección:</strong> Llena los campos arriba y haz clic en "Buscar Dirección"</li>
                    <li><strong>Para centrar en ciudad:</strong> Solo llena la ciudad y usa "Centrar en Ciudad"</li>
                    <li><strong>Para seleccionar manual:</strong> Haz clic directamente en el mapa</li>
                </ul>
                
                <div style="margin-top: 10px;">
                    <button type="button" id="search-full-address" class="btn-search-address">
                        🔍 Buscar Dirección
                    </button>
                    <button type="button" id="search-city-only" class="btn-search-address" style="background-color: #17a2b8 !important;">
                        🏙️ Centrar en Ciudad
                    </button>
                </div>
            </div>
            
            <!-- MAPA -->
            <div id="map-inmueble" class="map-container form-map"></div>
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

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    
    <!-- Script simple para mapas -->
    <script src="js/leaflet-simple.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Inicializando página de inmuebles...');
            
            // Verificar si Leaflet está cargado
            if (typeof L === 'undefined') {
                console.error('Leaflet no está cargado');
                document.getElementById('map-inmueble').innerHTML = 
                    '<div style="padding: 20px; text-align: center; color: red;">Error: No se pudo cargar Leaflet</div>';
                return;
            }
            
            // Inicializar mapa
            try {
                initMap('map-inmueble', 'latitude', 'longitud');
                console.log('Mapa inicializado exitosamente');
            } catch (error) {
                console.error('Error inicializando mapa:', error);
                document.getElementById('map-inmueble').innerHTML = 
                    '<div style="padding: 20px; text-align: center; color: red;">Error inicializando mapa: ' + error.message + '</div>';
            }
            
            // Configurar botones
            setupButtons();
        });
        
        function setupButtons() {
            // Botón buscar dirección
            document.getElementById('search-full-address').addEventListener('click', function() {
                const direccion = document.getElementById('dir_inm').value.trim();
                const ciudad = document.getElementById('ciudad_inm').value.trim();
                
                if (!direccion && !ciudad) {
                    alert('Por favor ingrese al menos una dirección o ciudad');
                    return;
                }
                
                let searchQuery = '';
                if (direccion) searchQuery += direccion;
                if (ciudad) searchQuery += (searchQuery ? ', ' : '') + ciudad;
                searchQuery += ', Colombia';
                
                this.innerHTML = '🔄 Buscando...';
                this.disabled = true;
                
                const button = this;
                searchAddress(searchQuery, function(result, error) {
                    button.innerHTML = '🔍 Buscar Dirección';
                    button.disabled = false;
                    
                    if (error) {
                        alert('❌ ' + error);
                    } else {
                        currentMap.setView([result.lat, result.lng], 16);
                        placeMarker(result.lat, result.lng, 'latitude', 'longitud');
                        alert('✅ Encontrado: ' + result.display_name);
                    }
                });
            });
            
            // Botón centrar en ciudad
            document.getElementById('search-city-only').addEventListener('click', function() {
                const ciudad = document.getElementById('ciudad_inm').value.trim();
                
                if (!ciudad) {
                    alert('Por favor ingrese la ciudad');
                    return;
                }
                
                this.innerHTML = '🔄 Centrando...';
                this.disabled = true;
                
                const button = this;
                setTimeout(function() {
                    const success = centerOnCity(ciudad);
                    button.innerHTML = '🏙️ Centrar en Ciudad';
                    button.disabled = false;
                    
                    if (success) {
                        // Limpiar coordenadas para selección manual
                        document.getElementById('latitude').value = '';
                        document.getElementById('longitud').value = '';
                    }
                }, 500);
            });
        }
    </script>
</body>
</html>