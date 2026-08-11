#!/bin/sh
set -e

cd /var/www

export PORT="${PORT:-8080}"

# Parse DATABASE_URL if provided (Render, Railway, etc.)
if [ -n "$DATABASE_URL" ]; then
    export DB_CONNECTION="pgsql"
    export DB_HOST=$(echo "$DATABASE_URL" | sed -n 's|.*@\([^:]*\):.*|\1|p')
    export DB_PORT=$(echo "$DATABASE_URL" | sed -n 's|.*:\([0-9]*\)/.*|\1|p')
    export DB_DATABASE=$(echo "$DATABASE_URL" | sed -n 's|.*/\([^?]*\).*|\1|p')
    export DB_USERNAME=$(echo "$DATABASE_URL" | sed -n 's|.*://\([^:]*\):.*|\1|p')
    export DB_PASSWORD=$(echo "$DATABASE_URL" | sed -n 's|.*://[^:]*:\([^@]*\)@.*|\1|p')
fi

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Render Nginx config with dynamic PORT
envsubst '${PORT}' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

# Cache configuration for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
php artisan migrate --force

# Create storage symlink if missing
php artisan storage:link 2>/dev/null || true

exec "$@"
