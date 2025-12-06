<?php
// Script para configurar la conexión y crear las tablas necesarias en PostgreSQL

// 1. OBTENER CREDENCIALES DE RENDER (Variables de Entorno)
// Estos valores se inyectan automáticamente desde tu Base de Datos de Render
$servername = getenv('DB_HOST');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME'); // Debería ser 'inmobil'

try {
    // 2. CREAR CONEXIÓN PDO CON DRIVER POSTGRESQL (pgsql)
    // Conexión directa a la DB 'inmobil'
    $pdo = new PDO("pgsql:host=$servername;dbname=$dbname;user=$username;password=$password");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexión exitosa a la base de datos PostgreSQL 'inmobil'.<br>";
    
    // --------------------------------------------------------------------------------
    // 3. CREACIÓN DE ESQUEMA (AJUSTADO A POSTGRESQL)
    // --------------------------------------------------------------------------------
    
    // NOTA: Eliminamos 'CREATE DATABASE' y 'USE inmobil' ya que la DB la administra Render.
    
    // Crear tabla tipo_inmueble
    $sql_tipo_inmueble = "CREATE TABLE IF NOT EXISTS tipo_inmueble (
        cod_tipoinm SERIAL PRIMARY KEY, -- CORRECCIÓN: AUTO_INCREMENT -> SERIAL
        nom_tipoinm VARCHAR(50) NOT NULL UNIQUE,
        descripcion TEXT
    )";
    $pdo->exec($sql_tipo_inmueble);
    echo "✅ Tabla 'tipo_inmueble' creada.<br>";
    
    // Insertar tipos de inmueble básicos si no existen (PostgreSQL usa ON CONFLICT)
    // Nota: Esta sintaxis de inserción es más robusta y moderna.
    $insert_tipos = "
        INSERT INTO tipo_inmueble (nom_tipoinm, descripcion) VALUES 
        ('Apartamento', 'Vivienda en edificio con varias unidades'),
        ('Casa', 'Vivienda unifamiliar'),
        ('Local Comercial', 'Espacio destinado a actividad comercial'),
        ('Oficina', 'Espacio destinado a actividades administrativas'),
        ('Bodega', 'Espacio de almacenamiento'),
        ('Lote', 'Terreno sin construcción')
        ON CONFLICT (nom_tipoinm) DO NOTHING; -- CORRECCIÓN: INSERT IGNORE -> ON CONFLICT
    ";
    $pdo->exec($insert_tipos);
    
    // Crear tabla oficinas
    $sql_oficinas = "CREATE TABLE IF NOT EXISTS oficinas (
        cod_oficina SERIAL PRIMARY KEY, -- CORRECCIÓN: AUTO_INCREMENT -> SERIAL
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
    
    // Crear tabla 'oficina' (compatibilidad)
    $sql_oficina_compat = "CREATE TABLE IF NOT EXISTS oficina (
        Id_ofi SERIAL PRIMARY KEY, -- CORRECCIÓN: AUTO_INCREMENT -> SERIAL
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
        cod_emp SERIAL PRIMARY KEY, -- CORRECCIÓN: AUTO_INCREMENT -> SERIAL
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
        cod_prop SERIAL PRIMARY KEY, -- CORRECCIÓN: AUTO_INCREMENT -> SERIAL
        nom_prop VARCHAR(100) NOT NULL,
        ape_prop VARCHAR(100) NOT NULL,
        tel_prop VARCHAR(20),
        email_prop VARCHAR(100) UNIQUE,
        dir_prop VARCHAR(200),
        fecha_registro TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP -- MEJORA: PostgreSQL prefiere 'WITHOUT TIME ZONE'
    )";
    $pdo->exec($sql_propietarios);
    echo "✅ Tabla 'propietarios' creada.<br>";
    
    // Crear tabla inmuebles
    $sql_inmuebles = "CREATE TABLE IF NOT EXISTS inmuebles (
        cod_inm SERIAL PRIMARY KEY, -- CORRECCIÓN: AUTO_INCREMENT -> SERIAL
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
        estado VARCHAR(50) DEFAULT 'Disponible', -- CORRECCIÓN: ENUM -> VARCHAR (PostgreSQL no tiene ENUM, se simula con VARCHAR o CHECK)
        fecha_registro TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP, -- MEJORA: TIMESTAMP
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
        cod_cli SERIAL PRIMARY KEY, -- CORRECCIÓN: AUTO_INCREMENT -> SERIAL
        nom_cli VARCHAR(100) NOT NULL,
        ape_cli VARCHAR(100) NOT NULL,
        tel_cli VARCHAR(20),
        email_cli VARCHAR(100) UNIQUE,
        dir_cli VARCHAR(200),
        fecha_registro TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP, -- MEJORA: TIMESTAMP
        fk_cod_emp_gestion INT,
        FOREIGN KEY (fk_cod_emp_gestion) REFERENCES empleados(cod_emp)
    )";
    $pdo->exec($sql_clientes);
    echo "✅ Tabla 'clientes' creada.<br>";
    
    // Crear tabla contratos
    $sql_contratos = "CREATE TABLE IF NOT EXISTS contratos (
        cod_contrato SERIAL PRIMARY KEY, -- CORRECCIÓN: AUTO_INCREMENT -> SERIAL
        tipo_contrato VARCHAR(50) NOT NULL, -- CORRECCIÓN: ENUM -> VARCHAR
        fecha_inicio DATE NOT NULL,
        fecha_fin DATE,
        valor_contrato DECIMAL(12,2) NOT NULL,
        descripcion TEXT,
        estado VARCHAR(50) DEFAULT 'Activo', -- CORRECCIÓN: ENUM -> VARCHAR
        fecha_creacion TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP, -- MEJORA: TIMESTAMP
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
        cod_visita SERIAL PRIMARY KEY, -- CORRECCIÓN: AUTO_INCREMENT -> SERIAL
        fecha_visita TIMESTAMP WITHOUT TIME ZONE NOT NULL, -- MEJORA: DATETIME -> TIMESTAMP
        observaciones TEXT,
        estado VARCHAR(50) DEFAULT 'Programada', -- CORRECCIÓN: ENUM -> VARCHAR
        fecha_programacion TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP, -- MEJORA: TIMESTAMP
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
        cod_inspeccion SERIAL PRIMARY KEY, -- CORRECCIÓN: AUTO_INCREMENT -> SERIAL
        fecha_inspeccion DATE NOT NULL,
        tipo_inspeccion VARCHAR(100),
        observaciones TEXT,
        resultado VARCHAR(50) DEFAULT 'Pendiente', -- CORRECCIÓN: ENUM -> VARCHAR
        fecha_creacion TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP, -- MEJORA: TIMESTAMP
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
    echo "❌ Error de Conexión/Configuración de Tablas: " . $e->getMessage();
}
?>