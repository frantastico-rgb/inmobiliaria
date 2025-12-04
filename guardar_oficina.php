<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir los datos del formulario
    $nom_ofi = $_POST['nom_ofi'];
    $dir_ofi = $_POST['dir_ofi'];
    $tel_ofi = $_POST['tel_ofi'];
    $email_ofi = $_POST['email_ofi'];
    $ciudad_ofi = $_POST['ciudad_ofi'] ?? '';
    $pais_ofi = $_POST['pais_ofi'] ?? 'Colombia';
    $latitud = $_POST['latitud'];
    $longitud = $_POST['longitud'];
    $video_url_ofi = $_POST['video_url_ofi'] ?? null;
    $web_p1_ofi = $_POST['web_p1_ofi'] ?? null;
    $web_p2_ofi = $_POST['web_p2_ofi'] ?? null;

    // Manejo de archivos multimedia
    $foto_ofi_nombre = null;
    $foto_secundaria_ofi_nombre = null;
    $video_ofi_nombre = null;

    // Manejo de la foto principal (opcional)
    if (isset($_FILES['foto_ofi']) && $_FILES['foto_ofi']['error'] === UPLOAD_ERR_OK) {
        // Validar tipo y tamaño de imagen
        $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxImageSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['foto_ofi']['type'], $allowedImageTypes)) {
            echo "❌ Error: Formato de foto principal no válido. Use JPG, PNG o GIF.";
            exit();
        }
        
        if ($_FILES['foto_ofi']['size'] > $maxImageSize) {
            echo "❌ Error: La foto principal es muy grande. Máximo 5MB.";
            exit();
        }
        
        $nombre_temporal = $_FILES['foto_ofi']['tmp_name'];
        $nombre_archivo = time() . '_' . basename($_FILES['foto_ofi']['name']);
        $ruta_destino = 'uploads/' . $nombre_archivo;

        if (move_uploaded_file($nombre_temporal, $ruta_destino)) {
            $foto_ofi_nombre = $ruta_destino;
        } else {
            echo "❌ Error al subir la foto principal.";
            exit();
        }
    }
    
    // Manejo de la foto secundaria (opcional)
    if (isset($_FILES['foto_secundaria_ofi']) && $_FILES['foto_secundaria_ofi']['error'] === UPLOAD_ERR_OK) {
        // Validar tipo y tamaño de imagen
        $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxImageSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['foto_secundaria_ofi']['type'], $allowedImageTypes)) {
            echo "❌ Error: Formato de foto secundaria no válido. Use JPG, PNG o GIF.";
            exit();
        }
        
        if ($_FILES['foto_secundaria_ofi']['size'] > $maxImageSize) {
            echo "❌ Error: La foto secundaria es muy grande. Máximo 5MB.";
            exit();
        }
        
        $nombre_temporal = $_FILES['foto_secundaria_ofi']['tmp_name'];
        $nombre_archivo = time() . '_sec_' . basename($_FILES['foto_secundaria_ofi']['name']);
        $ruta_destino = 'uploads/' . $nombre_archivo;

        if (move_uploaded_file($nombre_temporal, $ruta_destino)) {
            $foto_secundaria_ofi_nombre = $ruta_destino;
        } else {
            echo "❌ Error al subir la foto secundaria.";
            exit();
        }
    }
    
    // Manejo del video (opcional)
    if (isset($_FILES['video_ofi']) && $_FILES['video_ofi']['error'] === UPLOAD_ERR_OK) {
        // Validar tipo y tamaño de video
        $allowedVideoTypes = ['video/mp4', 'video/mov', 'video/quicktime', 'video/avi', 'video/x-msvideo'];
        $maxVideoSize = 50 * 1024 * 1024; // 50MB
        
        if (!in_array($_FILES['video_ofi']['type'], $allowedVideoTypes)) {
            echo "❌ Error: Formato de video no válido. Use MP4, MOV o AVI.";
            exit();
        }
        
        if ($_FILES['video_ofi']['size'] > $maxVideoSize) {
            echo "❌ Error: El video es muy grande. Máximo 50MB.";
            exit();
        }
        
        $nombre_temporal = $_FILES['video_ofi']['tmp_name'];
        $nombre_archivo = time() . '_video_' . basename($_FILES['video_ofi']['name']);
        $ruta_destino = 'uploads/' . $nombre_archivo;

        if (move_uploaded_file($nombre_temporal, $ruta_destino)) {
            $video_ofi_nombre = $ruta_destino;
        } else {
            echo "❌ Error al subir el video.";
            exit();
        }
    }

    // Preparar la consulta SQL para la inserción (con nuevos campos)
    $sql = "INSERT INTO oficina (nom_ofi, dir_ofi, tel_ofi, email_ofi, ciudad_ofi, pais_ofi, latitud, longitud, foto_ofi, foto_secundaria_ofi, video_ofi, video_url_ofi, web_p1_ofi, web_p2_ofi)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssddssssss", 
        $nom_ofi, $dir_ofi, $tel_ofi, $email_ofi, 
        $ciudad_ofi, $pais_ofi, $latitud, $longitud, 
        $foto_ofi_nombre, $foto_secundaria_ofi_nombre, $video_ofi_nombre, $video_url_ofi,
        $web_p1_ofi, $web_p2_ofi);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "✅ Oficina agregada con éxito. Se han guardado todos los datos incluyendo multimedia y geolocalización.";
        header("Location: lista_oficinas.php");
        exit();
    } else {
        echo "❌ Error al guardar la oficina: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Acceso no permitido.";
}

$conn->close();
?>