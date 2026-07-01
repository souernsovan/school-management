#!/bin/sh

PORT="${PORT:-8080}"
sed "s/__PORT__/${PORT}/" /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

php artisan storage:link || true
php artisan optimize

# Wait for DB to be ready (up to 30 seconds)
echo "Waiting for database..."
for i in $(seq 1 15); do
    php artisan db:show --json > /dev/null 2>&1 && echo "Database ready." && break
    echo "Attempt $i failed, retrying in 2s..."
    sleep 2
done

# Run migrations and seeders only when explicitly enabled to avoid blocking container startup.
# Set RUN_MIGRATIONS=true in the deployment environment to run them during startup.
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "RUN_MIGRATIONS=true — running migrations and seeders"
    php artisan migrate --force && echo "Migrations done." || echo "Migration failed — check logs."

    # Seed all (safe to run multiple times — most seeders use firstOrCreate)
    php artisan db:seed --force && echo "Seeding done." || echo "Seeding failed — check logs."
    php artisan db:seed --class=PermissionSeeder --force && echo "PermissionSeeder done." || echo "PermissionSeeder failed."
    php artisan db:seed --class=RoleSeeder --force && echo "RoleSeeder done." || echo "RoleSeeder failed."
    php artisan db:seed --class=SchoolClassSeeder --force && echo "SchoolClassSeeder done." || echo "SchoolClassSeeder failed."
    php artisan db:seed --class=TeacherSeeder --force && echo "TeacherSeeder done." || echo "TeacherSeeder failed."
    php artisan db:seed --class=SubjectSeeder --force && echo "SubjectSeeder done." || echo "SubjectSeeder failed."
    php artisan db:seed --class=StudentSeeder --force && echo "StudentSeeder done." || echo "StudentSeeder failed."
    php artisan db:seed --class=AdminUserSeeder --force && echo "AdminUserSeeder done." || echo "AdminUserSeeder failed."
    php artisan db:seed --class=TimetableSeeder --force && echo "TimetableSeeder done." || echo "TimetableSeeder failed."
else
    echo "Skipping migrations and seeders on container start (set RUN_MIGRATIONS=true to enable)."
fi
# Start php-fpm in background, nginx in foreground
php-fpm -D
nginx -g 'daemon off;'
