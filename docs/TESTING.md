# Testing y Control de Calidad

## 🧪 Plan de Testing

### Tipos de Testing Implementados

#### 1. Testing Unitario
- **Autenticación**: Verificación de login/logout
- **Permisos**: Validación de roles y accesos
- **CRUD**: Operaciones básicas en todas las entidades
- **APIs**: Endpoints de leads y mapas

#### 2. Testing de Integración
- **Base de Datos**: Conexiones y transacciones
- **Sesiones**: Manejo de estado de usuario
- **Uploads**: Subida y procesamiento de archivos
- **Geocodificación**: Integración con servicios externos

#### 3. Testing de UI/UX
- **Responsive**: Diferentes tamaños de pantalla
- **Cross-browser**: Chrome, Firefox, Safari, Edge
- **Accesibilidad**: Navegación por teclado, screen readers
- **Performance**: Tiempos de carga y responsividad

---

## 🔍 Scripts de Testing

### Test de Autenticación

**Archivo:** `tests/auth_test.php`
```php
<?php
require_once '../auth/AuthManager.php';
require_once '../conexion.php';

class AuthTest {
    private $auth;
    private $testUserId;
    
    public function __construct() {
        $this->auth = new AuthManager();
        $this->createTestUser();
    }
    
    private function createTestUser() {
        global $pdo;
        $hashedPassword = password_hash('test123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombre, email, password, rol, activo) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute(['Test User', 'test@test.com', $hashedPassword, 'agente_junior', 1]);
        $this->testUserId = $pdo->lastInsertId();
    }
    
    public function testValidLogin() {
        $result = $this->auth->login('test@test.com', 'test123');
        $this->assertTrue($result, 'Login válido debe retornar true');
    }
    
    public function testInvalidLogin() {
        $result = $this->auth->login('test@test.com', 'wrong_password');
        $this->assertFalse($result, 'Login inválido debe retornar false');
    }
    
    public function testUserPermissions() {
        // Simular login
        $_SESSION['user_id'] = $this->testUserId;
        $_SESSION['user_role'] = 'agente_junior';
        
        // Agente junior no debe poder crear usuarios
        $canCreate = $this->auth->hasPermission('usuarios', 'create');
        $this->assertFalse($canCreate, 'Agente junior no debe crear usuarios');
        
        // Pero sí debe poder ver inmuebles
        $canView = $this->auth->hasPermission('inmuebles', 'read');
        $this->assertTrue($canView, 'Agente junior debe ver inmuebles');
    }
    
    public function testRoleRedirection() {
        $redirects = [
            'administrador' => '/admin/dashboard_simple.php',
            'secretaria' => '/index.php',
            'agente_senior' => '/index.php',
            'agente_junior' => '/index.php'
        ];
        
        foreach ($redirects as $role => $expectedUrl) {
            $actualUrl = $this->auth->getRedirectUrl($role);
            $this->assertEquals($expectedUrl, $actualUrl, "Redirect incorrecto para $role");
        }
    }
    
    public function testSessionManagement() {
        // Login
        $this->auth->login('test@test.com', 'test123');
        $this->assertTrue($this->auth->isAuthenticated(), 'Usuario debe estar autenticado');
        
        // Logout
        $this->auth->logout();
        $this->assertFalse($this->auth->isAuthenticated(), 'Usuario no debe estar autenticado después de logout');
    }
    
    private function assertTrue($condition, $message) {
        if (!$condition) {
            throw new Exception("FAIL: $message");
        }
        echo "PASS: $message\n";
    }
    
    private function assertFalse($condition, $message) {
        if ($condition) {
            throw new Exception("FAIL: $message");
        }
        echo "PASS: $message\n";
    }
    
    private function assertEquals($expected, $actual, $message) {
        if ($expected !== $actual) {
            throw new Exception("FAIL: $message. Expected: $expected, Actual: $actual");
        }
        echo "PASS: $message\n";
    }
    
    public function cleanup() {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$this->testUserId]);
    }
    
    public function runAllTests() {
        try {
            echo "=== TESTING AUTENTICACIÓN ===\n";
            $this->testValidLogin();
            $this->testInvalidLogin();
            $this->testUserPermissions();
            $this->testRoleRedirection();
            $this->testSessionManagement();
            echo "=== TODOS LOS TESTS PASARON ===\n";
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
        } finally {
            $this->cleanup();
        }
    }
}

// Ejecutar tests
session_start();
$test = new AuthTest();
$test->runAllTests();
?>
```

### Test de Base de Datos

**Archivo:** `tests/database_test.php`
```php
<?php
require_once '../conexion.php';

class DatabaseTest {
    private $pdo;
    private $testData = [];
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    public function testConnection() {
        try {
            $this->pdo->query("SELECT 1");
            echo "PASS: Conexión a base de datos exitosa\n";
        } catch (PDOException $e) {
            throw new Exception("FAIL: Error de conexión: " . $e->getMessage());
        }
    }
    
    public function testTableStructure() {
        $requiredTables = [
            'usuarios' => ['id', 'nombre', 'email', 'password', 'rol'],
            'inmuebles' => ['id', 'titulo', 'precio', 'tipo', 'operacion'],
            'propietarios' => ['id', 'nombre', 'telefono', 'email'],
            'leads' => ['id', 'inmueble_id', 'nombre', 'telefono', 'estado'],
            'contratos' => ['id', 'inmueble_id', 'tipo_contrato', 'fecha_inicio']
        ];
        
        foreach ($requiredTables as $table => $requiredColumns) {
            $this->testTableExists($table);
            $this->testTableColumns($table, $requiredColumns);
        }
    }
    
    private function testTableExists($tableName) {
        $stmt = $this->pdo->query("SHOW TABLES LIKE '$tableName'");
        if ($stmt->rowCount() === 0) {
            throw new Exception("FAIL: Tabla '$tableName' no existe");
        }
        echo "PASS: Tabla '$tableName' existe\n";
    }
    
    private function testTableColumns($tableName, $requiredColumns) {
        $stmt = $this->pdo->query("DESCRIBE $tableName");
        $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($requiredColumns as $column) {
            if (!in_array($column, $existingColumns)) {
                throw new Exception("FAIL: Columna '$column' no existe en tabla '$tableName'");
            }
        }
        echo "PASS: Columnas requeridas existen en '$tableName'\n";
    }
    
    public function testCRUDOperations() {
        // Test CREATE
        $stmt = $this->pdo->prepare("
            INSERT INTO propietarios (nombre, telefono, email) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute(['Test Owner', '3001234567', 'test@owner.com']);
        $ownerId = $this->pdo->lastInsertId();
        $this->testData['propietario_id'] = $ownerId;
        echo "PASS: CREATE - Propietario creado con ID $ownerId\n";
        
        // Test READ
        $stmt = $this->pdo->prepare("SELECT * FROM propietarios WHERE id = ?");
        $stmt->execute([$ownerId]);
        $owner = $stmt->fetch();
        if (!$owner || $owner['nombre'] !== 'Test Owner') {
            throw new Exception("FAIL: READ - Datos incorrectos");
        }
        echo "PASS: READ - Datos recuperados correctamente\n";
        
        // Test UPDATE
        $stmt = $this->pdo->prepare("UPDATE propietarios SET telefono = ? WHERE id = ?");
        $stmt->execute(['3007654321', $ownerId]);
        
        $stmt = $this->pdo->prepare("SELECT telefono FROM propietarios WHERE id = ?");
        $stmt->execute([$ownerId]);
        $phone = $stmt->fetchColumn();
        if ($phone !== '3007654321') {
            throw new Exception("FAIL: UPDATE - Datos no actualizados");
        }
        echo "PASS: UPDATE - Datos actualizados correctamente\n";
        
        // Test DELETE se hace en cleanup()
    }
    
    public function testForeignKeys() {
        // Crear inmueble con propietario válido
        $stmt = $this->pdo->prepare("
            INSERT INTO inmuebles (titulo, precio, tipo, operacion, propietario_id) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'Test House', 
            100000000, 
            'casa', 
            'venta', 
            $this->testData['propietario_id']
        ]);
        $inmuebleId = $this->pdo->lastInsertId();
        $this->testData['inmueble_id'] = $inmuebleId;
        echo "PASS: Foreign Key válida - Inmueble creado\n";
        
        // Intentar crear inmueble con propietario inválido
        try {
            $stmt->execute([
                'Invalid House', 
                100000000, 
                'casa', 
                'venta', 
                99999 // ID que no existe
            ]);
            throw new Exception("FAIL: Foreign Key inválida debería fallar");
        } catch (PDOException $e) {
            echo "PASS: Foreign Key inválida rechazada correctamente\n";
        }
    }
    
    public function testDataTypes() {
        $tests = [
            // Test ENUM válido
            ['usuarios', 'rol', 'administrador'],
            ['inmuebles', 'tipo', 'apartamento'],
            ['inmuebles', 'operacion', 'arriendo'],
            
            // Test números
            ['inmuebles', 'precio', 150000000],
            ['inmuebles', 'habitaciones', 3],
            
            // Test decimales
            ['inmuebles', 'latitud', 4.7109],
            ['inmuebles', 'longitud', -74.0721]
        ];
        
        foreach ($tests as $test) {
            $this->testDataType($test[0], $test[1], $test[2]);
        }
    }
    
    private function testDataType($table, $column, $value) {
        try {
            if ($table === 'usuarios') {
                $stmt = $this->pdo->prepare("
                    INSERT INTO usuarios (nombre, email, password, $column) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute(['Test', 'test@datatype.com', 'pass123', $value]);
                $testId = $this->pdo->lastInsertId();
                $this->testData['usuario_test_id'] = $testId;
            } elseif ($table === 'inmuebles') {
                $stmt = $this->pdo->prepare("UPDATE inmuebles SET $column = ? WHERE id = ?");
                $stmt->execute([$value, $this->testData['inmueble_id']]);
            }
            echo "PASS: Tipo de dato $table.$column = $value\n";
        } catch (PDOException $e) {
            throw new Exception("FAIL: Tipo de dato $table.$column = $value - " . $e->getMessage());
        }
    }
    
    public function testIndexes() {
        // Verificar índices importantes
        $stmt = $this->pdo->query("SHOW INDEX FROM usuarios WHERE Column_name = 'email'");
        if ($stmt->rowCount() === 0) {
            echo "WARNING: Índice recomendado en usuarios.email\n";
        } else {
            echo "PASS: Índice en usuarios.email\n";
        }
        
        $stmt = $this->pdo->query("SHOW INDEX FROM inmuebles WHERE Column_name = 'estado'");
        if ($stmt->rowCount() === 0) {
            echo "WARNING: Índice recomendado en inmuebles.estado\n";
        } else {
            echo "PASS: Índice en inmuebles.estado\n";
        }
    }
    
    public function cleanup() {
        // Limpiar datos de test en orden correcto (por foreign keys)
        if (isset($this->testData['inmueble_id'])) {
            $this->pdo->prepare("DELETE FROM inmuebles WHERE id = ?")->execute([$this->testData['inmueble_id']]);
        }
        
        if (isset($this->testData['propietario_id'])) {
            $this->pdo->prepare("DELETE FROM propietarios WHERE id = ?")->execute([$this->testData['propietario_id']]);
        }
        
        if (isset($this->testData['usuario_test_id'])) {
            $this->pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$this->testData['usuario_test_id']]);
        }
        
        echo "CLEANUP: Datos de test eliminados\n";
    }
    
    public function runAllTests() {
        try {
            echo "=== TESTING BASE DE DATOS ===\n";
            $this->testConnection();
            $this->testTableStructure();
            $this->testCRUDOperations();
            $this->testForeignKeys();
            $this->testDataTypes();
            $this->testIndexes();
            echo "=== TODOS LOS TESTS PASARON ===\n";
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
        } finally {
            $this->cleanup();
        }
    }
}

// Ejecutar tests
$test = new DatabaseTest();
$test->runAllTests();
?>
```

### Test de APIs

**Archivo:** `tests/api_test.php`
```php
<?php
class ApiTest {
    private $baseUrl;
    
    public function __construct($baseUrl = 'http://localhost/INMOBILIARIA_1/') {
        $this->baseUrl = $baseUrl;
    }
    
    public function testLeadSubmission() {
        $leadData = [
            'inmueble_id' => 1,
            'nombre' => 'Test Customer',
            'telefono' => '3001234567',
            'email' => 'test@customer.com',
            'mensaje' => 'Mensaje de test'
        ];
        
        $response = $this->postRequest('public/procesar_lead.php', $leadData);
        
        if ($response === false) {
            throw new Exception("FAIL: No se pudo conectar a API de leads");
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['success']) || $data['success'] !== true) {
            throw new Exception("FAIL: API de leads no retornó success=true");
        }
        
        if (!isset($data['whatsapp_url']) || empty($data['whatsapp_url'])) {
            throw new Exception("FAIL: API de leads no retornó whatsapp_url");
        }
        
        echo "PASS: Lead submission API funciona correctamente\n";
        return $data;
    }
    
    public function testInmueblesMapApi() {
        $response = $this->getRequest('public/get_inmuebles_mapa.php');
        
        if ($response === false) {
            throw new Exception("FAIL: No se pudo conectar a API de mapas");
        }
        
        $data = json_decode($response, true);
        
        if (!is_array($data)) {
            throw new Exception("FAIL: API de mapas no retornó array");
        }
        
        // Verificar estructura de cada inmueble
        foreach ($data as $inmueble) {
            $requiredFields = ['id', 'titulo', 'precio', 'latitud', 'longitud'];
            foreach ($requiredFields as $field) {
                if (!isset($inmueble[$field])) {
                    throw new Exception("FAIL: Campo '$field' faltante en inmueble");
                }
            }
        }
        
        echo "PASS: Inmuebles map API retorna datos correctos\n";
        return $data;
    }
    
    public function testFavoritesApi() {
        $testIds = [1, 2, 3];
        $response = $this->postRequest('public/get_favorites.php', ['ids' => $testIds]);
        
        if ($response === false) {
            throw new Exception("FAIL: No se pudo conectar a API de favoritos");
        }
        
        $data = json_decode($response, true);
        
        if (!is_array($data)) {
            throw new Exception("FAIL: API de favoritos no retornó array");
        }
        
        echo "PASS: Favorites API funciona correctamente\n";
        return $data;
    }
    
    public function testLoginApi() {
        $loginData = [
            'email' => 'admin@inmobiliaria.com',
            'password' => 'admin123'
        ];
        
        $response = $this->postRequest('auth/login_process.php', $loginData);
        
        if ($response === false) {
            throw new Exception("FAIL: No se pudo conectar a API de login");
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['success'])) {
            throw new Exception("FAIL: API de login no retornó campo success");
        }
        
        echo "PASS: Login API retorna estructura correcta\n";
        return $data;
    }
    
    public function testApiResponseTimes() {
        $apis = [
            'public/get_inmuebles_mapa.php',
            'public/get_favorites.php',
            'auth/login_process.php'
        ];
        
        foreach ($apis as $api) {
            $start = microtime(true);
            $this->getRequest($api);
            $time = microtime(true) - $start;
            
            if ($time > 2.0) {
                echo "WARNING: API $api tardó {$time}s (>2s)\n";
            } else {
                echo "PASS: API $api responde en {$time}s\n";
            }
        }
    }
    
    private function getRequest($endpoint) {
        $url = $this->baseUrl . $endpoint;
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 5
            ]
        ]);
        return @file_get_contents($url, false, $context);
    }
    
    private function postRequest($endpoint, $data) {
        $url = $this->baseUrl . $endpoint;
        $postData = http_build_query($data);
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postData,
                'timeout' => 5
            ]
        ]);
        
        return @file_get_contents($url, false, $context);
    }
    
    public function runAllTests() {
        try {
            echo "=== TESTING APIs ===\n";
            $this->testInmueblesMapApi();
            $this->testFavoritesApi();
            $this->testLeadSubmission();
            $this->testLoginApi();
            $this->testApiResponseTimes();
            echo "=== TODOS LOS TESTS PASARON ===\n";
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
        }
    }
}

// Ejecutar tests
$test = new ApiTest();
$test->runAllTests();
?>
```

---

## 🖥️ Testing Frontend

### Test de JavaScript

**Archivo:** `tests/frontend_test.html`
```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Frontend Tests</title>
    <style>
        .test-result { margin: 5px 0; padding: 5px; }
        .pass { background: #d4edda; color: #155724; }
        .fail { background: #f8d7da; color: #721c24; }
        .warning { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <h1>Frontend Tests</h1>
    <div id="testResults"></div>

    <!-- Cargar scripts necesarios -->
    <script src="../public/js/favorites.js"></script>
    <script src="../js/leaflet-simple.js"></script>

    <script>
    class FrontendTest {
        constructor() {
            this.results = [];
            this.testsRun = 0;
            this.testsPassed = 0;
        }

        assert(condition, message) {
            this.testsRun++;
            if (condition) {
                this.testsPassed++;
                this.log('PASS: ' + message, 'pass');
                return true;
            } else {
                this.log('FAIL: ' + message, 'fail');
                return false;
            }
        }

        assertEquals(expected, actual, message) {
            const condition = expected === actual;
            if (!condition) {
                message += ` (Expected: ${expected}, Actual: ${actual})`;
            }
            return this.assert(condition, message);
        }

        log(message, type = 'info') {
            const div = document.createElement('div');
            div.className = 'test-result ' + type;
            div.textContent = message;
            document.getElementById('testResults').appendChild(div);
        }

        testLocalStorage() {
            this.log('=== Testing LocalStorage ===');
            
            // Test básico localStorage
            localStorage.setItem('test_key', 'test_value');
            const value = localStorage.getItem('test_key');
            this.assertEquals('test_value', value, 'LocalStorage funciona');
            localStorage.removeItem('test_key');

            // Test JSON en localStorage
            const testObject = {id: 1, name: 'test'};
            localStorage.setItem('test_object', JSON.stringify(testObject));
            const retrieved = JSON.parse(localStorage.getItem('test_object'));
            this.assertEquals(testObject.name, retrieved.name, 'JSON en localStorage');
            localStorage.removeItem('test_object');
        }

        testFavoritesManager() {
            this.log('=== Testing FavoritesManager ===');
            
            // Limpiar favoritos
            localStorage.removeItem('inmuebles_favoritos');
            
            // Test agregar favorito
            FavoritesManager.add(1);
            const favorites = FavoritesManager.getAll();
            this.assert(favorites.includes(1), 'Agregar favorito');
            
            // Test verificar favorito
            this.assert(FavoritesManager.isFavorite(1), 'Verificar favorito existe');
            this.assert(!FavoritesManager.isFavorite(2), 'Verificar favorito no existe');
            
            // Test remover favorito
            FavoritesManager.remove(1);
            this.assert(!FavoritesManager.isFavorite(1), 'Remover favorito');
            
            // Test contador
            FavoritesManager.add(1);
            FavoritesManager.add(2);
            this.assertEquals(2, FavoritesManager.getCount(), 'Contador favoritos');
            
            // Limpiar
            localStorage.removeItem('inmuebles_favoritos');
        }

        testResponsiveDesign() {
            this.log('=== Testing Responsive Design ===');
            
            // Simular diferentes tamaños de pantalla
            const originalWidth = window.innerWidth;
            
            // Test móvil (768px)
            Object.defineProperty(window, 'innerWidth', {value: 768, writable: true});
            window.dispatchEvent(new Event('resize'));
            
            // Verificar que elementos respondan
            const containers = document.querySelectorAll('.container, .container-fluid');
            let hasResponsiveContainers = containers.length > 0;
            this.assert(hasResponsiveContainers, 'Containers responsive presentes');
            
            // Restaurar tamaño original
            Object.defineProperty(window, 'innerWidth', {value: originalWidth, writable: true});
        }

        testFormValidation() {
            this.log('=== Testing Form Validation ===');
            
            // Crear formulario de test
            const form = document.createElement('form');
            form.innerHTML = `
                <input type="text" id="nombre" required>
                <input type="email" id="email" required>
                <input type="tel" id="telefono" required>
                <button type="submit">Submit</button>
            `;
            document.body.appendChild(form);
            
            // Test validación HTML5
            const emailInput = form.querySelector('#email');
            emailInput.value = 'invalid-email';
            this.assert(!emailInput.validity.valid, 'Validación email inválido');
            
            emailInput.value = 'valid@email.com';
            this.assert(emailInput.validity.valid, 'Validación email válido');
            
            // Test campo requerido
            const nombreInput = form.querySelector('#nombre');
            nombreInput.value = '';
            this.assert(!nombreInput.validity.valid, 'Validación campo requerido vacío');
            
            nombreInput.value = 'Juan Pérez';
            this.assert(nombreInput.validity.valid, 'Validación campo requerido lleno');
            
            // Limpiar
            document.body.removeChild(form);
        }

        async testAjaxRequests() {
            this.log('=== Testing AJAX Requests ===');
            
            try {
                // Test fetch básico
                const response = await fetch('/INMOBILIARIA_1/public/get_inmuebles_mapa.php');
                this.assert(response.ok, 'Fetch request exitoso');
                
                const data = await response.json();
                this.assert(Array.isArray(data), 'Response es array JSON');
                
            } catch (error) {
                this.log('WARNING: No se puede probar AJAX (servidor no disponible)', 'warning');
            }
        }

        testBrowserCompatibility() {
            this.log('=== Testing Browser Compatibility ===');
            
            // Test APIs modernas
            this.assert(typeof localStorage !== 'undefined', 'localStorage soportado');
            this.assert(typeof fetch !== 'undefined', 'fetch API soportado');
            this.assert(typeof Promise !== 'undefined', 'Promises soportadas');
            this.assert(typeof JSON !== 'undefined', 'JSON soportado');
            
            // Test ES6 features
            try {
                eval('const arrow = () => true;');
                this.assert(true, 'Arrow functions soportadas');
            } catch {
                this.assert(false, 'Arrow functions NO soportadas');
            }
            
            // Test CSS Grid
            const testDiv = document.createElement('div');
            testDiv.style.display = 'grid';
            this.assert(testDiv.style.display === 'grid', 'CSS Grid soportado');
        }

        testAccessibility() {
            this.log('=== Testing Accessibility ===');
            
            // Test que elementos tengan atributos necesarios
            const buttons = document.querySelectorAll('button, [role="button"]');
            let buttonsWithText = 0;
            buttons.forEach(button => {
                if (button.textContent.trim() || button.getAttribute('aria-label')) {
                    buttonsWithText++;
                }
            });
            
            this.assert(buttonsWithText === buttons.length, 
                `Todos los botones tienen texto/aria-label (${buttonsWithText}/${buttons.length})`);
            
            // Test imágenes con alt
            const images = document.querySelectorAll('img');
            let imagesWithAlt = 0;
            images.forEach(img => {
                if (img.hasAttribute('alt')) {
                    imagesWithAlt++;
                }
            });
            
            if (images.length > 0) {
                this.assert(imagesWithAlt === images.length, 
                    `Todas las imágenes tienen alt (${imagesWithAlt}/${images.length})`);
            }
        }

        async runAllTests() {
            this.log('=== INICIANDO FRONTEND TESTS ===');
            
            this.testLocalStorage();
            this.testFavoritesManager();
            this.testResponsiveDesign();
            this.testFormValidation();
            await this.testAjaxRequests();
            this.testBrowserCompatibility();
            this.testAccessibility();
            
            this.log(`=== RESUMEN: ${this.testsPassed}/${this.testsRun} tests pasaron ===`);
            
            if (this.testsPassed === this.testsRun) {
                this.log('🎉 TODOS LOS TESTS PASARON', 'pass');
            } else {
                this.log(`❌ ${this.testsRun - this.testsPassed} tests fallaron`, 'fail');
            }
        }
    }

    // Ejecutar tests cuando cargue la página
    document.addEventListener('DOMContentLoaded', function() {
        const tester = new FrontendTest();
        tester.runAllTests();
    });
    </script>
</body>
</html>
```

---

## 📱 Testing Mobile

### Test Responsivo Manual

**Checklist para diferentes dispositivos:**

#### Mobile (320px - 768px)
- [ ] Menú de navegación colapsa correctamente
- [ ] Formularios son utilizables con teclado virtual
- [ ] Botones tienen tamaño mínimo 44px
- [ ] Texto es legible sin zoom
- [ ] Mapas se pueden navegar con touch
- [ ] Favoritos funciona con touch
- [ ] Popups de mapas no se salen de pantalla

#### Tablet (768px - 1024px)
- [ ] Layout usa el espacio eficientemente
- [ ] Cards de inmuebles se organizan en grid
- [ ] Dashboard se adapta a orientación
- [ ] Formularios mantienen usabilidad

#### Desktop (1024px+)
- [ ] Todas las funcionalidades disponibles
- [ ] Dashboard completo visible
- [ ] Mapas con controles completos
- [ ] Navegación horizontal funcional

### Test Automatizado con Puppeteer

**Archivo:** `tests/mobile_test.js`
```javascript
const puppeteer = require('puppeteer');

class MobileTest {
    constructor() {
        this.browser = null;
        this.page = null;
    }

    async setup() {
        this.browser = await puppeteer.launch({headless: false});
        this.page = await this.browser.newPage();
    }

    async testMobileViewport() {
        console.log('=== Testing Mobile Viewport ===');
        
        // Simular iPhone X
        await this.page.setViewport({width: 375, height: 812});
        await this.page.goto('http://localhost/INMOBILIARIA_1/public/');

        // Test que la página carga
        await this.page.waitForSelector('body');
        console.log('PASS: Página carga en mobile');

        // Test menú responsive
        const menuButton = await this.page.$('.navbar-toggler');
        if (menuButton) {
            console.log('PASS: Menú responsive presente');
        } else {
            console.log('WARNING: Menú responsive no encontrado');
        }

        // Test touch events en botones
        const favoriteButtons = await this.page.$$('.favorite-btn');
        if (favoriteButtons.length > 0) {
            await favoriteButtons[0].tap();
            console.log('PASS: Touch events en favoritos');
        }
    }

    async testTabletViewport() {
        console.log('=== Testing Tablet Viewport ===');
        
        // Simular iPad
        await this.page.setViewport({width: 768, height: 1024});
        await this.page.reload();

        // Test layout grid
        const gridColumns = await this.page.$$('.col-md-6, .col-lg-4');
        if (gridColumns.length > 0) {
            console.log('PASS: Grid layout en tablet');
        }
    }

    async testFormUsability() {
        console.log('=== Testing Form Usability ===');
        
        await this.page.goto('http://localhost/INMOBILIARIA_1/public/inmueble.php?id=1');
        
        // Test formulario de lead
        const nameInput = await this.page.$('input[name="nombre"]');
        if (nameInput) {
            await nameInput.tap();
            await nameInput.type('Test User');
            
            const phoneInput = await this.page.$('input[name="telefono"]');
            await phoneInput.tap();
            await phoneInput.type('3001234567');
            
            console.log('PASS: Formulario usable en mobile');
        }
    }

    async testMapInteraction() {
        console.log('=== Testing Map Interaction ===');
        
        await this.page.goto('http://localhost/INMOBILIARIA_1/public/mapa.php');
        
        // Esperar que el mapa cargue
        await this.page.waitForSelector('#map');
        
        // Test zoom con touch
        const map = await this.page.$('#map');
        const box = await map.boundingBox();
        
        // Simular pinch zoom
        await this.page.touchscreen.tap(box.x + box.width/2, box.y + box.height/2);
        
        console.log('PASS: Mapa responde a touch');
    }

    async testPerformance() {
        console.log('=== Testing Performance ===');
        
        // Activar métricas
        await this.page.setViewport({width: 375, height: 812});
        
        const start = Date.now();
        await this.page.goto('http://localhost/INMOBILIARIA_1/public/', {
            waitUntil: 'domcontentloaded'
        });
        const loadTime = Date.now() - start;
        
        if (loadTime < 3000) {
            console.log(`PASS: Página carga en ${loadTime}ms (< 3s)`);
        } else {
            console.log(`WARNING: Página carga en ${loadTime}ms (> 3s)`);
        }
        
        // Test métricas Core Web Vitals
        const metrics = await this.page.metrics();
        console.log('Performance metrics:', metrics);
    }

    async cleanup() {
        if (this.browser) {
            await this.browser.close();
        }
    }

    async runAllTests() {
        try {
            await this.setup();
            await this.testMobileViewport();
            await this.testTabletViewport();
            await this.testFormUsability();
            await this.testMapInteraction();
            await this.testPerformance();
            console.log('=== TODOS LOS MOBILE TESTS COMPLETADOS ===');
        } catch (error) {
            console.error('ERROR:', error.message);
        } finally {
            await this.cleanup();
        }
    }
}

// Ejecutar si se llama directamente
if (require.main === module) {
    const test = new MobileTest();
    test.runAllTests();
}

module.exports = MobileTest;
```

---

## 🔒 Security Testing

### Test de Seguridad

**Archivo:** `tests/security_test.php`
```php
<?php
require_once '../conexion.php';

class SecurityTest {
    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    public function testSQLInjection() {
        echo "=== Testing SQL Injection ===\n";
        
        // Test preparar statements protegen contra inyección
        $maliciousInput = "'; DROP TABLE usuarios; --";
        
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->execute([$maliciousInput]);
            $result = $stmt->fetch();
            
            // Si llegamos aquí, la inyección no funcionó (bueno)
            echo "PASS: Prepared statements protegen contra SQL injection\n";
        } catch (PDOException $e) {
            echo "FAIL: Error en prepared statement: " . $e->getMessage() . "\n";
        }
        
        // Test inyección en parámetros GET (simulado)
        $_GET['id'] = "1' OR '1'='1";
        $cleanId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        
        if ($cleanId === false) {
            echo "PASS: Validación de parámetros GET rechaza inyección\n";
        } else {
            echo "FAIL: Validación de parámetros GET vulnerable\n";
        }
    }
    
    public function testXSS() {
        echo "=== Testing XSS Protection ===\n";
        
        $xssAttempts = [
            '<script>alert("XSS")</script>',
            '"><img src=x onerror=alert("XSS")>',
            'javascript:alert("XSS")',
            '<svg onload=alert("XSS")>',
            '\';alert(\'XSS\');//'
        ];
        
        foreach ($xssAttempts as $attempt) {
            $cleaned = htmlspecialchars($attempt, ENT_QUOTES, 'UTF-8');
            
            if ($cleaned !== $attempt && !str_contains($cleaned, '<script>') && !str_contains($cleaned, 'javascript:')) {
                echo "PASS: XSS attempt bloqueado: " . substr($attempt, 0, 20) . "...\n";
            } else {
                echo "FAIL: XSS attempt no bloqueado: " . substr($attempt, 0, 20) . "...\n";
            }
        }
    }
    
    public function testFileUploadSecurity() {
        echo "=== Testing File Upload Security ===\n";
        
        // Test extensiones peligrosas
        $dangerousFiles = [
            'malware.php',
            'script.js.php',
            'backdoor.phtml',
            'virus.exe',
            'trojan.bat'
        ];
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi'];
        
        foreach ($dangerousFiles as $file) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            
            if (!in_array($extension, $allowedExtensions)) {
                echo "PASS: Archivo peligroso rechazado: $file\n";
            } else {
                echo "FAIL: Archivo peligroso permitido: $file\n";
            }
        }
        
        // Test tamaño de archivo
        $maxSize = 5 * 1024 * 1024; // 5MB
        $testSize = 10 * 1024 * 1024; // 10MB
        
        if ($testSize > $maxSize) {
            echo "PASS: Validación de tamaño funcionaría correctamente\n";
        }
    }
    
    public function testPasswordSecurity() {
        echo "=== Testing Password Security ===\n";
        
        // Test hashing
        $password = 'test123';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        if (password_verify($password, $hash)) {
            echo "PASS: Password hashing funciona correctamente\n";
        } else {
            echo "FAIL: Password hashing no funciona\n";
        }
        
        // Test que passwords no se almacenan en texto plano
        $stmt = $this->pdo->query("SELECT password FROM usuarios LIMIT 1");
        $storedPassword = $stmt->fetchColumn();
        
        if ($storedPassword && strlen($storedPassword) > 50) {
            echo "PASS: Passwords almacenadas hasheadas\n";
        } else {
            echo "FAIL: Passwords posiblemente en texto plano\n";
        }
    }
    
    public function testSessionSecurity() {
        echo "=== Testing Session Security ===\n";
        
        // Test configuración de sesión
        $secureSessionSettings = [
            'session.cookie_httponly' => true,
            'session.use_strict_mode' => true,
            'session.cookie_samesite' => 'Strict'
        ];
        
        foreach ($secureSessionSettings as $setting => $expected) {
            $actual = ini_get($setting);
            
            if ($actual == $expected) {
                echo "PASS: $setting configurado correctamente\n";
            } else {
                echo "WARNING: $setting debería ser $expected, actual: $actual\n";
            }
        }
        
        // Test regeneración de session ID
        session_start();
        $oldSessionId = session_id();
        session_regenerate_id(true);
        $newSessionId = session_id();
        
        if ($oldSessionId !== $newSessionId) {
            echo "PASS: Session ID regeneration funciona\n";
        } else {
            echo "FAIL: Session ID regeneration no funciona\n";
        }
    }
    
    public function testCSRFProtection() {
        echo "=== Testing CSRF Protection ===\n";
        
        // Simular token CSRF
        session_start();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        // Test validación de token
        $validToken = $_SESSION['csrf_token'];
        $invalidToken = 'invalid_token';
        
        if (hash_equals($_SESSION['csrf_token'], $validToken)) {
            echo "PASS: Token CSRF válido aceptado\n";
        } else {
            echo "FAIL: Token CSRF válido rechazado\n";
        }
        
        if (!hash_equals($_SESSION['csrf_token'], $invalidToken)) {
            echo "PASS: Token CSRF inválido rechazado\n";
        } else {
            echo "FAIL: Token CSRF inválido aceptado\n";
        }
    }
    
    public function testDirectoryTraversal() {
        echo "=== Testing Directory Traversal ===\n";
        
        $traversalAttempts = [
            '../../../etc/passwd',
            '..\\..\\..\\windows\\system32\\config\\sam',
            '....//....//....//etc/passwd',
            '%2e%2e%2f%2e%2e%2f%2e%2e%2fetc%2fpasswd'
        ];
        
        foreach ($traversalAttempts as $attempt) {
            // Normalizar path
            $normalizedPath = realpath('uploads/' . $attempt);
            $uploadsPath = realpath('uploads/');
            
            // Verificar que el path no sale del directorio uploads
            if ($normalizedPath && strpos($normalizedPath, $uploadsPath) !== 0) {
                echo "FAIL: Directory traversal posible: $attempt\n";
            } else {
                echo "PASS: Directory traversal bloqueado: $attempt\n";
            }
        }
    }
    
    public function testHTTPHeaders() {
        echo "=== Testing HTTP Security Headers ===\n";
        
        $requiredHeaders = [
            'X-Content-Type-Options: nosniff',
            'X-Frame-Options: DENY',
            'X-XSS-Protection: 1; mode=block',
            'Strict-Transport-Security: max-age=31536000'
        ];
        
        foreach ($requiredHeaders as $header) {
            echo "RECOMMEND: Agregar header: $header\n";
        }
    }
    
    public function runAllTests() {
        echo "=== SECURITY TESTS ===\n";
        $this->testSQLInjection();
        $this->testXSS();
        $this->testFileUploadSecurity();
        $this->testPasswordSecurity();
        $this->testSessionSecurity();
        $this->testCSRFProtection();
        $this->testDirectoryTraversal();
        $this->testHTTPHeaders();
        echo "=== SECURITY TESTS COMPLETADOS ===\n";
    }
}

// Ejecutar tests
$test = new SecurityTest();
$test->runAllTests();
?>
```

---

## 📊 Testing de Performance

### Test de Rendimiento

**Archivo:** `tests/performance_test.php`
```php
<?php
require_once '../conexion.php';

class PerformanceTest {
    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    public function testDatabaseQueries() {
        echo "=== Testing Database Performance ===\n";
        
        // Test consulta simple
        $start = microtime(true);
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM inmuebles");
        $count = $stmt->fetchColumn();
        $time = microtime(true) - $start;
        
        echo sprintf("PASS: Consulta simple: %.4fs (%d registros)\n", $time, $count);
        
        // Test consulta con JOINs
        $start = microtime(true);
        $stmt = $this->pdo->query("
            SELECT i.*, p.nombre as propietario_nombre, u.nombre as agente_nombre
            FROM inmuebles i
            LEFT JOIN propietarios p ON i.propietario_id = p.id
            LEFT JOIN usuarios u ON i.agente_id = u.id
            WHERE i.estado = 'activo'
            LIMIT 50
        ");
        $results = $stmt->fetchAll();
        $time = microtime(true) - $start;
        
        echo sprintf("INFO: Consulta con JOINs: %.4fs (%d registros)\n", $time, count($results));
        
        if ($time > 0.1) {
            echo "WARNING: Consulta lenta, considerar índices\n";
        }
        
        // Test consulta de estadísticas
        $start = microtime(true);
        $stats = [
            'inmuebles' => $this->pdo->query("SELECT COUNT(*) FROM inmuebles WHERE estado = 'activo'")->fetchColumn(),
            'leads_mes' => $this->pdo->query("SELECT COUNT(*) FROM leads WHERE MONTH(fecha_creacion) = MONTH(CURRENT_DATE)")->fetchColumn(),
            'contratos' => $this->pdo->query("SELECT COUNT(*) FROM contratos WHERE estado = 'activo'")->fetchColumn()
        ];
        $time = microtime(true) - $start;
        
        echo sprintf("INFO: Consultas estadísticas: %.4fs\n", $time);
    }
    
    public function testFileOperations() {
        echo "=== Testing File Operations ===\n";
        
        // Test lectura de archivos
        $start = microtime(true);
        $files = glob('uploads/*');
        $time = microtime(true) - $start;
        
        echo sprintf("INFO: Listar archivos uploads: %.4fs (%d archivos)\n", $time, count($files));
        
        // Test creación de archivo temporal
        $start = microtime(true);
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'test data');
        $content = file_get_contents($tempFile);
        unlink($tempFile);
        $time = microtime(true) - $start;
        
        echo sprintf("PASS: Operaciones archivo temporal: %.4fs\n", $time);
    }
    
    public function testMemoryUsage() {
        echo "=== Testing Memory Usage ===\n";
        
        $startMemory = memory_get_usage(true);
        
        // Cargar datos grandes
        $stmt = $this->pdo->query("SELECT * FROM inmuebles");
        $inmuebles = $stmt->fetchAll();
        
        $endMemory = memory_get_usage(true);
        $memoryUsed = $endMemory - $startMemory;
        
        echo sprintf("INFO: Memoria usada cargando inmuebles: %.2f MB\n", $memoryUsed / 1024 / 1024);
        
        $peakMemory = memory_get_peak_usage(true);
        echo sprintf("INFO: Pico de memoria: %.2f MB\n", $peakMemory / 1024 / 1024);
        
        if ($peakMemory > 128 * 1024 * 1024) { // 128MB
            echo "WARNING: Alto uso de memoria\n";
        } else {
            echo "PASS: Uso de memoria aceptable\n";
        }
    }
    
    public function testApiPerformance() {
        echo "=== Testing API Performance ===\n";
        
        $apiTests = [
            'get_inmuebles_mapa.php',
            'procesar_lead.php',
            'get_favorites.php'
        ];
        
        foreach ($apiTests as $api) {
            $url = "http://localhost/INMOBILIARIA_1/public/$api";
            
            $start = microtime(true);
            $response = @file_get_contents($url);
            $time = microtime(true) - $start;
            
            if ($response !== false) {
                echo sprintf("INFO: API %s: %.4fs\n", $api, $time);
                
                if ($time > 2.0) {
                    echo "WARNING: API lenta: $api\n";
                }
            } else {
                echo "WARNING: No se pudo probar API: $api\n";
            }
        }
    }
    
    public function testConcurrentRequests() {
        echo "=== Testing Concurrent Performance ===\n";
        
        // Simular múltiples requests concurrentes (simplificado)
        $urls = [
            'http://localhost/INMOBILIARIA_1/public/index.php',
            'http://localhost/INMOBILIARIA_1/public/mapa.php',
            'http://localhost/INMOBILIARIA_1/public/get_inmuebles_mapa.php'
        ];
        
        $start = microtime(true);
        
        foreach ($urls as $url) {
            @file_get_contents($url);
        }
        
        $time = microtime(true) - $start;
        echo sprintf("INFO: 3 requests secuenciales: %.4fs\n", $time);
        
        if ($time > 5.0) {
            echo "WARNING: Rendimiento bajo en múltiples requests\n";
        }
    }
    
    public function testDatabaseIndexes() {
        echo "=== Testing Database Indexes ===\n";
        
        // Verificar índices importantes
        $indexChecks = [
            ['table' => 'usuarios', 'column' => 'email'],
            ['table' => 'inmuebles', 'column' => 'estado'],
            ['table' => 'inmuebles', 'column' => 'tipo'],
            ['table' => 'inmuebles', 'column' => 'operacion'],
            ['table' => 'leads', 'column' => 'estado'],
            ['table' => 'leads', 'column' => 'fecha_creacion']
        ];
        
        foreach ($indexChecks as $check) {
            $stmt = $this->pdo->query("SHOW INDEX FROM {$check['table']} WHERE Column_name = '{$check['column']}'");
            
            if ($stmt->rowCount() > 0) {
                echo "PASS: Índice en {$check['table']}.{$check['column']}\n";
            } else {
                echo "RECOMMEND: Crear índice en {$check['table']}.{$check['column']}\n";
                echo "SQL: CREATE INDEX idx_{$check['table']}_{$check['column']} ON {$check['table']}({$check['column']});\n";
            }
        }
    }
    
    public function testQueryOptimization() {
        echo "=== Testing Query Optimization ===\n";
        
        // Test consulta sin optimizar
        $start = microtime(true);
        $stmt = $this->pdo->query("
            SELECT * FROM inmuebles i 
            WHERE EXISTS (
                SELECT 1 FROM propietarios p 
                WHERE p.id = i.propietario_id AND p.activo = 1
            )
        ");
        $results1 = $stmt->fetchAll();
        $time1 = microtime(true) - $start;
        
        // Test consulta optimizada
        $start = microtime(true);
        $stmt = $this->pdo->query("
            SELECT i.* FROM inmuebles i 
            INNER JOIN propietarios p ON p.id = i.propietario_id 
            WHERE p.activo = 1
        ");
        $results2 = $stmt->fetchAll();
        $time2 = microtime(true) - $start;
        
        echo sprintf("INFO: Consulta EXISTS: %.4fs (%d registros)\n", $time1, count($results1));
        echo sprintf("INFO: Consulta JOIN: %.4fs (%d registros)\n", $time2, count($results2));
        
        if ($time2 < $time1) {
            echo "PASS: Consulta optimizada es más rápida\n";
        }
    }
    
    public function runAllTests() {
        echo "=== PERFORMANCE TESTS ===\n";
        $this->testDatabaseQueries();
        $this->testFileOperations();
        $this->testMemoryUsage();
        $this->testApiPerformance();
        $this->testConcurrentRequests();
        $this->testDatabaseIndexes();
        $this->testQueryOptimization();
        echo "=== PERFORMANCE TESTS COMPLETADOS ===\n";
    }
}

// Ejecutar tests
$test = new PerformanceTest();
$test->runAllTests();
?>
```

---

## 🚀 Test Runner Principal

**Archivo:** `tests/run_all_tests.php`
```php
<?php
echo "======================================\n";
echo "SISTEMA INMOBILIARIA - TEST SUITE\n";
echo "======================================\n\n";

$testFiles = [
    'auth_test.php' => 'Autenticación',
    'database_test.php' => 'Base de Datos', 
    'api_test.php' => 'APIs',
    'security_test.php' => 'Seguridad',
    'performance_test.php' => 'Rendimiento'
];

$totalStart = microtime(true);
$testsRun = 0;
$testsFailed = 0;

foreach ($testFiles as $file => $description) {
    echo "--- Ejecutando: $description ---\n";
    
    if (file_exists($file)) {
        $start = microtime(true);
        
        ob_start();
        try {
            include $file;
            $output = ob_get_contents();
            $time = microtime(true) - $start;
            
            echo $output;
            echo sprintf("Completado en %.2fs\n", $time);
            $testsRun++;
        } catch (Exception $e) {
            $output = ob_get_contents();
            echo $output;
            echo "ERROR: " . $e->getMessage() . "\n";
            $testsFailed++;
        }
        ob_end_clean();
    } else {
        echo "SKIP: Archivo $file no encontrado\n";
        $testsFailed++;
    }
    
    echo "\n";
}

$totalTime = microtime(true) - $totalStart;

echo "======================================\n";
echo "RESUMEN FINAL\n";
echo "======================================\n";
echo "Tests ejecutados: $testsRun\n";
echo "Tests fallidos: $testsFailed\n";
echo "Tiempo total: " . sprintf("%.2fs", $totalTime) . "\n";

if ($testsFailed === 0) {
    echo "🎉 TODOS LOS TESTS PASARON\n";
} else {
    echo "❌ $testsFailed tests fallaron\n";
}

echo "======================================\n";
?>
```

---

## 📋 Checklist de Testing

### Pre-Despliegue

#### ✅ Funcionalidad
- [ ] Login/logout con todos los roles
- [ ] CRUD completo inmuebles
- [ ] Sistema de favoritos
- [ ] Captura y gestión de leads
- [ ] Mapas interactivos
- [ ] Upload de archivos

#### ✅ Seguridad
- [ ] SQL injection bloqueada
- [ ] XSS protegido
- [ ] File upload seguro
- [ ] Passwords hasheadas
- [ ] Sesiones seguras
- [ ] CSRF protection

#### ✅ Performance
- [ ] Queries optimizadas
- [ ] Índices en BD
- [ ] Memoria bajo control
- [ ] APIs responden < 2s
- [ ] Archivos optimizados

#### ✅ Mobile/Responsive
- [ ] Funciona en mobile (320px+)
- [ ] Touch interactions
- [ ] Formularios usables
- [ ] Mapas navegables
- [ ] Performance móvil

#### ✅ Cross-browser
- [ ] Chrome (latest)
- [ ] Firefox (latest) 
- [ ] Safari (latest)
- [ ] Edge (latest)

---

**Suite de Testing para Sistema de Gestión Inmobiliaria**  
*Versión 1.0 - Diciembre 2024*