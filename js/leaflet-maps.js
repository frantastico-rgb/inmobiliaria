/**
 * SISTEMA DE MAPAS INMOBILIARIOS CON LEAFLET + OPENSTREETMAP
 * Funciones para formularios y visualización de inmuebles y oficinas
 */

// Configuración global de mapas
const MAP_CONFIG = {
    defaultLat: 4.6097,  // Bogotá, Colombia
    defaultLng: -74.0817,
    defaultZoom: 6,
    detailZoom: 16,
    cityZoom: 12,
    tileLayer: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
};

// Base de datos de ciudades colombianas con coordenadas precisas
const COLOMBIA_CITIES = {
    // Ciudades principales
    'bogotá': { lat: 4.6097, lng: -74.0817, zoom: 11 },
    'bogota': { lat: 4.6097, lng: -74.0817, zoom: 11 },
    'medellín': { lat: 6.2442, lng: -75.5812, zoom: 11 },
    'medellin': { lat: 6.2442, lng: -75.5812, zoom: 11 },
    'cali': { lat: 3.4516, lng: -76.5320, zoom: 11 },
    'barranquilla': { lat: 10.9639, lng: -74.7964, zoom: 11 },
    'cartagena': { lat: 10.3910, lng: -75.4794, zoom: 12 },
    'bucaramanga': { lat: 7.1254, lng: -73.1198, zoom: 11 },
    
    // Ciudades intermedias
    'villavicencio': { lat: 4.1420, lng: -73.6266, zoom: 12 },
    'pereira': { lat: 4.8133, lng: -75.6961, zoom: 12 },
    'santa marta': { lat: 11.2408, lng: -74.2110, zoom: 12 },
    'ibagué': { lat: 4.4389, lng: -75.2322, zoom: 12 },
    'ibague': { lat: 4.4389, lng: -75.2322, zoom: 12 },
    'pasto': { lat: 1.2136, lng: -77.2811, zoom: 12 },
    'manizales': { lat: 5.0670, lng: -75.5174, zoom: 12 },
    'neiva': { lat: 2.9273, lng: -75.2819, zoom: 12 },
    'soledad': { lat: 10.9185, lng: -74.7648, zoom: 12 },
    'armenia': { lat: 4.5339, lng: -75.6811, zoom: 12 },
    'valledupar': { lat: 10.4731, lng: -73.2532, zoom: 12 },
    'montería': { lat: 8.7479, lng: -75.8814, zoom: 12 },
    'monteria': { lat: 8.7479, lng: -75.8814, zoom: 12 },
    
    // Ciudades menores que mencionaste
    'girardot': { lat: 4.3017, lng: -74.8022, zoom: 13 },
    'sincelejo': { lat: 9.3047, lng: -75.3978, zoom: 13 },
    'popayán': { lat: 2.4448, lng: -76.6147, zoom: 12 },
    'popayan': { lat: 2.4448, lng: -76.6147, zoom: 12 },
    'tunja': { lat: 5.5353, lng: -73.3678, zoom: 13 },
    'florencia': { lat: 1.6144, lng: -75.6062, zoom: 13 },
    'riohacha': { lat: 11.5444, lng: -72.9072, zoom: 13 },
    'quibdó': { lat: 5.6947, lng: -76.6583, zoom: 13 },
    'quibdo': { lat: 5.6947, lng: -76.6583, zoom: 13 },
    'yopal': { lat: 5.3478, lng: -72.3959, zoom: 13 },
    'arauca': { lat: 7.0906, lng: -70.7574, zoom: 13 },
    'mocoa': { lat: 1.1522, lng: -76.6511, zoom: 13 },
    'leticia': { lat: -4.2153, lng: -69.9406, zoom: 13 },
    'mitú': { lat: 1.2581, lng: -70.1676, zoom: 14 },
    'mitu': { lat: 1.2581, lng: -70.1676, zoom: 14 },
    'inírida': { lat: 3.8653, lng: -67.9239, zoom: 14 },
    'inirida': { lat: 3.8653, lng: -67.9239, zoom: 14 },
    'san josé del guaviare': { lat: 2.5722, lng: -72.6459, zoom: 13 },
    'puerto carreño': { lat: 6.1890, lng: -67.4858, zoom: 14 },
    
    // Áreas metropolitanas y municipios importantes
    'soacha': { lat: 4.5877, lng: -74.2464, zoom: 13 },
    'bello': { lat: 6.3370, lng: -75.5563, zoom: 13 },
    'itagüí': { lat: 6.1645, lng: -75.5990, zoom: 13 },
    'itagui': { lat: 6.1645, lng: -75.5990, zoom: 13 },
    'palmira': { lat: 3.5394, lng: -76.3036, zoom: 13 },
    'floridablanca': { lat: 7.0621, lng: -73.0894, zoom: 13 },
    'chía': { lat: 4.8579, lng: -74.0559, zoom: 14 },
    'chia': { lat: 4.8579, lng: -74.0559, zoom: 14 },
    'zipaquirá': { lat: 5.0220, lng: -74.0046, zoom: 13 },
    'zipaquira': { lat: 5.0220, lng: -74.0046, zoom: 13 },
    'fusagasugá': { lat: 4.3403, lng: -74.3639, zoom: 13 },
    'fusagasuga': { lat: 4.3403, lng: -74.3639, zoom: 13 },
    'facatativá': { lat: 4.8142, lng: -74.3547, zoom: 13 },
    'facatativa': { lat: 4.8142, lng: -74.3547, zoom: 13 }
};

/**
 * Buscar ciudad en coordenadas predefinidas
 */
function findCityInPredefined(cityName) {
    const normalized = cityName.toLowerCase().trim();
    
    // Buscar coincidencia exacta
    if (COLOMBIA_CITIES[normalized]) {
        return {
            lat: COLOMBIA_CITIES[normalized].lat,
            lng: COLOMBIA_CITIES[normalized].lng,
            zoom: COLOMBIA_CITIES[normalized].zoom,
            display_name: cityName + ', Colombia',
            source: 'predefined'
        };
    }
    
    // Buscar coincidencia parcial
    for (const [key, coords] of Object.entries(COLOMBIA_CITIES)) {
        if (key.includes(normalized) || normalized.includes(key)) {
            return {
                lat: coords.lat,
                lng: coords.lng,
                zoom: coords.zoom,
                display_name: key.charAt(0).toUpperCase() + key.slice(1) + ', Colombia',
                source: 'predefined'
            };
        }
    }
    
    return null;
}

// Variables globales para mapas
let currentMap = null;
let currentMarker = null;

/**
 * FUNCIÓN 1: Inicializar mapa en formularios (crear/editar)
 * Para seleccionar ubicación haciendo click en el mapa
 */
function initFormMap(mapContainerId, latInputId, lngInputId, initialLat = null, initialLng = null) {
    // Coordenadas iniciales (usar proporcionadas o por defecto)
    const lat = initialLat || MAP_CONFIG.defaultLat;
    const lng = initialLng || MAP_CONFIG.defaultLng;
    const zoom = (initialLat && initialLng) ? MAP_CONFIG.detailZoom : MAP_CONFIG.defaultZoom;
    
    // Crear mapa
    currentMap = L.map(mapContainerId).setView([lat, lng], zoom);
    
    // Agregar capa de tiles de OpenStreetMap
    L.tileLayer(MAP_CONFIG.tileLayer, {
        attribution: MAP_CONFIG.attribution,
        maxZoom: 19
    }).addTo(currentMap);
    
    // Crear marcador inicial si hay coordenadas
    if (initialLat && initialLng) {
        currentMarker = L.marker([lat, lng], {draggable: true})
            .addTo(currentMap)
            .bindPopup('Ubicación seleccionada. Puedes arrastrar este marcador.')
            .openPopup();
        
        // Evento cuando se arrastra el marcador
        currentMarker.on('dragend', function(e) {
            const position = e.target.getLatLng();
            updateCoordinates(position.lat, position.lng, latInputId, lngInputId);
        });
    }
    
    // Evento click en el mapa para colocar/mover marcador
    currentMap.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        
        if (currentMarker) {
            currentMarker.setLatLng(e.latlng);
        } else {
            currentMarker = L.marker(e.latlng, {draggable: true})
                .addTo(currentMap)
                .bindPopup('Ubicación seleccionada. Puedes arrastrar este marcador.');
            
            // Evento cuando se arrastra el marcador recién creado
            currentMarker.on('dragend', function(dragEvent) {
                const position = dragEvent.target.getLatLng();
                updateCoordinates(position.lat, position.lng, latInputId, lngInputId);
            });
        }
        
        currentMarker.openPopup();
        updateCoordinates(lat, lng, latInputId, lngInputId);
    });
    
    return currentMap;
}

/**
 * FUNCIÓN 2: Actualizar campos de coordenadas
 */
function updateCoordinates(lat, lng, latInputId, lngInputId) {
    const latInput = document.getElementById(latInputId);
    const lngInput = document.getElementById(lngInputId);
    
    if (latInput) latInput.value = lat.toFixed(6);
    if (lngInput) lngInput.value = lng.toFixed(6);
    
    // Trigger evento change para validaciones del formulario
    if (latInput) latInput.dispatchEvent(new Event('change'));
    if (lngInput) lngInput.dispatchEvent(new Event('change'));
}

/**
 * FUNCIÓN 3: Inicializar mapa de lista (múltiples marcadores)
 */
function initListMap(mapContainerId, locations, popupTemplate) {
    if (!locations || locations.length === 0) {
        document.getElementById(mapContainerId).innerHTML = 
            '<div style="padding: 20px; text-align: center; color: #666;">No hay ubicaciones para mostrar en el mapa</div>';
        return null;
    }
    
    // Crear mapa centrado en la primera ubicación o coordenadas por defecto
    const firstLocation = locations[0];
    const centerLat = firstLocation.lat || MAP_CONFIG.defaultLat;
    const centerLng = firstLocation.lng || MAP_CONFIG.defaultLng;
    
    const map = L.map(mapContainerId).setView([centerLat, centerLng], MAP_CONFIG.defaultZoom);
    
    // Agregar capa de tiles
    L.tileLayer(MAP_CONFIG.tileLayer, {
        attribution: MAP_CONFIG.attribution,
        maxZoom: 19
    }).addTo(map);
    
    // Grupo de marcadores para ajustar vista
    const markersGroup = L.featureGroup();
    
    // Agregar marcadores para cada ubicación
    locations.forEach(function(location, index) {
        if (location.lat && location.lng) {
            const marker = L.marker([location.lat, location.lng])
                .bindPopup(generatePopupContent(location, popupTemplate));
            
            markersGroup.addLayer(marker);
        }
    });
    
    // Agregar grupo al mapa
    markersGroup.addTo(map);
    
    // Ajustar vista para mostrar todos los marcadores
    if (markersGroup.getLayers().length > 1) {
        map.fitBounds(markersGroup.getBounds(), {padding: [10, 10]});
    } else if (markersGroup.getLayers().length === 1) {
        map.setView([centerLat, centerLng], MAP_CONFIG.detailZoom);
    }
    
    return map;
}

/**
 * FUNCIÓN 4: Generar contenido de popup para marcadores
 */
function generatePopupContent(location, template) {
    let content = template;
    
    // Reemplazar variables en el template
    for (const key in location) {
        const placeholder = `{${key}}`;
        const value = location[key] || 'No especificado';
        content = content.replace(new RegExp(placeholder, 'g'), value);
    }
    
    return content;
}

/**
 * FUNCIÓN 5: Buscar ubicación por dirección (Geocodificación mejorada para Colombia)
 */
function searchAddress(address, callback) {
    if (!address || address.trim() === '') {
        callback(null, 'Dirección vacía');
        return;
    }
    
    console.log('Iniciando búsqueda para:', address);
    
    // Normalizar dirección para Colombia
    const normalizedAddress = normalizeColombianAddress(address);
    console.log('Dirección normalizada:', normalizedAddress);
    
    // Intentar múltiples estrategias de búsqueda
    searchWithMultipleStrategies(normalizedAddress, callback);
}

/**
 * Normalizar direcciones colombianas para mejor geocodificación
 */
function normalizeColombianAddress(address) {
    let normalized = address.toLowerCase().trim();
    
    // Reemplazar abreviaciones comunes
    const replacements = {
        'cra': 'carrera',
        'cr': 'carrera',
        'cll': 'calle',
        'cl': 'calle',
        'av': 'avenida',
        'avd': 'avenida',
        'diag': 'diagonal',
        'trans': 'transversal',
        'kra': 'carrera',
        'k': 'carrera'
    };
    
    // Aplicar reemplazos
    for (const [abbr, full] of Object.entries(replacements)) {
        const regex = new RegExp(`\\b${abbr}\\b`, 'gi');
        normalized = normalized.replace(regex, full);
    }
    
    // Convertir primera letra de cada palabra a mayúscula
    normalized = normalized.replace(/\b\w/g, l => l.toUpperCase());
    
    return normalized;
}

/**
 * Intentar búsqueda con múltiples estrategias
 */
function searchWithMultipleStrategies(address, callback) {
    // Estrategia 0: Verificar si es solo una ciudad en nuestro catálogo predefinido
    const cityFromAddress = extractCityFromAddress(address);
    if (cityFromAddress) {
        const predefinedCity = findCityInPredefined(cityFromAddress);
        if (predefinedCity) {
            console.log('Ciudad encontrada en catálogo predefinido:', predefinedCity);
            callback(predefinedCity, null);
            return;
        }
    }
    
    const strategies = [
        // Estrategia 1: Dirección completa
        address,
        // Estrategia 2: Solo ciudad y país (si la dirección completa falla)
        extractCityFromAddress(address),
        // Estrategia 3: Ciudad principal + Colombia
        extractCityFromAddress(address) + ', Colombia'
    ];
    
    console.log('Estrategias de búsqueda:', strategies);
    
    trySearchStrategy(strategies, 0, callback);
}

/**
 * Intentar estrategias de búsqueda secuencialmente
 */
function trySearchStrategy(strategies, index, callback) {
    if (index >= strategies.length) {
        callback(null, 'No se pudo encontrar la ubicación con ninguna estrategia');
        return;
    }
    
    const currentStrategy = strategies[index];
    console.log(`Probando estrategia ${index + 1}: ${currentStrategy}`);
    
    if (!currentStrategy) {
        trySearchStrategy(strategies, index + 1, callback);
        return;
    }
    
    // Usar Nominatim API con parámetros específicos para Colombia
    const url = `https://nominatim.openstreetmap.org/search?` + 
                `format=json&` +
                `q=${encodeURIComponent(currentStrategy)}&` +
                `countrycodes=co&` +
                `limit=3&` +
                `addressdetails=1&` +
                `bounded=0`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            console.log(`Resultados estrategia ${index + 1}:`, data);
            
            if (data && data.length > 0) {
                // Encontrado - seleccionar mejor resultado
                const bestResult = selectBestResult(data, currentStrategy);
                callback({
                    lat: parseFloat(bestResult.lat),
                    lng: parseFloat(bestResult.lon),
                    display_name: bestResult.display_name,
                    strategy: index + 1,
                    address_type: bestResult.type
                }, null);
            } else {
                // No encontrado - probar siguiente estrategia
                console.log(`Estrategia ${index + 1} sin resultados, probando siguiente...`);
                trySearchStrategy(strategies, index + 1, callback);
            }
        })
        .catch(error => {
            console.error(`Error en estrategia ${index + 1}:`, error);
            trySearchStrategy(strategies, index + 1, callback);
        });
}

/**
 * Extraer ciudad de una dirección completa
 */
function extractCityFromAddress(address) {
    const parts = address.split(',');
    
    // Lista de ciudades principales de Colombia
    const colombianCities = [
        'bogotá', 'bogota', 'medellín', 'medellin', 'cali', 'barranquilla',
        'cartagena', 'cúcuta', 'cucuta', 'bucaramanga', 'pereira', 'ibagué', 'ibague',
        'santa marta', 'villavicencio', 'manizales', 'neiva', 'palmira',
        'montería', 'monteria', 'pasto', 'valledupar', 'buenaventura',
        'tunja', 'florencia', 'popayán', 'popayan', 'armenia', 'sincelejo',
        'riohacha', 'yopal', 'quibdó', 'quibdo', 'mocoa', 'leticia'
    ];
    
    // Buscar ciudad en las partes de la dirección
    for (const part of parts) {
        const cleanPart = part.trim().toLowerCase();
        for (const city of colombianCities) {
            if (cleanPart.includes(city)) {
                return part.trim();
            }
        }
    }
    
    // Si no se encuentra ciudad específica, tomar última parte antes de "Colombia"
    for (let i = parts.length - 1; i >= 0; i--) {
        const part = parts[i].trim().toLowerCase();
        if (!part.includes('colombia') && part.length > 2) {
            return parts[i].trim();
        }
    }
    
    return null;
}

/**
 * Seleccionar mejor resultado de múltiples opciones
 */
function selectBestResult(results, originalQuery) {
    // Priorizar resultados que contengan la ciudad específica
    const cityFromQuery = extractCityFromAddress(originalQuery);
    
    if (cityFromQuery) {
        const cityResults = results.filter(r => 
            r.display_name.toLowerCase().includes(cityFromQuery.toLowerCase())
        );
        if (cityResults.length > 0) {
            return cityResults[0];
        }
    }
    
    // Priorizar por tipo de lugar (más específico primero)
    const priorities = ['house', 'building', 'road', 'suburb', 'city', 'state'];
    for (const priority of priorities) {
        const match = results.find(r => r.type === priority);
        if (match) return match;
    }
    
    // Devolver el primero si no hay mejor opción
    return results[0];
}

/**
 * FUNCIÓN 6: Botón para buscar dirección en formularios
 */
function setupAddressSearch(addressInputId, latInputId, lngInputId, mapContainer) {
    const addressInput = document.getElementById(addressInputId);
    if (!addressInput) return;
    
    // Crear botón de búsqueda
    const searchButton = document.createElement('button');
    searchButton.type = 'button';
    searchButton.innerHTML = '🔍 Buscar en Mapa';
    searchButton.className = 'btn-search-address';
    searchButton.style.marginLeft = '10px';
    searchButton.style.padding = '8px 12px';
    searchButton.style.backgroundColor = '#28a745';
    searchButton.style.color = 'white';
    searchButton.style.border = 'none';
    searchButton.style.borderRadius = '4px';
    searchButton.style.cursor = 'pointer';
    
    // Insertar botón después del input de dirección
    addressInput.parentNode.appendChild(searchButton);
    
    // Evento click del botón
    searchButton.addEventListener('click', function() {
        const address = addressInput.value;
        
        if (!address) {
            alert('Por favor ingrese una dirección');
            return;
        }
        
        searchButton.innerHTML = '🔄 Buscando...';
        searchButton.disabled = true;
        
        searchAddress(address, function(result, error) {
            searchButton.innerHTML = '🔍 Buscar en Mapa';
            searchButton.disabled = false;
            
            if (error) {
                alert('Error: ' + error);
                return;
            }
            
            if (result && currentMap) {
                // Mover mapa a la ubicación encontrada
                currentMap.setView([result.lat, result.lng], MAP_CONFIG.detailZoom);
                
                // Crear o mover marcador
                if (currentMarker) {
                    currentMarker.setLatLng([result.lat, result.lng]);
                } else {
                    currentMarker = L.marker([result.lat, result.lng], {draggable: true})
                        .addTo(currentMap);
                    
                    currentMarker.on('dragend', function(e) {
                        const position = e.target.getLatLng();
                        updateCoordinates(position.lat, position.lng, latInputId, lngInputId);
                    });
                }
                
                currentMarker.bindPopup(`Dirección encontrada: ${result.display_name}`).openPopup();
                updateCoordinates(result.lat, result.lng, latInputId, lngInputId);
            }
        });
    });
}

/**
 * FUNCIÓN 7: Toggle entre vista lista y mapa
 */
function setupMapToggle(toggleButtonId, listContainerId, mapContainerId) {
    const toggleButton = document.getElementById(toggleButtonId);
    const listContainer = document.getElementById(listContainerId);
    const mapContainer = document.getElementById(mapContainerId);
    
    if (!toggleButton || !listContainer || !mapContainer) return;
    
    let showingMap = false;
    
    toggleButton.addEventListener('click', function() {
        if (showingMap) {
            // Mostrar lista, ocultar mapa
            listContainer.style.display = 'block';
            mapContainer.style.display = 'none';
            toggleButton.innerHTML = '🗺️ Ver en Mapa';
            showingMap = false;
        } else {
            // Mostrar mapa, ocultar lista
            listContainer.style.display = 'none';
            mapContainer.style.display = 'block';
            toggleButton.innerHTML = '📋 Ver Lista';
            showingMap = true;
            
            // Refrescar mapa (necesario cuando se hace visible)
            setTimeout(function() {
                if (currentMap) {
                    currentMap.invalidateSize();
                }
            }, 100);
        }
    });
}

/**
 * FUNCIÓN 8: Utilidad para validar coordenadas
 */
function validateCoordinates(lat, lng) {
    const latitude = parseFloat(lat);
    const longitude = parseFloat(lng);
    
    if (isNaN(latitude) || isNaN(longitude)) {
        return false;
    }
    
    return latitude >= -90 && latitude <= 90 && longitude >= -180 && longitude <= 180;
}