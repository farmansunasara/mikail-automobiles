#!/bin/bash

# Mikail Automobiles - Railway Deployment Script
# This script automates the deployment process to Railway

set -e  # Exit on any error

echo "🚀 Starting Mikail Automobiles deployment to Railway..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Railway CLI is installed
check_railway_cli() {
    if ! command -v railway &> /dev/null; then
        print_error "Railway CLI is not installed!"
        echo "Please install it with: npm install -g @railway/cli"
        exit 1
    fi
    print_success "Railway CLI is installed"
}

# Check if user is logged in to Railway
check_railway_auth() {
    if ! railway whoami &> /dev/null; then
        print_error "You are not logged in to Railway!"
        echo "Please run: railway login"
        exit 1
    fi
    print_success "Railway authentication verified"
}

# Generate Laravel application key
generate_app_key() {
    print_status "Generating Laravel application key..."
    if [ -f .env ]; then
        APP_KEY=$(php artisan key:generate --show)
        print_success "Application key generated: $APP_KEY"
        echo "APP_KEY=$APP_KEY" >> .env.railway
    else
        print_warning ".env file not found, key will be set in Railway variables"
        APP_KEY=$(php artisan key:generate --show)
        echo "APP_KEY=$APP_KEY" > .env.railway
    fi
}

# Build assets locally to ensure they work
build_assets() {
    print_status "Building frontend assets..."
    
    if [ ! -d "node_modules" ]; then
        print_status "Installing npm dependencies..."
        npm install
    fi
    
    print_status "Building production assets..."
    npm run build
    
    if [ $? -eq 0 ]; then
        print_success "Assets built successfully"
    else
        print_error "Asset build failed!"
        exit 1
    fi
}

# Optimize Laravel for production
optimize_laravel() {
    print_status "Optimizing Laravel for production..."
    
    # Clear all caches first
    php artisan optimize:clear
    
    # Install production dependencies
    composer install --optimize-autoloader --no-dev
    
    print_success "Laravel optimized for production"
}

# Create Railway project if it doesn't exist
create_railway_project() {
    print_status "Checking Railway project status..."
    
    if ! railway status &> /dev/null; then
        print_status "Creating new Railway project..."
        railway login
        railway new
        print_success "Railway project created"
    else
        print_success "Railway project already exists"
    fi
}

# Set up environment variables in Railway
setup_environment_variables() {
    print_status "Setting up environment variables in Railway..."
    
    # Read APP_KEY from generated file
    if [ -f .env.railway ]; then
        source .env.railway
    fi
    
    # Set essential environment variables
    railway variables set APP_NAME="Mikail Automobiles"
    railway variables set APP_ENV=production
    railway variables set APP_DEBUG=false
    railway variables set APP_KEY="$APP_KEY"
    railway variables set SESSION_DRIVER=database
    railway variables set CACHE_STORE=database
    railway variables set QUEUE_CONNECTION=database
    railway variables set LOG_CHANNEL=stack
    railway variables set LOG_LEVEL=error
    
    print_success "Environment variables configured"
}

# Add MySQL database to Railway project
setup_database() {
    print_status "Setting up MySQL database..."
    
    # Check if database already exists
    if railway run echo "Database check" &> /dev/null; then
        print_status "Database service already exists"
    else
        print_status "Adding MySQL database service..."
        # This would typically be done through Railway dashboard
        print_warning "Please add MySQL database through Railway dashboard"
        print_warning "Go to your project → New → Database → Add MySQL"
    fi
}

# Deploy to Railway
deploy_to_railway() {
    print_status "Deploying to Railway..."
    
    # Commit any changes
    git add .
    git commit -m "Prepare for Railway deployment" || true
    
    # Deploy
    railway up
    
    if [ $? -eq 0 ]; then
        print_success "Deployment initiated successfully"
    else
        print_error "Deployment failed!"
        exit 1
    fi
}

# Run post-deployment tasks
post_deployment_tasks() {
    print_status "Running post-deployment tasks..."
    
    # Wait for deployment to complete
    sleep 30
    
    print_status "Running database migrations..."
    railway run php artisan migrate --force
    
    print_status "Seeding database with initial data..."
    railway run php artisan db:seed --force || print_warning "Database seeding failed or no seeders found"
    
    print_status "Optimizing application..."
    railway run php artisan config:cache
    railway run php artisan route:cache
    railway run php artisan view:cache
    
    print_success "Post-deployment tasks completed"
}

# Get deployment URL
get_deployment_url() {
    print_status "Getting deployment URL..."
    
    DEPLOYMENT_URL=$(railway domain)
    if [ ! -z "$DEPLOYMENT_URL" ]; then
        print_success "Application deployed at: $DEPLOYMENT_URL"
    else
        print_warning "Could not retrieve deployment URL"
        print_status "Check Railway dashboard for your application URL"
    fi
}

# Main deployment function
main() {
    echo "=============================================="
    echo "  Mikail Automobiles - Railway Deployment"
    echo "=============================================="
    echo ""
    
    # Pre-deployment checks
    check_railway_cli
    check_railway_auth
    
    # Prepare application
    generate_app_key
    build_assets
    optimize_laravel
    
    # Railway setup
    create_railway_project
    setup_environment_variables
    setup_database
    
    # Deploy
    deploy_to_railway
    
    # Post-deployment
    post_deployment_tasks
    get_deployment_url
    
    echo ""
    echo "=============================================="
    print_success "Deployment completed successfully! 🎉"
    echo "=============================================="
    echo ""
    echo "Next steps:"
    echo "1. Add MySQL database through Railway dashboard if not done"
    echo "2. Update APP_URL environment variable with your domain"
    echo "3. Create admin user: railway run php artisan tinker"
    echo "4. Import initial data if needed"
    echo ""
    echo "For troubleshooting, check logs with: railway logs"
}

# Run main function
main "$@"
