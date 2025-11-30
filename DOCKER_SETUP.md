# Docker Setup Guide

This guide will help you set up and run the LGU Document Tracking System using Docker.

## Prerequisites

- Docker installed (version 20.10 or higher)
- Docker Compose installed (version 2.0 or higher)

## Quick Start

### 1. Configure Environment Variables

Create a `.env` file in the root directory (or copy from `.env.example` if available) with the following database settings:

```env
APP_NAME="LGU Document Tracking"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=lgu_document_tracking
DB_USERNAME=lgu_user
DB_PASSWORD=lgu_password
```

**Note:** The `DB_HOST` should be set to `db` (the service name in docker-compose.yml).

### 2. Build and Start Containers

```bash
docker-compose up -d --build
```

This will:
- Build the frontend assets using Vite
- Build the PHP application container
- Start MySQL database
- Start Nginx web server
- Start PHP-FPM application

### 3. Generate Application Key

```bash
docker exec -it lgu-app php artisan key:generate
```

### 4. Run Database Migrations

```bash
docker exec -it lgu-app php artisan migrate
```

### 5. Seed Database (Optional)

```bash
docker exec -it lgu-app php artisan db:seed
```

### 6. Set Storage Permissions

```bash
docker exec -it lgu-app chmod -R 775 storage bootstrap/cache
docker exec -it lgu-app chown -R www-data:www-data storage bootstrap/cache
```

## Accessing the Application

- **Web Application:** http://localhost
- **Database:** localhost:3307 (use credentials from `.env`)

## Useful Commands

### View Logs
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f db
```

### Stop Containers
```bash
docker-compose down
```

### Stop and Remove Volumes (WARNING: Deletes database data)
```bash
docker-compose down -v
```

### Rebuild Containers
```bash
docker-compose up -d --build
```

### Access Container Shell
```bash
# PHP application container
docker exec -it lgu-app bash

# Database container
docker exec -it lgu-db bash
```

### Run Artisan Commands
```bash
docker exec -it lgu-app php artisan [command]
```

### Clear Laravel Caches
```bash
docker exec -it lgu-app php artisan config:clear
docker exec -it lgu-app php artisan route:clear
docker exec -it lgu-app php artisan view:clear
docker exec -it lgu-app php artisan cache:clear
```

## Troubleshooting

### Port Already in Use
If port 80 is already in use, modify the `ports` section in `docker-compose.yml`:
```yaml
ports:
  - "8080:80"  # Change to use port 8080 instead
```

### Permission Issues
If you encounter permission issues with storage:
```bash
docker exec -it lgu-app chown -R www-data:www-data storage bootstrap/cache
docker exec -it lgu-app chmod -R 775 storage bootstrap/cache
```

### Database Connection Issues
Ensure your `.env` file has:
- `DB_HOST=db` (not `localhost` or `127.0.0.1`)
- Database credentials match those in `docker-compose.yml`

### Rebuild Frontend Assets
If you need to rebuild frontend assets:
```bash
docker-compose exec app npm run build
```

Or rebuild the entire container:
```bash
docker-compose up -d --build
```

## Production Deployment

For production deployment:

1. Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`
2. Ensure `APP_KEY` is set
3. Use strong database passwords
4. Configure proper domain in `APP_URL`
5. Set up SSL/TLS certificates (modify nginx.conf for HTTPS)
6. Use environment-specific database credentials
7. Consider using Docker secrets for sensitive data

## File Structure

- `Dockerfile` - Multi-stage build for frontend and backend
- `docker-compose.yml` - Orchestrates all services
- `nginx.conf` - Nginx web server configuration
- `.dockerignore` - Files excluded from Docker build context

