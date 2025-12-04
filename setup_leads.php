<?php
// Script para crear tabla de leads y contactos
require_once 'conexion.php';

try {
    // Crear tabla de leads
    $sql_leads = "CREATE TABLE IF NOT EXISTS leads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL,
        telefono VARCHAR(20) NOT NULL,
        tipo_interes ENUM('comprar', 'alquilar', 'vender', 'consulta') DEFAULT 'consulta',
        inmueble_interes INT NULL,
        mensaje TEXT,
        presupuesto_min DECIMAL(12,2) NULL,
        presupuesto_max DECIMAL(12,2) NULL,
        zona_interes VARCHAR(100) NULL,
        acepta_contacto BOOLEAN DEFAULT TRUE,
        acepta_marketing BOOLEAN DEFAULT FALSE,
        fuente VARCHAR(50) DEFAULT 'web',
        estado ENUM('nuevo', 'contactado', 'interesado', 'convertido', 'descartado') DEFAULT 'nuevo',
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_contacto TIMESTAMP NULL,
        notas TEXT NULL,
        agente_asignado INT NULL,
        prioridad ENUM('baja', 'media', 'alta') DEFAULT 'media',
        INDEX idx_estado (estado),
        INDEX idx_fecha_registro (fecha_registro),
        INDEX idx_tipo_interes (tipo_interes),
        FOREIGN KEY (inmueble_interes) REFERENCES inmuebles(cod_inm) ON DELETE SET NULL,
        FOREIGN KEY (agente_asignado) REFERENCES empleados(cod_emp) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($sql_leads)) {
        echo "✅ Tabla 'leads' creada exitosamente<br>";
    }

    // Crear tabla de seguimientos
    $sql_seguimientos = "CREATE TABLE IF NOT EXISTS seguimientos_leads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        lead_id INT NOT NULL,
        tipo_contacto ENUM('llamada', 'email', 'whatsapp', 'visita', 'otro') NOT NULL,
        descripcion TEXT NOT NULL,
        resultado ENUM('exitoso', 'no_contesta', 'reagendar', 'no_interesado') NULL,
        fecha_contacto TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        proxima_accion DATE NULL,
        agente_id INT NULL,
        FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
        FOREIGN KEY (agente_id) REFERENCES empleados(cod_emp) ON DELETE SET NULL,
        INDEX idx_lead_fecha (lead_id, fecha_contacto)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($sql_seguimientos)) {
        echo "✅ Tabla 'seguimientos_leads' creada exitosamente<br>";
    }

    // Crear tabla de configuración de empresa
    $sql_config = "CREATE TABLE IF NOT EXISTS configuracion_empresa (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre_empresa VARCHAR(100) DEFAULT 'Casa Meta',
        telefono_principal VARCHAR(20) DEFAULT '',
        whatsapp_principal VARCHAR(20) DEFAULT '',
        email_contacto VARCHAR(100) DEFAULT '',
        direccion_oficina TEXT DEFAULT '',
        horario_atencion VARCHAR(100) DEFAULT 'Lunes a Viernes 8:00 AM - 6:00 PM',
        mensaje_whatsapp_default TEXT DEFAULT '',
        activo BOOLEAN DEFAULT TRUE,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($sql_config)) {
        echo "✅ Tabla 'configuracion_empresa' creada exitosamente<br>";
        
        // Insertar configuración inicial
        $sql_insert_config = "INSERT IGNORE INTO configuracion_empresa 
            (id, nombre_empresa, telefono_principal, whatsapp_principal, email_contacto, mensaje_whatsapp_default) 
            VALUES (1, 'Casa Meta', '+57 300 123 4567', '573001234567', 'info@casameta.com', 
            'Hola! Me interesa obtener más información sobre la propiedad que vi en su página web. ¿Podrían ayudarme?')";
        
        if ($conn->query($sql_insert_config)) {
            echo "✅ Configuración inicial de empresa insertada<br>";
        }
    }

    echo "<br>🎉 <strong>Base de datos de leads configurada exitosamente!</strong><br>";
    echo "📊 Ahora puedes capturar y gestionar leads de manera profesional.";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Configuración de Leads - Casa Meta</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; padding: 15px; border-radius: 5px; color: #155724; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>🚀 Sistema de Leads Configurado</h1>
    <div class="success">
        <h3>✅ Configuración Completada</h3>
        <p><strong>Próximos pasos:</strong></p>
        <ul>
            <li>✅ Base de datos de leads creada</li>
            <li>🔄 Formularios de contacto listos para implementar</li>
            <li>📱 WhatsApp integrado para seguimiento</li>
            <li>📊 Sistema de gestión de leads operativo</li>
        </ul>
        <br>
        <a href="public/index.php" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            🏠 Volver al Portal Público
        </a>
    </div>
</body>
</html>