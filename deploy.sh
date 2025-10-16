#!/bin/bash

# Fidelity Voting System - Production Deployment Script
# Make this file executable: chmod +x deploy.sh

echo "🚀 Starting Fidelity Voting System deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Check if running as root
if [ "$EUID" -eq 0 ]; then
    print_warning "Running as root. Consider running as a dedicated user for security."
fi

# Backup current .env if it exists
if [ -f .env ]; then
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
    print_status "Current .env backed up"
fi

# Copy production environment file
if [ -f .env.production ]; then
    print_warning "Please update .env.production with your production settings before proceeding."
    read -p "Have you updated .env.production with your database and domain settings? (y/N): " confirm
    if [[ $confirm == [yY] || $confirm == [yY][eE][sS] ]]; then
        cp .env.production .env
        print_status ".env.production copied to .env"
    else
        print_error "Please update .env.production first, then run this script again."
        exit 1
    fi
else
    print_error ".env.production file not found!"
    exit 1
fi

# Update APP_KEY in .env with new production key
NEW_KEY="base64:jX86ProjyOqLBykkINKPh1yhq/N+oxhdLKlmq5/LfE0="
sed -i.bak "s/APP_KEY=.*/APP_KEY=$NEW_KEY/" .env
print_status "New application key set"

# Install/update dependencies
print_status "Installing production dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Install npm dependencies and build assets
print_status "Building production assets..."
npm ci --only=production
npm run build

# Set proper permissions
print_status "Setting file permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# Run database migrations
print_status "Running database migrations..."
php artisan migrate --force

# Seed database with admin user
print_status "Seeding database..."
php artisan db:seed --force

# Cache optimization for production
print_status "Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Generate storage link
php artisan storage:link

# Clear any existing caches
php artisan cache:clear

print_status "🎉 Deployment completed successfully!"
echo ""
echo "📋 Next steps:"
echo "1. Configure your web server (Apache/Nginx) to point to the 'public' directory"
echo "2. Set up SSL certificate for HTTPS"
echo "3. Configure your firewall"
echo "4. Set up regular backups"
echo "5. Monitor application logs in storage/logs/"
echo ""
echo "🔐 Default admin credentials:"
echo "Email: admin@fidelity.com"
echo "Password: password"
echo ""
print_warning "Please change the admin password after first login!"

# Display application URL
if grep -q "APP_URL" .env; then
    APP_URL=$(grep "APP_URL" .env | cut -d '=' -f2)
    echo "🌐 Application URL: $APP_URL"
fi