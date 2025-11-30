# LGU Document Tracking - Docker Setup Script (PowerShell)
# This script sets up the Docker environment for production hosting

Write-Host "🚀 Starting Docker Setup for LGU Document Tracking..." -ForegroundColor Cyan

# Check if .env exists
if (-not (Test-Path .env)) {
    if (Test-Path env.production.template) {
        Write-Host "📝 Creating .env file from env.production.template..." -ForegroundColor Yellow
        Copy-Item env.production.template .env
    } elseif (Test-Path .env.example) {
        Write-Host "📝 Creating .env file from .env.example..." -ForegroundColor Yellow
        Copy-Item .env.example .env
    } else {
        Write-Host "⚠️  No .env template found. Please create .env manually." -ForegroundColor Red
        exit 1
    }
    Write-Host "⚠️  Please update .env with your production settings before continuing!" -ForegroundColor Red
    Write-Host "   Important: Set strong passwords for DB_PASSWORD and DB_ROOT_PASSWORD" -ForegroundColor Red
    Read-Host "Press Enter to continue after updating .env"
}

# Check if Docker is available
try {
    docker --version | Out-Null
} catch {
    Write-Host "❌ Docker is not installed or not in PATH!" -ForegroundColor Red
    Write-Host "   Please install Docker Desktop from: https://www.docker.com/products/docker-desktop" -ForegroundColor Yellow
    exit 1
}

# Check if docker compose is available
$dockerComposeCmd = "docker compose"
try {
    docker compose version | Out-Null
} catch {
    $dockerComposeCmd = "docker-compose"
    try {
        docker-compose version | Out-Null
    } catch {
        Write-Host "❌ Docker Compose is not available!" -ForegroundColor Red
        exit 1
    }
}

# Build Docker images
Write-Host "🔨 Building Docker images..." -ForegroundColor Cyan
& $dockerComposeCmd.Split(' ') build

# Start containers
Write-Host "🚀 Starting Docker containers..." -ForegroundColor Cyan
& $dockerComposeCmd.Split(' ') up -d

# Wait for database to be ready
Write-Host "⏳ Waiting for database to be ready..." -ForegroundColor Yellow
Start-Sleep -Seconds 10

# Generate application key if not set
Write-Host "🔑 Generating application key..." -ForegroundColor Cyan
docker exec -it lgu-app php artisan key:generate --force

# Run migrations
Write-Host "📊 Running database migrations..." -ForegroundColor Cyan
docker exec -it lgu-app php artisan migrate --force

# Set permissions
Write-Host "🔐 Setting storage permissions..." -ForegroundColor Cyan
docker exec -it lgu-app chmod -R 775 storage bootstrap/cache
docker exec -it lgu-app chown -R www-data:www-data storage bootstrap/cache

# Clear caches
Write-Host "🧹 Clearing application caches..." -ForegroundColor Cyan
docker exec -it lgu-app php artisan config:clear
docker exec -it lgu-app php artisan route:clear
docker exec -it lgu-app php artisan view:clear
docker exec -it lgu-app php artisan cache:clear

# Optimize for production
Write-Host "⚡ Optimizing for production..." -ForegroundColor Cyan
docker exec -it lgu-app php artisan config:cache
docker exec -it lgu-app php artisan route:cache
docker exec -it lgu-app php artisan view:cache

Write-Host "✅ Setup complete!" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Next steps:" -ForegroundColor Cyan
Write-Host "   1. Access your application at: http://localhost"
Write-Host "   2. Run seeders if needed: docker exec -it lgu-app php artisan db:seed"
Write-Host "   3. Check logs: $dockerComposeCmd logs -f"
Write-Host ""
Write-Host "🔒 Security reminders:" -ForegroundColor Yellow
Write-Host "   - Update .env with strong passwords"
Write-Host "   - Set APP_DEBUG=false for production"
Write-Host "   - Configure proper APP_URL"
Write-Host "   - Set up SSL/TLS certificates for HTTPS"

