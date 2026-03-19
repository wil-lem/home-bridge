#!/bin/bash

# Exit immediately if a command exits with a non-zero status
set -e

# Pull the latest changes from the repository
echo "Pulling latest changes from git repository..."
git pull origin main

cd funk-app


# Install PHP dependencies
echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies and build assets
echo "Installing Node.js dependencies and building assets..."
npm install
npm run build

php artisan migrate --force
php artisan config:clear
php artisan config:cache

# Route cache
echo "Clearing and caching routes..."
php artisan route:clear
php artisan route:cache

SERVICE="server-manager-horizon"

if systemctl list-unit-files --type=service | grep -q "^${SERVICE}.service"; then
    echo "Restarting ${SERVICE}.service"
    sudo systemctl restart "${SERVICE}.service"
else
    echo "Service ${SERVICE}.service not found, skipping Horizon restart"
fi
