#!/bin/bash

echo "=== Starting Laravel Application ==="

# Check for APP_KEY
if [ -z "$APP_KEY" ]; then
    echo "WARNING: APP_KEY is not set! This will cause 500 errors."
    echo "Please set APP_KEY in your Render environment variables."
    echo "You can generate one with: php artisan key:generate"
fi

# Set proper permissions
echo "Setting permissions..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Create storage link if it doesn't exist
if [ ! -L /var/www/public/storage ]; then
    echo "Creating storage symlink..."
    php artisan storage:link || echo "Warning: Could not create storage link"
fi

# Clear caches (helps with 500 errors)
echo "Clearing caches..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Test if Laravel can bootstrap
echo "Testing Laravel bootstrap..."
php artisan about 2>&1 | head -20 || echo "Warning: Laravel bootstrap test failed"

# Optimize for production (only if APP_KEY is set)
if [ ! -z "$APP_KEY" ]; then
    echo "Caching configuration..."
    php artisan config:cache || echo "Warning: Config cache failed"
    php artisan route:cache || echo "Warning: Route cache failed"
    php artisan view:cache || echo "Warning: View cache failed"
else
    echo "Skipping cache optimization (APP_KEY not set)"
fi

echo "=== Starting Supervisor ==="
# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf -n

