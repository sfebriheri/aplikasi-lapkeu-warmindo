# LAPKEU Warmindo - Installation Guide

## System Requirements

- **PHP**: 7.4 or higher
- **Database**: PostgreSQL 12 or higher
- **Web Server**: Apache 2.4+ with mod_rewrite enabled, or Nginx
- **Node.js**: 16+ (for frontend development with Tailwind CSS)
- **Composer**: For PHP dependency management

## Installation Steps

### 1. Clone Repository

```bash
git clone <repository-url>
cd aplikasi-lapkeu-warmindo
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Environment Configuration

Copy the example environment file and configure it:

```bash
cp .env.example .env
```

Edit `.env` with your settings:

```env
# Database Configuration
DB_DRIVER=postgre
DB_HOST=localhost
DB_USER=postgres
DB_PASS=your_password
DB_NAME=lapkeu_warmindo

# Application Settings
APP_URL=https://your-domain.com/

# Email Configuration
EMAIL_SMTP_USER=your-email@gmail.com
EMAIL_SMTP_PASS=your-app-password
```

### 4. Database Setup

#### Create PostgreSQL Database

```bash
createdb -U postgres lapkeu_warmindo
```

#### Run Migrations

```bash
# Via CLI
php index.php cli migrations latest

# Or through web interface (if available)
```

This will create all necessary tables with proper relationships and indexes.

### 5. Frontend Setup (Optional but Recommended)

Install Node.js dependencies:

```bash
npm install
```

Build CSS with Tailwind:

```bash
npm run build
```

Or for development with auto-reload:

```bash
npm run dev
```

### 6. Directory Permissions

Ensure proper permissions on writable directories:

```bash
chmod -R 755 application/
chmod -R 755 assets/
chmod -R 755 application/logs/
chmod -R 755 application/cache/
chmod -R 755 assets/uploads/
chmod -R 755 assets/img/profile/
```

### 7. Web Server Configuration

#### Apache (.htaccess)

Make sure mod_rewrite is enabled and `.htaccess` is properly configured:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [L]
</IfModule>
```

#### Nginx

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

### 8. Access the Application

Open your browser and navigate to:

```
https://your-domain.com/
```

### Default Credentials

Default admin account is created during migration:
- **Email**: admin@lapkeu.local
- **Password**: Admin@123456

⚠️ **Important**: Change the default password immediately after first login!

## Database Migration

### Create a New Migration

```bash
php index.php cli make:migration create_your_table_name
```

This will generate a migration file in `application/migrations/`

### Run All Pending Migrations

```bash
php index.php cli migrations latest
```

### Rollback Last Migration

```bash
php index.php cli migrations previous
```

## Development Commands

### Build Frontend Assets

```bash
# Build CSS (Tailwind + SCSS)
npm run build:all

# Watch for changes
npm run dev
npm run scss
```

### Database

```bash
# Run migrations
php index.php cli migrations latest

# Rollback migrations
php index.php cli migrations previous

# Refresh (drop and recreate)
php index.php cli migrations refresh
```

## Troubleshooting

### Database Connection Error

Check your `.env` file for correct database credentials:

```env
DB_HOST=localhost
DB_USER=postgres
DB_PASS=your_password
DB_NAME=lapkeu_warmindo
```

### Permission Denied Error

Fix directory permissions:

```bash
sudo chown -R www-data:www-data /path/to/aplikasi-lapkeu-warmindo
sudo chmod -R 755 application/ assets/
```

### 404 Error on Routes

Ensure mod_rewrite is enabled and `.htaccess` is in the root directory.

### Missing CSRF Token

Verify CSRF protection is enabled in `application/config/config.php`:

```php
$config['csrf_protection'] = TRUE;
```

## Security Best Practices

1. **Never commit `.env`** - Always use `.env.example` as template
2. **Change default passwords** immediately after setup
3. **Use strong encryption keys** in configuration
4. **Enable HTTPS** in production
5. **Regular backups** of database and files
6. **Keep CodeIgniter updated** with latest security patches
7. **Monitor log files** regularly

## Performance Optimization

### Database Indexes

All necessary indexes are created during migration setup.

### Caching

Enable query caching in `application/config/database.php`:

```php
'cache_on' => TRUE,
'cachedir' => APPPATH . 'cache/',
```

### CSS/JS Minification

Files are already minified when running `npm run build:all`

## Support & Documentation

- CodeIgniter Docs: https://codeigniter.com/user_guide/
- PostgreSQL Docs: https://www.postgresql.org/docs/
- Tailwind CSS: https://tailwindcss.com/docs

## License

MIT License - See LICENSE file for details
