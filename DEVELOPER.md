# Developer Documentation - Warehouse Management System

## Architecture Overview

The WMS follows a clean MVC-inspired architecture optimized for XAMPP deployment:

```
┌─────────────────────────────────────────────┐
│              User Interface                  │
│   (Bootstrap 5 + Custom Admin Theme)        │
└──────────────────┬──────────────────────────┘
                   │
┌──────────────────▼──────────────────────────┐
│          Application Layer                   │
│  (PHP Views + Controllers in public/)       │
└──────────────────┬──────────────────────────┘
                   │
┌──────────────────▼──────────────────────────┐
│           Business Logic                     │
│   (Helper functions in config/config.php)   │
└──────────────────┬──────────────────────────┘
                   │
┌──────────────────▼──────────────────────────┐
│          Data Access Layer                   │
│        (PDO with prepared statements)        │
└──────────────────┬──────────────────────────┘
                   │
┌──────────────────▼──────────────────────────┐
│            MySQL Database                    │
│      (Normalized schema with indexes)       │
└─────────────────────────────────────────────┘
```

## File Structure

### Configuration Files
- `config/config.php` - Application settings, helper functions, session management
- `config/database.php` - Database connection settings and PDO initialization

### Views
- `app/views/layouts/main.php` - Main layout template with sidebar and navigation
- `app/views/dashboard/index.php` - Dashboard view with KPIs and charts

### Public Pages
- `public/login.php` - Authentication page
- `public/receiving.php` - Receiving module
- `public/binning.php` - Binning module  
- `public/inventory.php` - Inventory overview
- `public/releasing.php` - Releasing scaffold
- `public/reports.php` - Reports and analytics
- `public/settings.php` - System settings (admin only)

### Assets
- `public/css/style.css` - Custom styles
- `public/js/main.js` - JavaScript utilities and interactions

### Database
- `database/schema.sql` - Complete database schema
- `database/sample_data.sql` - Sample test data

## Key Design Patterns

### 1. Layout Template Pattern
```php
// In any page
ob_start();
// ... page content ...
$content = ob_get_clean();
include __DIR__ . '/../app/views/layouts/main.php';
```

### 2. PDO Prepared Statements
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();
```

### 3. Session-Based Authentication
```php
if (!isLoggedIn()) {
    redirect(BASE_URL . '/public/login.php');
}
```

### 4. Activity Logging
```php
logActivity('create', 'receiving', "Created new shipment: $invoiceNumber");
```

## Database Schema

### Core Tables

**users**
- Stores user accounts with roles
- Password hashing with PHP password_hash()
- Tracks last login

**shipments**
- Main receiving record
- Status flow: pending → ongoing_checking → finished_checking
- Links to user who received

**shipment_items**
- Individual scanned parts
- Auto-increment quantity support
- Barcode tracking

**pallets**
- Pallet records for binning
- Links to shipment and location
- Status tracking

**locations**
- Warehouse location master
- Capacity management
- Zone/Aisle/Rack/Level structure

**secondary_locations**
- Overflow inventory tracking
- Crate number management

### Relationship Diagram
```
users ─┐
       ├─> shipments ──> shipment_items
       ├─> pallets ──> pallet_items
       └─> activity_logs

locations ──> pallets ──> pallet_items
            
pallets ──> secondary_locations
```

## API Patterns (AJAX)

### Scan Part (Receiving)
```javascript
$.ajax({
    url: '',
    method: 'POST',
    data: {
        action: 'scan_part',
        shipment_id: shipmentId,
        barcode: barcode,
        part_number: partNumber
    },
    dataType: 'json',
    success: function(response) {
        if (response.success) {
            // Handle success
        }
    }
});
```

### Add to Pallet (Binning)
```javascript
$.ajax({
    url: '',
    method: 'POST',
    data: {
        action: 'add_to_pallet',
        pallet_id: palletId,
        part_number: partNumber,
        quantity: quantity
    },
    dataType: 'json'
});
```

## Security Measures

### 1. SQL Injection Prevention
- All queries use PDO prepared statements
- Never concatenate user input into SQL

### 2. XSS Prevention
```php
// Always escape output
<?= e($user['username']) ?>
// Which calls htmlspecialchars()
```

### 3. Authentication
- Password hashing with password_hash()
- Session-based auth with secure session settings
- Role-based access control

### 4. CSRF (Future Enhancement)
- Could add CSRF tokens for forms
- Currently relying on session validation

## Helper Functions

Located in `config/config.php`:

### Authentication
- `isLoggedIn()` - Check if user is authenticated
- `getCurrentUserId()` - Get current user ID
- `getCurrentUser()` - Get current user data
- `hasRole($role)` - Check user role

### Utilities
- `redirect($url)` - Redirect to URL
- `logActivity()` - Log activity to database
- `e($string)` - Escape output (XSS protection)
- `formatDate()` - Format date strings

## Extending the System

### Adding a New Module

1. **Create the page** in `public/yourmodule.php`:
```php
<?php
require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn()) {
    redirect(BASE_URL . '/public/login.php');
}

$currentPage = 'yourmodule';
$pageTitle = 'Your Module';

// Your logic here

ob_start();
?>
<!-- Your HTML here -->
<?php
$content = ob_get_clean();
include __DIR__ . '/../app/views/layouts/main.php';
?>
```

2. **Add navigation** in `app/views/layouts/main.php`:
```php
<li>
    <a href="<?= BASE_URL ?>/public/yourmodule.php" 
       class="<?= ($currentPage ?? '') === 'yourmodule' ? 'active' : '' ?>">
        <i class="fas fa-icon"></i> Your Module
    </a>
</li>
```

3. **Add database tables** in a migration SQL file

### Adding a New Model (Laravel-ready)

Create in `app/models/YourModel.php`:
```php
<?php
class YourModel {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDBConnection();
    }
    
    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM your_table WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function all() {
        return $this->pdo->query("SELECT * FROM your_table")->fetchAll();
    }
}
```

### Adding a Controller

Create in `app/controllers/YourController.php`:
```php
<?php
require_once __DIR__ . '/../models/YourModel.php';

class YourController {
    private $model;
    
    public function __construct() {
        $this->model = new YourModel();
    }
    
    public function index() {
        $items = $this->model->all();
        // Return or render view
    }
}
```

## JavaScript Utilities

Located in `public/js/main.js`:

### Toast Notifications
```javascript
showToast('Operation successful', 'success');
showToast('Error occurred', 'danger');
```

### Loading Indicator
```javascript
showLoading();
// ... ajax operation ...
hideLoading();
```

### Barcode Scanner
- Automatically detects rapid typing (scanner input)
- Fills the first visible `.scan-input` field

## Chart.js Integration

Dashboard uses Chart.js for visualizations:

```javascript
new Chart(ctx, {
    type: 'line', // or 'bar', 'doughnut', etc.
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            data: <?= json_encode($data) ?>,
            borderColor: 'rgb(102, 126, 234)',
            backgroundColor: 'rgba(102, 126, 234, 0.1)'
        }]
    }
});
```

## Testing Checklist

- [ ] Database connection works
- [ ] Login with default credentials
- [ ] Create a new shipment
- [ ] Scan parts in receiving
- [ ] Finish checking a shipment
- [ ] Create a pallet
- [ ] Add items to pallet
- [ ] Assign pallet to location
- [ ] Assign overflow to secondary location
- [ ] View inventory
- [ ] Generate reports
- [ ] Create users (admin)
- [ ] Create locations (admin)
- [ ] Test barcode scanner (if available)

## Performance Optimization

### Database
- Indexes on foreign keys
- Indexes on frequently queried columns
- Efficient JOIN queries

### Frontend
- CDN for libraries (Bootstrap, Font Awesome)
- Minified assets (in production)
- Chart caching (client-side)

### PHP
- PDO with prepared statements (faster than mysqli)
- Static connection (singleton pattern)
- Minimal queries per page

## Migration to Laravel (Future)

The codebase is designed to be Laravel-ready:

1. Move models to `app/Models/`
2. Convert helpers to Laravel helpers
3. Move views to `resources/views/`
4. Add routes in `routes/web.php`
5. Use Eloquent ORM instead of raw PDO
6. Implement middleware for auth
7. Add form requests for validation

## Troubleshooting

### Common Issues

**"Cannot modify header information"**
- Check for output before redirect()
- Use ob_start() at page top

**"Call to undefined function"**
- Ensure config.php is included
- Check function is in correct file

**"Access denied for user"**
- Check database credentials in config/database.php
- Verify MySQL user has proper permissions

**Charts not showing**
- Check console for JavaScript errors
- Verify Chart.js CDN is accessible
- Ensure JSON data is properly formatted

## Code Style Guidelines

- Use 4 spaces for indentation
- Always use `<?php` opening tags
- Escape all output with `e()` function
- Use prepared statements for all queries
- Add comments for complex logic
- Follow PSR-style naming (future Laravel compatibility)

## Contributing

When adding features:
1. Follow existing code style
2. Add activity logging where appropriate
3. Include proper error handling
4. Test with sample data
5. Update documentation

## Version History

- **v1.0.0** (2026-02-03) - Initial release
  - Dashboard module
  - Receiving module
  - Binning module
  - Inventory overview
  - Reports
  - Settings
  - Releasing scaffold
