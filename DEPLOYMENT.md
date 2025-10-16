# Fidelity Voting System - Deployment Guide

## 🚀 Quick Deployment

### Prerequisites
- Ubuntu/CentOS server with root access
- PHP 8.1+ with required extensions
- MySQL 8.0+ or MariaDB 10.3+
- Nginx or Apache web server
- Composer
- Node.js 20.19+ and npm

### 1. Server Setup

#### Install PHP and Extensions
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install php8.1 php8.1-fpm php8.1-mysql php8.1-mbstring php8.1-xml php8.1-curl php8.1-zip php8.1-bcmath php8.1-tokenizer

# CentOS/RHEL
sudo yum install php php-fpm php-mysql php-mbstring php-xml php-curl php-zip php-bcmath php-tokenizer
```

#### Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

#### Install Node.js
```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### 2. Database Setup

#### Create MySQL Database
```bash
mysql -u root -p < database/production-setup.sql
```

Or manually:
```sql
CREATE DATABASE fidelity_voting_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'fidelity_app'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON fidelity_voting_production.* TO 'fidelity_app'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Application Deployment

#### Clone/Upload Application
```bash
# If using Git
git clone your-repository.git /var/www/fidelity-voting
cd /var/www/fidelity-voting

# If uploading files, extract to /var/www/fidelity-voting
```

#### Configure Environment
```bash
# Copy and edit production environment file
cp .env.production .env
nano .env
```

**Required .env updates:**
- `APP_URL`: Your domain (https://your-domain.com)
- `DB_DATABASE`: fidelity_voting_production
- `DB_USERNAME`: fidelity_app
- `DB_PASSWORD`: Your database password
- `MAIL_*`: Your email server settings

#### Run Deployment Script
```bash
chmod +x deploy.sh
./deploy.sh
```

### 4. Web Server Configuration

#### Nginx Configuration
Create `/etc/nginx/sites-available/fidelity-voting`:
```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/fidelity-voting/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/fidelity-voting /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### Apache Configuration
Create `/etc/apache2/sites-available/fidelity-voting.conf`:
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/fidelity-voting/public

    <Directory /var/www/fidelity-voting/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/fidelity-voting_error.log
    CustomLog ${APACHE_LOG_DIR}/fidelity-voting_access.log combined
</VirtualHost>
```

Enable site:
```bash
sudo a2ensite fidelity-voting
sudo a2enmod rewrite
sudo systemctl reload apache2
```

### 5. SSL Certificate (Recommended)

#### Using Let's Encrypt
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

### 6. File Permissions
```bash
sudo chown -R www-data:www-data /var/www/fidelity-voting
sudo chmod -R 755 /var/www/fidelity-voting
sudo chmod -R 775 /var/www/fidelity-voting/storage
sudo chmod -R 775 /var/www/fidelity-voting/bootstrap/cache
```

### 7. Process Management (Optional)

#### Using Supervisor for Queue Workers
Create `/etc/supervisor/conf.d/fidelity-voting.conf`:
```ini
[program:fidelity-voting-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/fidelity-voting/artisan queue:work database --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/fidelity-voting/storage/logs/worker.log
```

### 8. Security Hardening

#### Firewall Configuration
```bash
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'  # or 'Apache Full'
sudo ufw enable
```

#### Hide Server Information
Add to Nginx configuration:
```nginx
server_tokens off;
```

#### Disable PHP Information
In `php.ini`:
```ini
expose_php = Off
```

### 9. Backup Strategy

#### Database Backup Script
Create `/home/backup/backup-db.sh`:
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u fidelity_app -p fidelity_voting_production > /home/backup/fidelity_db_$DATE.sql
find /home/backup -name "fidelity_db_*.sql" -mtime +7 -delete
```

#### Application Backup
```bash
tar -czf /home/backup/fidelity_app_$(date +%Y%m%d_%H%M%S).tar.gz /var/www/fidelity-voting
```

### 10. Monitoring

#### Log Files to Monitor
- `/var/www/fidelity-voting/storage/logs/laravel.log`
- `/var/log/nginx/access.log` (or Apache equivalent)
- `/var/log/nginx/error.log`
- `/var/log/mysql/error.log`

#### Health Check Endpoint
The application includes a health check at `/health` that returns system status.

## 🔧 Troubleshooting

### Common Issues

1. **Permission Denied Errors**
   ```bash
   sudo chown -R www-data:www-data storage bootstrap/cache
   sudo chmod -R 775 storage bootstrap/cache
   ```

2. **Database Connection Failed**
   - Check database credentials in `.env`
   - Verify MySQL service is running
   - Check firewall settings

3. **Assets Not Loading**
   ```bash
   npm run build
   php artisan storage:link
   ```

4. **Session/Cache Issues**
   ```bash
   php artisan cache:clear
   php artisan session:flush
   ```

## 📞 Support

- Check logs in `storage/logs/laravel.log`
- Verify environment configuration
- Check web server error logs
- Ensure all PHP extensions are installed

## 🔐 Default Credentials

**Admin Account:**
- Email: admin@fidelity.com
- Password: password

**⚠️ Important:** Change the admin password immediately after first login!

## 🚀 Post-Deployment Checklist

- [ ] SSL certificate installed and working
- [ ] Database connection successful
- [ ] Admin login working
- [ ] QR code generation working
- [ ] Voting process functional
- [ ] Email notifications working (if configured)
- [ ] Backup strategy implemented
- [ ] Monitoring set up
- [ ] Security hardening applied
- [ ] Performance optimization applied

## 📱 Testing the Deployment

1. Visit your domain
2. Login with admin credentials
3. Create a test event
4. Generate QR code
5. Test voting process
6. Verify results display
7. Check real-time chart updates