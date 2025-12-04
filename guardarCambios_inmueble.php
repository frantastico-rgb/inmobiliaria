<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php';

// Iniciar sesión para mensajes
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir los datos del formulario
    $cod_inm = $_POST['cod_inm'];
    $dir_inm = $_POST['dir_inm'];
    $barrio_inm = $_POST['barrio_inm'];
    $ciudad_inm = $_POST['ciudad_inm'];
    $pais_inm = $_POST['pais_inm'] ?? 'Colombia';
    $latitude = $_POST['latitude'];
    $longitud = $_POST['longitud'];
    $video_url = $_POST['video_url'] ?? null;
    $web_p1 = $_POST['web_p1'];
    $web_p2 = $_POST['web_p2'];
    $cod_tipoinm = $_POST['cod_tipoinm'];
    $num_hab = $_POST['num_hab'];
    $precio_alq = $_POST['precio_alq'];
    $cod_prop = $_POST['cod_prop'];
    $caract_inm = $_POST['caract_inm'];
    $notas_inm = $_POST['notas_inm'];

    // Manejo de archivos multimedia
    $foto_nombre = $_POST['foto_actual'] ?? null; // Foto principal actual
    $foto_secundaria_nombre = $_POST['foto_secundaria_actual'] ?? null; // Foto secundaria actual
    $video_nombre = $_POST['video_actual'] ?? null; // Video actual

    // Manejo de la foto principal (opcional)
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        // Validar tipo y tamaño de imagen
        $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxImageSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['foto']['type'], $allowedImageTypes)) {
            echo "❌ Error: Formato de foto principal no válido. Use JPG, PNG o GIF.";
            exit();
        }
        
        if ($_FILES['foto']['size'] > $maxImageSize) {
            echo "❌ Error: La foto principal es muy grande. Máximo 5MB.";
            exit();
        }
        
        $nombre_temporal = $_FILES['foto']['tmp_name'];
        $nombre_archivo = time() . '_' . basename($_FILES['foto']['name']);
        $ruta_destino = 'uploads/' . $nombre_archivo;

        if (move_uploaded_file($nombre_temporal, $ruta_destino)) {
            $foto_nombre = $ruta_destino;
        } else {
            echo "❌ Error al subir la foto principal.";
            exit();
        }
    }
    
    // Manejo de la foto secundaria (opcional)
    if (isset($_FILES['foto_secundaria']) && $_FILES['foto_secundaria']['error'] === UPLOAD_ERR_OK) {
        // Validar tipo y tamaño de imagen
        $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxImageSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['foto_secundaria']['type'], $allowedImageTypes)) {
            echo "❌ Error: Formato de foto secundaria no válido. Use JPG, PNG o GIF.";
            exit();
        }
        
        if ($_FILES['foto_secundaria']['size'] > $maxImageSize) {
            echo "❌ Error: La foto secundaria es muy grande. Máximo 5MB.";
            exit();
        }
        
        $nombre_temporal = $_FILES['foto_secundaria']['tmp_name'];
        $nombre_archivo = time() . '_sec_' . basename($_FILES['foto_secundaria']['name']);
        $ruta_destino = 'uploads/' . $nombre_archivo;

        if (move_uploaded_file($nombre_temporal, $ruta_destino)) {
            $foto_secundaria_nombre = $ruta_destino;
        } else {
            echo "❌ Error al subir la foto secundaria.";
            exit();
        }
    }
    
    // Manejo del video (opcional)
    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        // Validar tipo y tamaño de video
        $allowedVideoTypes = ['video/mp4', 'video/mov', 'video/quicktime', 'video/avi', 'video/x-msvideo'];
        $maxVideoSize = 50 * 1024 * 1024; // 50MB
        
        if (!in_array($_FILES['video']['type'], $allowedVideoTypes)) {
            echo "❌ Error: Formato de video no válido. Use MP4, MOV o AVI.";
            exit();
        }
        
        if ($_FILES['video']['size'] > $maxVideoSize) {
            echo "❌ Error: El video es muy grande. Máximo 50MB.";
            exit();
        }
        
        $nombre_temporal = $_FILES['video']['tmp_name'];
        $nombre_archivo = time() . '_video_' . basename($_FILES['video']['name']);
        $ruta_destino = 'uploads/' . $nombre_archivo;

        if (move_uploaded_file($nombre_temporal, $ruta_destino)) {
            $video_nombre = $ruta_destino;
        } else {
            echo "❌ Error al subir el video.";
            exit();
        }
    }

    // Preparar la consulta SQL para la actualización (incluyendo video_url)
    $sql = "UPDATE inmuebles SET
            dir_inm = ?,
            barrio_inm = ?,
            ciudad_inm = ?,
            pais_inm = ?,
            latitude = ?,
            longitud = ?,
            foto = ?,
            foto_2 = ?,
            video = ?,
            video_url = ?,
            web_p1 = ?,
            web_p2 = ?,
            cod_tipoinm = ?,
            num_hab = ?,
            precio_alq = ?,
            cod_prop = ?,
            caract_inm = ?,
            notas_inm = ?
            WHERE cod_inm = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssddssssssiidissi", 
        $dir_inm, $barrio_inm, $ciudad_inm, $pais_inm,
        $latitude, $longitud, 
        $foto_nombre, $foto_secundaria_nombre, $video_nombre, $video_url,
        $web_p1, $web_p2, 
        $cod_tipoinm, $num_hab, $precio_alq, $cod_prop, 
        $caract_inm, $notas_inm, $cod_inm);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "✅ Inmueble actualizado con éxito. Se han guardado todos los cambios incluyendo multimedia y geolocalización.";
        header("Location: lista_inmuebles.php");
        exit();
    } else {
        echo "❌ Error al actualizar el inmueble: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Acceso no permitido.";
}

$conn->close();
?>