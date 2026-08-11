#!/bin/sh
set -e

cd /var/www

export PORT="${PORT:-8080}"

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

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force 2>/dev/null || true
    export APP_KEY=$(grep '^APP_KEY=' .env | cut -d '=' -f2-)
fi

# Render Nginx config with dynamic PORT
sed "s/\${PORT}/$PORT/g" /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

# Ensure storage permissions
chown -R www-data:www-data storage bootstrap/cache

# Run database migrations (non-fatal)
php artisan migrate --force 2>/dev/null || echo "WARNING: Migration skipped"

# Seed if tables are empty
php artisan db:seed --force 2>/dev/null || true

exec "$@"
