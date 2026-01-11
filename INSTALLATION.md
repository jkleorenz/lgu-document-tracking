# LGU Document Tracking System - Installation Guide

## System Requirements

- PHP >= 8.1
- Composer
- Node.js >= 16.x & NPM
- MySQL >= 5.7 or MariaDB >= 10.3
- Apache/Nginx Web Server

## Step-by-Step Installation

### 1. Install PHP Dependencies

```bash
composer install
```

### 2. Install JavaScript Dependencies

```bash
npm install
```

### 3. Environment Configuration

Copy the example environment file:
```bash
cp .env.example .env
```

Edit `.env` file and configure:
```
APP_NAME="LGU Document Tracking System"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lgu_document_tracking
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Create Database

Create a MySQL database:
```sql
CREATE DATABASE lgu_document_tracking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Run Migrations and Seeders

```bash
php artisan migrate --seed
```

This will:
- Create all database tables
- Set up roles and permissions
- Create 10 departments
- Create default admin user and sample users

### 7. Create Storage Link

```bash
php artisan storage:link
```

### 8. Create QR Code Directory

```bash
mkdir -p public/qrcodes
chmod -R 775 public/qrcodes
```

### 9. Build Frontend Assets

For development:
```bash
npm run dev
```

For production:
```bash
npm run build
```

### 10. Start Development Server

```bash
php artisan serve
```

The application will be available at: `http://localhost:8000`

## Default Login Credentials

### Administrator
- **Email:** admin@lgu.gov
- **Password:** password

### Department Head (HR)
- **Email:** maria.santos@lgu.gov
- **Password:** password

### Department Head (Finance)
- **Email:** juan.delacruz@lgu.gov
- **Password:** password

### LGU Staff
- **Email:** ana.reyes@lgu.gov
- **Password:** password

- **Email:** pedro.garcia@lgu.gov
- **Password:** password

- **Email:** carmen.lopez@lgu.gov
- **Password:** password

### Pending User (for testing verification)
- **Email:** roberto.mendoza@lgu.gov
- **Password:** password
- **Status:** Pending (needs admin approval)

## File Permissions

For Linux/Mac:
```bash
chmod -R 775 storage bootstrap/cache public/qrcodes
chown -R www-data:www-data storage bootstrap/cache public/qrcodes
```

For Windows with XAMPP:
- No special permissions needed, but ensure the directories are writable

## Troubleshooting

### Issue: QR codes not generating
**Solution:** Ensure `public/qrcodes` directory exists and is writable

### Issue: Database connection error
**Solution:** Check `.env` database credentials and ensure MySQL is running

### Issue: 500 error on first load
**Solution:** 
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Issue: Assets not loading
**Solution:** 
```bash
npm run build
php artisan storage:link
```

## Production Deployment

For production deployment:

1. Set environment to production:
```
APP_ENV=production
APP_DEBUG=false
```

2. Optimize application:
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

3. Set proper permissions:
```bash
chmod -R 755 storage bootstrap/cache
```

4. Configure web server (Apache/Nginx) to point to `public/` directory

## System Features

### Core Functionality
- ✅ Role-based authentication (Administrator, LGU Staff, Department Head)
- ✅ Document creation and management
- ✅ QR code generation and scanning
- ✅ Document status tracking
- ✅ Real-time notifications
- ✅ Document archiving
- ✅ User verification system
- ✅ Priority document flagging
- ✅ Comprehensive audit trail

### Security Features
- ✅ Password hashing (Bcrypt)
- ✅ CSRF protection
- ✅ Role-based access control
- ✅ Input validation
- ✅ Session management

## Support

For issues or questions:
1. Check the README.md file
2. Review the troubleshooting section
3. Check Laravel logs in `storage/logs/`

## License

MIT License

