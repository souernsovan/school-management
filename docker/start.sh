#!/bin/sh

PORT="${PORT:-8080}"
sed "s/__PORT__/${PORT}/" /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Wait for DB to be ready (up to 30 seconds)
echo "Waiting for database..."
for i in $(seq 1 15); do
    php artisan db:show --json > /dev/null 2>&1 && echo "Database ready." && break
    echo "Attempt $i failed, retrying in 2s..."
    sleep 2
done

# Run migrations
php artisan migrate --force && echo "Migrations done." || echo "Migration failed — check logs."

# Start php-fpm in background, nginx in foreground
php-fpm -D
nginx -g 'daemon off;'
