#!/bin/bash

# LGU Document Tracking - Docker Setup Script
# This script sets up the Docker environment for production hosting

set -e

echo "🚀 Starting Docker Setup for LGU Document Tracking..."

# Check if .env exists
if [ ! -f .env ]; then
    if [ -f env.production.template ]; then
        echo "📝 Creating .env file from env.production.template..."
        cp env.production.template .env
    elif [ -f .env.example ]; then
        echo "📝 Creating .env file from .env.example..."
        cp .env.example .env
    else
        echo "⚠️  No .env template found. Please create .env manually."
        exit 1
    fi
    echo "⚠️  Please update .env with your production settings before continuing!"
    echo "   Important: Set strong passwords for DB_PASSWORD and DB_ROOT_PASSWORD"
    read -p "Press Enter to continue after updating .env..."
fi

# Build Docker images
echo "🔨 Building Docker images..."
docker compose build

# Start containers
echo "🚀 Starting Docker containers..."
docker compose up -d

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
sleep 10

# Generate application key if not set
echo "🔑 Generating application key..."
docker exec -it lgu-app php artisan key:generate --force

# Run migrations
echo "📊 Running database migrations..."
docker exec -it lgu-app php artisan migrate --force

# Set permissions
echo "🔐 Setting storage permissions..."
docker exec -it lgu-app chmod -R 775 storage bootstrap/cache
docker exec -it lgu-app chown -R www-data:www-data storage bootstrap/cache

# Clear caches
echo "🧹 Clearing application caches..."
docker exec -it lgu-app php artisan config:clear
docker exec -it lgu-app php artisan route:clear
docker exec -it lgu-app php artisan view:clear
docker exec -it lgu-app php artisan cache:clear

# Optimize for production
echo "⚡ Optimizing for production..."
docker exec -it lgu-app php artisan config:cache
docker exec -it lgu-app php artisan route:cache
docker exec -it lgu-app php artisan view:cache

echo "✅ Setup complete!"
echo ""
echo "📋 Next steps:"
echo "   1. Access your application at: http://localhost"
echo "   2. Run seeders if needed: docker exec -it lgu-app php artisan db:seed"
echo "   3. Check logs: docker compose logs -f"
echo ""
echo "🔒 Security reminders:"
echo "   - Update .env with strong passwords"
echo "   - Set APP_DEBUG=false for production"
echo "   - Configure proper APP_URL"
echo "   - Set up SSL/TLS certificates for HTTPS"

