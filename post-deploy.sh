#!/bin/bash

# Post-deployment script for Railway
# This script runs after the application is deployed

set -e

echo "🔧 Running post-deployment tasks..."

# Run database migrations
echo "📊 Running database migrations..."
php artisan migrate --force

# Run production seeder
echo "🌱 Seeding database with initial data..."
php artisan db:seed --class=ProductionSeeder --force

# Clear and cache configurations
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link if needed
php artisan storage:link || true

echo "✅ Post-deployment tasks completed successfully!"
