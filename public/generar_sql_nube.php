<?php
// Script para generar SQL de sincronización para TiDB Cloud
// Ejecuta esto en LOCAL para obtener los datos precisos del ID 15

require_once '../conexion.php'; // Ajuste: Busca la conexión en la carpeta raíz

// Obtener ID de la URL (ej: generar_sql_nube.php?id=20) o usar 15 por defecto
$id_inmueble = isset($_GET['id']) ? intval($_GET['id']) : 15;

echo "<h2>🚀 Sincronización a Nube (TiDB Cloud)</h2>";
echo "<p>Generando SQL para el Inmueble ID: <strong>$id_inmueble</strong></p>";
echo "<p><small>Para cambiar de inmueble, agrega ?id=NUMERO a la URL o usa el botón desde la edición.</small></p>";

$sql = "SELECT * FROM inmuebles WHERE cod_inm = $id_inmueble";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    $columns = [];
    $values = [];
    $updates = [];
    
    foreach ($row as $key => $value) {
        $columns[] = "`$key`";
        
        if ($value === null) {
            $values[] = "NULL";
            $updates[] = "`$key` = NULL";
        } else {
            $escaped = $conn->real_escape_string($value);
            $values[] = "'$escaped'";
            $updates[] = "`$key` = '$escaped'";
        }
    }
    
    $final_sql = "INSERT INTO inmuebles (" . implode(", ", $columns) . ")\nVALUES (" . implode(", ", $values) . ")\nON DUPLICATE KEY UPDATE\n" . implode(",\n", $updates) . ";";
    
    echo "<p>Copia el siguiente código y ejecútalo en la consola SQL de TiDB Cloud:</p>";
    echo "<textarea style='width:100%; height:400px; font-family:monospace; padding:10px; border:2px solid #27ae60; border-radius:5px;'>";
    echo $final_sql;
    echo "</textarea>";
    
} else {
    echo "<p style='color:red'>❌ No se encontró el inmueble ID $id_inmueble en la base de datos LOCAL.</p>";
}
?>