<?php
function get_foto_url($foto) {
    // 1. Limpieza inicial
    $foto = trim(str_replace('"', '', $foto));
    $cloudinary_base = "https://res.cloudinary.com/drbeqchej/image/upload/";
    
    if (empty($foto)) {
        return $cloudinary_base . "no-disponible.png"; // Imagen por defecto en Cloudinary
    }
    
    // 2. Extraer solo el nombre del archivo final.
    // Esto funciona para URLs completas (ej: .../uploads/mi_foto.jpg) y rutas locales (ej: uploads/mi_foto.jpg),
    // ya que en ambos casos, el public_id en Cloudinary es simplemente "mi_foto.jpg".
    $nombre_archivo = basename($foto);
    
    // 3. Construir la URL final, limpia y no versionada.
    // Cloudinary encontrará el archivo por su public_id, que es el nombre del archivo.
    return $cloudinary_base . str_replace(' ', '_', $nombre_archivo);
}