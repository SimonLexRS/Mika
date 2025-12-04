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

# Generar APP_KEY si no existe o está vacío
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "APP_KEY no encontrada, generando nueva..."
    # Generar key y capturarla
    NEW_KEY=$(php artisan key:generate --show 2>/dev/null)
    if [ -n "$NEW_KEY" ]; then
        export APP_KEY="$NEW_KEY"
        echo "APP_KEY generada: ${APP_KEY:0:20}..."
        # Actualizar .env con la nueva key
        sed -i "s|APP_KEY=.*|APP_KEY=$NEW_KEY|" .env 2>/dev/null || true
    else
        echo "Generando APP_KEY con método alternativo..."
        php artisan key:generate --force || true
        # Leer la key generada del .env
        NEW_KEY=$(grep "^APP_KEY=" .env | cut -d'=' -f2)
        if [ -n "$NEW_KEY" ]; then
            export APP_KEY="$NEW_KEY"
            echo "APP_KEY obtenida del .env"
        fi
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

# Ejecutar seeders si la base de datos está vacía (primer deploy)
echo "Verificando datos iniciales..."
USER_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null | tail -1)
if [ "$USER_COUNT" = "0" ] || [ -z "$USER_COUNT" ]; then
    echo "Base de datos vacía, ejecutando seeders..."
    php artisan db:seed --force || echo "Seeders ya ejecutados o error"
else
    echo "Datos existentes encontrados ($USER_COUNT usuarios), omitiendo seeders"
fi

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
