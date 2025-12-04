# Sistema de Gestión Inmobiliaria

Sistema completo de gestión inmobiliaria desarrollado en PHP/MySQL con autenticación por roles, mapas interactivos, sistema de leads y gestión integral de propiedades.

## 🏗️ Arquitectura del Sistema

### Tecnologías Principales
- **Backend**: PHP 7.4+, MySQL 8.0+
- **Frontend**: Bootstrap 4.6, JavaScript ES6, Leaflet.js
- **Servidor**: Apache 2.4 (XAMPP)
- **Mapas**: Leaflet.js con OpenStreetMap
- **Base de Datos**: MySQL con estructura relacional

### Estructura del Proyecto

```
INMOBILIARIA_1/
├── admin/                  # Panel administrativo
│   ├── dashboard.php      # Dashboard principal admin
│   ├── dashboard_simple.php # Dashboard estadísticas
│   └── users_manage.php   # Gestión de usuarios
├── auth/                   # Sistema de autenticación
│   ├── AuthManager.php    # Clase principal de auth
│   ├── login.php         # Formulario de login
│   ├── login_process.php # Procesamiento login
│   └── logout.php        # Cerrar sesión
├── public/                 # Área pública/catálogo
│   ├── index.php         # Catálogo público
│   ├── mapa.php          # Mapa público
│   ├── favoritos.php     # Sistema favoritos
│   ├── inmueble.php      # Detalle inmueble
│   ├── procesar_lead.php # Captura leads
│   ├── css/              # Estilos públicos
│   └── js/               # Scripts públicos
├── css/                    # Estilos sistema
├── js/                     # Scripts sistema
├── uploads/               # Archivos multimedia
├── debug/                 # Scripts debugging
└── [módulos_gestión].php  # CRUD inmuebles/usuarios
```

## 👥 Sistema de Roles

### Jerarquía de Usuarios (4 niveles)

1. **Administrador** (`administrador`)
   - Acceso total al sistema
   - Gestión de usuarios y roles
   - Estadísticas completas
   - Configuración del sistema

2. **Secretaria** (`secretaria`)
   - Gestión de clientes y propietarios
   - Creación de contratos
   - Programación de visitas
   - Acceso a reportes

3. **Agente Senior** (`agente_senior`)
   - Gestión de inmuebles asignados
   - Seguimiento de leads
   - Edición de propiedades
   - Reportes de ventas

4. **Agente Junior** (`agente_junior`)
   - Visualización de inmuebles
   - Captura básica de leads
   - Consulta de información
   - Sin permisos de edición

### Permisos por Módulo

| Módulo | Admin | Secretaria | Agente Senior | Agente Junior |
|--------|-------|------------|---------------|---------------|
| Usuarios | ✅ CRUD | ❌ | ❌ | ❌ |
| Inmuebles | ✅ CRUD | ✅ CRUD | ✅ CRU | 👁️ R |
| Propietarios | ✅ CRUD | ✅ CRUD | 👁️ R | 👁️ R |
| Clientes | ✅ CRUD | ✅ CRUD | ✅ CRUD | 👁️ R |
| Contratos | ✅ CRUD | ✅ CRUD | 👁️ R | ❌ |
| Leads | ✅ CRUD | ✅ CRUD | ✅ CRUD | ✅ C |
| Reportes | ✅ Todo | ✅ Básico | ✅ Asignados | ❌ |

## 🗄️ Base de Datos

### Tablas Principales

#### `usuarios`
```sql
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('administrador', 'secretaria', 'agente_senior', 'agente_junior') NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### `inmuebles`
```sql
CREATE TABLE inmuebles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(15,2) NOT NULL,
    tipo ENUM('casa', 'apartamento', 'oficina', 'local', 'lote', 'bodega'),
    operacion ENUM('venta', 'arriendo'),
    direccion VARCHAR(300),
    ciudad VARCHAR(100),
    barrio VARCHAR(100),
    latitud DECIMAL(10,8),
    longitud DECIMAL(11,8),
    area_construida INT,
    area_lote INT,
    habitaciones INT,
    baños INT,
    garajes INT,
    imagen VARCHAR(255),
    imagen_secundaria VARCHAR(255),
    video VARCHAR(255),
    propietario_id INT,
    agente_id INT,
    estado ENUM('activo', 'vendido', 'arrendado', 'suspendido') DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (propietario_id) REFERENCES propietarios(id),
    FOREIGN KEY (agente_id) REFERENCES usuarios(id)
);
```

#### `leads`
```sql
CREATE TABLE leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inmueble_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    mensaje TEXT,
    origen ENUM('web', 'whatsapp', 'telefono', 'referido') DEFAULT 'web',
    estado ENUM('nuevo', 'contactado', 'interesado', 'cerrado', 'perdido') DEFAULT 'nuevo',
    agente_asignado INT,
    fecha_contacto TIMESTAMP NULL,
    notas TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inmueble_id) REFERENCES inmuebles(id),
    FOREIGN KEY (agente_asignado) REFERENCES usuarios(id)
);
```

### Relaciones Clave
- `inmuebles.propietario_id → propietarios.id`
- `inmuebles.agente_id → usuarios.id`
- `leads.inmueble_id → inmuebles.id`
- `leads.agente_asignado → usuarios.id`
- `contratos.inmueble_id → inmuebles.id`
- `visitas.inmueble_id → inmuebles.id`

## 🔐 Sistema de Autenticación

### AuthManager.php - Funcionalidades

```php
class AuthManager {
    // Verificar credenciales y crear sesión
    public function login($email, $password)
    
    // Verificar si usuario está autenticado
    public function isAuthenticated()
    
    // Obtener datos del usuario actual
    public function getCurrentUser()
    
    // Verificar permisos específicos
    public function hasPermission($module, $action)
    
    // Requerir rol específico (con redirección)
    public function requireRole($allowedRoles)
    
    // Redirección inteligente por rol
    public function getRedirectUrl($userRole)
    
    // Cerrar sesión
    public function logout()
}
```

### Flujo de Autenticación

1. **Login**: `auth/login.php` → `login_process.php`
2. **Validación**: Verificar credenciales en BD
3. **Sesión**: Crear `$_SESSION['user_id']` y `$_SESSION['user_role']`
4. **Redirección**: Según rol del usuario
5. **Protección**: Cada página verifica autenticación

### Rutas de Redirección

```php
$redirectRoutes = [
    'administrador' => '/admin/dashboard_simple.php',
    'secretaria' => '/index.php',
    'agente_senior' => '/index.php',
    'agente_junior' => '/index.php'
];
```

## 🗺️ Sistema de Mapas

### Implementación Leaflet.js

#### Mapa Principal (`public/mapa.php`)
```javascript
// Inicialización mapa
const map = L.map('map').setView([4.7109, -74.0721], 6);

// Tiles OpenStreetMap
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

// Marcadores dinámicos desde BD
fetch('get_inmuebles_api.php')
    .then(response => response.json())
    .then(data => {
        data.forEach(inmueble => {
            const marker = L.marker([inmueble.latitud, inmueble.longitud])
                .bindPopup(createPopupContent(inmueble))
                .addTo(map);
        });
    });
```

#### Funcionalidades del Mapa
- **Geolocalización**: Centrado automático en ubicación del usuario
- **Filtros dinámicos**: Por tipo, precio, ciudad
- **Popups informativos**: Imagen, precio, detalles básicos
- **Integración favoritos**: Botón ♥️ en cada popup
- **WhatsApp directo**: Enlace desde popup

## ❤️ Sistema de Favoritos

### Implementación localStorage

```javascript
// Gestión de favoritos en cliente
class FavoritesManager {
    static add(inmuebleId) {
        let favorites = this.getAll();
        if (!favorites.includes(inmuebleId)) {
            favorites.push(inmuebleId);
            localStorage.setItem('inmuebles_favoritos', JSON.stringify(favorites));
        }
    }
    
    static remove(inmuebleId) {
        let favorites = this.getAll();
        favorites = favorites.filter(id => id !== inmuebleId);
        localStorage.setItem('inmuebles_favoritos', JSON.stringify(favorites));
    }
    
    static getAll() {
        return JSON.parse(localStorage.getItem('inmuebles_favoritos') || '[]');
    }
}
```

### Sincronización con Backend
- `public/get_favorites.php`: API para obtener detalles de favoritos
- Persistencia en localStorage (no requiere login)
- Visualización en `public/favoritos.php`

## 📞 Sistema de Leads

### Captura de Leads

#### Formulario Público
```html
<!-- En public/inmueble.php -->
<form id="leadForm" class="lead-form">
    <input type="hidden" name="inmueble_id" value="<?= $inmueble['id'] ?>">
    <input type="text" name="nombre" placeholder="Tu nombre" required>
    <input type="tel" name="telefono" placeholder="Tu teléfono" required>
    <input type="email" name="email" placeholder="Tu email">
    <textarea name="mensaje" placeholder="Mensaje adicional"></textarea>
    <button type="submit">Solicitar Información</button>
</form>
```

#### Procesamiento Backend (`public/procesar_lead.php`)
```php
// Capturar lead
$stmt = $pdo->prepare("
    INSERT INTO leads (inmueble_id, nombre, telefono, email, mensaje, origen) 
    VALUES (?, ?, ?, ?, ?, 'web')
");
$stmt->execute([$inmueble_id, $nombre, $telefono, $email, $mensaje]);

// Generar enlace WhatsApp
$whatsapp_msg = "Hola, me interesa el inmueble: " . $inmueble['titulo'];
$whatsapp_url = "https://wa.me/573001234567?text=" . urlencode($whatsapp_msg);

return json_encode(['success' => true, 'whatsapp_url' => $whatsapp_url]);
```

### Gestión de Leads

#### Dashboard de Leads
- **Nuevos**: Leads sin contactar (resaltados)
- **En proceso**: Leads contactados
- **Convertidos**: Leads que resultaron en venta/arriendo
- **Perdidos**: Leads no interesados

#### Asignación Automática
```php
// En procesar_lead.php
$agente_id = getAgenteDisponible($inmueble['ciudad']);
updateLead($lead_id, ['agente_asignado' => $agente_id]);
```

## 📊 Dashboard y Estadísticas

### Dashboard Administrativo (`admin/dashboard_simple.php`)

#### Métricas Principales
1. **Total Inmuebles**: Activos/Vendidos/Arrendados
2. **Total Propietarios**: Registrados en sistema
3. **Leads del Mes**: Nuevos/Contactados/Convertidos
4. **Usuarios Activos**: Por rol
5. **Contratos**: Firmados/Pendientes
6. **Visitas**: Programadas/Realizadas
7. **Inspecciones**: Pendientes/Completadas
8. **Oficinas**: Activas en el sistema

```php
// Ejemplo consulta estadísticas
$stats = [
    'inmuebles' => $pdo->query("SELECT COUNT(*) FROM inmuebles WHERE estado = 'activo'")->fetchColumn(),
    'leads_mes' => $pdo->query("SELECT COUNT(*) FROM leads WHERE MONTH(fecha_creacion) = MONTH(CURRENT_DATE)")->fetchColumn(),
    'contratos_activos' => $pdo->query("SELECT COUNT(*) FROM contratos WHERE estado = 'activo'")->fetchColumn()
];
```

### Dashboard Operacional (`index.php`)

#### Vista Adaptativa por Rol
```php
// Permisos dinámicos
if (in_array($user['rol'], ['secretaria', 'agente_senior'])) {
    echo '<a href="inmuebles.php" class="nav-link">Gestionar Inmuebles</a>';
}

if ($user['rol'] === 'administrador') {
    echo '<a href="admin/users_manage.php" class="nav-link">Gestión Usuarios</a>';
}
```

## 🔍 Testing y Debugging

### Scripts de Debugging

#### `debug/diagnose_users.php`
```php
// Verificar estructura usuarios
$users = $pdo->query("SELECT id, nombre, email, rol, activo FROM usuarios")->fetchAll();
foreach ($users as $user) {
    echo "Usuario: {$user['nombre']} - Rol: {$user['rol']} - Activo: {$user['activo']}\n";
}
```

#### `debug/test_login_direct.php`
```php
// Simular login para testing
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'administrador';
header('Location: /admin/dashboard_simple.php');
```

#### `debug/check_database_structure.php`
```php
// Verificar estructura tablas
$tables = ['usuarios', 'inmuebles', 'propietarios', 'leads', 'contratos'];
foreach ($tables as $table) {
    $columns = $pdo->query("DESCRIBE $table")->fetchAll();
    echo "Tabla $table:\n";
    foreach ($columns as $column) {
        echo "  - {$column['Field']}: {$column['Type']}\n";
    }
}
```

### Tests Recomendados

#### 1. Test de Autenticación
```php
// Verificar login con credenciales válidas
$auth = new AuthManager();
$result = $auth->login('admin@test.com', 'password123');
assert($result === true, 'Login debe ser exitoso');

// Verificar redirección por rol
$redirect = $auth->getRedirectUrl('administrador');
assert($redirect === '/admin/dashboard_simple.php', 'Redirección admin incorrecta');
```

#### 2. Test de Permisos
```php
// Verificar permisos por rol
assert($auth->hasPermission('usuarios', 'create') === true); // Admin
$auth->setCurrentUser('agente_junior');
assert($auth->hasPermission('usuarios', 'create') === false); // Agente junior
```

#### 3. Test de API Leads
```php
// Test captura de lead
$data = [
    'inmueble_id' => 1,
    'nombre' => 'Test User',
    'telefono' => '3001234567',
    'email' => 'test@example.com'
];

$response = file_get_contents('public/procesar_lead.php?' . http_build_query($data));
$result = json_decode($response, true);
assert($result['success'] === true, 'Lead debe guardarse exitosamente');
```

## 🐳 Recomendaciones para Docker

### Dockerfile Propuesto

```dockerfile
FROM php:7.4-apache

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Copiar código fuente
COPY . /var/www/html/

# Permisos para uploads
RUN chmod -R 755 /var/www/html/uploads/
RUN chown -R www-data:www-data /var/www/html/

# Puerto
EXPOSE 80
```

### docker-compose.yml

```yaml
version: '3.8'

services:
  web:
    build: .
    ports:
      - "8080:80"
    depends_on:
      - db
    environment:
      - DB_HOST=db
      - DB_NAME=inmobil
      - DB_USER=root
      - DB_PASSWORD=rootpassword
    volumes:
      - ./uploads:/var/www/html/uploads

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: inmobil
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql
      - ./database/init.sql:/docker-entrypoint-initdb.d/init.sql

volumes:
  mysql_data:
```

### Configuración para Producción

#### Variables de Entorno
```env
# .env
DB_HOST=localhost
DB_NAME=inmobil
DB_USER=inmobil_user
DB_PASSWORD=secure_password_here
WHATSAPP_NUMBER=573001234567
MAPS_API_KEY=optional_for_geocoding
APP_ENV=production
DEBUG_MODE=false
```

#### Configuración Apache
```apache
# .htaccess
RewriteEngine On

# Redirect HTTP to HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

### Optimizaciones de Rendimiento

#### 1. Cache de Consultas
```php
// Implementar cache Redis para consultas frecuentes
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$cacheKey = "inmuebles_activos";
$inmuebles = $redis->get($cacheKey);

if (!$inmuebles) {
    $inmuebles = getInmueblesFromDB();
    $redis->setex($cacheKey, 300, serialize($inmuebles)); // 5 min cache
}
```

#### 2. Compresión de Imágenes
```php
// Auto-resize de imágenes upload
function resizeImage($source, $destination, $maxWidth = 800) {
    $image = imagecreatefromjpeg($source);
    $width = imagesx($image);
    $height = imagesy($image);
    
    if ($width > $maxWidth) {
        $newWidth = $maxWidth;
        $newHeight = ($height * $maxWidth) / $width;
        
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagejpeg($newImage, $destination, 85);
    }
}
```

#### 3. Lazy Loading
```javascript
// Lazy loading para imágenes del catálogo
const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.dataset.src;
            img.classList.remove('lazy');
            observer.unobserve(img);
        }
    });
});

document.querySelectorAll('img[data-src]').forEach(img => {
    imageObserver.observe(img);
});
```

## 🚀 Guía de Despliegue

### Pre-requisitos Producción
- **PHP**: 7.4 o superior
- **MySQL**: 8.0 o superior  
- **Apache**: 2.4 con mod_rewrite
- **SSL**: Certificado válido
- **Memoria**: Mínimo 512MB RAM
- **Disco**: 2GB disponibles

### Checklist de Despliegue

#### ✅ Preparación
- [ ] Backup completo de datos
- [ ] Variables de entorno configuradas
- [ ] SSL certificado instalado
- [ ] Dominio DNS configurado

#### ✅ Base de Datos
- [ ] Usuario específico creado (no root)
- [ ] Permisos mínimos asignados
- [ ] Índices optimizados
- [ ] Backup automático configurado

#### ✅ Seguridad
- [ ] Passwords fuertes en producción
- [ ] Archivos de configuración protegidos
- [ ] Directory listing deshabilitado
- [ ] Error reporting desactivado

#### ✅ Performance
- [ ] Cache habilitado
- [ ] Compresión gzip activada
- [ ] CDN para assets estáticos
- [ ] Monitoring configurado

### Comandos de Despliegue

```bash
# 1. Clonar repositorio
git clone https://github.com/frantastico-rgb/inmobiliaria.git
cd inmobiliaria

# 2. Configurar permisos
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 777 uploads/

# 3. Configurar base de datos
mysql -u root -p < database/init.sql

# 4. Configurar Apache virtual host
sudo cp deploy/apache-vhost.conf /etc/apache2/sites-available/inmobiliaria.conf
sudo a2ensite inmobiliaria
sudo systemctl reload apache2

# 5. Configurar SSL (Let's Encrypt)
sudo certbot --apache -d tudominio.com
```

## 📋 Mantenimiento

### Tareas Regulares

#### Diarias
- Monitor de leads nuevos
- Backup incremental BD
- Review logs de error

#### Semanales  
- Limpieza archivos temporales
- Optimización tablas MySQL
- Review métricas rendimiento

#### Mensuales
- Backup completo sistema
- Actualización dependencias
- Auditoría de seguridad

### Monitoreo Recomendado

```php
// health-check.php
$checks = [
    'database' => checkDatabaseConnection(),
    'uploads' => is_writable('./uploads/'),
    'memory' => memory_get_usage() < (256 * 1024 * 1024), // 256MB
    'disk' => disk_free_space('.') > (1024 * 1024 * 1024) // 1GB
];

header('Content-Type: application/json');
echo json_encode(['status' => 'healthy', 'checks' => $checks]);
```

---

## 📞 Soporte

### Logs del Sistema
- **Apache**: `/var/log/apache2/error.log`
- **PHP**: `/var/log/php_errors.log`  
- **MySQL**: `/var/log/mysql/error.log`
- **Custom**: `log.php` (logs aplicación)

### Troubleshooting Común

#### Error: "No se pueden subir archivos"
```bash
# Verificar permisos uploads/
chmod -R 777 uploads/
# Verificar configuración PHP
php -i | grep upload_max_filesize
```

#### Error: "No se conecta a la base de datos"
```bash
# Verificar conexión MySQL
mysql -u usuario -p -h host inmobil
# Verificar credenciales en conexion.php
```

#### Error: "Redirección infinita en login"
```bash
# Verificar sesiones PHP
ls -la /tmp/ | grep sess_
# Limpiar cache navegador
```

---

**Sistema desarrollado para gestión inmobiliaria integral**  
*Versión 1.0 - Diciembre 2024*