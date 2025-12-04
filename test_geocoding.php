<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Geocodificación - Colombia</title>
    <link rel="stylesheet" href="css/leaflet-maps.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
        .test-section { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 8px; }
        .test-input { width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; }
        .test-button { background: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .test-results { background: white; padding: 15px; margin: 10px 0; border-radius: 4px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>🧪 Prueba de Sistema de Mapas - Colombia</h1>
    
    <div class="test-section">
        <h3>🔍 Pruebas de Geocodificación</h3>
        
        <h4>Direcciones de Prueba Sugeridas:</h4>
        <ul>
            <li><strong>Bogotá:</strong> Carrera 7 # 32-16, Bogotá, Colombia</li>
            <li><strong>Cartagena:</strong> Carrera 20 # 40-10, Cartagena, Colombia</li>
            <li><strong>Villavicencio:</strong> Calle 37 # 28-15, Villavicencio, Colombia</li>
            <li><strong>Cali:</strong> Avenida 6N # 15-25, Cali, Colombia</li>
            <li><strong>Solo ciudad:</strong> Medellín, Colombia</li>
        </ul>
        
        <input type="text" id="test-address" class="test-input" placeholder="Ingresa dirección para probar...">
        <br>
        <button class="test-button" onclick="testSearch()">🔍 Probar Búsqueda</button>
        <button class="test-button" onclick="clearResults()">🧹 Limpiar</button>
        
        <div id="test-results" class="test-results" style="display: none;">
            <h4>Resultados:</h4>
            <div id="results-content"></div>
        </div>
    </div>
    
    <div class="test-section">
        <h3>🗺️ Mapa de Prueba</h3>
        <div id="test-map" class="map-container" style="height: 400px;"></div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    <script src="js/leaflet-maps.js"></script>
    
    <script>
        let testMap = null;
        let testMarker = null;
        
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar mapa de prueba
            testMap = L.map('test-map').setView([4.6097, -74.0817], 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(testMap);
        });
        
        function testSearch() {
            const address = document.getElementById('test-address').value;
            if (!address) {
                alert('Por favor ingresa una dirección');
                return;
            }
            
            console.log('=== INICIANDO PRUEBA DE BÚSQUEDA ===');
            console.log('Dirección:', address);
            
            document.getElementById('test-results').style.display = 'block';
            document.getElementById('results-content').innerHTML = '🔄 Buscando...';
            
            searchAddress(address, function(result, error) {
                let resultsHtml = '';
                
                if (error) {
                    resultsHtml = `
                        <div style="color: red;">
                            <strong>❌ Error:</strong> ${error}
                        </div>
                    `;
                } else if (result) {
                    resultsHtml = `
                        <div style="color: green;">
                            <strong>✅ Éxito!</strong><br>
                            <strong>Coordenadas:</strong> ${result.lat}, ${result.lng}<br>
                            <strong>Nombre:</strong> ${result.display_name}<br>
                            ${result.strategy ? '<strong>Estrategia:</strong> ' + result.strategy + '<br>' : ''}
                            ${result.source ? '<strong>Fuente:</strong> ' + result.source + '<br>' : ''}
                            ${result.address_type ? '<strong>Tipo:</strong> ' + result.address_type + '<br>' : ''}
                        </div>
                    `;
                    
                    // Actualizar mapa
                    testMap.setView([result.lat, result.lng], result.zoom || 14);
                    
                    if (testMarker) {
                        testMarker.remove();
                    }
                    
                    testMarker = L.marker([result.lat, result.lng])
                        .addTo(testMap)
                        .bindPopup(result.display_name)
                        .openPopup();
                }
                
                document.getElementById('results-content').innerHTML = resultsHtml;
                console.log('=== FIN PRUEBA ===');
            });
        }
        
        function clearResults() {
            document.getElementById('test-results').style.display = 'none';
            document.getElementById('test-address').value = '';
            
            if (testMarker) {
                testMarker.remove();
                testMarker = null;
            }
            
            testMap.setView([4.6097, -74.0817], 6);
        }
    </script>
</body>
</html>