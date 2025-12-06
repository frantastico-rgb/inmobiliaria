# Usar una imagen oficial de PHP con Apache (Basada en Debian)
FROM php:8.1-apache

# Instalación de dependencias del sistema operativo y configuración
RUN apt-get update \
    && apt-get install -y \
    # DEPENDENCIA CRÍTICA: Librerías de desarrollo de PostgreSQL
    libpq-dev \
    # Otras herramientas que puedas necesitar (opcional)
    # git \
    && rm -rf /var/lib/apt/lists/* \
    \
    # Instalar y habilitar las extensiones de PHP
    # Se instala pdo y pdo_pgsql (la extensión de PostgreSQL)
    && docker-php-ext-install pdo pdo_pgsql \
    && docker-php-ext-enable pdo pdo_pgsql \
    \
    # Habilitar mod_rewrite de Apache para URLs amigables
    && a2enmod rewrite

# Copiar el código de tu aplicación al directorio web del servidor en el contenedor
COPY . /var/www/html/

# DOCUMENTAR EL PUERTO QUE APACHE ESCUCHA
EXPOSE 80

# Establecer los permisos correctos para que Apache pueda leer/escribir en la carpeta de archivos subidos
RUN chown -R www-data:www-data /var/www/html/uploads