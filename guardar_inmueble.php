<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir los datos del formulario
    $dir_inm = $_POST['dir_inm'];
    $barrio_inm = $_POST['barrio_inm'];
    $ciudad_inm = $_POST['ciudad_inm'];
    $pais_inm = $_POST['pais_inm'] ?? 'Colombia';
    $latitude = $_POST['latitude'];
    $longitud = $_POST['longitud'];
    $web_p1 = $_POST['web_p1'];
    $web_p2 = $_POST['web_p2'];
    $cod_tipoinm = $_POST['cod_tipoinm'];
    $num_hab = $_POST['num_hab'];
    $precio_alq = $_POST['precio_alq'];
    $cod_prop = $_POST['cod_prop'];
    $caract_inm = $_POST['caract_inm'];
    $notas_inm = $_POST['notas_inm'];

    // Manejo de la subida de archivos
    $foto_nombre = null;
    $foto_secundaria_nombre = null;
    $video_nombre = null;
    
    // Foto principal
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $nombre_temporal = $_FILES['foto']['tmp_name'];
        $nombre_archivo = time() . '_' . basename($_FILES['foto']['name']);
        $ruta_destino = 'uploads/' . $nombre_archivo;

        if (move_uploaded_file($nombre_temporal, $ruta_destino)) {
            $foto_nombre = $ruta_destino;
        } else {
            echo "Error al subir la foto principal.";
            exit();
        }
    }
    
    // Foto secundaria
    if (isset($_FILES['foto_secundaria']) && $_FILES['foto_secundaria']['error'] === UPLOAD_ERR_OK) {
        $nombre_temporal = $_FILES['foto_secundaria']['tmp_name'];
        $nombre_archivo = time() . '_sec_' . basename($_FILES['foto_secundaria']['name']);
        $ruta_destino = 'uploads/' . $nombre_archivo;

        if (move_uploaded_file($nombre_temporal, $ruta_destino)) {
            $foto_secundaria_nombre = $ruta_destino;
        } else {
            echo "Error al subir la foto secundaria.";
            exit();
        }
    }
    
    // Video
    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $nombre_temporal = $_FILES['video']['tmp_name'];
        $nombre_archivo = time() . '_video_' . basename($_FILES['video']['name']);
        $ruta_destino = 'uploads/' . $nombre_archivo;

        if (move_uploaded_file($nombre_temporal, $ruta_destino)) {
            $video_nombre = $ruta_destino;
        } else {
            echo "Error al subir el video.";
            exit();
        }
    }

    // Preparar la consulta SQL para la inserción
    $sql = "INSERT INTO inmuebles (dir_inm, barrio_inm, ciudad_inm, pais_inm, latitude, longitud, foto, foto_2, video, web_p1, web_p2, cod_tipoinm, num_hab, precio_alq, cod_prop, caract_inm, notas_inm)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssiiisss", $dir_inm, $barrio_inm, $ciudad_inm, $pais_inm, $latitude, $longitud, $foto_nombre, $foto_secundaria_nombre, $video_nombre, $web_p1, $web_p2, $cod_tipoinm, $num_hab, $precio_alq, $cod_prop, $caract_inm, $notas_inm);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Inmueble agregado con éxito.";
        header("Location: lista_inmuebles.php");
        exit();
    } else {
        echo "Error al guardar el inmueble: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Acceso no permitido.";
}

$conn->close();
?>