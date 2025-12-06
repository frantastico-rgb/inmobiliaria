# Usar una imagen oficial de PHP con Apache
FROM php:8.1-apache

# Instalar la extensión de PHP para MySQL (MySQLi)
# RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

RUN docker-php-ext-install pdo pdo_pgsql && docker-php-ext-enable pdo pdo_pgsql

# Habilitar mod_rewrite de Apache para URLs amigables (si las usas)
RUN a2enmod rewrite

# Copiar el código de tu aplicación al directorio web del servidor en el contenedor
COPY . /var/www/html/

# DOCUMENTAR EL PUERTO QUE APACHE ESCUCHA
EXPOSE 80

# Establecer los permisos correctos para que Apache pueda leer/escribir en la carpeta de archivos subidos
RUN chown -R www-data:www-data /var/www/html/uploads
