# 🐳 Guía de Despliegue Docker - Sistema Inmobiliario

## 🏗️ Arquitectura Docker

### Stack Tecnológico
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   NGINX Proxy   │    │  PHP-FPM App    │    │  MySQL Database │
│   (Port 80/443) │◄──►│   (Port 9000)   │◄──►│   (Port 3306)   │
│                 │    │                 │    │                 │
│ - Load Balancer │    │ - Apache/Nginx  │    │ - Persistent Vol│
│ - SSL Termination│    │ - PHP 7.4+     │    │ - Backups      │
│ - Static Assets │    │ - Inmobiliaria  │    │ - Replication  │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Ventajas del Despliegue en Docker

✅ **Consistencia**: Mismo entorno en desarrollo, testing y producción  
✅ **Escalabilidad**: Fácil escalado horizontal con múltiples contenedores  
✅ **Aislamiento**: Dependencias encapsuladas, sin conflictos  
✅ **Portabilidad**: Funciona en cualquier servidor con Docker  
✅ **CI/CD**: Integración perfecta con pipelines de despliegue  
✅ **Rollback**: Volver a versiones anteriores instantáneamente  
✅ **Monitoring**: Logs centralizados y métricas por contenedor  

---

## 📁 Estructura de Archivos Docker

```
INMOBILIARIA_1/
├── docker/
│   ├── nginx/
│   │   ├── nginx.conf
│   │   ├── default.conf
│   │   └── ssl/
│   ├── php/
│   │   ├── Dockerfile
│   │   ├── php.ini
│   │   └── www.conf
│   ├── mysql/
│   │   ├── init.sql
│   │   ├── my.cnf
│   │   └── backups/
│   └── scripts/
│       ├── deploy.sh
│       ├── backup.sh
│       └── setup.sh
├── docker-compose.yml
├── docker-compose.prod.yml
├── .dockerignore
├── .env.example
└── [código aplicación]
```

---

## 🐋 Dockerfiles

### Dockerfile Principal (`docker/php/Dockerfile`)

```dockerfile
FROM php:8.1-fpm-alpine

# Información del mantenedor
LABEL maintainer="inmobiliaria@company.com"
LABEL description="Sistema de Gestión Inmobiliaria"

# Argumentos de construcción
ARG APP_ENV=production
ARG PHP_VERSION=8.1

# Variables de entorno
ENV APP_ENV=${APP_ENV}
ENV PHP_MEMORY_LIMIT=512M
ENV PHP_UPLOAD_MAX_FILESIZE=20M
ENV PHP_POST_MAX_SIZE=25M

# Instalar dependencias del sistema
RUN apk add --no-cache \
    curl \
    wget \
    git \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    mysql-client \
    nginx \
    supervisor

# Instalar extensiones PHP
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp

RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    gd \
    intl \
    zip \
    mbstring \
    opcache \
    bcmath \
    exif

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear usuario no-root
RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -S www -G www

# Crear directorios de trabajo
RUN mkdir -p /var/www/html /var/log/php-fpm /run/nginx

# Copiar archivos de configuración
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copiar código de la aplicación
WORKDIR /var/www/html
COPY --chown=www:www . .

# Crear directorio uploads y configurar permisos
RUN mkdir -p uploads logs cache && \
    chown -R www:www uploads logs cache && \
    chmod -R 755 uploads && \
    chmod -R 777 uploads logs cache

# Instalar dependencias PHP si existe composer.json
RUN if [ -f composer.json ]; then \
    composer install --no-dev --optimize-autoloader --no-scripts; \
    fi

# Optimizaciones de producción
RUN if [ "$APP_ENV" = "production" ]; then \
    # Precompilar OPcache
    php -d opcache.enable_cli=1 -r "echo 'OPcache enabled';" && \
    # Limpiar cache
    rm -rf /tmp/* /var/tmp/*; \
    fi

# Exponer puertos
EXPOSE 80 9000

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health.php || exit 1

# Comando de inicio
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

### Dockerfile para Nginx (`docker/nginx/Dockerfile`)

```dockerfile
FROM nginx:1.21-alpine

# Copiar configuración personalizada
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Crear directorio para logs
RUN mkdir -p /var/log/nginx

# Copiar archivos estáticos
COPY --chown=nginx:nginx public/ /var/www/html/public/
COPY --chown=nginx:nginx uploads/ /var/www/html/uploads/

# Permisos
RUN chown -R nginx:nginx /var/www/html && \
    chmod -R 755 /var/www/html

EXPOSE 80 443

CMD ["nginx", "-g", "daemon off;"]
```

---

## 🐘 Configuración MySQL

### Dockerfile MySQL (`docker/mysql/Dockerfile`)

```dockerfile
FROM mysql:8.0

# Variables de entorno
ENV MYSQL_ROOT_PASSWORD=rootpass123
ENV MYSQL_DATABASE=inmobil
ENV MYSQL_USER=inmobil_user
ENV MYSQL_PASSWORD=inmobil_pass123

# Copiar archivos de configuración
COPY docker/mysql/my.cnf /etc/mysql/conf.d/custom.cnf
COPY docker/mysql/init.sql /docker-entrypoint-initdb.d/

# Crear directorios para backups
RUN mkdir -p /var/backups/mysql

# Script de inicialización
COPY docker/mysql/init-script.sh /docker-entrypoint-initdb.d/
RUN chmod +x /docker-entrypoint-initdb.d/init-script.sh

VOLUME ["/var/lib/mysql", "/var/backups/mysql"]

EXPOSE 3306
```

### Configuración MySQL (`docker/mysql/my.cnf`)

```ini
[mysqld]
# Configuraciones de rendimiento
innodb_buffer_pool_size = 256M
innodb_buffer_pool_instances = 2
innodb_log_file_size = 64M
innodb_log_buffer_size = 16M
innodb_flush_log_at_trx_commit = 2

# Configuraciones de conexión
max_connections = 200
max_connect_errors = 10000
table_open_cache = 2000
table_definition_cache = 1400

# Configuraciones de query
query_cache_type = 1
query_cache_limit = 1M
query_cache_size = 32M

# Configuraciones de charset
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# Configuraciones de binlog para replicación
log-bin = mysql-bin
binlog_format = ROW
expire_logs_days = 7

# Configuraciones de slow query log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2

# Configuraciones de seguridad
skip-name-resolve
ssl-ca = /etc/mysql/certs/ca-cert.pem
ssl-cert = /etc/mysql/certs/server-cert.pem
ssl-key = /etc/mysql/certs/server-key.pem

[mysql]
default-character-set = utf8mb4

[client]
default-character-set = utf8mb4
```

---

## 🔧 Docker Compose

### Desarrollo (`docker-compose.yml`)

```yaml
version: '3.8'

services:
  # Aplicación PHP-FPM
  app:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
      args:
        APP_ENV: development
    container_name: inmobiliaria_app
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html
      - ./docker/php/php.ini:/usr/local/etc/php/conf.d/custom.ini
      - app_logs:/var/log/php-fpm
    environment:
      - APP_ENV=development
      - DB_HOST=db
      - DB_DATABASE=inmobil
      - DB_USERNAME=inmobil_user
      - DB_PASSWORD=inmobil_pass123
    depends_on:
      - db
    networks:
      - inmobiliaria_network

  # Servidor Web Nginx
  webserver:
    image: nginx:1.21-alpine
    container_name: inmobiliaria_webserver
    restart: unless-stopped
    ports:
      - "8080:80"
      - "8443:443"
    volumes:
      - ./:/var/www/html
      - ./docker/nginx/nginx.conf:/etc/nginx/nginx.conf
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
      - nginx_logs:/var/log/nginx
    depends_on:
      - app
    networks:
      - inmobiliaria_network

  # Base de Datos MySQL
  db:
    build:
      context: .
      dockerfile: docker/mysql/Dockerfile
    container_name: inmobiliaria_db
    restart: unless-stopped
    ports:
      - "3306:3306"
    environment:
      MYSQL_ROOT_PASSWORD: rootpass123
      MYSQL_DATABASE: inmobil
      MYSQL_USER: inmobil_user
      MYSQL_PASSWORD: inmobil_pass123
    volumes:
      - mysql_data:/var/lib/mysql
      - mysql_backups:/var/backups/mysql
      - ./docker/mysql/init.sql:/docker-entrypoint-initdb.d/init.sql
      - mysql_logs:/var/log/mysql
    networks:
      - inmobiliaria_network

  # Redis para Cache (Opcional)
  cache:
    image: redis:7-alpine
    container_name: inmobiliaria_cache
    restart: unless-stopped
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
    command: redis-server --appendonly yes --requirepass "cache_pass123"
    networks:
      - inmobiliaria_network

  # phpMyAdmin para desarrollo
  phpmyadmin:
    image: phpmyadmin/phpmyadmin:5
    container_name: inmobiliaria_phpmyadmin
    restart: unless-stopped
    ports:
      - "8081:80"
    environment:
      PMA_HOST: db
      PMA_PORT: 3306
      PMA_USER: inmobil_user
      PMA_PASSWORD: inmobil_pass123
      MYSQL_ROOT_PASSWORD: rootpass123
    depends_on:
      - db
    networks:
      - inmobiliaria_network

volumes:
  mysql_data:
    driver: local
  mysql_backups:
    driver: local
  redis_data:
    driver: local
  app_logs:
    driver: local
  nginx_logs:
    driver: local
  mysql_logs:
    driver: local

networks:
  inmobiliaria_network:
    driver: bridge
    ipam:
      config:
        - subnet: 172.20.0.0/16
```

### Producción (`docker-compose.prod.yml`)

```yaml
version: '3.8'

services:
  # Load Balancer / Reverse Proxy
  traefik:
    image: traefik:v2.9
    container_name: inmobiliaria_traefik
    restart: unless-stopped
    command:
      - --api.dashboard=true
      - --api.debug=true
      - --log.level=INFO
      - --providers.docker=true
      - --providers.docker.exposedbydefault=false
      - --entrypoints.web.address=:80
      - --entrypoints.websecure.address=:443
      - --certificatesresolvers.letsencrypt.acme.email=admin@inmobiliaria.com
      - --certificatesresolvers.letsencrypt.acme.storage=/acme.json
      - --certificatesresolvers.letsencrypt.acme.httpchallenge.entrypoint=web
    ports:
      - "80:80"
      - "443:443"
      - "8080:8080"  # Dashboard Traefik
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock:ro
      - traefik_acme:/acme.json
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.dashboard.rule=Host(`traefik.inmobiliaria.com`)"
      - "traefik.http.routers.dashboard.tls.certresolver=letsencrypt"
    networks:
      - inmobiliaria_network

  # Aplicación PHP-FPM (Escalable)
  app:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
      args:
        APP_ENV: production
    restart: unless-stopped
    deploy:
      replicas: 3  # 3 instancias para alta disponibilidad
      resources:
        limits:
          memory: 512M
          cpus: "0.5"
        reservations:
          memory: 256M
          cpus: "0.25"
    environment:
      - APP_ENV=production
      - DB_HOST=db
      - DB_DATABASE=inmobil
      - DB_USERNAME=inmobil_user
      - DB_PASSWORD=${DB_PASSWORD}
      - REDIS_HOST=cache
      - REDIS_PASSWORD=${REDIS_PASSWORD}
    volumes:
      - app_uploads:/var/www/html/uploads
      - app_logs:/var/www/html/logs
    depends_on:
      - db
      - cache
    networks:
      - inmobiliaria_network

  # Servidor Web Nginx
  webserver:
    image: nginx:1.21-alpine
    restart: unless-stopped
    volumes:
      - ./docker/nginx/nginx.conf:/etc/nginx/nginx.conf
      - ./docker/nginx/prod.conf:/etc/nginx/conf.d/default.conf
      - app_uploads:/var/www/html/uploads:ro
      - nginx_logs:/var/log/nginx
    depends_on:
      - app
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.inmobiliaria.rule=Host(`inmobiliaria.com`, `www.inmobiliaria.com`)"
      - "traefik.http.routers.inmobiliaria.tls.certresolver=letsencrypt"
      - "traefik.http.middlewares.redirect-to-https.redirectscheme.scheme=https"
      - "traefik.http.routers.inmobiliaria-http.rule=Host(`inmobiliaria.com`, `www.inmobiliaria.com`)"
      - "traefik.http.routers.inmobiliaria-http.entrypoints=web"
      - "traefik.http.routers.inmobiliaria-http.middlewares=redirect-to-https"
    networks:
      - inmobiliaria_network

  # Base de Datos MySQL Master
  db:
    image: mysql:8.0
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MYSQL_DATABASE: inmobil
      MYSQL_USER: inmobil_user
      MYSQL_PASSWORD: ${DB_PASSWORD}
    volumes:
      - mysql_data:/var/lib/mysql
      - mysql_backups:/var/backups/mysql
      - mysql_logs:/var/log/mysql
      - ./docker/mysql/prod.cnf:/etc/mysql/conf.d/custom.cnf
    deploy:
      resources:
        limits:
          memory: 1G
          cpus: "1.0"
        reservations:
          memory: 512M
          cpus: "0.5"
    networks:
      - inmobiliaria_network

  # Base de Datos MySQL Slave (Solo lectura)
  db_slave:
    image: mysql:8.0
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MYSQL_DATABASE: inmobil
      MYSQL_USER: inmobil_user
      MYSQL_PASSWORD: ${DB_PASSWORD}
    volumes:
      - mysql_slave_data:/var/lib/mysql
      - ./docker/mysql/slave.cnf:/etc/mysql/conf.d/custom.cnf
    depends_on:
      - db
    networks:
      - inmobiliaria_network

  # Redis Cluster para Cache
  cache:
    image: redis:7-alpine
    restart: unless-stopped
    command: redis-server --appendonly yes --requirepass "${REDIS_PASSWORD}"
    volumes:
      - redis_data:/data
    deploy:
      resources:
        limits:
          memory: 256M
          cpus: "0.25"
    networks:
      - inmobiliaria_network

  # Elasticsearch para búsquedas avanzadas (Opcional)
  elasticsearch:
    image: elasticsearch:7.17.0
    container_name: inmobiliaria_search
    restart: unless-stopped
    environment:
      - discovery.type=single-node
      - "ES_JAVA_OPTS=-Xms512m -Xmx512m"
    volumes:
      - elastic_data:/usr/share/elasticsearch/data
    networks:
      - inmobiliaria_network

  # Monitoring con Prometheus
  prometheus:
    image: prom/prometheus:latest
    container_name: inmobiliaria_prometheus
    restart: unless-stopped
    ports:
      - "9090:9090"
    volumes:
      - ./docker/monitoring/prometheus.yml:/etc/prometheus/prometheus.yml
      - prometheus_data:/prometheus
    networks:
      - inmobiliaria_network

  # Grafana para dashboards
  grafana:
    image: grafana/grafana:latest
    container_name: inmobiliaria_grafana
    restart: unless-stopped
    ports:
      - "3000:3000"
    environment:
      - GF_SECURITY_ADMIN_PASSWORD=${GRAFANA_PASSWORD}
    volumes:
      - grafana_data:/var/lib/grafana
    networks:
      - inmobiliaria_network

volumes:
  mysql_data:
  mysql_slave_data:
  mysql_backups:
  mysql_logs:
  redis_data:
  elastic_data:
  app_uploads:
  app_logs:
  nginx_logs:
  traefik_acme:
  prometheus_data:
  grafana_data:

networks:
  inmobiliaria_network:
    driver: bridge
```

---

## ⚙️ Configuraciones

### PHP Configuration (`docker/php/php.ini`)

```ini
; Configuración PHP para Producción

; Memoria y tiempo
memory_limit = 512M
max_execution_time = 300
max_input_time = 300

; Upload de archivos
upload_max_filesize = 20M
post_max_size = 25M
max_file_uploads = 20

; Sesiones
session.name = INMOBSESSID
session.auto_start = 0
session.gc_maxlifetime = 7200
session.cookie_lifetime = 0
session.cookie_httponly = 1
session.cookie_secure = 1
session.cookie_samesite = "Strict"
session.use_strict_mode = 1

; Seguridad
expose_php = Off
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php-fpm/error.log

; OPcache para rendimiento
opcache.enable = 1
opcache.enable_cli = 0
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 60
opcache.validate_timestamps = 0
opcache.fast_shutdown = 1

; Timezone
date.timezone = "America/Bogota"

; Configuraciones de base de datos
pdo_mysql.default_socket = /var/run/mysqld/mysqld.sock
mysqli.default_socket = /var/run/mysqld/mysqld.sock
```

### Nginx Configuration (`docker/nginx/nginx.conf`)

```nginx
user nginx;
worker_processes auto;
worker_rlimit_nofile 65535;

error_log /var/log/nginx/error.log warn;
pid /var/run/nginx.pid;

events {
    worker_connections 2048;
    use epoll;
    multi_accept on;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    # Configuraciones de seguridad
    server_tokens off;
    add_header X-Frame-Options DENY always;
    add_header X-Content-Type-Options nosniff always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Configuraciones de rendimiento
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    client_max_body_size 25M;
    client_body_buffer_size 16K;
    client_header_buffer_size 1k;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 10240;
    gzip_comp_level 6;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/javascript
        application/xml+rss
        application/json
        image/svg+xml;

    # Rate limiting
    limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;

    # Configuración de logs
    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for"';

    access_log /var/log/nginx/access.log main;

    # Cache de archivos estáticos
    map $sent_http_content_type $expires {
        default                    off;
        text/html                  1h;
        text/css                   1y;
        application/javascript     1y;
        ~image/                    1y;
        font/                      1y;
    }

    expires $expires;

    include /etc/nginx/conf.d/*.conf;
}
```

### Virtual Host (`docker/nginx/default.conf`)

```nginx
upstream php_backend {
    server app:9000;
    # Para múltiples instancias:
    # server app_1:9000 weight=3;
    # server app_2:9000 weight=3;
    # server app_3:9000 weight=3;
}

# Redirección HTTP a HTTPS
server {
    listen 80;
    server_name inmobiliaria.com www.inmobiliaria.com;
    
    location /.well-known/acme-challenge/ {
        root /var/www/html/public;
    }
    
    location / {
        return 301 https://$server_name$request_uri;
    }
}

# Configuración principal HTTPS
server {
    listen 443 ssl http2;
    server_name inmobiliaria.com www.inmobiliaria.com;
    
    root /var/www/html;
    index index.php index.html;

    # Configuración SSL
    ssl_certificate /etc/nginx/certs/fullchain.pem;
    ssl_certificate_key /etc/nginx/certs/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Rate limiting por ubicación
    location /auth/ {
        limit_req zone=login burst=3 nodelay;
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location /public/api/ {
        limit_req zone=api burst=5 nodelay;
        try_files $uri $uri/ /index.php$is_args$args;
    }

    # Archivos estáticos
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|pdf|txt)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        add_header Vary Accept-Encoding;
        access_log off;
    }

    # Uploads con seguridad
    location ^~ /uploads/ {
        alias /var/www/html/uploads/;
        location ~* \.(php|php5|phtml|pht|phps)$ {
            deny all;
        }
        expires 1M;
        add_header Cache-Control "public";
    }

    # Denegar acceso a archivos sensibles
    location ~ /\.(ht|git|svn) {
        deny all;
    }

    location ~ /(config|includes|vendor)/ {
        deny all;
    }

    location ~ /\.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass php_backend;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        
        # Timeouts
        fastcgi_connect_timeout 60s;
        fastcgi_send_timeout 60s;
        fastcgi_read_timeout 60s;
        
        # Buffer settings
        fastcgi_buffering on;
        fastcgi_buffer_size 16k;
        fastcgi_buffers 16 16k;
    }

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    # Health check
    location /health {
        access_log off;
        return 200 "healthy\n";
        add_header Content-Type text/plain;
    }
}
```

---

## 🚀 Scripts de Despliegue

### Deploy Script (`docker/scripts/deploy.sh`)

```bash
#!/bin/bash

# Script de despliegue automatizado
set -e

# Configuración
PROJECT_NAME="inmobiliaria"
DOCKER_REGISTRY="registry.inmobiliaria.com"
VERSION=${1:-latest}
ENVIRONMENT=${2:-production}

echo "🚀 Iniciando despliegue de $PROJECT_NAME v$VERSION en $ENVIRONMENT"

# Verificar dependencias
command -v docker >/dev/null 2>&1 || { echo "Docker no está instalado" >&2; exit 1; }
command -v docker-compose >/dev/null 2>&1 || { echo "Docker Compose no está instalado" >&2; exit 1; }

# Función de rollback
rollback() {
    echo "❌ Despliegue falló. Ejecutando rollback..."
    docker-compose -f docker-compose.prod.yml down
    docker-compose -f docker-compose.prod.yml up -d
    exit 1
}

# Trap para rollback automático
trap rollback ERR

# Backup de base de datos
echo "💾 Creando backup de base de datos..."
docker exec inmobiliaria_db mysqldump -u root -p${DB_ROOT_PASSWORD} inmobil > backup_$(date +%Y%m%d_%H%M%S).sql

# Build de nuevas imágenes
echo "🔨 Construyendo imágenes..."
docker-compose -f docker-compose.prod.yml build --no-cache

# Tag de imágenes para registry
docker tag ${PROJECT_NAME}_app:latest ${DOCKER_REGISTRY}/${PROJECT_NAME}_app:${VERSION}
docker tag ${PROJECT_NAME}_webserver:latest ${DOCKER_REGISTRY}/${PROJECT_NAME}_webserver:${VERSION}

# Push al registry
echo "📤 Subiendo imágenes al registry..."
docker push ${DOCKER_REGISTRY}/${PROJECT_NAME}_app:${VERSION}
docker push ${DOCKER_REGISTRY}/${PROJECT_NAME}_webserver:${VERSION}

# Deploy con zero-downtime
echo "🔄 Desplegando nueva versión..."

# Escalar down instancia por instancia
for i in 3 2 1; do
    echo "Escalando a $i instancias..."
    docker-compose -f docker-compose.prod.yml up -d --scale app=$i
    
    # Esperar que las instancias estén healthy
    for j in $(seq 1 30); do
        if curl -f http://localhost/health > /dev/null 2>&1; then
            break
        fi
        sleep 2
    done
    
    sleep 5
done

# Desplegar nueva versión
docker-compose -f docker-compose.prod.yml down
docker-compose -f docker-compose.prod.yml up -d

# Esperar que todos los servicios estén healthy
echo "🔍 Verificando health de servicios..."
for service in app webserver db; do
    for i in $(seq 1 60); do
        if docker-compose -f docker-compose.prod.yml ps $service | grep -q "healthy"; then
            echo "✅ $service está healthy"
            break
        fi
        sleep 2
    done
done

# Tests de smoke
echo "🧪 Ejecutando tests de smoke..."
curl -f http://localhost/health || rollback
curl -f http://localhost/public/ || rollback

# Limpiar imágenes antiguas
echo "🧹 Limpiando imágenes antiguas..."
docker image prune -f --filter "until=24h"

echo "🎉 Despliegue completado exitosamente!"
echo "📊 Logs: docker-compose -f docker-compose.prod.yml logs -f"
echo "📈 Monitoring: http://monitoring.inmobiliaria.com:3000"
```

### Backup Script (`docker/scripts/backup.sh`)

```bash
#!/bin/bash

# Script de backup automatizado
set -e

BACKUP_DIR="/var/backups/inmobiliaria"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30

mkdir -p $BACKUP_DIR

echo "📦 Iniciando backup completo..."

# Backup de base de datos
echo "💾 Backup de base de datos..."
docker exec inmobiliaria_db mysqldump \
    --single-transaction \
    --routines \
    --triggers \
    -u root -p${DB_ROOT_PASSWORD} \
    inmobil > $BACKUP_DIR/db_backup_$DATE.sql

# Comprimir backup de BD
gzip $BACKUP_DIR/db_backup_$DATE.sql

# Backup de uploads
echo "📁 Backup de archivos uploads..."
tar -czf $BACKUP_DIR/uploads_backup_$DATE.tar.gz \
    -C /var/lib/docker/volumes/inmobiliaria_app_uploads/_data .

# Backup de configuración
echo "⚙️ Backup de configuración..."
tar -czf $BACKUP_DIR/config_backup_$DATE.tar.gz \
    docker-compose.prod.yml \
    .env \
    docker/

# Sincronizar con storage remoto (S3, etc.)
if command -v aws >/dev/null 2>&1; then
    echo "☁️ Sincronizando con S3..."
    aws s3 sync $BACKUP_DIR s3://inmobiliaria-backups/daily/ \
        --exclude "*" \
        --include "*$DATE*"
fi

# Limpiar backups antiguos
echo "🧹 Limpiando backups antiguos..."
find $BACKUP_DIR -name "*.sql.gz" -mtime +$RETENTION_DAYS -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +$RETENTION_DAYS -delete

echo "✅ Backup completado: $BACKUP_DIR"
```

### Setup Script (`docker/scripts/setup.sh`)

```bash
#!/bin/bash

# Script de configuración inicial
set -e

echo "🚀 Configurando entorno Docker para Inmobiliaria..."

# Crear directorios necesarios
mkdir -p {logs,backups,certs,data/mysql,data/redis}

# Generar archivos de configuración
if [ ! -f .env ]; then
    echo "📝 Creando archivo .env..."
    cat > .env << EOF
# Database
DB_ROOT_PASSWORD=$(openssl rand -base64 32)
DB_PASSWORD=$(openssl rand -base64 32)

# Redis
REDIS_PASSWORD=$(openssl rand -base64 32)

# Grafana
GRAFANA_PASSWORD=$(openssl rand -base64 32)

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://inmobiliaria.com

# WhatsApp
WHATSAPP_NUMBER=573001234567
EOF
    echo "✅ Archivo .env creado con passwords seguros"
fi

# Configurar SSL (Let's Encrypt)
if [ ! -f docker/nginx/ssl/fullchain.pem ]; then
    echo "🔒 Configurando SSL..."
    
    # Crear certificados de desarrollo
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout docker/nginx/ssl/privkey.pem \
        -out docker/nginx/ssl/fullchain.pem \
        -subj "/C=CO/ST=Bogota/L=Bogota/O=Inmobiliaria/CN=localhost"
        
    echo "✅ Certificados SSL de desarrollo creados"
fi

# Configurar permisos
sudo chown -R $USER:$USER .
sudo chmod -R 755 uploads/
sudo chmod -R 755 logs/

# Inicializar Docker Swarm (para producción)
if ! docker info | grep -q "Swarm: active"; then
    echo "🐋 Inicializando Docker Swarm..."
    docker swarm init
    echo "✅ Docker Swarm inicializado"
fi

# Crear redes Docker
docker network create inmobiliaria_network 2>/dev/null || true

# Build inicial
echo "🔨 Construyendo imágenes iniciales..."
docker-compose -f docker-compose.yml build

echo "🎉 Setup completado!"
echo ""
echo "📋 Próximos pasos:"
echo "1. Editar .env con tus configuraciones"
echo "2. Ejecutar: docker-compose up -d"
echo "3. Visitar: http://localhost:8080"
echo ""
echo "🔧 Comandos útiles:"
echo "- Ver logs: docker-compose logs -f"
echo "- Entrar al contenedor: docker exec -it inmobiliaria_app bash"
echo "- Backup: ./docker/scripts/backup.sh"
```

---

## 📊 Monitoring y Logs

### Configuración Prometheus (`docker/monitoring/prometheus.yml`)

```yaml
global:
  scrape_interval: 15s
  evaluation_interval: 15s

rule_files: []

scrape_configs:
  - job_name: 'inmobiliaria-app'
    static_configs:
      - targets: ['app:9000']
    metrics_path: /metrics
    scrape_interval: 5s

  - job_name: 'mysql'
    static_configs:
      - targets: ['db:9104']
      
  - job_name: 'redis'
    static_configs:
      - targets: ['cache:9121']

  - job_name: 'nginx'
    static_configs:
      - targets: ['webserver:9113']

  - job_name: 'node-exporter'
    static_configs:
      - targets: ['node-exporter:9100']
```

### Docker Logging Configuration

```yaml
# En docker-compose.prod.yml
services:
  app:
    logging:
      driver: "json-file"
      options:
        max-size: "10m"
        max-file: "3"
        labels: "service,environment"

  webserver:
    logging:
      driver: "fluentd"
      options:
        fluentd-address: "fluentd:24224"
        tag: "nginx.{{.ID}}"
```

---

## 🔧 Comandos de Gestión

### Comandos de Desarrollo

```bash
# Levantar entorno de desarrollo
docker-compose up -d

# Ver logs en tiempo real
docker-compose logs -f app

# Entrar al contenedor de la app
docker exec -it inmobiliaria_app bash

# Ejecutar comandos PHP
docker exec inmobiliaria_app php -v
docker exec inmobiliaria_app composer install

# Reiniciar servicio específico
docker-compose restart app

# Ejecutar tests
docker exec inmobiliaria_app php tests/run_all_tests.php
```

### Comandos de Producción

```bash
# Deploy completo
./docker/scripts/deploy.sh v1.2.0 production

# Backup manual
./docker/scripts/backup.sh

# Escalar aplicación
docker-compose -f docker-compose.prod.yml up -d --scale app=5

# Actualizar configuración sin downtime
docker-compose -f docker-compose.prod.yml up -d --no-deps webserver

# Ver métricas de recursos
docker stats

# Limpiar recursos no utilizados
docker system prune -af
```

### Comandos de Troubleshooting

```bash
# Verificar estado de servicios
docker-compose ps

# Logs de un servicio específico
docker-compose logs app --tail=100

# Entrar a base de datos
docker exec -it inmobiliaria_db mysql -u root -p

# Verificar conectividad entre contenedores
docker exec inmobiliaria_app ping db

# Información detallada de contenedor
docker inspect inmobiliaria_app

# Métricas de rendimiento
docker exec inmobiliaria_app top
```

---

## 🔒 Seguridad en Producción

### Configuración de Firewall

```bash
# UFW (Ubuntu)
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80
sudo ufw allow 443
sudo ufw --force enable

# Fail2ban para Docker
sudo apt install fail2ban
```

### Docker Security

```yaml
# docker-compose.prod.yml - Configuraciones de seguridad
services:
  app:
    security_opt:
      - no-new-privileges:true
    read_only: true
    tmpfs:
      - /tmp:rw,size=100M
    cap_drop:
      - ALL
    cap_add:
      - CHOWN
      - SETGID
      - SETUID
    user: "1000:1000"
```

### Secrets Management

```bash
# Usar Docker Secrets para información sensible
echo "db_password_here" | docker secret create db_password -
echo "redis_password_here" | docker secret create redis_password -

# En docker-compose.prod.yml
services:
  app:
    secrets:
      - db_password
      - redis_password
      
secrets:
  db_password:
    external: true
  redis_password:
    external: true
```

---

## 📈 Escalamiento y Alta Disponibilidad

### Load Balancing

```yaml
# docker-compose.prod.yml - Múltiples instancias
services:
  app:
    deploy:
      replicas: 5
      resources:
        limits:
          cpus: '0.5'
          memory: 512M
        reservations:
          cpus: '0.25'
          memory: 256M
      restart_policy:
        condition: on-failure
        delay: 5s
        max_attempts: 3
        window: 120s
```

### Auto-scaling con Docker Swarm

```bash
# Crear servicio con auto-scaling
docker service create \
  --name inmobiliaria_app \
  --replicas 3 \
  --limit-memory 512M \
  --reserve-memory 256M \
  --update-parallelism 1 \
  --update-delay 10s \
  inmobiliaria_app:latest

# Escalar según demanda
docker service scale inmobiliaria_app=10
```

### Database Replication

```yaml
# Master-Slave MySQL setup
services:
  db_master:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
    volumes:
      - mysql_master_data:/var/lib/mysql
      - ./docker/mysql/master.cnf:/etc/mysql/conf.d/mysql.cnf
    command: --log-bin=mysql-bin --server-id=1

  db_slave:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
    volumes:
      - mysql_slave_data:/var/lib/mysql
      - ./docker/mysql/slave.cnf:/etc/mysql/conf.d/mysql.cnf
    command: --server-id=2 --relay-log=relay-bin
    depends_on:
      - db_master
```

---

**Guía completa de despliegue Docker para el Sistema de Gestión Inmobiliaria**  
*Versión 1.0 - Diciembre 2024*