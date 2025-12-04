# Tutorial: Configuración y Uso del Sistema Inmobiliario

## 🚀 Instalación Paso a Paso

### Prerrequisitos

1. **XAMPP** instalado (Apache + MySQL + PHP 7.4+)
2. **Git** para clonar el repositorio
3. **Navegador web** moderno (Chrome, Firefox, Safari)

### Paso 1: Obtener el Código

```bash
# Clonar desde GitHub
git clone https://github.com/frantastico-rgb/inmobiliaria.git

# O descargar ZIP y extraer en:
C:\xampp\htdocs\INMOBILIARIA_1\
```

### Paso 2: Configurar XAMPP

1. **Iniciar XAMPP Control Panel**
2. **Activar servicios:**
   - ✅ Apache (Puerto 80)
   - ✅ MySQL (Puerto 3306)

3. **Verificar funcionamiento:**
   - Ir a http://localhost/
   - Debería mostrar dashboard XAMPP

### Paso 3: Crear Base de Datos

1. **Acceder a phpMyAdmin:**
   ```
   http://localhost/phpmyadmin
   ```

2. **Crear base de datos:**
   - Nombre: `inmobil`
   - Cotejamiento: `utf8mb4_unicode_ci`

3. **Ejecutar script inicial:**
   ```sql
   -- Copiar y ejecutar desde setup_database.php
   ```

### Paso 4: Configurar Conexión

1. **Editar `conexion.php`:**
   ```php
   <?php
   $host = 'localhost';
   $dbname = 'inmobil';
   $username = 'root';
   $password = '';  // Dejar vacío en XAMPP local
   
   try {
       $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", 
                      $username, $password);
       $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch(PDOException $e) {
       die("Error de conexión: " . $e->getMessage());
   }
   ?>
   ```

### Paso 5: Configurar Permisos

```bash
# En Windows (PowerShell como Administrador)
icacls "C:\xampp\htdocs\INMOBILIARIA_1\uploads" /grant Everyone:(OI)(CI)F

# En Linux/Mac
chmod -R 755 /opt/lampp/htdocs/INMOBILIARIA_1/
chmod -R 777 /opt/lampp/htdocs/INMOBILIARIA_1/uploads/
```

### Paso 6: Crear Usuario Administrador

1. **Ejecutar setup:**
   ```
   http://localhost/INMOBILIARIA_1/setup_users.php
   ```

2. **O crear manualmente:**
   ```sql
   INSERT INTO usuarios (nombre, email, password, rol) VALUES 
   ('Administrador', 'admin@inmobiliaria.com', '$2y$10$hash_aqui', 'administrador');
   ```

### Paso 7: Verificar Instalación

1. **Acceder al sistema:**
   ```
   http://localhost/INMOBILIARIA_1/
   ```

2. **Login con credenciales:**
   - Email: admin@inmobiliaria.com
   - Password: admin123

---

## 👤 Gestión de Usuarios

### Crear Usuarios por Rol

#### Administrador
```sql
INSERT INTO usuarios (nombre, email, password, rol, activo) VALUES 
('Juan Pérez', 'admin@inmobiliaria.com', '$2y$10$encrypted_password', 'administrador', 1);
```

#### Secretaria
```sql
INSERT INTO usuarios (nombre, email, password, rol, activo) VALUES 
('María García', 'secretaria@inmobiliaria.com', '$2y$10$encrypted_password', 'secretaria', 1);
```

#### Agente Senior
```sql
INSERT INTO usuarios (nombre, email, password, rol, activo) VALUES 
('Carlos Rodríguez', 'agente.senior@inmobiliaria.com', '$2y$10$encrypted_password', 'agente_senior', 1);
```

#### Agente Junior
```sql
INSERT INTO usuarios (nombre, email, password, rol, activo) VALUES 
('Ana López', 'agente.junior@inmobiliaria.com', '$2y$10$encrypted_password', 'agente_junior', 1);
```

### Panel de Administración

#### Acceso: `admin/users_manage.php`

**Funcionalidades:**
- ✅ Crear nuevos usuarios
- ✅ Editar información existente
- ✅ Cambiar roles
- ✅ Activar/desactivar usuarios
- ✅ Resetear contraseñas

**Formulario Crear Usuario:**
```html
<form method="POST" action="users_manage.php">
    <input type="text" name="nombre" placeholder="Nombre completo" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <select name="rol" required>
        <option value="agente_junior">Agente Junior</option>
        <option value="agente_senior">Agente Senior</option>
        <option value="secretaria">Secretaria</option>
        <option value="administrador">Administrador</option>
    </select>
    <button type="submit">Crear Usuario</button>
</form>
```

---

## 🏠 Gestión de Inmuebles

### Agregar Nuevo Inmueble

1. **Acceder a:** `inmuebles.php`
2. **Llenar formulario completo:**

```html
<form method="POST" enctype="multipart/form-data">
    <!-- Información Básica -->
    <input type="text" name="titulo" placeholder="Título atractivo" required>
    <textarea name="descripcion" placeholder="Descripción detallada"></textarea>
    <input type="number" name="precio" placeholder="Precio en COP" required>
    
    <!-- Tipo y Operación -->
    <select name="tipo" required>
        <option value="casa">Casa</option>
        <option value="apartamento">Apartamento</option>
        <option value="oficina">Oficina</option>
        <option value="local">Local Comercial</option>
        <option value="lote">Lote</option>
        <option value="bodega">Bodega</option>
    </select>
    
    <select name="operacion" required>
        <option value="venta">Venta</option>
        <option value="arriendo">Arriendo</option>
    </select>
    
    <!-- Ubicación -->
    <input type="text" name="direccion" placeholder="Dirección completa" required>
    <input type="text" name="ciudad" placeholder="Ciudad" required>
    <input type="text" name="barrio" placeholder="Barrio">
    
    <!-- Coordenadas (opcional - se pueden geocodificar) -->
    <input type="text" name="latitud" placeholder="Latitud">
    <input type="text" name="longitud" placeholder="Longitud">
    
    <!-- Características -->
    <input type="number" name="area_construida" placeholder="Área construida (m²)">
    <input type="number" name="area_lote" placeholder="Área lote (m²)">
    <input type="number" name="habitaciones" placeholder="Habitaciones">
    <input type="number" name="baños" placeholder="Baños">
    <input type="number" name="garajes" placeholder="Garajes">
    
    <!-- Multimedia -->
    <input type="file" name="imagen" accept="image/*">
    <input type="file" name="imagen_secundaria" accept="image/*">
    <input type="file" name="video" accept="video/*">
    
    <!-- Propietario y Agente -->
    <select name="propietario_id">
        <option value="">Seleccionar propietario</option>
        <!-- Se llena dinámicamente desde BD -->
    </select>
    
    <select name="agente_id">
        <option value="">Asignar agente</option>
        <!-- Se llena con agentes activos -->
    </select>
    
    <button type="submit">Guardar Inmueble</button>
</form>
```

### Geocodificación Automática

**Función en JavaScript:**
```javascript
async function geocodeAddress(address) {
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address + ', Colombia')}`);
        const data = await response.json();
        
        if (data.length > 0) {
            const lat = parseFloat(data[0].lat);
            const lon = parseFloat(data[0].lon);
            
            document.getElementById('latitud').value = lat;
            document.getElementById('longitud').value = lon;
            
            return {lat, lon};
        }
    } catch (error) {
        console.error('Error geocodificando:', error);
    }
}

// Usar cuando se llena dirección
document.getElementById('direccion').addEventListener('blur', function() {
    const fullAddress = this.value + ', ' + document.getElementById('ciudad').value;
    geocodeAddress(fullAddress);
});
```

### Optimización de Imágenes

**Función PHP para redimensionar:**
```php
function optimizeImage($source, $destination, $quality = 85, $maxWidth = 1200) {
    $imageInfo = getimagesize($source);
    $imageType = $imageInfo[2];
    
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($source);
            break;
        default:
            return false;
    }
    
    $width = imagesx($image);
    $height = imagesy($image);
    
    if ($width > $maxWidth) {
        $newWidth = $maxWidth;
        $newHeight = ($height * $maxWidth) / $width;
        
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        imagejpeg($newImage, $destination, $quality);
        imagedestroy($newImage);
    } else {
        imagejpeg($image, $destination, $quality);
    }
    
    imagedestroy($image);
    return true;
}

// Usar en guardar_inmueble.php
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
    $uploadDir = 'uploads/';
    $fileName = time() . '_' . $_FILES['imagen']['name'];
    $uploadPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadPath)) {
        optimizeImage($uploadPath, $uploadPath);
        // Guardar $fileName en BD
    }
}
```

---

## 📞 Sistema de Leads

### Configuración WhatsApp

1. **Editar número en `public/procesar_lead.php`:**
   ```php
   $whatsapp_number = '573001234567'; // Tu número con código país
   ```

2. **Personalizar mensaje automático:**
   ```php
   $inmueble = getInmuebleById($inmueble_id);
   $mensaje = "Hola! Me interesa el inmueble: " . $inmueble['titulo'] . 
              " - Precio: $" . number_format($inmueble['precio']) . 
              " - Ubicación: " . $inmueble['direccion'];
   
   $whatsapp_url = "https://wa.me/{$whatsapp_number}?text=" . urlencode($mensaje);
   ```

### Formulario Público

**Ubicación:** `public/inmueble.php`

```html
<div class="lead-form-container">
    <h4>Solicitar Información</h4>
    <form id="leadForm" class="lead-form">
        <input type="hidden" name="inmueble_id" value="<?= $inmueble['id'] ?>">
        
        <div class="form-group">
            <input type="text" name="nombre" placeholder="Tu nombre completo" required>
        </div>
        
        <div class="form-group">
            <input type="tel" name="telefono" placeholder="Tu número de teléfono" required>
        </div>
        
        <div class="form-group">
            <input type="email" name="email" placeholder="Tu email (opcional)">
        </div>
        
        <div class="form-group">
            <textarea name="mensaje" placeholder="¿Alguna pregunta específica?"></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary btn-block">
            <i class="fas fa-paper-plane"></i> Solicitar Información
        </button>
    </form>
</div>

<script>
document.getElementById('leadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('procesar_lead.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('¡Información enviada! Te contactaremos pronto.');
            
            // Abrir WhatsApp automáticamente
            window.open(data.whatsapp_url, '_blank');
            
            this.reset();
        } else {
            alert('Error al enviar información. Intenta nuevamente.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al enviar información. Intenta nuevamente.');
    });
});
</script>
```

### Dashboard de Leads

**Para agentes:** `lista_leads.php`

```php
// Filtrar leads por agente (si no es admin)
if ($_SESSION['user_role'] !== 'administrador') {
    $whereClause = "WHERE l.agente_asignado = " . $_SESSION['user_id'];
} else {
    $whereClause = "";
}

$query = "
    SELECT l.*, i.titulo as inmueble_titulo, i.precio, i.direccion,
           u.nombre as agente_nombre
    FROM leads l
    LEFT JOIN inmuebles i ON l.inmueble_id = i.id
    LEFT JOIN usuarios u ON l.agente_asignado = u.id
    $whereClause
    ORDER BY l.fecha_creacion DESC
";
```

**Vista de leads:**
```html
<div class="leads-dashboard">
    <div class="leads-summary">
        <div class="stat-card nuevos">
            <h3><?= $stats['nuevos'] ?></h3>
            <p>Leads Nuevos</p>
        </div>
        <div class="stat-card contactados">
            <h3><?= $stats['contactados'] ?></h3>
            <p>Contactados</p>
        </div>
        <div class="stat-card interesados">
            <h3><?= $stats['interesados'] ?></h3>
            <p>Interesados</p>
        </div>
        <div class="stat-card convertidos">
            <h3><?= $stats['convertidos'] ?></h3>
            <p>Convertidos</p>
        </div>
    </div>
    
    <div class="leads-table">
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Inmueble</th>
                    <th>Estado</th>
                    <th>Agente</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leads as $lead): ?>
                <tr class="<?= $lead['estado'] === 'nuevo' ? 'table-warning' : '' ?>">
                    <td><?= date('d/m/Y H:i', strtotime($lead['fecha_creacion'])) ?></td>
                    <td>
                        <strong><?= $lead['nombre'] ?></strong><br>
                        <small><?= $lead['telefono'] ?></small>
                    </td>
                    <td>
                        <?= $lead['inmueble_titulo'] ?><br>
                        <small>$<?= number_format($lead['precio']) ?></small>
                    </td>
                    <td>
                        <span class="badge badge-<?= getStatusColor($lead['estado']) ?>">
                            <?= ucfirst($lead['estado']) ?>
                        </span>
                    </td>
                    <td><?= $lead['agente_nombre'] ?: 'Sin asignar' ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="contactarLead(<?= $lead['id'] ?>)">
                            <i class="fas fa-phone"></i> Contactar
                        </button>
                        <button class="btn btn-sm btn-success" onclick="whatsappLead('<?= $lead['telefono'] ?>', '<?= $lead['inmueble_titulo'] ?>')">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
```

---

## 🗺️ Configuración de Mapas

### Mapa Principal (`public/mapa.php`)

```javascript
// Configuración inicial del mapa
const map = L.map('map').setView([4.7109, -74.0721], 6); // Colombia centrada

// Añadir tiles de OpenStreetMap
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 18
}).addTo(map);

// Geolocalización del usuario
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
        const userLat = position.coords.latitude;
        const userLon = position.coords.longitude;
        
        // Centrar mapa en ubicación del usuario
        map.setView([userLat, userLon], 12);
        
        // Añadir marcador de ubicación
        L.marker([userLat, userLon])
            .addTo(map)
            .bindPopup('Tu ubicación')
            .openPopup();
    });
}

// Cargar inmuebles desde API
async function loadInmuebles() {
    try {
        const response = await fetch('get_inmuebles_mapa.php');
        const inmuebles = await response.json();
        
        inmuebles.forEach(inmueble => {
            if (inmueble.latitud && inmueble.longitud) {
                const marker = L.marker([inmueble.latitud, inmueble.longitud])
                    .addTo(map);
                
                const popupContent = createPopupContent(inmueble);
                marker.bindPopup(popupContent);
            }
        });
    } catch (error) {
        console.error('Error cargando inmuebles:', error);
    }
}

// Crear contenido del popup
function createPopupContent(inmueble) {
    const isFavorite = FavoritesManager.isFavorite(inmueble.id);
    
    return `
        <div class="popup-inmueble">
            <div class="popup-header">
                <h6>${inmueble.titulo}</h6>
                <button class="btn btn-sm favorite-btn ${isFavorite ? 'favorited' : ''}" 
                        onclick="toggleFavorite(${inmueble.id})">
                    <i class="fas fa-heart"></i>
                </button>
            </div>
            
            ${inmueble.imagen ? `<img src="uploads/${inmueble.imagen}" alt="Inmueble" class="popup-image">` : ''}
            
            <div class="popup-info">
                <div class="precio">$${new Intl.NumberFormat('es-CO').format(inmueble.precio)}</div>
                <div class="direccion">${inmueble.direccion}</div>
                <div class="caracteristicas">
                    ${inmueble.habitaciones ? `${inmueble.habitaciones} hab` : ''}
                    ${inmueble.baños ? ` • ${inmueble.baños} baños` : ''}
                    ${inmueble.area_construida ? ` • ${inmueble.area_construida}m²` : ''}
                </div>
            </div>
            
            <div class="popup-actions">
                <a href="inmueble.php?id=${inmueble.id}" class="btn btn-primary btn-sm">
                    Ver Detalles
                </a>
                <button class="btn btn-success btn-sm" onclick="contactarWhatsApp(${inmueble.id})">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </button>
            </div>
        </div>
    `;
}

// Inicializar mapa
loadInmuebles();
```

### API para Inmuebles (`get_inmuebles_mapa.php`)

```php
<?php
require_once 'conexion.php';

header('Content-Type: application/json');

try {
    $query = "
        SELECT id, titulo, precio, direccion, ciudad, latitud, longitud, 
               habitaciones, baños, area_construida, imagen, tipo, operacion
        FROM inmuebles 
        WHERE estado = 'activo' 
          AND latitud IS NOT NULL 
          AND longitud IS NOT NULL
    ";
    
    // Agregar filtros si existen
    $params = [];
    
    if (isset($_GET['tipo']) && $_GET['tipo'] !== '') {
        $query .= " AND tipo = ?";
        $params[] = $_GET['tipo'];
    }
    
    if (isset($_GET['operacion']) && $_GET['operacion'] !== '') {
        $query .= " AND operacion = ?";
        $params[] = $_GET['operacion'];
    }
    
    if (isset($_GET['precio_min']) && $_GET['precio_min'] !== '') {
        $query .= " AND precio >= ?";
        $params[] = $_GET['precio_min'];
    }
    
    if (isset($_GET['precio_max']) && $_GET['precio_max'] !== '') {
        $query .= " AND precio <= ?";
        $params[] = $_GET['precio_max'];
    }
    
    if (isset($_GET['ciudad']) && $_GET['ciudad'] !== '') {
        $query .= " AND ciudad LIKE ?";
        $params[] = '%' . $_GET['ciudad'] . '%';
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $inmuebles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($inmuebles);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener inmuebles']);
}
?>
```

### Filtros del Mapa

```html
<div class="map-filters">
    <form id="mapFilters">
        <div class="row">
            <div class="col-md-2">
                <select name="tipo" class="form-control">
                    <option value="">Tipo</option>
                    <option value="casa">Casa</option>
                    <option value="apartamento">Apartamento</option>
                    <option value="oficina">Oficina</option>
                    <option value="local">Local</option>
                    <option value="lote">Lote</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <select name="operacion" class="form-control">
                    <option value="">Operación</option>
                    <option value="venta">Venta</option>
                    <option value="arriendo">Arriendo</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <input type="number" name="precio_min" class="form-control" placeholder="Precio mínimo">
            </div>
            
            <div class="col-md-2">
                <input type="number" name="precio_max" class="form-control" placeholder="Precio máximo">
            </div>
            
            <div class="col-md-2">
                <input type="text" name="ciudad" class="form-control" placeholder="Ciudad">
            </div>
            
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <button type="button" class="btn btn-secondary" onclick="clearFilters()">Limpiar</button>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById('mapFilters').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const params = new URLSearchParams(formData);
    
    // Limpiar marcadores existentes
    map.eachLayer(function(layer) {
        if (layer instanceof L.Marker) {
            map.removeLayer(layer);
        }
    });
    
    // Cargar inmuebles filtrados
    fetch('get_inmuebles_mapa.php?' + params.toString())
        .then(response => response.json())
        .then(inmuebles => {
            inmuebles.forEach(inmueble => {
                const marker = L.marker([inmueble.latitud, inmueble.longitud])
                    .addTo(map);
                
                const popupContent = createPopupContent(inmueble);
                marker.bindPopup(popupContent);
            });
        });
});

function clearFilters() {
    document.getElementById('mapFilters').reset();
    document.getElementById('mapFilters').dispatchEvent(new Event('submit'));
}
</script>
```

---

## ❤️ Sistema de Favoritos

### Implementación Frontend

**JavaScript (`public/js/favorites.js`):**
```javascript
class FavoritesManager {
    static STORAGE_KEY = 'inmuebles_favoritos';
    
    static add(inmuebleId) {
        let favorites = this.getAll();
        if (!favorites.includes(inmuebleId)) {
            favorites.push(inmuebleId);
            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(favorites));
            this.updateUI(inmuebleId, true);
            this.showNotification('Inmueble agregado a favoritos');
        }
    }
    
    static remove(inmuebleId) {
        let favorites = this.getAll();
        favorites = favorites.filter(id => id !== inmuebleId);
        localStorage.setItem(this.STORAGE_KEY, JSON.stringify(favorites));
        this.updateUI(inmuebleId, false);
        this.showNotification('Inmueble removido de favoritos');
    }
    
    static getAll() {
        const stored = localStorage.getItem(this.STORAGE_KEY);
        return stored ? JSON.parse(stored) : [];
    }
    
    static isFavorite(inmuebleId) {
        return this.getAll().includes(parseInt(inmuebleId));
    }
    
    static toggle(inmuebleId) {
        if (this.isFavorite(inmuebleId)) {
            this.remove(inmuebleId);
        } else {
            this.add(inmuebleId);
        }
    }
    
    static updateUI(inmuebleId, isFavorite) {
        const buttons = document.querySelectorAll(`[data-inmueble-id="${inmuebleId}"]`);
        buttons.forEach(button => {
            const icon = button.querySelector('i');
            if (isFavorite) {
                button.classList.add('favorited');
                icon.classList.add('fas');
                icon.classList.remove('far');
            } else {
                button.classList.remove('favorited');
                icon.classList.add('far');
                icon.classList.remove('fas');
            }
        });
    }
    
    static showNotification(message) {
        // Crear notificación toast
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }
    
    static initializeUI() {
        const favorites = this.getAll();
        favorites.forEach(inmuebleId => {
            this.updateUI(inmuebleId, true);
        });
    }
    
    static getCount() {
        return this.getAll().length;
    }
    
    static updateCounter() {
        const counter = document.getElementById('favoritesCounter');
        if (counter) {
            counter.textContent = this.getCount();
        }
    }
}

// Función global para toggle favoritos
function toggleFavorite(inmuebleId) {
    FavoritesManager.toggle(inmuebleId);
    FavoritesManager.updateCounter();
}

// Inicializar al cargar página
document.addEventListener('DOMContentLoaded', function() {
    FavoritesManager.initializeUI();
    FavoritesManager.updateCounter();
});
```

### Página de Favoritos (`public/favoritos.php`)

```php
<?php
// Este archivo maneja los favoritos del lado del servidor
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Favoritos - Inmobiliaria</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="css/favoritos.css">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-heart text-danger"></i> Mis Favoritos</h2>
            <span class="badge badge-primary" id="favoritesCounter">0</span>
        </div>
        
        <div id="favoritesContainer" class="row">
            <div id="noFavorites" class="col-12 text-center py-5" style="display: none;">
                <i class="fas fa-heart text-muted" style="font-size: 4rem;"></i>
                <h4 class="text-muted mt-3">No tienes favoritos</h4>
                <p class="text-muted">Explora nuestros inmuebles y agrega los que más te gusten</p>
                <a href="index.php" class="btn btn-primary">Ver Inmuebles</a>
            </div>
        </div>
    </div>

    <script src="js/favorites.js"></script>
    <script>
    // Cargar favoritos al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        loadFavorites();
    });

    async function loadFavorites() {
        const favoriteIds = FavoritesManager.getAll();
        
        if (favoriteIds.length === 0) {
            document.getElementById('noFavorites').style.display = 'block';
            return;
        }

        try {
            const response = await fetch('get_favorites.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ids: favoriteIds})
            });

            const inmuebles = await response.json();
            displayFavorites(inmuebles);
        } catch (error) {
            console.error('Error cargando favoritos:', error);
        }
    }

    function displayFavorites(inmuebles) {
        const container = document.getElementById('favoritesContainer');
        container.innerHTML = '';

        inmuebles.forEach(inmueble => {
            const card = createInmuebleCard(inmueble);
            container.appendChild(card);
        });
    }

    function createInmuebleCard(inmueble) {
        const col = document.createElement('div');
        col.className = 'col-lg-4 col-md-6 mb-4';
        
        col.innerHTML = `
            <div class="card h-100 inmueble-card">
                <div class="card-img-wrapper">
                    ${inmueble.imagen ? 
                        `<img src="../uploads/${inmueble.imagen}" class="card-img-top" alt="${inmueble.titulo}">` :
                        '<div class="no-image">Sin imagen</div>'
                    }
                    <button class="favorite-btn favorited" data-inmueble-id="${inmueble.id}" 
                            onclick="removeFavorite(${inmueble.id})">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>
                
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">${inmueble.titulo}</h5>
                    <p class="card-text text-muted">${inmueble.direccion}</p>
                    
                    <div class="caracteristicas mb-2">
                        ${inmueble.habitaciones ? `<span><i class="fas fa-bed"></i> ${inmueble.habitaciones}</span>` : ''}
                        ${inmueble.baños ? `<span><i class="fas fa-bath"></i> ${inmueble.baños}</span>` : ''}
                        ${inmueble.area_construida ? `<span><i class="fas fa-ruler-combined"></i> ${inmueble.area_construida}m²</span>` : ''}
                    </div>
                    
                    <div class="precio mb-3">
                        <strong>$${new Intl.NumberFormat('es-CO').format(inmueble.precio)}</strong>
                    </div>
                    
                    <div class="mt-auto">
                        <a href="inmueble.php?id=${inmueble.id}" class="btn btn-primary btn-block">
                            Ver Detalles
                        </a>
                    </div>
                </div>
            </div>
        `;
        
        return col;
    }

    function removeFavorite(inmuebleId) {
        FavoritesManager.remove(inmuebleId);
        loadFavorites(); // Recargar la lista
    }
    </script>
</body>
</html>
```

### API de Favoritos (`public/get_favorites.php`)

```php
<?php
require_once '../conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['ids']) || !is_array($input['ids'])) {
    echo json_encode([]);
    exit;
}

$ids = array_filter($input['ids'], 'is_numeric');

if (empty($ids)) {
    echo json_encode([]);
    exit;
}

try {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    $query = "
        SELECT id, titulo, descripcion, precio, direccion, ciudad, 
               habitaciones, baños, area_construida, imagen, tipo, operacion
        FROM inmuebles 
        WHERE id IN ($placeholders) AND estado = 'activo'
        ORDER BY fecha_creacion DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($ids);
    $inmuebles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($inmuebles);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener inmuebles']);
}
?>
```

---

## 🔧 Troubleshooting Común

### Problema: No se pueden subir archivos

**Error:** "Failed to move uploaded file"

**Soluciones:**
```bash
# 1. Verificar permisos directorio uploads
chmod 777 uploads/

# 2. Verificar configuración PHP
php -i | grep -E "(upload_max_filesize|post_max_size|max_execution_time)"

# 3. Editar php.ini si es necesario
upload_max_filesize = 20M
post_max_size = 25M
max_execution_time = 300
```

### Problema: Error de conexión a base de datos

**Error:** "Connection refused"

**Soluciones:**
```php
// 1. Verificar credenciales en conexion.php
$host = 'localhost';  // o 127.0.0.1
$dbname = 'inmobil';
$username = 'root';
$password = '';       // Vacío en XAMPP por defecto

// 2. Verificar que MySQL está ejecutando
// En XAMPP Control Panel, MySQL debe estar "Running"

// 3. Probar conexión directa
try {
    $pdo = new PDO("mysql:host=localhost;dbname=inmobil", "root", "");
    echo "Conexión exitosa";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
```

### Problema: Redirección infinita en login

**Error:** Loop infinito después de login

**Soluciones:**
```php
// 1. Verificar sesiones PHP
session_start();
print_r($_SESSION); // Ver qué hay en la sesión

// 2. Limpiar cookies/cache del navegador
// En Chrome: F12 > Application > Clear Storage

// 3. Verificar archivo AuthManager.php
// Asegurar que getRedirectUrl() retorna URLs correctas

// 4. Debug temporal en login_process.php
error_log("Usuario logueado: " . print_r($_SESSION, true));
error_log("Redirect URL: " . $redirectUrl);
```

### Problema: Mapas no cargan

**Error:** Mapa aparece gris o en blanco

**Soluciones:**
```javascript
// 1. Verificar consola del navegador (F12)
// Buscar errores de JavaScript

// 2. Verificar que Leaflet.js está cargando
console.log(typeof L); // Debe mostrar "object"

// 3. Verificar inicialización
const map = L.map('map', {
    center: [4.7109, -74.0721],
    zoom: 6,
    zoomControl: true
});

// 4. Verificar tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap',
    maxZoom: 18
}).addTo(map);
```

### Problema: Favoritos no persisten

**Error:** Favoritos se pierden al recargar

**Soluciones:**
```javascript
// 1. Verificar localStorage en navegador
console.log(localStorage.getItem('inmuebles_favoritos'));

// 2. Verificar que JavaScript se ejecuta
FavoritesManager.add(1); // Test manual

// 3. Verificar que eventos están vinculados
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado');
    FavoritesManager.initializeUI();
});
```

### Problema: Leads no se envían

**Error:** Formulario no responde

**Soluciones:**
```php
// 1. Verificar logs PHP
error_log("Lead recibido: " . print_r($_POST, true));

// 2. Verificar tabla leads existe
DESCRIBE leads;

// 3. Verificar procesar_lead.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    var_dump($_POST); // Debug temporal
    
    // Verificar campos requeridos
    if (!isset($_POST['nombre']) || empty($_POST['nombre'])) {
        echo json_encode(['error' => 'Nombre requerido']);
        exit;
    }
}
```

### Logs y Debugging

**Archivo de logs personalizado (`log.php`):**
```php
<?php
function writeLog($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
    file_put_contents('logs/system.log', $logEntry, FILE_APPEND | LOCK_EX);
}

// Usar en cualquier archivo
writeLog("Usuario " . $_SESSION['user_id'] . " accedió a inmuebles.php");
writeLog("Error al guardar inmueble: " . $e->getMessage(), 'ERROR');
```

**Ver logs en tiempo real:**
```bash
# En Linux/Mac
tail -f logs/system.log

# En Windows (PowerShell)
Get-Content logs/system.log -Wait -Tail 10
```

---

## 📱 Optimización Móvil

### Responsive Design

**Meta viewport en todas las páginas:**
```html
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
```

**CSS móvil específico:**
```css
/* css/mobile.css */
@media (max-width: 768px) {
    .inmueble-card {
        margin-bottom: 1rem;
    }
    
    .map-filters .form-control {
        margin-bottom: 0.5rem;
    }
    
    .popup-inmueble {
        max-width: 250px;
    }
    
    .popup-image {
        max-height: 120px;
        object-fit: cover;
    }
    
    .lead-form {
        padding: 1rem;
    }
    
    .dashboard-stats {
        display: flex;
        flex-wrap: wrap;
    }
    
    .stat-card {
        flex: 1 1 50%;
        min-width: 150px;
    }
}

@media (max-width: 576px) {
    .container {
        padding: 0 10px;
    }
    
    .card-body {
        padding: 1rem 0.75rem;
    }
    
    .btn-block {
        font-size: 0.9rem;
    }
}
```

### Touch Interactions

**JavaScript para touch:**
```javascript
// Detectar dispositivos táctiles
function isTouchDevice() {
    return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
}

// Optimizar botones para touch
if (isTouchDevice()) {
    document.querySelectorAll('.btn').forEach(btn => {
        btn.style.minHeight = '44px'; // Tamaño mínimo recomendado
        btn.style.padding = '12px 16px';
    });
}

// Swipe en cards de inmuebles (opcional)
let startX, startY, currentX, currentY;

document.querySelectorAll('.inmueble-card').forEach(card => {
    card.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
    });
    
    card.addEventListener('touchmove', function(e) {
        currentX = e.touches[0].clientX;
        currentY = e.touches[0].clientY;
    });
    
    card.addEventListener('touchend', function() {
        const diffX = startX - currentX;
        const diffY = startY - currentY;
        
        // Swipe horizontal para favoritos
        if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
            if (diffX > 0) {
                // Swipe izquierda - agregar favorito
                const inmuebleId = this.dataset.inmuebleId;
                if (inmuebleId) {
                    toggleFavorite(parseInt(inmuebleId));
                }
            }
        }
    });
});
```

---

## 🔒 Seguridad

### Validación de Entrada

```php
// functions/security.php
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePhone($phone) {
    return preg_match('/^[+]?[0-9\s\-\(\)]{7,15}$/', $phone);
}

function validatePrice($price) {
    return is_numeric($price) && $price > 0;
}

// Usar en formularios
$nombre = sanitizeInput($_POST['nombre']);
$email = sanitizeInput($_POST['email']);

if (!validateEmail($email)) {
    die('Email inválido');
}
```

### Protección CSRF

```php
// functions/csrf.php
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// En formularios
<input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

// Al procesar
if (!validateCSRFToken($_POST['csrf_token'])) {
    die('Token CSRF inválido');
}
```

### Upload Seguro

```php
// functions/upload.php
function secureFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']) {
    $uploadDir = 'uploads/';
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    // Verificar errores
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Error en upload'];
    }
    
    // Verificar tamaño
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'Archivo muy grande'];
    }
    
    // Verificar tipo MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    $allowedMimes = [
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif',
        'video/mp4', 'video/avi'
    ];
    
    if (!in_array($mimeType, $allowedMimes)) {
        return ['success' => false, 'error' => 'Tipo de archivo no permitido'];
    }
    
    // Generar nombre único
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $uploadPath = $uploadDir . $fileName;
    
    // Mover archivo
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'filename' => $fileName];
    }
    
    return ['success' => false, 'error' => 'Error al guardar archivo'];
}
```

---

**Tutorial desarrollado para el Sistema de Gestión Inmobiliaria**  
*Versión 1.0 - Diciembre 2024*