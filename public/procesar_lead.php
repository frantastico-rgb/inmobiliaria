<?php
// API para procesar formularios de leads
require_once '../conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

try {
    // Obtener datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $tipo_interes = $_POST['tipo_interes'] ?? 'consulta';
    $inmueble_interes = !empty($_POST['inmueble_id']) ? intval($_POST['inmueble_id']) : null;
    $mensaje = trim($_POST['mensaje'] ?? '');
    $presupuesto_min = !empty($_POST['presupuesto_min']) ? floatval($_POST['presupuesto_min']) : null;
    $presupuesto_max = !empty($_POST['presupuesto_max']) ? floatval($_POST['presupuesto_max']) : null;
    $zona_interes = trim($_POST['zona_interes'] ?? '');
    $acepta_contacto = isset($_POST['acepta_contacto']) ? 1 : 0;
    $acepta_marketing = isset($_POST['acepta_marketing']) ? 1 : 0;
    $fuente = $_POST['fuente'] ?? 'web';

    // Validaciones básicas
    if (empty($nombre) || empty($email) || empty($telefono)) {
        throw new Exception('Nombre, email y teléfono son obligatorios');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email no válido');
    }

    // Verificar si ya existe un lead con este email recientemente (últimos 30 días)
    $sql_check = "SELECT id, fecha_registro FROM leads 
                  WHERE email = ? AND fecha_registro > DATE_SUB(NOW(), INTERVAL 30 DAY) 
                  ORDER BY fecha_registro DESC LIMIT 1";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $existing = $stmt_check->get_result()->fetch_assoc();

    if ($existing) {
        // Actualizar lead existente en lugar de crear uno nuevo
        $sql_update = "UPDATE leads SET 
                       nombre = ?, telefono = ?, tipo_interes = ?, 
                       inmueble_interes = ?, mensaje = ?, presupuesto_min = ?, 
                       presupuesto_max = ?, zona_interes = ?, acepta_contacto = ?, 
                       acepta_marketing = ?, estado = 'nuevo', fecha_registro = NOW()
                       WHERE id = ?";
        
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("sssisssibii", $nombre, $telefono, $tipo_interes, $inmueble_interes, 
                         $mensaje, $presupuesto_min, $presupuesto_max, $zona_interes, 
                         $acepta_contacto, $acepta_marketing, $existing['id']);
        
        if (!$stmt->execute()) {
            throw new Exception('Error al actualizar el lead');
        }
        
        $lead_id = $existing['id'];
        $is_update = true;
    } else {
        // Crear nuevo lead
        $sql_insert = "INSERT INTO leads 
                       (nombre, email, telefono, tipo_interes, inmueble_interes, mensaje, 
                        presupuesto_min, presupuesto_max, zona_interes, acepta_contacto, 
                        acepta_marketing, fuente) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql_insert);
        $stmt->bind_param("ssssisdsiibis", $nombre, $email, $telefono, $tipo_interes, 
                         $inmueble_interes, $mensaje, $presupuesto_min, $presupuesto_max, 
                         $zona_interes, $acepta_contacto, $acepta_marketing, $fuente);

        if (!$stmt->execute()) {
            throw new Exception('Error al guardar el lead');
        }
        
        $lead_id = $conn->insert_id;
        $is_update = false;
    }

    // Obtener información del inmueble si aplica
    $inmueble_info = null;
    if ($inmueble_interes) {
        $sql_inmueble = "SELECT i.cod_inm, i.dir_inm, i.precio_alq, i.ciudad_inm, t.nom_tipoinm, p.nom_prop, p.tel_prop
                         FROM inmuebles i 
                         LEFT JOIN tipo_inmueble t ON i.cod_tipoinm = t.cod_tipoinm
                         LEFT JOIN propietarios p ON i.cod_prop = p.cod_prop
                         WHERE i.cod_inm = ?";
        $stmt_inmueble = $conn->prepare($sql_inmueble);
        $stmt_inmueble->bind_param("i", $inmueble_interes);
        $stmt_inmueble->execute();
        $inmueble_info = $stmt_inmueble->get_result()->fetch_assoc();
    }

    // Obtener configuración de la empresa para WhatsApp
    $sql_config = "SELECT * FROM configuracion_empresa WHERE activo = 1 LIMIT 1";
    $config_result = $conn->query($sql_config);
    $config = $config_result->fetch_assoc();

    // Generar mensaje de WhatsApp personalizado
    $whatsapp_message = generateWhatsAppMessage($nombre, $tipo_interes, $inmueble_info, $mensaje, $config);

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'lead_id' => $lead_id,
        'is_update' => $is_update,
        'message' => $is_update ? 'Información actualizada exitosamente' : 'Lead registrado exitosamente',
        'whatsapp_url' => generateWhatsAppUrl($config['whatsapp_principal'], $whatsapp_message),
        'whatsapp_message' => $whatsapp_message
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
}

function generateWhatsAppMessage($nombre, $tipo_interes, $inmueble_info, $mensaje_personal, $config) {
    $message = "🏠 *{$config['nombre_empresa']}*\n\n";
    $message .= "Hola! Soy *{$nombre}* y me interesa ";
    
    switch ($tipo_interes) {
        case 'comprar':
            $message .= "comprar una propiedad";
            break;
        case 'alquilar':
            $message .= "alquilar una propiedad";
            break;
        case 'vender':
            $message .= "vender mi propiedad";
            break;
        default:
            $message .= "obtener información sobre sus servicios";
    }
    
    if ($inmueble_info) {
        $message .= ".\n\n📍 *Propiedad de interés:*\n";
        $message .= "• {$inmueble_info['dir_inm']}\n";
        $message .= "• {$inmueble_info['nom_tipoinm']} en {$inmueble_info['ciudad_inm']}\n";
        $message .= "• Precio: $" . number_format($inmueble_info['precio_alq'], 0, ',', '.') . "\n";
    }
    
    if (!empty($mensaje_personal)) {
        $message .= "\n💬 *Mi consulta:*\n{$mensaje_personal}\n";
    }
    
    $message .= "\n¿Podrían contactarme para brindarme más información?\n\n";
    $message .= "Gracias! 😊";
    
    return $message;
}

function generateWhatsAppUrl($whatsapp_number, $message) {
    // Limpiar número (remover espacios, guiones, paréntesis)
    $clean_number = preg_replace('/[^0-9]/', '', $whatsapp_number);
    
    // Si no empieza con código de país, asumir Colombia (+57)
    if (!str_starts_with($clean_number, '57')) {
        $clean_number = '57' . $clean_number;
    }
    
    return "https://wa.me/{$clean_number}?text=" . urlencode($message);
}

$conn->close();
?>