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
    // 1. Obtención y Saneamiento de datos
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $tipo_interes = $_POST['tipo_interes'] ?? 'consulta';
    $inmueble_id = !empty($_POST['inmueble_id']) ? intval($_POST['inmueble_id']) : null;
    $mensaje = trim($_POST['mensaje'] ?? '');
    $presupuesto_min = !empty($_POST['presupuesto_min']) ? floatval($_POST['presupuesto_min']) : 0;
    $presupuesto_max = !empty($_POST['presupuesto_max']) ? floatval($_POST['presupuesto_max']) : 0;
    $zona_interes = trim($_POST['zona_interes'] ?? '');
    $acepta_contacto = isset($_POST['acepta_contacto']) ? 1 : 0;
    $acepta_marketing = isset($_POST['acepta_marketing']) ? 1 : 0;
    $fuente = $_POST['fuente'] ?? 'web';

    if (empty($nombre) || empty($email) || empty($telefono)) {
        throw new Exception('Nombre, email y teléfono son obligatorios');
    }

    // 2. Lógica de Duplicados (Conciliación con la DB)
    $sql_check = "SELECT id FROM leads WHERE email = ? AND fecha_registro > DATE_SUB(NOW(), INTERVAL 30 DAY) LIMIT 1";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $existing = $stmt_check->get_result()->fetch_assoc();

    if ($existing) {
        $sql_update = "UPDATE leads SET nombre=?, telefono=?, tipo_interes=?, inmueble_interes=?, mensaje=?, presupuesto_min=?, presupuesto_max=?, zona_interes=?, acepta_contacto=?, acepta_marketing=?, estado='nuevo', fecha_registro=NOW() WHERE id=?";
        $stmt = $conn->prepare($sql_update);
        // Tipos: s=string, i=int, d=double. 
        // nombre(s), tel(s), tipo(s), inm(i), mens(s), p_min(d), p_max(d), zona(s), a_c(i), a_m(i), id(i)
        $stmt->bind_param("sssisddsiii", $nombre, $telefono, $tipo_interes, $inmueble_id, $mensaje, $presupuesto_min, $presupuesto_max, $zona_interes, $acepta_contacto, $acepta_marketing, $existing['id']);
        $lead_id = $existing['id'];
        $is_update = true;
    } else {
        $sql_insert = "INSERT INTO leads (nombre, email, telefono, tipo_interes, inmueble_interes, mensaje, presupuesto_min, presupuesto_max, zona_interes, acepta_contacto, acepta_marketing, fuente) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_insert);
        // Tipos: s(4), i(1), s(1), d(2), s(1), i(2), s(1) = ssssisddsii s
        $stmt->bind_param("ssssisddsiii", $nombre, $email, $telefono, $tipo_interes, $inmueble_id, $mensaje, $presupuesto_min, $presupuesto_max, $zona_interes, $acepta_contacto, $acepta_marketing, $fuente);
        $is_update = false;
    }

    if (!$stmt->execute()) { throw new Exception('Error en la base de datos: ' . $conn->error); }
    if (!$is_update) { $lead_id = $conn->insert_id; }

    // 3. Conciliación con Cloudinary (Obtener datos para WhatsApp)
    $inmueble_info = null;
    if ($inmueble_id) {
        // Seleccionamos la foto para tener la URL de Cloudinary a mano
        $sql_inm = "SELECT i.dir_inm, i.precio_alq, i.ciudad_inm, i.foto, t.nom_tipoinm 
                    FROM inmuebles i 
                    LEFT JOIN tipo_inmueble t ON i.cod_tipoinm = t.cod_tipoinm 
                    WHERE i.cod_inm = ?";
        $stmt_inm = $conn->prepare($sql_inm);
        $stmt_inm->bind_param("i", $inmueble_id);
        $stmt_inm->execute();
        $inmueble_info = $stmt_inm->get_result()->fetch_assoc();
    }

    // 4. Configuración y WhatsApp
    $config = $conn->query("SELECT * FROM configuracion_empresa WHERE activo = 1 LIMIT 1")->fetch_assoc();
    $whatsapp_message = generateWhatsAppMessage($nombre, $tipo_interes, $inmueble_info, $mensaje, $config);

    echo json_encode([
        'success' => true,
        'lead_id' => $lead_id,
        'whatsapp_url' => generateWhatsAppUrl($config['whatsapp_principal'], $whatsapp_message)
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

// Funciones de ayuda (se mantienen similares pero optimizadas)
function generateWhatsAppMessage($nombre, $tipo_interes, $inm, $mensaje_p, $config) {
    $msg = "🏠 *{$config['nombre_empresa']}*\n\nHola, soy *{$nombre}*. Me interesa *{$tipo_interes}*.\n";
    if ($inm) {
        $msg .= "\n📍 *Propiedad:* {$inm['dir_inm']}\n💰 *Precio:* $" . number_format($inm['precio_alq'], 0, ',', '.') . "\n";
        // Opcional: Incluir link de Cloudinary en el texto
        $msg .= "🖼️ *Ver foto:* {$inm['foto']}\n";
    }
    if ($mensaje_p) $msg .= "\n💬 *Consulta:* {$mensaje_p}";
    return $msg;
}

function generateWhatsAppUrl($num, $msg) {
    $num = preg_replace('/[^0-9]/', '', $num);
    if (!str_starts_with($num, '57')) $num = '57' . $num;
    return "https://wa.me/{$num}?text=" . urlencode($msg);
}
?>