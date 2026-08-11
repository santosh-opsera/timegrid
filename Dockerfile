# Legacy TimeGrid — Laravel 5.3 on PHP 7.1
FROM php:7.1-fpm-alpine

RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    zlib-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    sqlite-dev \
    autoconf \
    g++ \
    make

RUN docker-php-ext-configure gd \
        --with-freetype-dir=/usr/include/ \
        --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    pdo_sqlite \
    mbstring \
    zip \
    bcmath \
    gd \
    intl \
    opcache \
    tokenizer

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" 2>/dev/null || true

RUN echo "opcache.enable=1" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && echo "opcache.memory_consumption=128" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && echo "opcache.validate_timestamps=0" >> "$PHP_INI_DIR/conf.d/opcache.ini"

COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

RUN rm -rf node_modules tests .env .env.travis \
    && mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p storage/app/public \
    && mkdir -p bootstrap/cache

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/nginx/default.conf.template /etc/nginx/conf.d/default.conf.template
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

RUN mkdir -p /run/nginx \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisord.conf", "-n"]
