<?php
// Evitar que cualquier error de PHP ensucie la salida JSON
error_reporting(0);
header('Content-Type: application/json');

// CORRECCIÓN 1: Incluir la conexión a la base de datos. Sin esto, la consulta falla.
require_once __DIR__ . '/../src/conexion.php';

if (file_exists(__DIR__ . '/foto_utils.php')) {
    require_once __DIR__ . '/foto_utils.php';
} else {
    // Definimos una función de emergencia por si el archivo no carga
    function get_foto_url($foto) { return $foto; }
}

$data = json_decode(file_get_contents('php://input'), true);
$ids = isset($data['ids']) ? $data['ids'] : [];

if (empty($ids)) {
    echo json_encode([]);
    exit;
}

// CORRECCIÓN 2: Usar la columna correcta 'cod_tipoinm' en el JOIN.
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$sql = "SELECT i.*, t.nom_tipoinm 
        FROM inmuebles i 
        LEFT JOIN tipo_inmueble t ON i.cod_tipoinm = t.cod_tipoinm 
        WHERE i.cod_inm IN ($placeholders)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
$stmt->execute();
$result = $stmt->get_result();

$favorites = [];

while ($row = $result->fetch_assoc()) {
    $row['foto'] = get_foto_url($row['foto'] ?? '');
    $favorites[] = $row;
}

echo json_encode($favorites);
exit;
