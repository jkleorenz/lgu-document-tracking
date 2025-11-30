# Quick Start Guide - Docker Deployment

## 🚀 For Production Hosting

### Step 1: Install Docker

**Windows:**
- Download and install [Docker Desktop](https://www.docker.com/products/docker-desktop)
- Restart your computer after installation

**Linux (Ubuntu/Debian):**
```bash
sudo apt-get update
sudo apt-get install -y docker.io docker-compose
sudo systemctl start docker
sudo systemctl enable docker
```

**Linux (CentOS/RHEL):**
```bash
sudo yum install -y docker docker-compose
sudo systemctl start docker
sudo systemctl enable docker
```

### Step 2: Create Environment File

Copy the template and customize it:
```bash
# Windows PowerShell
Copy-Item env.production.template .env

# Linux/Mac
cp env.production.template .env
```

**Edit `.env` and update:**
- `APP_URL` - Your domain (e.g., `https://yourdomain.com`)
- `DB_PASSWORD` - Strong password for database
- `DB_ROOT_PASSWORD` - Strong password for MySQL root
- `MAIL_*` settings - Your email configuration

### Step 3: Run Setup Script

**Windows (PowerShell):**
```powershell
.\setup-docker.ps1
```

**Linux/Mac:**
```bash
chmod +x setup-docker.sh
./setup-docker.sh
```

**Or manually:**
```bash
# Build and start
docker compose up -d --build

# Wait for database
sleep 10

# Setup Laravel
docker exec -it lgu-app php artisan key:generate --force
docker exec -it lgu-app php artisan migrate --force
docker exec -it lgu-app chmod -R 775 storage bootstrap/cache
docker exec -it lgu-app php artisan config:cache
docker exec -it lgu-app php artisan route:cache
docker exec -it lgu-app php artisan view:cache
```

### Step 4: Access Application

- **Local:** http://localhost
- **Production:** Configure your domain to point to the server

### Step 5: Seed Database (Optional)

```bash
docker exec -it lgu-app php artisan db:seed
```

## 📋 Common Commands

```bash
# View logs
docker compose logs -f

# Stop containers
docker compose down

# Restart containers
docker compose restart

# Update application
git pull
docker compose up -d --build
docker exec -it lgu-app php artisan migrate
docker exec -it lgu-app php artisan config:cache
```

## 🔒 Security Checklist

Before going live:
- [ ] Strong passwords in `.env`
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] SSL certificate installed
- [ ] Firewall configured
- [ ] Backups set up

## 📚 Full Documentation

- `PRODUCTION_DEPLOYMENT.md` - Complete production deployment guide
- `DOCKER_SETUP.md` - Detailed Docker setup instructions
- `USER_GUIDE.txt` - Application user guide


