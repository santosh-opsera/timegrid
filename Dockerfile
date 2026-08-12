# =============================================================================
# Stage 1: Frontend build
# =============================================================================
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

COPY vite.config.js tsconfig.json ./
COPY resources ./resources
COPY public ./public

RUN npm run build

# =============================================================================
# Stage 2: PHP base with extensions
# =============================================================================
FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libzip-dev \
        icu-dev \
        oniguruma-dev \
        linux-headers \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        pdo_sqlite \
        gd \
        zip \
        bcmath \
        opcache \
        intl \
        mbstring \
        pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS \
    && rm -rf /tmp/pear

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-timegrid.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/10-opcache.ini

WORKDIR /var/www/html

# =============================================================================
# Stage 3: Development (docker-compose local stack)
# =============================================================================
FROM base AS development

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache git unzip rsync

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts --no-autoloader

COPY . .
RUN composer dump-autoload --optimize

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]

# =============================================================================
# Stage 4: Production
# =============================================================================
FROM base AS production

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache git unzip rsync

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
        --no-scripts

COPY . .

RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi

COPY --from=frontend /app/public/build ./public/build

# Staging copy for shared-volume sync with nginx in production
RUN cp -a public /opt/timegrid-public

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD php /var/www/html/scripts/health-check.php --quiet || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
