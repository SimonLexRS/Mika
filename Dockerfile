# =============================================================================
# Dockerfile para Mika - Optimizado para Dokploy
# Imagen todo-en-uno con FrankenPHP (PHP + Servidor Web)
# =============================================================================

# -----------------------------------------------------------------------------
# Etapa 1: Compilar assets con Node.js
# -----------------------------------------------------------------------------
FROM node:20-alpine AS assets-builder

WORKDIR /app

COPY package*.json ./
RUN npm install --silent --legacy-peer-deps

COPY resources/ resources/
COPY vite.config.js tailwind.config.js postcss.config.js ./

RUN npm run build

# -----------------------------------------------------------------------------
# Etapa 2: Instalar dependencias PHP con Composer
# -----------------------------------------------------------------------------
FROM composer:2.7 AS php-deps

WORKDIR /app

COPY composer.json composer.lock* ./

RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

# -----------------------------------------------------------------------------
# Etapa 3: Imagen final - FrankenPHP (todo-en-uno)
# -----------------------------------------------------------------------------
FROM dunglas/frankenphp:latest-php8.3-alpine

LABEL maintainer="Mika Team"
LABEL description="Mika - Asistente Financiero Chat-First"

# Instalar wget para healthcheck y extensiones PHP adicionales
RUN apk add --no-cache wget \
    && install-php-extensions \
    pdo_pgsql \
    pgsql \
    intl \
    zip \
    gd \
    opcache \
    pcntl \
    bcmath

# Configuración PHP
RUN echo "memory_limit=256M" > /usr/local/etc/php/conf.d/mika.ini \
    && echo "upload_max_filesize=50M" >> /usr/local/etc/php/conf.d/mika.ini \
    && echo "post_max_size=50M" >> /usr/local/etc/php/conf.d/mika.ini \
    && echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/mika.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/mika.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/mika.ini

WORKDIR /app

# Copiar composer desde la imagen de composer
COPY --from=php-deps /usr/bin/composer /usr/bin/composer

# Copiar dependencias de Composer
COPY --from=php-deps /app/vendor ./vendor

# Copiar assets compilados
COPY --from=assets-builder /app/public/build ./public/build

# Copiar código fuente
COPY . .

# Generar autoload optimizado (ignorar platform check heredado)
RUN /usr/bin/composer dump-autoload --optimize --classmap-authoritative --ignore-platform-reqs

# Configurar permisos
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Crear Caddyfile para FrankenPHP
# php_server debe estar dentro de un bloque route
RUN printf '{\n\
    auto_https off\n\
    admin off\n\
}\n\
\n\
:8040 {\n\
    root * /app/public\n\
    encode gzip\n\
    route {\n\
        php_server\n\
    }\n\
}\n' > /etc/caddy/Caddyfile

# Variables de entorno por defecto
ENV SERVER_NAME=:8040
ENV APP_ENV=production
ENV APP_DEBUG=false

# Puerto expuesto
EXPOSE 8040

# Script de inicio
COPY docker/entrypoint.prod.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
