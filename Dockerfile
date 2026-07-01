FROM php:8.4-fpm-alpine

# System deps + PHP extensions (mysql AND pgsql so either DB works)
RUN apk add --no-cache \
        nginx \
        libpng-dev libjpeg-turbo-dev freetype-dev \
        libzip-dev icu-dev oniguruma-dev postgresql-dev \
        nodejs npm \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring zip gd bcmath exif \
    # enable opcache extension and create opcache config
    && docker-php-ext-install opcache || true \
    && rm -rf /var/cache/apk/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Install PHP + JS deps and build front-end assets
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && npm ci && npm run build \
    && chown -R www-data:www-data storage bootstrap/cache public/build

# Nginx config (port substituted at runtime) + start script
COPY docker/nginx.conf /etc/nginx/http.d/default.conf.template
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# PHP opcache settings
RUN mkdir -p /usr/local/etc/php/conf.d \
    && printf "opcache.enable=1\nopcache.memory_consumption=192\nopcache.max_accelerated_files=10000\nopcache.validate_timestamps=0\nopcache.revalidate_freq=0\n" > /usr/local/etc/php/conf.d/opcache.ini

EXPOSE 8080
CMD ["/start.sh"]