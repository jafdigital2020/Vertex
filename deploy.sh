#!/bin/bash

# Vertex Deployment Script
echo "🚀 Starting deployment..."

# Pull latest code
echo "📥 Pulling latest code..."
git pull origin main

# Install/update dependencies
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader

# Clear all caches
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# Optimize for production
echo "⚡ Optimizing..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations (if any)
echo "🗃️ Running migrations..."
php artisan migrate --force

# Set proper permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Update asset version in .env (force cache bust)
echo "🎨 Updating asset version..."
TIMESTAMP=$(date +%s)
sed -i "s/ASSET_VERSION=.*/ASSET_VERSION=$TIMESTAMP/" .env || echo "ASSET_VERSION=$TIMESTAMP" >> .env

echo "✅ Deployment complete!"
echo "Asset version: $TIMESTAMP"
