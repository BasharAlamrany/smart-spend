# Alamrani Real Estate - Complete Real Estate Website

A modern, production-ready bilingual (Arabic/English) real estate website built with PHP, MySQL, and vanilla JavaScript. Features comprehensive property management, agent profiles, user accounts, admin panel, and full RTL/LTR language support.

## 🌟 Features

### Frontend Features
- **Bilingual Support**: Full Arabic (RTL) and English (LTR) language support
- **Responsive Design**: Mobile-first design that works on all devices
- **Modern UI/UX**: Clean, professional design with smooth animations
- **Advanced Search**: Property search with multiple filters and suggestions
- **Property Listings**: Grid/list view with detailed property information
- **Interactive Maps**: Leaflet.js integration with OpenStreetMap (no API keys required)
- **User Accounts**: Registration, login, favorites, and inquiry management
- **SEO Optimized**: Meta tags, structured data, and clean URLs
- **Performance**: Lazy loading, image optimization, and caching

### Backend Features
- **Admin Panel**: Complete property, agent, and user management
- **RESTful API**: JSON API endpoints for all operations
- **Security**: CSRF protection, input validation, password hashing
- **Database**: Optimized MySQL schema with proper indexes
- **Email System**: Automated notifications and welcome emails
- **File Upload**: Secure image upload with validation
- **Analytics**: Visit tracking and admin statistics

### Technical Stack
- **Backend**: PHP 8.0+, MySQL 8.0+, PDO
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Maps**: Leaflet.js with OpenStreetMap
- **Email**: PHP mail() with HTML templates
- **Database**: MySQL with InnoDB engine
- **Server**: Apache with mod_rewrite

## 📋 Requirements

### Server Requirements
- **PHP**: 8.0 or higher
- **MySQL**: 8.0 or higher (or MariaDB 10.4+)
- **Apache**: 2.4+ with mod_rewrite enabled
- **Extensions**: PDO, PDO_MySQL, GD, mbstring, openssl

### Development Tools
- **XAMPP**: 8.0+ (recommended for local development)
- **Composer**: Not required (no external dependencies)
- **Node.js**: Not required (no build process)

## 🚀 Installation Guide

### Option 1: XAMPP Installation (Recommended)

1. **Download and Install XAMPP**
   ```bash
   # Download XAMPP 8.0+ from https://www.apachefriends.org/
   # Install and start Apache and MySQL services
   ```

2. **Clone/Extract Project**
   ```bash
   # Extract all files to: C:\xampp\htdocs\alamrani\
   # Or clone: git clone [repository] C:\xampp\htdocs\alamrani
   ```

3. **Create Database**
   ```bash
   # Open phpMyAdmin: http://localhost/phpmyadmin
   # Create new database: alamrani_realestate
   # Import: db.sql file
   ```

4. **Configure Settings**
   ```bash
   # Copy config.php and update database credentials if needed
   # Default settings work with XAMPP out of the box
   ```

5. **Set Permissions**
   ```bash
   # Ensure uploads/ directory is writable
   # Windows: Right-click uploads/ → Properties → Security → Allow full control
   # Linux/Mac: chmod 755 uploads/
   ```

6. **Access Website**
   ```bash
   # Frontend: http://localhost/alamrani/
   # Admin Panel: http://localhost/alamrani/admin.php
   # API: http://localhost/alamrani/api.php
   ```

### Option 2: Manual Server Setup

1. **Server Setup**
   ```bash
   # Install Apache, PHP 8.0+, MySQL 8.0+
   sudo apt update
   sudo apt install apache2 php8.0 mysql-server php8.0-mysql php8.0-gd php8.0-mbstring
   ```

2. **Enable Modules**
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

3. **Database Setup**
   ```bash
   mysql -u root -p
   CREATE DATABASE alamrani_realestate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'alamrani'@'localhost' IDENTIFIED BY 'your_password';
   GRANT ALL PRIVILEGES ON alamrani_realestate.* TO 'alamrani'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   
   # Import database
   mysql -u alamrani -p alamrani_realestate < db.sql
   ```

4. **Configure PHP**
   ```bash
   # Edit php.ini
   upload_max_filesize = 10M
   post_max_size = 10M
   max_execution_time = 300
   memory_limit = 256M
   ```

5. **Virtual Host (Optional)**
   ```apache
   # /etc/apache2/sites-available/alamrani.conf
   <VirtualHost *:80>
       ServerName alamrani.local
       DocumentRoot /var/www/html/alamrani
       <Directory /var/www/html/alamrani>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

## ⚙️ Configuration

### Database Configuration
```php
// config.php - Update these settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'alamrani_realestate');
define('DB_USER', 'root'); // Change for production
define('DB_PASS', '');     // Set strong password for production
```

### Site Configuration
```php
// config.php - Update site settings
define('SITE_URL', 'http://localhost/alamrani'); // Change for production
define('ADMIN_EMAIL', 'admin@alamrani-realestate.com');
define('DEBUG_MODE', false); // Set to false in production
```

### Email Configuration (Optional)
```php
// config.php - For email notifications
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_email@domain.com');
define('SMTP_PASSWORD', 'your_password');
```

### Google Maps (Optional)
```php
// config.php - For Google Maps instead of OpenStreetMap
define('GOOGLE_MAPS_API_KEY', 'your_api_key_here');
```

## 👤 Default Admin Account

```
Username: admin
Password: admin123
Email: admin@alamrani-realestate.com
```

**⚠️ Important**: Change the default admin password immediately after installation!

## 📱 Usage Guide

### Admin Panel Functions

1. **Dashboard**
   - View site statistics and recent activity
   - Monitor inquiries and property performance

2. **Property Management**
   - Add/edit/delete properties
   - Upload multiple images per property
   - Set featured properties
   - Manage property status

3. **Agent Management**
   - Create agent profiles
   - Assign properties to agents
   - Manage agent contact information

4. **User Management**
   - View registered users
   - Monitor user activity
   - Manage user accounts

5. **Inquiry Management**
   - View and respond to property inquiries
   - Update inquiry status
   - Export inquiries to CSV

### API Endpoints

```bash
# Properties
GET    /api.php/properties              # List properties with filters
GET    /api.php/properties/{id}         # Get single property
POST   /api.php/properties              # Create property (admin only)
PUT    /api.php/properties/{id}         # Update property (admin only)
DELETE /api.php/properties/{id}         # Delete property (admin only)

# Inquiries
POST   /api.php/inquiries               # Submit inquiry
GET    /api.php/inquiries               # Get user inquiries (auth required)

# Favorites
GET    /api.php/favorites               # Get user favorites (auth required)
POST   /api.php/favorites               # Add to favorites (auth required)
DELETE /api.php/favorites/{id}          # Remove from favorites (auth required)

# Search
GET    /api.php/search/suggestions      # Get search suggestions

# Contact
POST   /api.php/contact                 # Submit contact form

# Admin
GET    /api.php/admin/stats             # Get admin statistics (admin only)
GET    /api.php/admin/inquiries         # Get all inquiries (admin only)
```

### Search Parameters

```bash
# Property search filters
?keyword=villa                    # Text search
?city=Sana'a                     # Filter by city
?type=sale                       # sale or rent
?property_type=apartment         # apartment, villa, house, office, shop, land
?min_price=100000               # Minimum price
?max_price=500000               # Maximum price
?rooms=3                        # Minimum rooms
?min_area=100                   # Minimum area (sqm)
?max_area=300                   # Maximum area (sqm)
?agent_id=1                     # Filter by agent
?featured=1                     # Featured properties only
?page=1                         # Pagination
?per_page=12                    # Items per page
?sort_by=price                  # Sort field
?sort_order=ASC                 # Sort direction
```

## 🎨 Customization

### Styling
```css
/* css/styles.css - Main stylesheet with CSS variables */
:root {
  --primary: #4FC3F7;      /* Primary color */
  --primary-dark: #0B6E79;  /* Primary dark */
  --accent: #0B6E79;        /* Accent color */
  /* Customize colors, fonts, spacing */
}
```

### Language Files
```php
// lang/ar.php - Arabic translations
// lang/en.php - English translations
return [
    'site_title' => 'Your Custom Title',
    'nav_home' => 'Home',
    // Add/modify translations
];
```

### Database Schema
```sql
-- Add custom fields to properties table
ALTER TABLE properties ADD COLUMN custom_field VARCHAR(255);

-- Add custom tables
CREATE TABLE custom_table (
    id INT AUTO_INCREMENT PRIMARY KEY,
    -- your fields
);
```

## 🔒 Security Best Practices

### Production Deployment

1. **Environment Configuration**
   ```php
   // config.php - Production settings
   define('DEBUG_MODE', false);
   define('DB_PASS', 'strong_random_password');
   define('CSRF_SECRET', 'random_32_character_string');
   ```

2. **File Permissions**
   ```bash
   # Set proper permissions
   chmod 644 *.php
   chmod 755 uploads/
   chmod 600 config.php  # Restrict config access
   ```

3. **Apache Security**
   ```apache
   # .htaccess - Add security headers
   Header always set X-Frame-Options DENY
   Header always set X-Content-Type-Options nosniff
   Header always set X-XSS-Protection "1; mode=block"
   Header always set Referrer-Policy "strict-origin-when-cross-origin"
   ```

4. **Database Security**
   ```sql
   -- Create limited database user
   CREATE USER 'alamrani_web'@'localhost' IDENTIFIED BY 'strong_password';
   GRANT SELECT, INSERT, UPDATE, DELETE ON alamrani_realestate.* TO 'alamrani_web'@'localhost';
   ```

5. **SSL Certificate**
   ```bash
   # Install Let's Encrypt SSL
   sudo certbot --apache -d yourdomain.com
   ```

### Security Features Included

- ✅ **CSRF Protection**: All forms include CSRF tokens
- ✅ **SQL Injection Prevention**: PDO prepared statements
- ✅ **XSS Prevention**: Input sanitization and output escaping
- ✅ **Password Security**: bcrypt hashing with salt
- ✅ **Session Security**: Secure session configuration
- ✅ **File Upload Security**: Type, size, and extension validation
- ✅ **Rate Limiting**: Basic API rate limiting
- ✅ **Input Validation**: Server-side validation for all inputs

## 🚀 Performance Optimization

### Frontend Optimization
```html
<!-- Implemented optimizations -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preload" href="css/styles.css" as="style">
<img loading="lazy" src="image.jpg" alt="Description">
```

### Database Optimization
```sql
-- Indexes already included for optimal performance
-- Monitor slow queries and add indexes as needed
SHOW SLOW_LOG;
```

### Caching
```php
// Optional: Implement Redis/Memcached
// Basic file caching is included in the API
```

## 🌐 Deployment Options

### Shared Hosting
1. Upload files via FTP to public_html/
2. Create MySQL database via cPanel
3. Import db.sql via phpMyAdmin
4. Update config.php with hosting details

### VPS/Dedicated Server
1. Follow manual server setup
2. Configure domain and SSL
3. Set up automated backups
4. Monitor server resources

### Cloud Deployment (AWS/DigitalOcean)
1. Launch Ubuntu 20.04+ instance
2. Install LAMP stack
3. Configure security groups/firewall
4. Set up automated backups and monitoring

## 🔧 Troubleshooting

### Common Issues

**Database Connection Error**
```bash
# Check MySQL service
sudo systemctl status mysql

# Verify credentials in config.php
# Check database exists and user has permissions
```

**File Upload Issues**
```bash
# Check PHP settings
php -i | grep upload

# Verify directory permissions
ls -la uploads/
```

**Rewrite Rules Not Working**
```bash
# Enable mod_rewrite
sudo a2enmod rewrite

# Check .htaccess file exists
# Verify AllowOverride All in virtual host
```

**Email Not Sending**
```bash
# Check PHP mail configuration
php -i | grep mail

# Test with simple mail() function
# Configure SMTP settings if needed
```

### Debug Mode
```php
// config.php - Enable for debugging
define('DEBUG_MODE', true);

// This will show detailed error messages
// Disable in production!
```

## 📊 Monitoring & Analytics

### Built-in Analytics
- Page view tracking
- Property view counts
- Inquiry statistics
- User registration metrics

### External Analytics
```html
<!-- Add Google Analytics (optional) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=GA_TRACKING_ID"></script>
```

### Performance Monitoring
```bash
# Monitor server resources
htop
df -h
mysql -e "SHOW PROCESSLIST;"
```

## 🔄 Backup & Maintenance

### Database Backup
```bash
# Daily backup script
#!/bin/bash
mysqldump -u alamrani -p alamrani_realestate > backup_$(date +%Y%m%d).sql
```

### File Backup
```bash
# Backup uploads directory
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz uploads/
```

### Maintenance Tasks
- Regular database optimization
- Log file rotation
- Security updates
- Performance monitoring
- Broken link checking

## 🆘 Support & Documentation

### Getting Help
1. Check this README for common issues
2. Review error logs in Apache/PHP
3. Test with DEBUG_MODE enabled
4. Check database connectivity and permissions

### Additional Resources
- **PHP Documentation**: https://www.php.net/docs.php
- **MySQL Documentation**: https://dev.mysql.com/doc/
- **Apache Documentation**: https://httpd.apache.org/docs/
- **Leaflet.js Documentation**: https://leafletjs.com/reference.html

## 📝 License & Credits

### License
This project is open-source and available under the MIT License.

### Credits
- **Fonts**: Google Fonts (Cairo, Poppins, Playfair Display)
- **Maps**: OpenStreetMap & Leaflet.js
- **Icons**: Custom SVG icons
- **Framework**: Pure PHP with vanilla JavaScript

### Third-Party Libraries
- **Leaflet.js**: BSD 2-Clause License
- **OpenStreetMap**: Open Database License

## 🔮 Future Enhancements

### Planned Features
- [ ] Advanced property comparison tool
- [ ] Mortgage calculator
- [ ] Virtual property tours
- [ ] Mobile app (React Native)
- [ ] Advanced analytics dashboard
- [ ] Multi-currency support
- [ ] Social media integration
- [ ] Advanced search filters
- [ ] Property valuation tools
- [ ] Agent performance metrics

### Technical Improvements
- [ ] Redis caching implementation
- [ ] Elasticsearch integration
- [ ] CDN integration
- [ ] Image optimization pipeline
- [ ] API rate limiting improvements
- [ ] Advanced security features
- [ ] Automated testing suite
- [ ] CI/CD pipeline

## 📞 Contact Information

For technical support or customization requests:

- **Website**: http://localhost/alamrani
- **Email**: admin@alamrani-realestate.com
- **Phone**: +967-1-234567

---

**Built with ❤️ for the Yemeni real estate market**

*Last updated: January 2024*