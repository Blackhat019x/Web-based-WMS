# Web-based Warehouse Management System (WMS)

A full-stack Warehouse Management System built with PHP, MySQL, and XAMPP featuring professional admin dashboard, barcode scanning, and comprehensive inventory management.

## 🚀 Features

### Core Functionality
- ✅ **Professional Admin Dashboard** with KPIs and interactive charts
- ✅ **Barcode Scanning** support for all major operations
- ✅ **Receiving Module** - Scan shipments by invoice and parts one-by-one
- ✅ **Inventory Management** - Track products with real-time quantities
- ✅ **Binning System** - Pallet-based binning with primary/secondary locations
- ✅ **Label Printing** - Print inventory labels with location details
- ✅ **Releasing Module** - Placeholder for future order fulfillment
- ✅ **Activity Logging** - Complete audit trail of all operations

### Technical Features
- 🔐 Secure user authentication with role-based access
- 📊 Real-time KPIs and data visualization using Chart.js
- 🎨 Clean, responsive UI with professional styling
- 🏗️ Modular, scalable architecture
- 💾 MySQL database with optimized schema
- 📱 Mobile-friendly responsive design

## 📋 Requirements

- **XAMPP** (Apache + MySQL + PHP 7.4+)
- **Web Browser** (Chrome, Firefox, Edge, Safari)
- **Barcode Scanner** (optional, keyboard input also supported)

## 🔧 Installation

### 1. Install XAMPP
Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)

### 2. Clone/Copy the Project
Copy this project to your XAMPP `htdocs` directory:
```
C:\xampp\htdocs\Web-based-WMS\
```

### 3. Start XAMPP Services
- Open XAMPP Control Panel
- Start **Apache** service
- Start **MySQL** service

### 4. Create Database
1. Open phpMyAdmin: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click "Import" tab
3. Choose file: `database/schema.sql`
4. Click "Go" to execute

**OR** run the SQL file directly:
```sql
mysql -u root -p < database/schema.sql
```

### 5. Configure Database Connection
Edit `config/database.php` if needed (default settings work with XAMPP):
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'wms_db');
```

### 6. Access the Application
Open your browser and navigate to:
```
http://localhost/Web-based-WMS/
```

## 🔑 Default Login Credentials

```
Username: admin
Password: admin123
```

**Important:** Change the default password after first login!

## 📖 User Guide

### Dashboard
- View key performance indicators (KPIs)
- Monitor pending shipments and inventory levels
- Visualize inventory distribution by zone
- Quick navigation to all modules

### Receiving Workflow
1. **Create Invoice**: Enter invoice number, supplier, and date
2. **Scan Items**: Use barcode scanner or manual entry
3. **Track Progress**: Monitor received vs. expected quantities
4. **Complete**: Mark invoice as complete when done

### Inventory & Binning Workflow
1. **View Products**: Check total and binned quantities
2. **Select Product**: Choose products with unbinned items
3. **Scan Bin**: Scan or select bin location
4. **Assign Quantity**: Specify quantity and pallet number
5. **Print Labels**: Generate location labels

### Product Management
- Add new products with part numbers and barcodes
- Track quantities across warehouse
- Monitor binned vs. unbinned inventory

### Bin Management
- Create bin locations with zone/aisle/rack/level structure
- Monitor capacity and utilization
- Support primary, secondary, and overflow locations

## 🏗️ Project Structure

```
Web-based-WMS/
├── assets/
│   ├── css/
│   │   └── style.css          # Professional UI styling
│   └── js/
│       └── main.js            # Barcode scanner & utilities
├── config/
│   └── database.php           # Database configuration
├── database/
│   └── schema.sql             # Database schema
├── includes/
│   ├── auth.php               # Authentication functions
│   └── sidebar.php            # Navigation sidebar
├── modules/
│   ├── receiving.php          # Receiving management
│   ├── receiving_scan.php     # Barcode scanning interface
│   ├── inventory.php          # Inventory overview
│   ├── binning.php            # Binning interface
│   ├── releasing.php          # Releasing (future)
│   ├── products.php           # Product management
│   └── bins.php               # Bin management
├── dashboard.php              # Main dashboard
├── login.php                  # Login page
├── logout.php                 # Logout handler
├── index.php                  # Entry point
└── README.md                  # This file
```

## 🔒 Security Features

- Password hashing using PHP's `password_hash()`
- Session-based authentication
- SQL injection prevention with prepared statements
- Input sanitization with `htmlspecialchars()`
- Role-based access control (admin, manager, operator)
- Activity logging for audit trail

## 🎯 Barcode Scanning

### Supported Barcode Scanners
- USB barcode scanners (keyboard emulation)
- Wireless barcode scanners
- Manual keyboard entry

### How It Works
1. Focus is automatically maintained on scanner input
2. Scanner acts as keyboard input device
3. Enter key triggers scan processing
4. Works with all standard barcode formats

### Manual Entry
If you don't have a scanner, simply type the barcode and press Enter.

## 📊 Database Schema

### Main Tables
- **users** - User accounts and authentication
- **products** - Parts/products catalog
- **invoices** - Receiving invoices
- **shipments** - Individual shipment items
- **bins** - Warehouse bin locations
- **inventory** - Binned inventory records
- **releases** - Outbound releases (future)
- **activity_log** - Audit trail

## 🛠️ Customization

### Adding New Users
1. Go to phpMyAdmin
2. Navigate to `wms_db` > `users` table
3. Insert new record with password: `password_hash('yourpassword', PASSWORD_DEFAULT)`

### Modifying Bin Structure
Edit the bin creation logic in `modules/bins.php` to match your warehouse layout.

### Adjusting KPIs
Modify dashboard queries in `dashboard.php` to add custom metrics.

## 🐛 Troubleshooting

### Database Connection Error
- Ensure MySQL service is running in XAMPP
- Check database credentials in `config/database.php`
- Verify database was created: `SHOW DATABASES;`

### Login Not Working
- Clear browser cache and cookies
- Check if user exists in database
- Verify password is hashed correctly

### Charts Not Displaying
- Ensure internet connection (Chart.js loaded from CDN)
- Check browser console for JavaScript errors

### Barcode Scanner Not Working
- Test scanner in notepad to verify it works
- Ensure scanner is in keyboard emulation mode
- Check scanner configuration matches barcode format

## 📈 Future Enhancements

- Complete Releasing/Picking module
- Advanced reporting and analytics
- Multi-warehouse support
- API integration for external systems
- Mobile app for scanning
- Real-time notifications
- Export to Excel/PDF

## 📝 License

This project is open source and available for educational and commercial use.

## 👨‍💻 Support

For issues, questions, or contributions, please contact the development team.

## 🎉 Credits

Built with ❤️ using:
- PHP
- MySQL
- Chart.js
- Modern CSS3
- Vanilla JavaScript

---

**Version:** 1.0.0  
**Last Updated:** 2026-02-03
