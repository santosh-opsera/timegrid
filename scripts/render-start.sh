#!/bin/sh
set -e

cd /var/www/html

echo "==> Generating application key if missing..."
if [ -z "$APP_KEY" ]; then
    if [ ! -f .env ]; then
        touch .env
    fi
    php artisan key:generate --force --no-interaction
fi

echo "==> Running database migrations..."
php artisan migrate:fresh --force --no-interaction --seed

echo "==> Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Starting PHP server on 0.0.0.0:${PORT:-10000}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
