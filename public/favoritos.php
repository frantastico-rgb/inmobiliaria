<?php
// Portal Público - Página de Favoritos
require_once '../conexion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Favoritos - Casa Meta</title>
    <link rel="stylesheet" href="css/catalogo.css">
    <link rel="stylesheet" href="css/compare-widget.css">
    <link rel="stylesheet" href="css/leads-system.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Estilos específicos para favoritos */
        .favorites-header {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 40px 0;
            text-align: center;
            margin-bottom: 40px;
        }

        .favorites-header h1 {
            margin: 0 0 10px 0;
            font-size: 2.5em;
        }

        .favorites-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1em;
        }

        .favorites-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            display: block;
        }

        .stat-label {
            font-size: 0.9em;
            opacity: 0.8;
        }

        .empty-favorites {
            text-align: center;
            padding: 80px 20px;
            background: #f8f9fa;
            border-radius: 12px;
            margin: 40px 0;
        }

        .empty-favorites i {
            font-size: 4em;
            color: #bdc3c7;
            margin-bottom: 20px;
        }

        .empty-favorites h2 {
            color: #7f8c8d;
            margin-bottom: 15px;
        }

        .empty-favorites p {
            color: #95a5a6;
            margin-bottom: 30px;
            font-size: 1.1em;
        }

        .btn-browse {
            background: #3498db;
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            display: inline-block;
            transition: background 0.3s;
        }

        .btn-browse:hover {
            background: #2980b9;
        }

        .favorites-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-clear {
            background: #e74c3c;
            color: white;
        }

        .btn-clear:hover {
            background: #c0392b;
        }

        .btn-compare {
            background: #f39c12;
            color: white;
        }

        .btn-compare:hover {
            background: #d68910;
        }

        .btn-compare:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
        }

        .btn-share {
            background: #27ae60;
            color: white;
        }

        .btn-share:hover {
            background: #229954;
        }

        /* Modificaciones a las tarjetas de favoritos */
        .property-card.favorite-item {
            position: relative;
            border: 2px solid #e74c3c;
        }

        .favorite-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #e74c3c;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            z-index: 2;
        }

        .compare-checkbox {
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 2;
        }

        .compare-checkbox input {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .property-actions-favorite {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-remove-favorite {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            flex: 1;
        }

        .btn-remove-favorite:hover {
            background: #c0392b;
        }

        .btn-view-details {
            background: #3498db;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            flex: 2;
            text-decoration: none;
            text-align: center;
        }

        .btn-view-details:hover {
            background: #2980b9;
        }

        .comparison-panel {
            background: white;
            border: 2px solid #f39c12;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            display: none;
        }

        .comparison-panel.active {
            display: block;
        }

        .comparison-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .comparison-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        @media (max-width: 768px) {
            .favorites-stats {
                flex-direction: column;
                gap: 15px;
            }

            .favorites-actions {
                flex-direction: column;
            }

            .btn-action {
                justify-content: center;
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
                <p>Tus Propiedades Favoritas</p>
            </div>
            <nav>
                <ul class="nav-links">
                    <li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
                    <li><a href="mapa.php"><i class="fas fa-map"></i> Mapa</a></li>
                    <li><a href="favoritos.php" class="active"><i class="fas fa-heart"></i> Favoritos</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Header de Favoritos -->
    <section class="favorites-header">
        <div class="container">
            <h1><i class="fas fa-heart"></i> Mis Favoritos</h1>
            <p>Guarda y compara las propiedades que más te interesan</p>
            
            <div class="favorites-stats">
                <div class="stat-item">
                    <span class="stat-number" id="favoritesCount">0</span>
                    <span class="stat-label">Propiedades</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" id="selectedCount">0</span>
                    <span class="stat-label">Seleccionadas</span>
                </div>
            </div>
        </div>
    </section>

    <main class="container">
        <!-- Sección de favoritos vacía -->
        <div id="emptyFavorites" class="empty-favorites">
            <i class="fas fa-heart-broken"></i>
            <h2>No tienes propiedades favoritas aún</h2>
            <p>Explora nuestro catálogo y guarda las propiedades que más te interesen haciendo clic en el corazón ❤️</p>
            <a href="index.php" class="btn-browse">
                <i class="fas fa-search"></i> Explorar Propiedades
            </a>
        </div>

        <!-- Acciones de favoritos -->
        <div id="favoritesActions" class="favorites-actions" style="display: none;">
            <button class="btn-action btn-compare" onclick="toggleComparison()" disabled id="btnCompare">
                <i class="fas fa-balance-scale"></i> Comparar Seleccionadas (<span id="compareCount">0</span>)
            </button>
            <button class="btn-action btn-share" onclick="shareFavorites()">
                <i class="fas fa-share-alt"></i> Compartir Lista
            </button>
            <button class="btn-action btn-clear" onclick="clearAllFavorites()">
                <i class="fas fa-trash"></i> Limpiar Todo
            </button>
        </div>

        <!-- Panel de comparación -->
        <div id="comparisonPanel" class="comparison-panel">
            <div class="comparison-header">
                <h3><i class="fas fa-balance-scale"></i> Comparación de Propiedades</h3>
                <button class="btn-action" onclick="closeComparison()" style="background: #95a5a6;">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
            <div class="comparison-grid" id="comparisonGrid"></div>
        </div>

        <!-- Grid de favoritos -->
        <section id="favoritesGrid" class="properties-grid" style="display: none;"></section>
    </main>

    <!-- JavaScript -->
    <script>
        // Variables globales
        let allFavorites = [];
        let selectedForComparison = [];

        // Cargar favoritos al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            loadFavorites();
            updateFavoritesDisplay();
            checkAutoCompare();
        });

        // Verificar si se debe abrir comparación automáticamente
        function checkAutoCompare() {
            const urlParams = new URLSearchParams(window.location.search);
            const compareIds = urlParams.get('compare');
            
            if (compareIds) {
                const ids = compareIds.split(',').map(id => parseInt(id));
                selectedForComparison = ids.filter(id => allFavorites.some(fav => fav.cod_inm == id));
                
                if (selectedForComparison.length >= 2) {
                    setTimeout(() => {
                        toggleComparison();
                        // Marcar checkboxes correspondientes
                        selectedForComparison.forEach(id => {
                            const checkbox = document.querySelector(`input[type="checkbox"][onchange*="${id}"]`);
                            if (checkbox) checkbox.checked = true;
                        });
                        updateCompareButton();
                    }, 1000);
                }
            }
        }

        // Cargar favoritos desde localStorage
        function loadFavorites() {
            const stored = localStorage.getItem('favoriteProperties');
            allFavorites = stored ? JSON.parse(stored) : [];
        }

        // Actualizar visualización de favoritos
        function updateFavoritesDisplay() {
            const favoritesGrid = document.getElementById('favoritesGrid');
            const emptySection = document.getElementById('emptyFavorites');
            const actionsSection = document.getElementById('favoritesActions');
            const favoritesCount = document.getElementById('favoritesCount');

            favoritesCount.textContent = allFavorites.length;

            if (allFavorites.length === 0) {
                emptySection.style.display = 'block';
                favoritesGrid.style.display = 'none';
                actionsSection.style.display = 'none';
            } else {
                emptySection.style.display = 'none';
                favoritesGrid.style.display = 'grid';
                actionsSection.style.display = 'flex';
                displayFavorites();
            }

            updateCompareButton();
        }

        // Mostrar favoritos en el grid
        function displayFavorites() {
            if (allFavorites.length === 0) {
                loadFavoritesFromServer();
                return;
            }

            const grid = document.getElementById('favoritesGrid');
            grid.innerHTML = '';

            allFavorites.forEach(favorite => {
                const card = createFavoriteCard(favorite);
                grid.appendChild(card);
            });
        }

        // Cargar datos desde servidor para IDs de favoritos
        async function loadFavoritesFromServer() {
            const favoriteIds = JSON.parse(localStorage.getItem('favoriteProperties') || '[]');
            
            if (favoriteIds.length === 0) {
                updateFavoritesDisplay();
                return;
            }

            try {
                const response = await fetch('get_favorites.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ ids: favoriteIds })
                });

                if (response.ok) {
                    allFavorites = await response.json();
                    updateFavoritesDisplay();
                }
            } catch (error) {
                console.error('Error al cargar favoritos:', error);
                updateFavoritesDisplay();
            }
        }

        // Crear tarjeta de favorito
        function createFavoriteCard(property) {
            const card = document.createElement('div');
            card.className = 'property-card favorite-item';
            
            const imageUrl = property.foto ? 
                `../${property.foto}` : 
                'https://via.placeholder.com/400x250/e0e0e0/666666?text=Sin+Imagen';

            card.innerHTML = `
                <div class="compare-checkbox">
                    <input type="checkbox" onchange="toggleCompareSelection(${property.cod_inm}, this)" 
                           title="Seleccionar para comparar">
                </div>
                <div class="favorite-badge">
                    <i class="fas fa-heart"></i> Favorito
                </div>
                
                <div class="property-image">
                    <img src="${imageUrl}" alt="${property.dir_inm}" 
                         onerror="this.src='https://via.placeholder.com/400x250/e0e0e0/666666?text=Sin+Imagen'">
                </div>

                <div class="property-content">
                    <div class="property-title">${property.dir_inm}</div>
                    
                    <div class="property-location">
                        <i class="fas fa-map-marker-alt"></i>
                        ${property.barrio_inm}, ${property.ciudad_inm}
                    </div>

                    <div class="property-features">
                        <div class="feature">
                            <i class="fas fa-home"></i>
                            <span>${property.nom_tipoinm || 'Inmueble'}</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-bed"></i>
                            <span>${property.num_hab} hab</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-bath"></i>
                            <span>${property.num_ban} baños</span>
                        </div>
                    </div>

                    <div class="property-price">
                        $${new Intl.NumberFormat('es-CO').format(property.precio_alq)}
                        <span class="price-period">/mes</span>
                    </div>

                    <div class="property-actions-favorite">
                        <button class="btn-remove-favorite" onclick="removeFromFavorites(${property.cod_inm})">
                            <i class="fas fa-trash"></i> Quitar
                        </button>
                        <a href="inmueble.php?id=${property.cod_inm}" class="btn-view-details">
                            <i class="fas fa-eye"></i> Ver detalles
                        </a>
                    </div>
                </div>
            `;

            return card;
        }

        // Remover de favoritos
        function removeFromFavorites(propertyId) {
            if (confirm('¿Seguro que deseas quitar esta propiedad de favoritos?')) {
                // Remover de allFavorites
                allFavorites = allFavorites.filter(fav => fav.cod_inm != propertyId);
                
                // Actualizar localStorage
                const favoriteIds = allFavorites.map(fav => fav.cod_inm);
                localStorage.setItem('favoriteProperties', JSON.stringify(favoriteIds));
                
                // Remover de seleccionados para comparar
                selectedForComparison = selectedForComparison.filter(id => id != propertyId);
                
                // Actualizar display
                updateFavoritesDisplay();
                
                // Mostrar notificación
                showNotification('Propiedad removida de favoritos', 'info');
            }
        }

        // Limpiar todos los favoritos
        function clearAllFavorites() {
            if (confirm('¿Seguro que deseas eliminar TODOS los favoritos? Esta acción no se puede deshacer.')) {
                allFavorites = [];
                selectedForComparison = [];
                localStorage.removeItem('favoriteProperties');
                updateFavoritesDisplay();
                closeComparison();
                showNotification('Todos los favoritos han sido eliminados', 'info');
            }
        }

        // Alternar selección para comparar
        function toggleCompareSelection(propertyId, checkbox) {
            if (checkbox.checked) {
                if (selectedForComparison.length >= 3) {
                    checkbox.checked = false;
                    alert('Solo puedes comparar hasta 3 propiedades a la vez');
                    return;
                }
                selectedForComparison.push(propertyId);
            } else {
                selectedForComparison = selectedForComparison.filter(id => id != propertyId);
            }
            
            updateCompareButton();
        }

        // Actualizar botón de comparar
        function updateCompareButton() {
            const btn = document.getElementById('btnCompare');
            const count = document.getElementById('compareCount');
            const selectedCountSpan = document.getElementById('selectedCount');
            
            count.textContent = selectedForComparison.length;
            selectedCountSpan.textContent = selectedForComparison.length;
            
            btn.disabled = selectedForComparison.length < 2;
        }

        // Alternar panel de comparación
        function toggleComparison() {
            const panel = document.getElementById('comparisonPanel');
            
            if (selectedForComparison.length < 2) {
                alert('Selecciona al menos 2 propiedades para comparar');
                return;
            }
            
            panel.classList.add('active');
            displayComparison();
        }

        // Mostrar comparación
        function displayComparison() {
            const grid = document.getElementById('comparisonGrid');
            grid.innerHTML = '';
            
            const selectedProperties = allFavorites.filter(fav => 
                selectedForComparison.includes(fav.cod_inm)
            );
            
            selectedProperties.forEach(property => {
                const compareCard = createComparisonCard(property);
                grid.appendChild(compareCard);
            });
        }

        // Crear tarjeta de comparación
        function createComparisonCard(property) {
            const card = document.createElement('div');
            card.className = 'property-card';
            
            const imageUrl = property.foto ? 
                `../${property.foto}` : 
                'https://via.placeholder.com/300x200/e0e0e0/666666?text=Sin+Imagen';

            card.innerHTML = `
                <div class="property-image">
                    <img src="${imageUrl}" alt="${property.dir_inm}">
                </div>
                
                <div class="property-content">
                    <div class="property-title">${property.dir_inm}</div>
                    
                    <div style="margin: 15px 0;">
                        <strong>Precio:</strong> $${new Intl.NumberFormat('es-CO').format(property.precio_alq)}<br>
                        <strong>Tipo:</strong> ${property.nom_tipoinm}<br>
                        <strong>Ubicación:</strong> ${property.barrio_inm}, ${property.ciudad_inm}<br>
                        <strong>Habitaciones:</strong> ${property.num_hab}<br>
                        <strong>Baños:</strong> ${property.num_ban}<br>
                        <strong>Área:</strong> ${property.area_inm || 'No especificada'} m²<br>
                        <strong>Estrato:</strong> ${property.estrato_inm || 'No especificado'}
                    </div>
                    
                    <a href="inmueble.php?id=${property.cod_inm}" class="btn-view-details" style="width: 100%; margin-top: 10px;">
                        <i class="fas fa-eye"></i> Ver detalles completos
                    </a>
                </div>
            `;

            return card;
        }

        // Cerrar comparación
        function closeComparison() {
            const panel = document.getElementById('comparisonPanel');
            panel.classList.remove('active');
            
            // Desmarcar checkboxes
            document.querySelectorAll('.compare-checkbox input').forEach(cb => {
                cb.checked = false;
            });
            
            selectedForComparison = [];
            updateCompareButton();
        }

        // Compartir favoritos
        function shareFavorites() {
            if (allFavorites.length === 0) {
                alert('No tienes propiedades en favoritos para compartir');
                return;
            }
            
            let message = `🏠 *Mis Propiedades Favoritas - Casa Meta*\n\n`;
            
            allFavorites.forEach((fav, index) => {
                message += `${index + 1}. *${fav.dir_inm}*\n`;
                message += `   📍 ${fav.barrio_inm}, ${fav.ciudad_inm}\n`;
                message += `   💰 $${new Intl.NumberFormat('es-CO').format(fav.precio_alq)}\n`;
                message += `   🏠 ${fav.nom_tipoinm} - ${fav.num_hab} hab, ${fav.num_ban} baños\n\n`;
            });
            
            message += `Ver todas las propiedades en: ${window.location.origin}/INMOBILIARIA_1/public/`;
            
            const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }

        // Mostrar notificación
        function showNotification(message, type = 'success') {
            // Crear notificación temporal
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#27ae60' : '#3498db'};
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                z-index: 10000;
                animation: slideIn 0.3s ease;
            `;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Cargar favoritos del servidor al inicio
        loadFavoritesFromServer();
    </script>
    
    <script src="js/compare-system.js"></script>

    <style>
        @keyframes slideIn {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }

        .nav-links a.active {
            background: #e74c3c;
            color: white;
            border-radius: 5px;
        }
    </style>
</body>
</html>