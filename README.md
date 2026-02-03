# Warehouse Management System (WMS)

A complete full-stack Warehouse Management System designed for local deployment using XAMPP (Apache, PHP, MySQL). The system supports barcode-driven warehouse operations from Receiving → Inventory/Binning → (future) Releasing, with a professional admin-style UI and dashboard.

## Features

### Dashboard Module
- Real-time warehouse status with KPI cards:
  - Total shipments today
  - Pending receivings
  - Ongoing checking
  - Finished receivings
  - Pallets pending binning
  - Inventory in secondary locations
- Interactive charts:
  - Daily receiving volume
  - Inventory status breakdown
- Recent activity log with latest scans and binning actions
- Quick action buttons for common tasks

### Receiving Module
- Create or scan shipments by Invoice Number
- Barcode scanning for parts (one-by-one)
- Auto-increment quantity for duplicate scans
- Duplicate and invalid scan prevention
- Shipment status flow: Pending → Ongoing Checking → Finished Checking
- Complete tracking: Invoice number, Part number, Quantity, Date/time, User
- Only finished shipments can proceed to Binning

### Inventory / Binning Module
**Primary Binning:**
- Scan or enter Pallet Number
- Assign parts from finished shipments to pallets
- Validate pallet quantities vs receiving records
- Assign Main Location
- Issue pallet to binner
- Track inventory by pallet and location

**Secondary Location Binning (Overflow):**
- Handle main location capacity overflow
- Assign overflow inventory to Secondary Locations
- Track inventory by Crate Number
- Maintain visibility of main vs secondary location quantities
- Full traceability per pallet, crate, and location

### Releasing Module (Future Scope)
- Scaffolded structure ready for implementation
- Planned features:
  - Pick list generation
  - Barcode validation before release
  - FIFO/FEFO-ready logic
  - Release transaction logs

### Additional Features
- User authentication with role-based access control
- User roles: Admin, Receiver, Binner, Inventory Controller
- Comprehensive reports and analytics
- Activity logging for audit trails
- Location management
- User management (Admin only)
- Responsive design for warehouse terminals
- Keyboard-first navigation for fast operations

## Technology Stack

- **Backend:** PHP 7.4+ (MVC-style structure, framework-ready)
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5.3
- **UI Framework:** Bootstrap with custom admin-style design
- **Charts:** Chart.js
- **Icons:** Font Awesome 6
- **Server:** Apache (via XAMPP)

## Requirements

- XAMPP (or similar LAMP/WAMP stack)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache 2.4 or higher
- Modern web browser (Chrome, Firefox, Edge, Safari)
- Barcode scanner (USB HID) - optional but recommended

## Installation

### 1. Install XAMPP
Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)

### 2. Clone or Extract Files
Clone or extract this repository to your XAMPP htdocs directory:
```bash
cd /path/to/xampp/htdocs
git clone <repository-url> warehouse-wms
```
Or extract the ZIP file to `C:\xampp\htdocs\warehouse-wms` (Windows) or `/Applications/XAMPP/htdocs/warehouse-wms` (Mac)

### 3. Database Setup

1. Start XAMPP Control Panel and start Apache and MySQL services

2. Open phpMyAdmin: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)

3. Import the database schema:
   - Click "Import" tab
   - Choose file: `database/schema.sql`
   - Click "Go"

   **OR** run the SQL file manually:
   ```bash
   mysql -u root -p < database/schema.sql
   ```

### 4. Configure Database Connection

Edit `config/database.php` if your MySQL credentials are different from defaults:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'warehouse_wms');
define('DB_USER', 'root');
define('DB_PASS', ''); // Change if you set a MySQL password
```

### 5. Configure Base URL

Edit `config/config.php` and update the BASE_URL if needed:

```php
define('BASE_URL', 'http://localhost/warehouse-wms');
```

### 6. Set Permissions (Linux/Mac only)

```bash
chmod -R 755 /path/to/warehouse-wms
chmod -R 777 /path/to/warehouse-wms/tmp
```

### 7. Access the Application

Open your web browser and navigate to:
```
http://localhost/warehouse-wms
```

## Default Login Credentials

- **Username:** admin
- **Password:** admin123

**Important:** Change the default password immediately after first login!

## Project Structure

```
warehouse-wms/
├── app/
│   ├── controllers/        # Controller classes (future use)
│   ├── models/            # Model classes (future use)
│   └── views/
│       ├── dashboard/     # Dashboard views
│       ├── receiving/     # Receiving views
│       ├── binning/       # Binning views
│       ├── releasing/     # Releasing views (scaffold)
│       ├── auth/          # Authentication views
│       └── layouts/       # Layout templates
├── config/
│   ├── config.php         # Application configuration
│   └── database.php       # Database configuration
├── public/
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript files
│   ├── images/           # Images and assets
│   ├── login.php         # Login page
│   ├── receiving.php     # Receiving module
│   ├── binning.php       # Binning module
│   ├── inventory.php     # Inventory overview
│   ├── releasing.php     # Releasing module (scaffold)
│   ├── reports.php       # Reports and analytics
│   └── settings.php      # Settings (Admin only)
├── database/
│   └── schema.sql        # Database schema
├── index.php             # Application entry point
├── .htaccess            # Apache configuration
└── README.md            # This file
```

## Database Schema

The system includes the following normalized tables:

- **users** - User accounts and authentication
- **shipments** - Incoming shipment records
- **shipment_items** - Scanned parts in shipments
- **pallets** - Pallet records
- **pallet_items** - Items assigned to pallets
- **locations** - Warehouse location master data
- **secondary_locations** - Overflow/crate locations
- **inventory_transactions** - Inventory movement logs
- **binning_logs** - Binning activity logs
- **activity_logs** - System-wide activity audit trail

## Usage Guide

### Receiving Workflow

1. Navigate to **Receiving** module
2. Click "Create New Shipment"
3. Scan or enter Invoice Number
4. Start scanning parts with barcode scanner
5. System auto-increments quantity for duplicate parts
6. Click "Finish Checking" when complete
7. Shipment is now ready for binning

### Binning Workflow

1. Navigate to **Binning** module
2. Click "Create New Pallet"
3. Scan or enter Pallet Number
4. Select a finished shipment
5. Add items to the pallet with quantities
6. Assign pallet to a Main Location
7. Click "Assign & Bin" to complete
8. For overflow: Use "Assign Overflow" form for secondary locations

### Inventory Management

1. Navigate to **Inventory** module
2. View all locations and their utilization
3. See inventory breakdown by location
4. Monitor main and secondary location quantities
5. Track overflow items

### Reports

1. Navigate to **Reports** module
2. Select date range for analysis
3. View receiving and binning statistics
4. Analyze top parts by quantity
5. Review user activity summaries

### Settings (Admin Only)

1. Navigate to **Settings** module
2. Create new users with appropriate roles
3. Add new warehouse locations
4. Manage existing users and locations

## Barcode Scanner Configuration

The system works with standard USB HID barcode scanners. Configure your scanner for:

- **Mode:** Keyboard emulation (HID)
- **Suffix:** Enter key (optional, for auto-submit)
- **Prefix:** None
- **Encoding:** Standard ASCII

## User Roles

- **Admin:** Full system access, user management, settings
- **Receiver:** Access to receiving module, view dashboard
- **Binner:** Access to binning module, view inventory
- **Inventory Controller:** Access to all modules except settings

## Security Features

- Password hashing using PHP password_hash()
- SQL injection protection via prepared statements
- XSS protection via output escaping
- Session-based authentication
- Role-based access control
- Activity logging for audit trails

## Browser Support

- Chrome 90+ (Recommended)
- Firefox 88+
- Safari 14+
- Edge 90+

## Performance

- Optimized for fast barcode scanning
- Minimal mouse usage required
- Large, easy-to-target input fields
- Keyboard-first navigation
- AJAX for instant feedback

## Troubleshooting

### Cannot connect to database
- Verify MySQL is running in XAMPP
- Check database credentials in `config/database.php`
- Ensure database `warehouse_wms` exists

### Page not found (404)
- Check Apache is running
- Verify BASE_URL in `config/config.php`
- Ensure .htaccess file exists

### Login fails
- Use default credentials: admin / admin123
- Clear browser cookies
- Check users table in database

### Barcode scanner not working
- Test scanner in text editor (should type characters)
- Ensure scanner is in HID keyboard mode
- Check USB connection

## Development

This system is built with Laravel-ready architecture:

- Clean MVC structure
- Separated concerns
- Reusable components
- Easily extendable

To extend functionality:
1. Add new models in `app/models/`
2. Add new controllers in `app/controllers/`
3. Add new views in `app/views/`
4. Update routing in respective public PHP files

## Future Enhancements

- Complete Releasing module implementation
- Advanced reporting with PDF export
- Email notifications
- Multi-warehouse support
- Mobile app companion
- REST API for integrations
- Advanced analytics dashboard
- Barcode label printing
- Import/export functionality

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review the database schema
3. Check Apache/PHP error logs
4. Verify XAMPP configuration

## License

This project is open source and available for use and modification.

## Credits

Built with:
- Bootstrap 5.3
- Font Awesome 6
- Chart.js
- jQuery
- PHP & MySQL

---

**Version:** 1.0.0  
**Last Updated:** 2026-02-03  
**Developed for:** Local XAMPP deployment
