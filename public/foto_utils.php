<?php
/**
 * Utilidad para obtener la URL Cloudinary de una foto
 * Uso: get_foto_url($foto)
 */
function get_foto_url($foto) {
    $foto = trim(str_replace('"', '', $foto));
    $cloudinary_base = "https://res.cloudinary.com/drbeqchej/image/upload/";
    $foto_default = $cloudinary_base . "no-disponible.png";

    if (empty($foto)) {
        return $foto_default;
    }
    if (stripos($foto, 'http') === 0) {
        return str_replace(' ', '_', $foto);
    }
    $nombre_archivo = basename($foto);
    return $cloudinary_base . str_replace(' ', '_', $nombre_archivo);
}
