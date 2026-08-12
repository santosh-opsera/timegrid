#!/bin/sh
set -e

cd /var/www/html

# Sync public assets for nginx shared-volume deployments
if [ -d /opt/timegrid-public ]; then
    rsync -a --delete /opt/timegrid-public/ /var/www/html/public/
fi

# Wait for database when DB_HOST is set (production/docker-compose)
if [ -n "${DB_HOST:-}" ]; then
    echo "Waiting for database at ${DB_HOST}:${DB_PORT:-3306}..."
    max_attempts=30
    attempt=0
    until php -r "
        try {
            new PDO(
                'mysql:host=${DB_HOST};port=${DB_PORT:-3306};dbname=${DB_DATABASE}',
                '${DB_USERNAME}',
                '${DB_PASSWORD}'
            );
            exit(0);
        } catch (Exception \$e) {
            exit(1);
        }
    " 2>/dev/null; do
        attempt=$((attempt + 1))
        if [ "$attempt" -ge "$max_attempts" ]; then
            echo "ERROR: Database not available after ${max_attempts} attempts"
            exit 1
        fi
        sleep 2
    done
    echo "Database is ready."
fi

echo "Running database migrations..."
php artisan migrate --force --no-interaction

echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data storage bootstrap/cache public 2>/dev/null || true

echo "Starting PHP-FPM..."
exec "$@"
