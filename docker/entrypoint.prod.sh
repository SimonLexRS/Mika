#!/bin/sh
set -e

echo "========================================"
echo "  Iniciando Mika - Dokploy"
echo "========================================"

cd /app

# Verificar que existe el archivo .env
if [ ! -f .env ]; then
    echo "Copiando .env.example a .env..."
    cp .env.example .env 2>/dev/null || true
fi

# Generar APP_KEY si no existe
if [ -z "$APP_KEY" ]; then
    if ! grep -q "APP_KEY=base64" .env 2>/dev/null; then
        echo "Generando APP_KEY..."
        php artisan key:generate --force 2>/dev/null || true
    fi
fi

# Esperar a que la base de datos esté lista
echo "Verificando conexión a PostgreSQL..."
max_attempts=30
attempt=1

while [ $attempt -le $max_attempts ]; do
    if php artisan db:monitor --databases=pgsql > /dev/null 2>&1; then
        echo "PostgreSQL disponible"
        break
    fi
    echo "Esperando PostgreSQL... (intento $attempt/$max_attempts)"
    sleep 2
    attempt=$((attempt + 1))
done

# Ejecutar migraciones
echo "Ejecutando migraciones..."
php artisan migrate --force 2>/dev/null || echo "Migraciones pendientes o error"

# Crear enlace de storage
php artisan storage:link 2>/dev/null || true

# Cachear para producción
echo "Optimizando para producción..."
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

echo "========================================"
echo "  Mika lista en puerto 8040"
echo "========================================"

# Ejecutar el comando pasado (frankenphp)
exec "$@"
