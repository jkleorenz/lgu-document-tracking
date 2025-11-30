# Render Deployment Guide

This guide will help you deploy the LGU Document Tracking System on Render.

## Prerequisites

- GitHub repository with your code
- Render account (free tier available)
- Database (Render PostgreSQL or external MySQL)

## Step-by-Step Deployment

### 1. Create a Web Service on Render

1. Go to https://dashboard.render.com
2. Click "New +" → "Web Service"
3. Connect your GitHub repository
4. Select the repository: `jkleorenz/lgu-document-tracking`
5. Configure the service:
   - **Name**: `lgu-document-tracking` (or your preferred name)
   - **Region**: Choose closest to your users
   - **Branch**: `main`
   - **Root Directory**: Leave empty (root)
   - **Runtime**: `Docker`
   - **Dockerfile Path**: `Dockerfile` (no dot, no slash)
   - **Docker Context**: `.` (dot)
   - **Instance Type**: Free tier or higher

### 2. Configure Environment Variables

In your Render service settings, go to "Environment" and add these variables:

**Required:**
```env
APP_NAME="LGU Document Tracking"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-service-name.onrender.com
```

**Database (if using Render PostgreSQL):**
```env
DB_CONNECTION=pgsql
DB_HOST=your-postgres-host.onrender.com
DB_PORT=5432
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

**Database (if using external MySQL):**
```env
DB_CONNECTION=mysql
DB_HOST=your-mysql-host
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

**Mail Configuration:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=your-email@yourdomain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Other Settings:**
```env
LOG_CHANNEL=stack
LOG_LEVEL=error
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

### 3. Generate Application Key

After the first deployment, you need to generate the application key:

1. Go to your service's "Shell" tab in Render
2. Run:
```bash
php artisan key:generate --show
```
3. Copy the generated key
4. Update `APP_KEY` in Environment Variables with the copied value
5. Redeploy the service

### 4. Create Database (if using Render PostgreSQL)

1. Go to Render Dashboard
2. Click "New +" → "PostgreSQL"
3. Configure:
   - **Name**: `lgu-document-tracking-db`
   - **Database**: `lgu_document_tracking`
   - **User**: Auto-generated
   - **Region**: Same as your web service
4. Copy the connection details to your environment variables

### 5. Run Database Migrations

After deployment, run migrations:

1. Go to your service's "Shell" tab
2. Run:
```bash
php artisan migrate --force
```

### 6. Seed Database (Optional)

```bash
php artisan db:seed
```

### 7. Set Storage Permissions

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Important Notes for Render

### Port Configuration
- The Dockerfile exposes port **80**
- Render automatically maps this to port **10000** internally
- No additional port configuration needed

### Health Checks
- Render will automatically check port 80
- The service should respond with HTTP 200 on the root path

### Build Settings
- **Build Command**: Leave empty (handled by Dockerfile)
- **Start Command**: Leave empty (handled by Dockerfile CMD)
- **Dockerfile Path**: `Dockerfile`

### Auto-Deploy
- Render automatically deploys on every push to the main branch
- You can disable this in service settings if needed

## Troubleshooting

### Build Fails
- Check build logs in Render dashboard
- Ensure all files are committed to Git
- Verify Dockerfile is in the root directory

### Service Won't Start
- Check logs in Render dashboard
- Verify environment variables are set correctly
- Ensure `APP_KEY` is generated and set

### Database Connection Issues
- Verify database credentials in environment variables
- Check if database is accessible from Render's network
- For external databases, ensure firewall allows Render IPs

### 500 Errors
- Check application logs: `storage/logs/laravel.log`
- Verify `APP_KEY` is set
- Ensure storage permissions are correct
- Run `php artisan config:clear` in Shell

### Port Issues
- Ensure Dockerfile has `EXPOSE 80`
- Verify Nginx is running (check logs)

## Post-Deployment Checklist

- [ ] Service is running and accessible
- [ ] Environment variables configured
- [ ] Application key generated
- [ ] Database migrations completed
- [ ] Storage permissions set
- [ ] Test login functionality
- [ ] Verify file uploads work
- [ ] Check email configuration (if used)

## Default Login Credentials (After Seeding)

- **Admin**: admin@lgu.gov / password
- **Department Head**: maria.santos@lgu.gov / password
- **Staff**: ana.reyes@lgu.gov / password

## Updating the Application

1. Push changes to GitHub
2. Render will automatically rebuild and deploy
3. Or manually trigger deployment from Render dashboard

## Custom Domain

1. Go to service settings → "Custom Domains"
2. Add your domain
3. Update DNS records as instructed
4. Update `APP_URL` environment variable
5. Render will provision SSL automatically

## Monitoring

- View logs in real-time from Render dashboard
- Set up alerts for service failures
- Monitor resource usage in the dashboard

## Support

For Render-specific issues:
- Render Documentation: https://render.com/docs
- Render Support: support@render.com

For application issues:
- Check `storage/logs/laravel.log`
- Review application documentation

