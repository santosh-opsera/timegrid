#!/bin/sh

cd /var/www

export PORT="${PORT:-10000}"

# Create .env from example if missing
if [ ! -f .env ]; then
    cp .env.example .env 2>/dev/null || touch .env
fi

# Use SQLite — create DB file with correct permissions
export DB_CONNECTION="${DB_CONNECTION:-sqlite}"
if [ "$DB_CONNECTION" = "sqlite" ]; then
    touch storage/database.sqlite
    chown www-data:www-data storage/database.sqlite
    chmod 664 storage/database.sqlite
fi

# Write env vars into .env for artisan
{
    echo "APP_ENV=${APP_ENV:-production}"
    echo "APP_DEBUG=${APP_DEBUG:-false}"
    echo "APP_KEY=${APP_KEY:-}"
    echo "APP_URL=${APP_URL:-http://localhost}"
    echo "DB_CONNECTION=${DB_CONNECTION:-sqlite}"
    echo "CACHE_DRIVER=${CACHE_DRIVER:-array}"
    echo "SESSION_DRIVER=${SESSION_DRIVER:-file}"
    echo "QUEUE_DRIVER=${QUEUE_DRIVER:-sync}"
    echo "MAIL_DRIVER=log"
} > .env

# Generate a valid Laravel APP_KEY (must start with base64:)
case "$APP_KEY" in
    base64:*) ;; # Already a valid Laravel key
    *)
        php artisan key:generate --force 2>&1 || true
        APP_KEY=$(grep '^APP_KEY=' .env | cut -d '=' -f2-)
        export APP_KEY
        ;;
esac

# Render Nginx config with dynamic PORT
sed "s/\${PORT}/$PORT/g" /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

# Ensure storage and bootstrap permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Run database migrations
php artisan migrate --force 2>&1 || echo "WARNING: Migration failed"

# Seed data
php artisan db:seed --force 2>&1 || true

echo "==> TimeGrid starting on port $PORT"

exec "$@"
