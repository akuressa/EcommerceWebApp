# Local Development Setup Guide

## Quick Setup for XAMPP Development

### 1. Prerequisites
- XAMPP installed and running
- MySQL service started in XAMPP Control Panel
- PHP 7.4+ (XAMPP comes with PHP 8.1+)

### 2. Database Setup
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create a new database named `ecommerceapp`
3. Import the database schema from `DATABASE FILE/ecommerceapp.sql`

### 3. Configuration
The application automatically detects your environment:
- **Local Development**: Uses `config/local_constants.php` (localhost, root, no password)
- **Azure Production**: Uses `config/azure_constants.php` (Azure database credentials)

### 4. Access Your Application
- **Main Application**: http://localhost/EcommerceWebApp/
- **Admin Panel**: http://localhost/EcommerceWebApp/admin/
- **Test Page**: http://localhost/EcommerceWebApp/test_local_dev.php

### 5. Development Features
- ✅ Automatic environment detection
- ✅ Local database configuration
- ✅ Session handling for web requests
- ✅ No CLI session warnings
- ✅ Ready for Azure deployment

### 6. File Structure
```
config/
├── constants.php          # Auto-detects environment
├── local_constants.php    # Local XAMPP settings
└── azure_constants.php    # Azure production settings
```

### 7. Troubleshooting
- **Database connection failed**: Check if MySQL service is running in XAMPP
- **Tables missing**: Import `DATABASE FILE/ecommerceapp.sql` in phpMyAdmin
- **Session warnings**: These are normal in CLI, won't appear in web browser

### 8. For Azure Deployment
1. Update `config/azure_constants.php` with your Azure database credentials
2. Upload your database schema to Azure MySQL
3. Deploy your code to Azure App Service
4. The application will automatically use Azure configuration

## Development Workflow
1. Develop locally using XAMPP
2. Test using the test page: `test_local_dev.php`
3. When ready, deploy to Azure
4. The application automatically switches to Azure configuration
