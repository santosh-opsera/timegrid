# ── Stage 1: Build frontend assets ──────────────────────────────────
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js tsconfig.json tailwind.config.js postcss.config.js ./
COPY resources/ resources/

RUN npm run build


# ── Stage 2: Install PHP dependencies ──────────────────────────────
FROM composer:2 AS composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

COPY . .
RUN composer dump-autoload --optimize


# ── Stage 3: Production image (PHP-FPM + Nginx) ───────────────────
FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libzip-dev \
    oniguruma-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    linux-headers \
    sqlite-dev \
    postgresql-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    mbstring \
    zip \
    bcmath \
    gd \
    intl \
    opcache \
    pcntl

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN echo "opcache.enable=1" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && echo "opcache.memory_consumption=128" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && echo "opcache.interned_strings_buffer=8" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && echo "opcache.max_accelerated_files=10000" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && echo "opcache.validate_timestamps=0" >> "$PHP_INI_DIR/conf.d/opcache.ini"

RUN echo "upload_max_filesize=10M" >> "$PHP_INI_DIR/conf.d/uploads.ini" \
    && echo "post_max_size=12M" >> "$PHP_INI_DIR/conf.d/uploads.ini"

WORKDIR /var/www

COPY --from=composer /app/vendor vendor
COPY . .
COPY --from=frontend /app/public/build public/build

RUN rm -rf node_modules tests .env docker-compose.yml \
    && mkdir -p storage/framework/{sessions,views,cache/data} \
    && mkdir -p storage/logs \
    && mkdir -p storage/app/public \
    && mkdir -p bootstrap/cache \
    && mkdir -p database

COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/nginx/default.conf.template /etc/nginx/http.d/default.conf.template
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

RUN chown -R www-data:www-data storage bootstrap/cache database

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisord.conf", "-n"]
