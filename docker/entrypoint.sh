#!/bin/sh
set -e

cd /var/www

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Cache configuration for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
php artisan migrate --force

# Create storage symlink if missing
php artisan storage:link 2>/dev/null || true

exec "$@"
