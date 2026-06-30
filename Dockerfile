FROM php:8.3-fpm-alpine

# System deps + PHP extensions (mysql AND pgsql so either DB works)
RUN apk add --no-cache \
        nginx \
        libpng-dev libjpeg-turbo-dev freetype-dev \
        libzip-dev icu-dev oniguruma-dev postgresql-dev \
        nodejs npm \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring zip gd bcmath exif \
    && rm -rf /var/cache/apk/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Install PHP + JS deps and build front-end assets
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && npm ci && npm run build \
    && chown -R www-data:www-data storage bootstrap/cache

# Nginx config (port substituted at runtime) + start script
COPY docker/nginx.conf /etc/nginx/http.d/default.conf.template
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080
CMD ["/start.sh"]