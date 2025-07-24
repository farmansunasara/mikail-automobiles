# Railway Deployment Guide for Mikail Automobiles

## Overview
This guide will help you deploy the Mikail Automobiles Laravel application to Railway, a modern cloud platform that simplifies deployment.

## Prerequisites
- Railway account (sign up at https://railway.app)
- Git repository with your project
- Basic understanding of environment variables

## Deployment Files Created
The following files have been created for Railway deployment:

1. **`Procfile`** - Defines how Railway should run your application
2. **`railway.json`** - Railway-specific configuration
3. **`nixpacks.toml`** - Build configuration for Nixpacks
4. **`.env.example`** - Environment variables template

## Step-by-Step Deployment Process

### 1. Prepare Your Repository
```bash
# Make sure all files are committed
git add .
git commit -m "Prepare for Railway deployment"
git push origin main
```

### 2. Create Railway Project
1. Go to https://railway.app and sign in
2. Click "New Project"
3. Select "Deploy from GitHub repo"
4. Choose your repository
5. Railway will automatically detect it's a Laravel project

### 3. Add MySQL Database
1. In your Railway project dashboard
2. Click "New" → "Database" → "Add MySQL"
3. Railway will create a MySQL instance and provide connection details

### 4. Configure Environment Variables
In your Railway project dashboard, go to "Variables" and add:

#### Required Variables:
```
APP_NAME=Mikail Automobiles
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_URL=https://your-app-name.railway.app

# Database (Railway will provide these)
DB_CONNECTION=mysql
DB_HOST=containers-us-west-xxx.railway.app
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=YOUR_DB_PASSWORD

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# Mail (optional - for password resets)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Mikail Automobiles"
```

#### Generate APP_KEY:
```bash
# Run locally to generate key
php artisan key:generate --show
```

### 5. Database Setup
After deployment, you need to run migrations:

1. Go to Railway dashboard → Your project → "Deployments"
2. Click on the latest deployment
3. Open "View Logs"
4. Or use Railway CLI:

```bash
# Install Railway CLI
npm install -g @railway/cli

# Login and link project
railway login
railway link

# Run migrations
railway run php artisan migrate --force

# Seed database with initial data
railway run php artisan db:seed --force
```

### 6. Import Initial Data
You can import your existing data:

```bash
# If you have the SQL file
railway run mysql -h $DB_HOST -P $DB_PORT -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE < mikail_automobiles.sql
```

## Post-Deployment Configuration

### 1. Create Admin User
```bash
railway run php artisan tinker

# In tinker console:
User::create([
    'name' => 'Admin User',
    'email' => 'admin@mikailautomobiles.com',
    'password' => Hash::make('your-secure-password'),
    'email_verified_at' => now()
]);
```

### 2. Optimize Application
```bash
railway run php artisan config:cache
railway run php artisan route:cache
railway run php artisan view:cache
```

## Domain Configuration

### Custom Domain (Optional)
1. In Railway dashboard → Settings → Domains
2. Add your custom domain
3. Update DNS records as instructed
4. Update `APP_URL` environment variable

## Monitoring & Maintenance

### 1. View Logs
```bash
railway logs
```

### 2. Database Backup
```bash
# Create backup
railway run mysqldump -h $DB_HOST -P $DB_PORT -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE > backup.sql
```

### 3. Update Application
```bash
# Push changes
git push origin main

# Railway will automatically redeploy
```

## Troubleshooting

### Common Issues:

#### 1. **500 Internal Server Error**
- Check logs: `railway logs`
- Ensure `APP_KEY` is set
- Verify database connection

#### 2. **Database Connection Failed**
- Verify database environment variables
- Check if MySQL service is running
- Ensure database exists

#### 3. **Assets Not Loading**
- Run `npm run build` locally and commit
- Check if Vite build completed successfully

#### 4. **Permission Errors**
- Laravel should handle permissions automatically on Railway
- If issues persist, check storage directory permissions

### Debug Commands:
```bash
# Check environment
railway run php artisan env

# Check database connection
railway run php artisan migrate:status

# Clear all caches
railway run php artisan optimize:clear
```

## Security Considerations

### 1. Environment Variables
- Never commit `.env` file
- Use strong passwords
- Rotate keys regularly

### 2. Database Security
- Use Railway's private networking
- Regular backups
- Monitor access logs

### 3. Application Security
- Keep Laravel updated
- Monitor for vulnerabilities
- Use HTTPS (Railway provides SSL)

## Performance Optimization

### 1. Caching
```bash
railway run php artisan config:cache
railway run php artisan route:cache
railway run php artisan view:cache
```

### 2. Database Optimization
- Add indexes for frequently queried columns
- Optimize queries with eager loading
- Consider database connection pooling

### 3. Asset Optimization
- Ensure Vite builds are optimized
- Use CDN for static assets (optional)

## Scaling Considerations

### 1. Vertical Scaling
- Railway allows easy resource scaling
- Monitor CPU and memory usage

### 2. Database Scaling
- Consider read replicas for heavy read workloads
- Database connection pooling

### 3. File Storage
- For production, consider cloud storage (AWS S3, etc.)
- Railway provides persistent storage

## Support & Resources

- **Railway Documentation**: https://docs.railway.app
- **Laravel Deployment**: https://laravel.com/docs/deployment
- **Railway Discord**: https://discord.gg/railway
- **Railway CLI**: https://docs.railway.app/develop/cli

## Cost Estimation

Railway pricing is usage-based:
- **Starter Plan**: $5/month per service
- **Database**: Additional cost based on usage
- **Bandwidth**: Included in reasonable limits

For a small to medium business application like Mikail Automobiles, expect $10-30/month depending on usage.

## Conclusion

Railway provides an excellent platform for deploying Laravel applications with minimal configuration. The platform handles most infrastructure concerns, allowing you to focus on your application development.

After successful deployment, your Mikail Automobiles application will be accessible at `https://your-app-name.railway.app` with full functionality including:

- User authentication
- Inventory management
- Customer management
- Dual invoice system (GST/Non-GST)
- Stock management
- Reporting system
- PDF generation

Remember to regularly backup your data and monitor application performance.
