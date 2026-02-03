# Quick Start Guide - WMS Installation

## Prerequisites Checklist
- [ ] XAMPP installed
- [ ] Apache server started
- [ ] MySQL server started
- [ ] Project files in `htdocs/Web-based-WMS/`

## Installation Steps

### Step 1: Database Setup
1. Open browser and go to: `http://localhost/phpmyadmin`
2. Click on "Import" tab
3. Click "Choose File" and select: `database/schema.sql`
4. Click "Go" button at bottom
5. You should see success message: "Import has been successfully finished"

### Step 2: Verify Database
1. In phpMyAdmin, click on `wms_db` in left sidebar
2. You should see these tables:
   - users
   - products
   - invoices
   - shipments
   - bins
   - inventory
   - releases
   - activity_log

### Step 3: Access Application
1. Open browser
2. Go to: `http://localhost/Web-based-WMS/`
3. You should see the login page

### Step 4: Login
Use these credentials:
- **Username:** admin
- **Password:** admin123

### Step 5: Test Basic Functions
1. After login, you should see the Dashboard
2. Click "Products" in sidebar - Add a test product
3. Click "Bins" in sidebar - Verify sample bins exist
4. Click "Receiving" - Create a test invoice

## Troubleshooting

### Error: "Connection failed"
**Problem:** Can't connect to database
**Solution:** 
- Check MySQL is running in XAMPP Control Panel
- Verify credentials in `config/database.php`

### Error: "Table doesn't exist"
**Problem:** Database not imported
**Solution:**
- Re-import `database/schema.sql` via phpMyAdmin
- Make sure database name is `wms_db`

### Error: "404 Not Found"
**Problem:** Wrong URL or location
**Solution:**
- Verify files are in: `C:\xampp\htdocs\Web-based-WMS\`
- Use correct URL: `http://localhost/Web-based-WMS/`

### Login doesn't work
**Problem:** Session or password issue
**Solution:**
- Clear browser cache and cookies
- Check if user exists in database
- Password is: admin123 (case sensitive)

## Testing Barcode Scanning

### Without Physical Scanner
1. Go to Receiving > Create Invoice
2. Click "Scan Items"
3. Type barcode "1234567890" and press Enter
4. Product "PART-001" should be scanned

### With Physical Scanner
1. Configure scanner for keyboard emulation mode
2. Scan will auto-trigger on Enter key
3. Test in notepad first to verify scanner works

## Sample Data

The system comes with:
- **1 Admin User** (admin/admin123)
- **3 Sample Products** (PART-001, PART-002, PART-003)
- **6 Sample Bins** (A-01-01-01, A-01-01-02, etc.)

## Next Steps

1. ✅ Change default admin password
2. ✅ Add your real products
3. ✅ Configure your bin structure
4. ✅ Create additional users
5. ✅ Start receiving inventory

## System Requirements

- **PHP:** 7.4 or higher
- **MySQL:** 5.7 or higher
- **Browser:** Modern browser (Chrome, Firefox, Edge, Safari)
- **Space:** Minimum 50MB

## Support

For help:
1. Check README.md for detailed documentation
2. Review database/schema.sql for data structure
3. Check PHP error logs in XAMPP

---
**Ready to start?** Go to: http://localhost/Web-based-WMS/
