# Production Deployment Guide

This guide will help you deploy the LGU Document Tracking System to a production server.

## Prerequisites

### Server Requirements
- **OS**: Ubuntu 20.04+ / Debian 11+ / CentOS 8+ (or any Linux distribution with Docker support)
- **RAM**: Minimum 2GB (4GB+ recommended)
- **CPU**: 2+ cores recommended
- **Storage**: 20GB+ free space
- **Docker**: Version 20.10+
- **Docker Compose**: Version 2.0+

### Install Docker on Linux Server

#### Ubuntu/Debian:
```bash
# Update system
sudo apt-get update

# Install prerequisites
sudo apt-get install -y ca-certificates curl gnupg lsb-release

# Add Docker's official GPG key
sudo mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg

# Set up repository
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker
sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Add user to docker group (optional, to run without sudo)
sudo usermod -aG docker $USER
```

#### CentOS/RHEL:
```bash
# Install prerequisites
sudo yum install -y yum-utils

# Add Docker repository
sudo yum-config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo

# Install Docker
sudo yum install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Start Docker
sudo systemctl start docker
sudo systemctl enable docker
```

## Deployment Steps

### 1. Transfer Files to Server

Upload all project files to your server. You can use:
- **Git**: `git clone` your repository
- **SCP**: `scp -r . user@server:/path/to/app`
- **SFTP**: Use FileZilla or similar tools
- **rsync**: `rsync -avz . user@server:/path/to/app`

### 2. Configure Environment Variables

```bash
# Copy example environment file
cp .env.example .env

# Edit .env with your production settings
nano .env
```

**Important .env settings for production:**

```env
APP_NAME="LGU Document Tracking"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database (use strong passwords!)
DB_HOST=db
DB_PORT=3306
DB_DATABASE=lgu_document_tracking
DB_USERNAME=lgu_user
DB_PASSWORD=STRONG_PASSWORD_HERE

# Mail Configuration (update with your SMTP settings)
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=your-email@yourdomain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 3. Run Setup Script

**Linux/Mac:**
```bash
chmod +x setup-docker.sh
./setup-docker.sh
```

**Windows (PowerShell):**
```powershell
.\setup-docker.ps1
```

**Or manually:**

```bash
# Build and start containers
docker compose up -d --build

# Wait for database
sleep 10

# Generate app key
docker exec -it lgu-app php artisan key:generate --force

# Run migrations
docker exec -it lgu-app php artisan migrate --force

# Set permissions
docker exec -it lgu-app chmod -R 775 storage bootstrap/cache
docker exec -it lgu-app chown -R www-data:www-data storage bootstrap/cache

# Optimize for production
docker exec -it lgu-app php artisan config:cache
docker exec -it lgu-app php artisan route:cache
docker exec -it lgu-app php artisan view:cache
```

### 4. Configure Domain and SSL

#### Option A: Using Nginx Reverse Proxy (Recommended)

Create `/etc/nginx/sites-available/lgu-tracking`:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    location / {
        proxy_pass http://localhost:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/lgu-tracking /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### Option B: Update docker-compose.yml to use port 443

Modify `docker-compose.yml`:
```yaml
nginx:
  ports:
    - "443:443"
    - "80:80"
```

Update `nginx.conf` to handle SSL.

### 5. Install SSL Certificate (Let's Encrypt)

```bash
# Install certbot
sudo apt-get install -y certbot python3-certbot-nginx

# Get certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal (already configured by certbot)
```

### 6. Configure Firewall

```bash
# Allow HTTP, HTTPS, and SSH
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 7. Set Up Automatic Backups

Create `/etc/cron.daily/lgu-backup.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/backups/lgu-tracking"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

# Backup database
docker exec lgu-db mysqldump -u lgu_user -p'DB_PASSWORD' lgu_document_tracking > $BACKUP_DIR/db_$DATE.sql

# Backup storage
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz storage/

# Keep only last 7 days
find $BACKUP_DIR -type f -mtime +7 -delete
```

Make executable:
```bash
chmod +x /etc/cron.daily/lgu-backup.sh
```

## Post-Deployment Checklist

- [ ] Application accessible via domain
- [ ] SSL certificate installed and working
- [ ] Database migrations completed
- [ ] Storage permissions set correctly
- [ ] Application key generated
- [ ] Environment variables configured
- [ ] Mail configuration tested
- [ ] Backups configured
- [ ] Firewall rules set
- [ ] Monitoring/logging set up
- [ ] Admin user created (via seeder or manually)

## Maintenance Commands

### View Logs
```bash
docker compose logs -f
docker compose logs -f app
docker compose logs -f nginx
docker compose logs -f db
```

### Update Application
```bash
# Pull latest changes
git pull

# Rebuild containers
docker compose up -d --build

# Run migrations
docker exec -it lgu-app php artisan migrate

# Clear and cache config
docker exec -it lgu-app php artisan config:clear
docker exec -it lgu-app php artisan config:cache
```

### Backup Database
```bash
docker exec lgu-db mysqldump -u lgu_user -p'PASSWORD' lgu_document_tracking > backup.sql
```

### Restore Database
```bash
docker exec -i lgu-db mysql -u lgu_user -p'PASSWORD' lgu_document_tracking < backup.sql
```

### Restart Services
```bash
docker compose restart
docker compose restart app
docker compose restart nginx
docker compose restart db
```

## Security Best Practices

1. **Strong Passwords**: Use complex passwords for database and root access
2. **Firewall**: Only expose necessary ports (80, 443, 22)
3. **SSL/TLS**: Always use HTTPS in production
4. **Regular Updates**: Keep Docker and system packages updated
5. **Backups**: Set up automated daily backups
6. **Monitoring**: Set up log monitoring and alerts
7. **Access Control**: Limit SSH access and use key-based authentication
8. **Environment Variables**: Never commit .env file to version control

## Troubleshooting

### Container won't start
```bash
docker compose logs app
docker compose ps
```

### Database connection issues
- Check DB_HOST is set to `db` (not localhost)
- Verify database credentials in .env
- Ensure database container is running: `docker compose ps`

### Permission errors
```bash
docker exec -it lgu-app chmod -R 775 storage bootstrap/cache
docker exec -it lgu-app chown -R www-data:www-data storage bootstrap/cache
```

### Port conflicts
- Change port mapping in docker-compose.yml
- Check what's using the port: `sudo netstat -tulpn | grep :80`

## Support

For issues or questions, refer to:
- `DOCKER_SETUP.md` - Local development setup
- `USER_GUIDE.txt` - Application user guide
- `INSTALLATION.md` - Manual installation guide







