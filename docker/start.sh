#!/bin/sh
set -e

# Render injects $PORT; default to 8080 locally
PORT="${PORT:-8080}"
sed "s/__PORT__/${PORT}/" /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

# Optimise + run migrations on each deploy
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

# Start php-fpm in background, nginx in foreground
php-fpm -D
nginx -g 'daemon off;'