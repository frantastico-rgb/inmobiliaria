/**
 * SISTEMA DE MAPAS SIMPLE - LEAFLET + OPENSTREETMAP
 * Versión simplificada y funcional
 */

// Variables globales
let currentMap = null;
let currentMarker = null;

// Ciudades colombianas principales
const COLOMBIA_CITIES = {
    'bogotá': { lat: 4.6097, lng: -74.0817 },
    'bogota': { lat: 4.6097, lng: -74.0817 },
    'medellín': { lat: 6.2442, lng: -75.5812 },
    'medellin': { lat: 6.2442, lng: -75.5812 },
    'cali': { lat: 3.4516, lng: -76.5320 },
    'barranquilla': { lat: 10.9639, lng: -74.7964 },
    'cartagena': { lat: 10.3910, lng: -75.4794 },
    'villavicencio': { lat: 4.1420, lng: -73.6266 },
    'girardot': { lat: 4.3017, lng: -74.8022 },
    'sincelejo': { lat: 9.3047, lng: -75.3978 },
    'bucaramanga': { lat: 7.1254, lng: -73.1198 },
    'pereira': { lat: 4.8133, lng: -75.6961 },
    'neiva': { lat: 2.9273, lng: -75.2819 },
    'ibagué': { lat: 4.4389, lng: -75.2322 },
    'ibague': { lat: 4.4389, lng: -75.2322 }
};

/**
 * Inicializar mapa básico
 */
function initMap(containerId, latInputId, lngInputId) {
    try {
        console.log('Iniciando mapa en:', containerId);
        
        // Limpiar mapa existente
        if (currentMap) {
            currentMap.remove();
            currentMap = null;
        }
        
        // Crear mapa centrado en Colombia
        currentMap = L.map(containerId).setView([4.6097, -74.0817], 6);
        
        // Agregar tiles de OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(currentMap);
        
        // Evento click en mapa
        currentMap.on('click', function(e) {
            placeMarker(e.latlng.lat, e.latlng.lng, latInputId, lngInputId);
        });
        
        console.log('Mapa inicializado correctamente');
        return currentMap;
        
    } catch (error) {
        console.error('Error inicializando mapa:', error);
        document.getElementById(containerId).innerHTML = 
            '<div style="padding: 20px; text-align: center; color: red;">Error cargando el mapa. Verifica tu conexión a internet.</div>';
    }
}

/**
 * Colocar marcador en coordenadas
 */
function placeMarker(lat, lng, latInputId, lngInputId) {
    try {
        // Remover marcador anterior
        if (currentMarker) {
            currentMarker.remove();
        }
        
        // Crear nuevo marcador
        currentMarker = L.marker([lat, lng], {draggable: true})
            .addTo(currentMap)
            .bindPopup('Ubicación seleccionada')
            .openPopup();
        
        // Actualizar inputs
        document.getElementById(latInputId).value = lat.toFixed(6);
        document.getElementById(lngInputId).value = lng.toFixed(6);
        
        // Evento arrastrar marcador
        currentMarker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            document.getElementById(latInputId).value = pos.lat.toFixed(6);
            document.getElementById(lngInputId).value = pos.lng.toFixed(6);
        });
        
    } catch (error) {
        console.error('Error colocando marcador:', error);
    }
}

/**
 * Buscar ciudad en base local
 */
function searchCity(cityName) {
    const city = cityName.toLowerCase().trim();
    
    for (const [name, coords] of Object.entries(COLOMBIA_CITIES)) {
        if (city.includes(name) || name.includes(city)) {
            return coords;
        }
    }
    
    return null;
}

/**
 * Centrar mapa en ciudad
 */
function centerOnCity(cityName) {
    try {
        const coords = searchCity(cityName);
        
        if (coords) {
            currentMap.setView([coords.lat, coords.lng], 12);
            alert(`Mapa centrado en ${cityName}. Ahora haz clic para seleccionar la ubicación exacta.`);
            return true;
        } else {
            alert(`No se encontró la ciudad "${cityName}". Ciudades disponibles incluyen: Bogotá, Medellín, Cali, Cartagena, etc.`);
            return false;
        }
    } catch (error) {
        console.error('Error centrando en ciudad:', error);
        return false;
    }
}

/**
 * Buscar dirección con Nominatim
 */
function searchAddress(address, callback) {
    try {
        // Primero buscar en ciudades locales
        const cityCoords = searchCity(address);
        if (cityCoords) {
            callback({
                lat: cityCoords.lat,
                lng: cityCoords.lng,
                display_name: `${address}, Colombia`
            }, null);
            return;
        }
        
        // Buscar con Nominatim
        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address + ', Colombia')}&limit=1`;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    const result = data[0];
                    callback({
                        lat: parseFloat(result.lat),
                        lng: parseFloat(result.lon),
                        display_name: result.display_name
                    }, null);
                } else {
                    callback(null, 'No se encontró la dirección');
                }
            })
            .catch(error => {
                console.error('Error Nominatim:', error);
                callback(null, 'Error en la búsqueda: ' + error.message);
            });
            
    } catch (error) {
        console.error('Error en searchAddress:', error);
        callback(null, 'Error interno: ' + error.message);
    }
}