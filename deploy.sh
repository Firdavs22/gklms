#!/bin/bash

# GloboKids LMS - Deployment Script for Ubuntu 24.04
# Run this script as root on your VPS

set -e

echo "=== Starting GloboKids LMS Deployment ==="

# Update system
echo ">>> Updating system packages..."
apt update && apt upgrade -y

# Install required packages
echo ">>> Installing PHP 8.3 and extensions..."
apt install -y software-properties-common
add-apt-repository -y ppa:ondrej/php
apt update
apt install -y php8.3 php8.3-fpm php8.3-mysql php8.3-sqlite3 php8.3-mbstring \
    php8.3-xml php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl \
    php8.3-readline php8.3-tokenizer

# Install Nginx
echo ">>> Installing Nginx..."
apt install -y nginx

# Install Composer
echo ">>> Installing Composer..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Git
apt install -y git unzip

# Create web directory
echo ">>> Setting up project directory..."
mkdir -p /var/www
cd /var/www

# Clone repository
echo ">>> Cloning repository..."
git clone https://github.com/Firdavs22/gklms.git
cd gklms

# Install dependencies
echo ">>> Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Setup environment
echo ">>> Configuring environment..."
cp .env.example .env
php artisan key:generate

# Update .env for production
sed -i 's/APP_ENV=local/APP_ENV=production/' .env
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
sed -i 's|APP_URL=http://localhost|APP_URL=http://212.67.11.168|' .env

# Setup SQLite database
touch database/database.sqlite
php artisan migrate --force

# Create storage link
php artisan storage:link

# Set permissions
echo ">>> Setting permissions..."
chown -R www-data:www-data /var/www/gklms
chmod -R 755 /var/www/gklms
chmod -R 775 /var/www/gklms/storage
chmod -R 775 /var/www/gklms/bootstrap/cache

# Create Nginx config
echo ">>> Configuring Nginx..."
cat > /etc/nginx/sites-available/gklms << 'NGINX'
server {
    listen 80;
    server_name 212.67.11.168;
    root /var/www/gklms/public;

    add_header X-Frame-Options "SAMEORIGIN";
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
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX

# Enable site
ln -sf /etc/nginx/sites-available/gklms /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Test and restart Nginx
nginx -t
systemctl restart nginx
systemctl restart php8.3-fpm

# Optimize Laravel
echo ">>> Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "=== Deployment Complete! ==="
echo ""
echo "Your LMS is now available at: http://212.67.11.168"
echo "Admin panel: http://212.67.11.168/admin"
echo ""
echo "To create an admin user, run:"
echo "cd /var/www/gklms && php artisan tinker"
echo "Then: \App\Models\User::create(['name'=>'Admin','email'=>'admin@example.com','password'=>bcrypt('password'),'is_admin'=>true])"
echo ""
