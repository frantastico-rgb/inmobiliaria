<?php
// Script para crear la base de datos y las tablas necesarias
$servername = "localhost";
$username = "root";
$password = "";

try {
    // Crear conexión sin especificar base de datos
    $pdo = new PDO("mysql:host=$servername", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crear base de datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS inmobil CHARACTER SET utf8 COLLATE utf8_spanish_ci");
    echo "✅ Base de datos 'inmobil' creada o ya existe.<br>";
    
    // Seleccionar la base de datos
    $pdo->exec("USE inmobil");
    
    // Crear tabla tipo_inmueble
    $sql_tipo_inmueble = "CREATE TABLE IF NOT EXISTS tipo_inmueble (
        cod_tipoinm INT AUTO_INCREMENT PRIMARY KEY,
        nom_tipoinm VARCHAR(50) NOT NULL UNIQUE,
        descripcion TEXT
    )";
    $pdo->exec($sql_tipo_inmueble);
    echo "✅ Tabla 'tipo_inmueble' creada.<br>";
    
    // Insertar tipos de inmueble básicos si no existen
    $pdo->exec("INSERT IGNORE INTO tipo_inmueble (nom_tipoinm, descripcion) VALUES 
        ('Apartamento', 'Vivienda en edificio con varias unidades'),
        ('Casa', 'Vivienda unifamiliar'),
        ('Local Comercial', 'Espacio destinado a actividad comercial'),
        ('Oficina', 'Espacio destinado a actividades administrativas'),
        ('Bodega', 'Espacio de almacenamiento'),
        ('Lote', 'Terreno sin construcción')");
    
    // Crear tabla oficinas
    $sql_oficinas = "CREATE TABLE IF NOT EXISTS oficinas (
        cod_oficina INT AUTO_INCREMENT PRIMARY KEY,
        nombre_oficina VARCHAR(100) NOT NULL,
        direccion_oficina VARCHAR(200) NOT NULL,
        telefono_oficina VARCHAR(20),
        email_oficina VARCHAR(100),
        ciudad VARCHAR(50),
        fecha_apertura DATE,
        latitud DECIMAL(10,8) NULL,
        longitud DECIMAL(11,8) NULL,
        foto_oficina VARCHAR(255)
    )";
    $pdo->exec($sql_oficinas);
    echo "✅ Tabla 'oficinas' creada.<br>";
    
    // También crear tabla 'oficina' para compatibilidad con lista_oficinas.php
    $sql_oficina_compat = "CREATE TABLE IF NOT EXISTS oficina (
        Id_ofi INT AUTO_INCREMENT PRIMARY KEY,
        nom_ofi VARCHAR(100) NOT NULL,
        dir_ofi VARCHAR(200) NOT NULL,
        tel_ofi VARCHAR(20),
        email_ofi VARCHAR(100),
        latitud DECIMAL(10,8) NULL,
        longitud DECIMAL(11,8) NULL,
        foto_ofi VARCHAR(255)
    )";
    $pdo->exec($sql_oficina_compat);
    echo "✅ Tabla 'oficina' (compatibilidad) creada.<br>";
    
    // Crear tabla empleados
    $sql_empleados = "CREATE TABLE IF NOT EXISTS empleados (
        cod_emp INT AUTO_INCREMENT PRIMARY KEY,
        nom_emp VARCHAR(100) NOT NULL,
        ape_emp VARCHAR(100) NOT NULL,
        tel_emp VARCHAR(20),
        email_emp VARCHAR(100) UNIQUE,
        cargo VARCHAR(50),
        fecha_ingreso DATE,
        salario DECIMAL(10,2),
        fk_cod_oficina INT,
        FOREIGN KEY (fk_cod_oficina) REFERENCES oficinas(cod_oficina)
    )";
    $pdo->exec($sql_empleados);
    echo "✅ Tabla 'empleados' creada.<br>";
    
    // Crear tabla propietarios
    $sql_propietarios = "CREATE TABLE IF NOT EXISTS propietarios (
        cod_prop INT AUTO_INCREMENT PRIMARY KEY,
        nom_prop VARCHAR(100) NOT NULL,
        ape_prop VARCHAR(100) NOT NULL,
        tel_prop VARCHAR(20),
        email_prop VARCHAR(100) UNIQUE,
        dir_prop VARCHAR(200),
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql_propietarios);
    echo "✅ Tabla 'propietarios' creada.<br>";
    
    // Crear tabla inmuebles
    $sql_inmuebles = "CREATE TABLE IF NOT EXISTS inmuebles (
        cod_inm INT AUTO_INCREMENT PRIMARY KEY,
        dir_inm VARCHAR(200) NOT NULL,
        barrio_inm VARCHAR(100),
        ciudad_inm VARCHAR(100),
        pais_inm VARCHAR(100) DEFAULT 'Colombia',
        latitude DECIMAL(10,8) NULL,
        longitud DECIMAL(11,8) NULL,
        foto VARCHAR(255),
        web_p1 VARCHAR(255),
        web_p2 VARCHAR(255),
        cod_tipoinm INT,
        num_hab INT,
        precio_alq DECIMAL(12,2),
        cod_prop INT,
        caract_inm TEXT,
        notas_inm TEXT,
        desc_inm TEXT,
        precio_inm DECIMAL(12,2),
        area_inm DECIMAL(8,2),
        habitaciones INT,
        banos INT,
        garaje BOOLEAN DEFAULT FALSE,
        estado ENUM('Disponible', 'Alquilado', 'Vendido', 'Mantenimiento') DEFAULT 'Disponible',
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        imagen_inm VARCHAR(255),
        fk_cod_tipoinm INT,
        fk_cod_prop INT,
        FOREIGN KEY (fk_cod_tipoinm) REFERENCES tipo_inmueble(cod_tipoinm),
        FOREIGN KEY (fk_cod_prop) REFERENCES propietarios(cod_prop)
    )";
    $pdo->exec($sql_inmuebles);
    echo "✅ Tabla 'inmuebles' creada.<br>";
    
    // Crear tabla clientes
    $sql_clientes = "CREATE TABLE IF NOT EXISTS clientes (
        cod_cli INT AUTO_INCREMENT PRIMARY KEY,
        nom_cli VARCHAR(100) NOT NULL,
        ape_cli VARCHAR(100) NOT NULL,
        tel_cli VARCHAR(20),
        email_cli VARCHAR(100) UNIQUE,
        dir_cli VARCHAR(200),
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fk_cod_emp_gestion INT,
        FOREIGN KEY (fk_cod_emp_gestion) REFERENCES empleados(cod_emp)
    )";
    $pdo->exec($sql_clientes);
    echo "✅ Tabla 'clientes' creada.<br>";
    
    // Crear tabla contratos
    $sql_contratos = "CREATE TABLE IF NOT EXISTS contratos (
        cod_contrato INT AUTO_INCREMENT PRIMARY KEY,
        tipo_contrato ENUM('Alquiler', 'Venta') NOT NULL,
        fecha_inicio DATE NOT NULL,
        fecha_fin DATE,
        valor_contrato DECIMAL(12,2) NOT NULL,
        descripcion TEXT,
        estado ENUM('Activo', 'Finalizado', 'Cancelado') DEFAULT 'Activo',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fk_cod_inm INT NOT NULL,
        fk_cod_cli INT NOT NULL,
        fk_cod_emp INT NOT NULL,
        FOREIGN KEY (fk_cod_inm) REFERENCES inmuebles(cod_inm),
        FOREIGN KEY (fk_cod_cli) REFERENCES clientes(cod_cli),
        FOREIGN KEY (fk_cod_emp) REFERENCES empleados(cod_emp)
    )";
    $pdo->exec($sql_contratos);
    echo "✅ Tabla 'contratos' creada.<br>";
    
    // Crear tabla visitas
    $sql_visitas = "CREATE TABLE IF NOT EXISTS visitas (
        cod_visita INT AUTO_INCREMENT PRIMARY KEY,
        fecha_visita DATETIME NOT NULL,
        observaciones TEXT,
        estado ENUM('Programada', 'Realizada', 'Cancelada') DEFAULT 'Programada',
        fecha_programacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fk_cod_inm INT NOT NULL,
        fk_cod_cli INT NOT NULL,
        fk_cod_emp INT NOT NULL,
        FOREIGN KEY (fk_cod_inm) REFERENCES inmuebles(cod_inm),
        FOREIGN KEY (fk_cod_cli) REFERENCES clientes(cod_cli),
        FOREIGN KEY (fk_cod_emp) REFERENCES empleados(cod_emp)
    )";
    $pdo->exec($sql_visitas);
    echo "✅ Tabla 'visitas' creada.<br>";
    
    // Crear tabla inspecciones
    $sql_inspecciones = "CREATE TABLE IF NOT EXISTS inspecciones (
        cod_inspeccion INT AUTO_INCREMENT PRIMARY KEY,
        fecha_inspeccion DATE NOT NULL,
        tipo_inspeccion VARCHAR(100),
        observaciones TEXT,
        resultado ENUM('Aprobado', 'Rechazado', 'Pendiente') DEFAULT 'Pendiente',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fk_cod_inm INT NOT NULL,
        fk_cod_emp INT NOT NULL,
        FOREIGN KEY (fk_cod_inm) REFERENCES inmuebles(cod_inm),
        FOREIGN KEY (fk_cod_emp) REFERENCES empleados(cod_emp)
    )";
    $pdo->exec($sql_inspecciones);
    echo "✅ Tabla 'inspecciones' creada.<br>";
    
    echo "<hr>";
    echo "<h3 style='color: green;'>🎉 ¡Base de datos configurada exitosamente!</h3>";
    echo "<p>Todas las tablas han sido creadas y la aplicación está lista para usar.</p>";
    echo "<a href='index.php' style='background: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir a la Página Principal</a>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>