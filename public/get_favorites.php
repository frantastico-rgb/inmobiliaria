<?php
// API para obtener datos de inmuebles favoritos
require_once '../conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['ids']) || !is_array($input['ids']) || empty($input['ids'])) {
    echo json_encode([]);
    exit;
}

try {
    // Sanitizar IDs
    $ids = array_filter(array_map('intval', $input['ids']));
    
    if (empty($ids)) {
        echo json_encode([]);
        exit;
    }

    // Crear placeholders para la consulta
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    
    // Consulta SQL
    $sql = "SELECT i.*, t.nom_tipoinm, p.nom_prop, p.tel_prop 
            FROM inmuebles i 
            LEFT JOIN tipo_inmueble t ON i.cod_tipoinm = t.cod_tipoinm 
            LEFT JOIN propietarios p ON i.cod_prop = p.cod_prop 
            WHERE i.cod_inm IN ($placeholders)
            ORDER BY i.cod_inm DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
    $stmt->execute();
    
    $resultado = $stmt->get_result();
    $inmuebles = [];

    while ($row = $resultado->fetch_assoc()) {
        $inmuebles[] = $row;
    }

    echo json_encode($inmuebles);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error del servidor']);
}

$conn->close();
?>