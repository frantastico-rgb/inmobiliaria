<?php
// Incluye el archivo de conexión a la base de datos
require_once 'conexion.php';

// Inicia la sesión para poder acceder a las variables de sesión
session_start();

// Verifica si hay un mensaje de éxito en la sesión
if (isset($_SESSION['mensaje'])) {
    echo "<p style='color: green;'>" . $_SESSION['mensaje'] . "</p>";
    unset($_SESSION['mensaje']); // Elimina el mensaje de la sesión
}

// Realiza la consulta para obtener todos los inmuebles y sus tipos y propietarios
$sql = "SELECT
            i.cod_inm,
            i.dir_inm,
            i.barrio_inm,
            i.ciudad_inm,
            i.precio_alq,
            i.latitude,
            i.longitud,
            t.nom_tipoinm,
            p.nom_prop
        FROM inmuebles i
        INNER JOIN tipo_inmueble t ON i.cod_tipoinm = t.cod_tipoinm
        INNER JOIN propietarios p ON i.cod_prop = p.cod_prop";
$resultado = $conn->query($sql);

// Preparar datos para el mapa
$inmuebles_mapa = [];
if ($resultado->num_rows > 0) {
    $resultado_mapa = $conn->query($sql); // Nueva consulta para el mapa
    while ($fila_mapa = $resultado_mapa->fetch_assoc()) {
        if ($fila_mapa['latitude'] && $fila_mapa['longitud']) {
            $inmuebles_mapa[] = [
                'lat' => floatval($fila_mapa['latitude']),
                'lng' => floatval($fila_mapa['longitud']),
                'id' => $fila_mapa['cod_inm'],
                'direccion' => $fila_mapa['dir_inm'],
                'barrio' => $fila_mapa['barrio_inm'],
                'ciudad' => $fila_mapa['ciudad_inm'],
                'precio' => number_format($fila_mapa['precio_alq'], 2),
                'tipo' => $fila_mapa['nom_tipoinm'],
                'propietario' => $fila_mapa['nom_prop']
            ];
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Inmuebles</title>
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
    <h1>Lista de Inmuebles</h1>

    <!-- Botón para toggle entre lista y mapa -->
    <button id="toggle-map" class="btn-map-toggle">🗺️ Ver en Mapa</button>

    <!-- MAPA DE INMUEBLES (inicialmente oculto) -->
    <div id="map-container-inmuebles" class="map-container list-map hidden"></div>

    <!-- TABLA DE INMUEBLES -->
    <div id="table-container-inmuebles">
        <?php if ($resultado->num_rows > 0): ?>
            <table border='1'>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Dirección</th>
                        <th>Barrio</th>
                        <th>Ciudad</th>
                        <th>Precio Alquiler</th>
                        <th>Tipo de Inmueble</th>
                        <th>Propietario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($fila = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $fila['cod_inm']; ?></td>
                            <td><?php echo $fila['dir_inm']; ?></td>
                            <td><?php echo $fila['barrio_inm']; ?></td>
                            <td><?php echo $fila['ciudad_inm']; ?></td>
                            <td><?php echo number_format($fila['precio_alq'], 2); ?></td>
                            <td><?php echo $fila['nom_tipoinm']; ?></td>
                            <td><?php echo $fila['nom_prop']; ?></td>
                            <td>
                                <a href='editar_inmueble.php?id=<?php echo $fila['cod_inm']; ?>'>Editar</a> |
                                <a href='eliminar_inmueble.php?id=<?php echo $fila['cod_inm']; ?>' onclick="return confirm('¿Está seguro que desea eliminar este inmueble?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay inmuebles registrados.</p>
        <?php endif; ?>
    </div>

    <p><a href="inmuebles.php">Agregar Nuevo Inmueble</a></p>

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    
    <!-- Script personalizado para mapas -->
    <script src="js/leaflet-maps.js"></script>
    
    <script>
        // Datos de inmuebles para el mapa
        const inmueblesData = <?php echo json_encode($inmuebles_mapa); ?>;
        
        // Template para popups de inmuebles
        const inmueblePopupTemplate = `
            <div class="popup-inmueble">
                <h4>🏠 Inmueble #{id}</h4>
                <p class="direccion"><strong>📍 Dirección:</strong> {direccion}</p>
                <p><strong>🏘️ Barrio:</strong> {barrio}</p>
                <p><strong>🏙️ Ciudad:</strong> {ciudad}</p>
                <p class="precio"><strong>💰 Precio:</strong> ${precio}</p>
                <p><strong>🏗️ Tipo:</strong> {tipo}</p>
                <p><strong>👤 Propietario:</strong> {propietario}</p>
                <p style="margin-top: 10px;">
                    <a href="editar_inmueble.php?id={id}" style="color: #007bff;">✏️ Editar</a>
                </p>
            </div>
        `;
        
        let mapaInmuebles = null;
        
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar toggle del mapa
            const toggleButton = document.getElementById('toggle-map');
            const tableContainer = document.getElementById('table-container-inmuebles');
            const mapContainer = document.getElementById('map-container-inmuebles');
            
            let showingMap = false;
            
            toggleButton.addEventListener('click', function() {
                if (!showingMap) {
                    // Mostrar mapa
                    tableContainer.style.display = 'none';
                    mapContainer.classList.remove('hidden');
                    toggleButton.innerHTML = '📋 Ver Lista';
                    showingMap = true;
                    
                    // Inicializar mapa si no existe
                    if (!mapaInmuebles && inmueblesData.length > 0) {
                        mapaInmuebles = initListMap('map-container-inmuebles', inmueblesData, inmueblePopupTemplate);
                    }
                    
                    // Refrescar mapa
                    setTimeout(function() {
                        if (mapaInmuebles) {
                            mapaInmuebles.invalidateSize();
                        }
                    }, 100);
                    
                } else {
                    // Mostrar tabla
                    tableContainer.style.display = 'block';
                    mapContainer.classList.add('hidden');
                    toggleButton.innerHTML = '🗺️ Ver en Mapa';
                    showingMap = false;
                }
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>