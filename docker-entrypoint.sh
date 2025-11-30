#!/bin/bash

# Wait for database to be ready (if needed)
# Uncomment if you need to wait for external database
# while ! nc -z $DB_HOST $DB_PORT; do
#   echo "Waiting for database..."
#   sleep 2
# done

# Set proper permissions
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

# Create storage link if it doesn't exist
if [ ! -L /var/www/public/storage ]; then
    php artisan storage:link 2>/dev/null || true
fi

# Clear caches (helps with 500 errors)
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# Optimize for production (only if APP_KEY is set)
if [ ! -z "$APP_KEY" ]; then
    php artisan config:cache 2>/dev/null || true
    php artisan route:cache 2>/dev/null || true
    php artisan view:cache 2>/dev/null || true
fi

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf -n

