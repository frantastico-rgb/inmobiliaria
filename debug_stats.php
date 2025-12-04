<?php
require_once 'conexion.php';

echo "<h2>Debug de Estadísticas del Dashboard</h2>";
echo "<pre>";

// Verificar si la tabla oficinas existe y sus registros
echo "=== Verificando tabla OFICINAS ===\n";
$result = $conn->query("SHOW TABLES LIKE '%oficina%'");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_array()) {
        echo "Tabla encontrada: " . $row[0] . "\n";
        
        // Contar registros en esta tabla
        $count_result = $conn->query("SELECT COUNT(*) as total FROM " . $row[0]);
        if ($count_result) {
            $count_data = $count_result->fetch_assoc();
            echo "Registros en " . $row[0] . ": " . $count_data['total'] . "\n";
        }
    }
} else {
    echo "No se encontró tabla que contenga 'oficina'\n";
    echo "Listando todas las tablas:\n";
    $all_tables = $conn->query("SHOW TABLES");
    while ($row = $all_tables->fetch_array()) {
        echo "- " . $row[0] . "\n";
    }
}

echo "\n=== Probando consultas del dashboard ===\n";
$tables = ['inmuebles', 'propietarios', 'leads', 'usuarios', 'contratos', 'visitas', 'inspecciones', 'oficinas'];

foreach ($tables as $table) {
    echo "\nTabla: $table\n";
    $sql = "SELECT COUNT(*) as total FROM $table";
    $result = $conn->query($sql);
    if ($result) {
        $data = $result->fetch_assoc();
        echo "Resultado: " . $data['total'] . " registros\n";
    } else {
        echo "ERROR: " . $conn->error . "\n";
    }
}

echo "</pre>";