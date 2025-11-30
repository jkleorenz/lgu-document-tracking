# Stage 1 - Build Frontend (Vite)
FROM node:18 AS frontend

WORKDIR /app

COPY package*.json ./

RUN npm install

COPY . .

RUN npm run build

# Stage 2 - Backend (Laravel + PHP + Composer + Nginx)
FROM php:8.2-fpm AS backend

# Install system dependencies and Nginx
RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libonig-dev libzip-dev zip \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev libxml2-dev \
    libcurl4-openssl-dev libicu-dev nginx supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        gd \
        xml \
        curl \
        intl \
        bcmath \
        exif \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies without scripts (artisan not available yet)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

# Copy built frontend from Stage 1
COPY --from=frontend /app/public/build ./public/build

# Copy remaining app files (including artisan)
COPY . .

# Run composer scripts now that artisan is available
RUN composer dump-autoload --optimize --no-dev && \
    php artisan package:discover --ansi

# Copy Nginx configuration
COPY nginx.conf /etc/nginx/sites-available/default

# Update Nginx config to use localhost instead of app:9000 for single container
RUN sed -i 's/fastcgi_pass app:9000/fastcgi_pass 127.0.0.1:9000/' /etc/nginx/sites-available/default && \
    ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default && \
    rm -rf /etc/nginx/sites-enabled/default.bak && \
    rm -f /etc/nginx/sites-enabled/default.bak

# Copy PHP-FPM pool configuration for graceful shutdown
# Remove default pool config if it exists to avoid conflicts
RUN rm -f /usr/local/etc/php-fpm.d/www.conf.default /usr/local/etc/php-fpm.d/zz-docker.conf 2>/dev/null || true
COPY www.conf /usr/local/etc/php-fpm.d/www.conf

# Copy supervisor configuration
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Create supervisor socket directory and set proper permissions
# Also ensure storage directories exist with proper structure
RUN mkdir -p /var/run/supervisor /var/log/supervisor \
    && chmod 755 /var/run/supervisor \
    && chmod 755 /var/log/supervisor \
    && mkdir -p /var/www/storage/framework/cache/data \
    && mkdir -p /var/www/storage/framework/sessions \
    && mkdir -p /var/www/storage/framework/views \
    && mkdir -p /var/www/storage/logs \
    && mkdir -p /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expose port 80 for Render
EXPOSE 80

# Use entrypoint script for proper Laravel initialization
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

