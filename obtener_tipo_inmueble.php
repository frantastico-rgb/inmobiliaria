<?php
require_once 'conexion.php';

if (isset($_GET['cod_tipoinm']) && is_numeric($_GET['cod_tipoinm'])) {
    $codTipoInm = $_GET['cod_tipoinm'];

    $sql = "SELECT nom_tipoinm FROM tipo_inmueble WHERE cod_tipoinm = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $codTipoInm);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['nom_tipoinm' => $row['nom_tipoinm']]);
    } else {
        echo json_encode([]); // Devuelve un array vacío si no se encuentra
    }

    $stmt->close();
} else {
    echo json_encode([]); // Devuelve un array vacío si el parámetro es inválido
}

$conn->close();
?>