# Deployment Checklist - Warehouse Management System

## Pre-Deployment Checklist

### System Requirements ✓
- [ ] XAMPP installed (or equivalent LAMP/WAMP)
- [ ] PHP 7.4+ available
- [ ] MySQL 5.7+ running
- [ ] Apache 2.4+ configured
- [ ] Web browser (Chrome/Firefox recommended)

### File Setup ✓
- [ ] Files extracted to `htdocs/warehouse-wms/`
- [ ] All files present (check with `install_check.php`)
- [ ] .htaccess file present
- [ ] Permissions set (Linux/Mac: `chmod -R 755`)

### Database Setup ✓
- [ ] MySQL service started in XAMPP
- [ ] phpMyAdmin accessible
- [ ] Database imported from `database/schema.sql`
- [ ] Sample data imported (optional) from `database/sample_data.sql`
- [ ] Database credentials configured in `config/database.php`

### Configuration ✓
- [ ] BASE_URL set in `config/config.php`
- [ ] Database credentials verified
- [ ] Error reporting configured (off for production)
- [ ] Timezone set correctly

## Installation Steps

### Step 1: Install XAMPP
```
1. Download XAMPP from apachefriends.org
2. Install to default location
3. Start Apache and MySQL services
```

### Step 2: Extract Files
```
1. Extract zip to: C:\xampp\htdocs\warehouse-wms
   or: /Applications/XAMPP/htdocs/warehouse-wms
2. Verify all files present
```

### Step 3: Create Database
```
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click "Import" tab
3. Choose file: warehouse-wms/database/schema.sql
4. Click "Go"
5. Verify 10 tables created
```

### Step 4: Configure Application
```
Edit config/database.php if needed:
- DB_HOST: localhost (default)
- DB_NAME: warehouse_wms
- DB_USER: root (default)
- DB_PASS: '' (empty by default)
```

### Step 5: Verify Installation
```
1. Run: http://localhost/warehouse-wms/install_check.php
2. Check all items are green/OK
3. Fix any issues reported
```

### Step 6: First Login
```
1. Access: http://localhost/warehouse-wms
2. Login with:
   Username: admin
   Password: admin123
3. Change admin password immediately!
```

## Post-Deployment Checklist

### Security ✓
- [ ] Default admin password changed
- [ ] Additional user accounts created
- [ ] Test user roles and permissions
- [ ] .htaccess working (test protected directories)
- [ ] Error display disabled in production

### Functionality Tests ✓
- [ ] Login/logout works
- [ ] Dashboard displays correctly
- [ ] Receiving module functional
- [ ] Binning module functional
- [ ] Inventory displays correctly
- [ ] Reports generate properly
- [ ] Settings accessible (admin only)

### Data Validation ✓
- [ ] Sample data imported (optional)
- [ ] Test creating a shipment
- [ ] Test scanning parts
- [ ] Test creating a pallet
- [ ] Test binning a pallet
- [ ] Test overflow assignment
- [ ] Test report generation

### UI/UX Verification ✓
- [ ] Sidebar navigation works
- [ ] All pages load correctly
- [ ] Charts display on dashboard
- [ ] Tables show data properly
- [ ] Forms submit correctly
- [ ] Error messages display
- [ ] Success messages display

### Performance ✓
- [ ] Page load time < 2 seconds
- [ ] Database queries optimized
- [ ] Charts load smoothly
- [ ] No JavaScript errors in console
- [ ] No PHP errors in logs

## Configuration Options

### Production Settings

Edit `config/config.php`:
```php
// Disable error display
error_reporting(0);
ini_set('display_errors', 0);

// Set production BASE_URL
define('BASE_URL', 'http://your-domain.com/warehouse-wms');
```

### Database Settings

Edit `config/database.php`:
```php
// Production database
define('DB_HOST', 'localhost');
define('DB_NAME', 'warehouse_wms');
define('DB_USER', 'wms_user');
define('DB_PASS', 'your_secure_password');
```

### Apache Settings

Verify `.htaccess`:
```apache
# Should include:
- URL rewriting enabled
- Directory listing disabled
- Security headers set
- File protection configured
```

## Barcode Scanner Setup

### Scanner Configuration
1. Set mode to: USB HID Keyboard Emulation
2. Set suffix to: Enter key (optional)
3. Set prefix to: None
4. Test in text editor (should type characters)

### Testing Scanner
1. Navigate to Receiving module
2. Create a new shipment
3. Focus on barcode input field
4. Scan a test barcode
5. Verify it appears in field
6. Verify auto-submit works (if Enter suffix set)

## User Setup

### Create Users

In Settings (Admin):
1. Click "Create New User"
2. Fill in details:
   - Username (unique)
   - Password (strong)
   - Full name
   - Email
   - Role
3. Test login with new user

### User Training

Train users on:
1. Login procedure
2. Module navigation
3. Receiving workflow
4. Binning workflow
5. Scanning procedures
6. Error handling

## Backup Setup

### Database Backup
```sql
-- Daily backup command
mysqldump -u root -p warehouse_wms > backup_YYYY-MM-DD.sql
```

### File Backup
```bash
# Backup entire application
tar -czf wms_backup_YYYY-MM-DD.tar.gz /path/to/warehouse-wms/
```

### Backup Schedule
- Daily: Database backup
- Weekly: Full system backup
- Monthly: Archive backup

## Monitoring

### Things to Monitor
- [ ] Database size growth
- [ ] Error logs (Apache, PHP, MySQL)
- [ ] User activity logs
- [ ] System performance
- [ ] Disk space usage
- [ ] Backup success

### Log Files
- Apache error log: `xampp/apache/logs/error.log`
- PHP error log: Check `error_log` in config
- MySQL error log: `xampp/mysql/data/*.err`

## Troubleshooting

### Common Issues

**Cannot access application**
- Check Apache is running
- Verify URL is correct
- Check .htaccess exists
- Try: http://localhost/warehouse-wms/install_check.php

**Database connection failed**
- Verify MySQL is running
- Check credentials in config/database.php
- Test phpMyAdmin access
- Check database exists

**Login fails**
- Verify users table has data
- Try default: admin/admin123
- Clear browser cookies
- Check session settings

**Charts not showing**
- Check browser console for errors
- Verify Chart.js CDN accessible
- Check data is present in database
- Test in different browser

**Scanner not working**
- Test scanner in text editor
- Verify HID keyboard mode
- Check USB connection
- Try different USB port

## Maintenance

### Daily Tasks
- [ ] Check error logs
- [ ] Verify backups completed
- [ ] Monitor system performance

### Weekly Tasks
- [ ] Review user activity logs
- [ ] Check database size
- [ ] Test backup restoration
- [ ] Update sample data if needed

### Monthly Tasks
- [ ] Archive old logs
- [ ] Review security settings
- [ ] Check for PHP/MySQL updates
- [ ] Performance optimization review

## Rollback Plan

If issues occur:

1. **Stop Apache service**
2. **Restore database from backup:**
   ```sql
   mysql -u root -p warehouse_wms < backup_YYYY-MM-DD.sql
   ```
3. **Restore files from backup:**
   ```bash
   tar -xzf wms_backup_YYYY-MM-DD.tar.gz
   ```
4. **Restart Apache service**
5. **Test functionality**

## Support Contacts

### Technical Support
- Review: README.md
- Check: DEVELOPER.md
- Run: install_check.php
- Check logs in: xampp/logs/

### Emergency Procedures
1. Stop affected service
2. Check error logs
3. Restore from backup if needed
4. Contact system administrator

## Sign-Off

Deployment completed by: _________________
Date: _________________
Signature: _________________

Verified by: _________________
Date: _________________
Signature: _________________

---

**Deployment Status**: ⬜ Not Started | ⬜ In Progress | ⬜ Complete

**Version**: 1.0.0  
**Deployment Date**: __________  
**Environment**: ⬜ Development | ⬜ Testing | ⬜ Production
