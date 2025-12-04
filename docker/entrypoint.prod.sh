#!/bin/sh

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
        php artisan key:generate --force || true
    fi
fi

# Esperar a que la base de datos esté lista
echo "Verificando conexión a PostgreSQL..."
max_attempts=30
attempt=1

while [ $attempt -le $max_attempts ]; do
    if php -r "
        \$host = getenv('DB_HOST') ?: 'db';
        \$port = getenv('DB_PORT') ?: '5432';
        \$fp = @fsockopen(\$host, \$port, \$errno, \$errstr, 5);
        if (\$fp) { fclose(\$fp); exit(0); }
        exit(1);
    " 2>/dev/null; then
        echo "PostgreSQL disponible"
        break
    fi
    echo "Esperando PostgreSQL... (intento $attempt/$max_attempts)"
    sleep 2
    attempt=$((attempt + 1))
done

# Ejecutar migraciones
echo "Ejecutando migraciones..."
php artisan migrate --force || echo "Migraciones pendientes o error"

# Crear enlace de storage
php artisan storage:link 2>/dev/null || true

# Limpiar y cachear para producción
echo "Optimizando para producción..."
php artisan config:clear || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "========================================"
echo "  Mika lista en puerto 8040"
echo "========================================"

# Ejecutar el comando pasado (frankenphp)
exec "$@"
