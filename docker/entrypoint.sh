#!/bin/sh
set -e

echo "========================================"
echo "  Iniciando Mika - Asistente Financiero"
echo "========================================"

# Función para esperar la base de datos
wait_for_db() {
    echo "Verificando conexión a PostgreSQL..."

    max_attempts=30
    attempt=1

    while [ $attempt -le $max_attempts ]; do
        if php artisan db:monitor --databases=pgsql > /dev/null 2>&1; then
            echo "PostgreSQL disponible"
            return 0
        fi

        echo "Esperando PostgreSQL... (intento $attempt/$max_attempts)"
        sleep 2
        attempt=$((attempt + 1))
    done

    echo "ERROR: No se pudo conectar a PostgreSQL después de $max_attempts intentos"
    exit 1
}

# Verificar que existe el archivo .env
if [ ! -f .env ]; then
    echo "Copiando .env.example a .env..."
    cp .env.example .env
fi

# Generar APP_KEY si no existe
if [ -z "$APP_KEY" ]; then
    if ! grep -q "APP_KEY=base64" .env; then
        echo "Generando APP_KEY..."
        php artisan key:generate --force
    fi
fi

# Esperar a que la base de datos esté lista
wait_for_db

# Ejecutar migraciones
echo "Ejecutando migraciones..."
php artisan migrate --force

# Crear enlace simbólico de storage
echo "Configurando storage..."
php artisan storage:link 2>/dev/null || true

# Cachear configuración para producción
if [ "$APP_ENV" = "production" ]; then
    echo "Optimizando para producción..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
fi

echo "========================================"
echo "  Mika lista para servir"
echo "========================================"

# Ejecutar el comando pasado (php-fpm por defecto)
exec "$@"
