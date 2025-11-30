#!/bin/bash

echo "=== Starting Laravel Application ==="

# Check for APP_KEY
if [ -z "$APP_KEY" ]; then
    echo "WARNING: APP_KEY is not set! This will cause 500 errors."
    echo "Please set APP_KEY in your Render environment variables."
    echo "You can generate one with: php artisan key:generate"
fi

# Create storage directories if they don't exist
echo "Creating storage directories..."
mkdir -p /var/www/storage/framework/cache/data
mkdir -p /var/www/storage/framework/sessions
mkdir -p /var/www/storage/framework/views
mkdir -p /var/www/storage/logs
mkdir -p /var/www/bootstrap/cache

# Ensure sessions directory is writable (critical for login)
touch /var/www/storage/framework/sessions/.gitkeep 2>/dev/null || true

# Set proper permissions (run as root, then switch to www-data)
echo "Setting permissions..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage
chmod -R 775 /var/www/bootstrap/cache

# Ensure specific directories are writable (critical for sessions and cache)
chmod -R 777 /var/www/storage/framework/cache
chmod -R 777 /var/www/storage/framework/sessions
chmod -R 777 /var/www/storage/framework/views
chmod -R 777 /var/www/storage/logs

# Verify sessions directory is writable
if [ ! -w /var/www/storage/framework/sessions ]; then
    echo "ERROR: Sessions directory is not writable!"
    chmod 777 /var/www/storage/framework/sessions
fi

# Create storage link if it doesn't exist
if [ ! -L /var/www/public/storage ]; then
    echo "Creating storage symlink..."
    php artisan storage:link 2>&1 || echo "Warning: Could not create storage link (may already exist)"
fi

# Clear caches (non-blocking - don't fail if this doesn't work)
echo "Clearing caches..."
php artisan config:clear 2>&1 || echo "Note: Config clear had issues (continuing anyway)"
php artisan cache:clear 2>&1 || echo "Note: Cache clear had issues (continuing anyway)"
php artisan route:clear 2>&1 || echo "Note: Route clear had issues (continuing anyway)"
php artisan view:clear 2>&1 || echo "Note: View clear had issues (continuing anyway)"

# Fix permissions again after cache clear (in case files were created)
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

# Test database connection and check if migrations are needed
echo "Checking database connection..."
php artisan migrate:status 2>&1 | head -10 || echo "Note: Could not check migration status (database may not be set up)"

# Test if Laravel can bootstrap (non-blocking)
echo "Testing Laravel bootstrap..."
php artisan about 2>&1 | head -20 || echo "Warning: Laravel bootstrap test had issues (continuing anyway)"

# Optimize for production (only if APP_KEY is set, and non-blocking)
if [ ! -z "$APP_KEY" ]; then
    echo "Caching configuration..."
    php artisan config:cache 2>&1 || echo "Warning: Config cache failed (app will still work)"
    php artisan route:cache 2>&1 || echo "Warning: Route cache failed (app will still work)"
    php artisan view:cache 2>&1 || echo "Warning: View cache failed (app will still work)"
    
    # Fix permissions after caching
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
else
    echo "Skipping cache optimization (APP_KEY not set)"
fi

echo "=== Starting Supervisor ==="
# Start supervisor (this should never fail)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf -n

