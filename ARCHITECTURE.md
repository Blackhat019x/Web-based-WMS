# WMS System Architecture

## 📐 System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                     WEB BROWSER (Client)                         │
│  - Chrome, Firefox, Safari, Edge                                 │
│  - HTML5 + CSS3 + Vanilla JavaScript                             │
│  - Chart.js for visualizations                                   │
└────────────────────────┬────────────────────────────────────────┘
                         │ HTTP/HTTPS
                         │
┌────────────────────────▼────────────────────────────────────────┐
│                    XAMPP (Apache Server)                         │
│  - Apache 2.4+                                                   │
│  - PHP 7.4+                                                      │
│  - Session Management                                            │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         │
        ┌────────────────┼────────────────┐
        │                │                │
        ▼                ▼                ▼
┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│   Frontend   │  │   Backend    │  │   Database   │
│   (UI/UX)    │  │   (Logic)    │  │   (MySQL)    │
└──────────────┘  └──────────────┘  └──────────────┘
```

---

## 🗂️ Application Layers

### 1️⃣ Presentation Layer (Frontend)
```
┌─────────────────────────────────────────┐
│         HTML Pages (Views)              │
├─────────────────────────────────────────┤
│  • login.php          - Authentication  │
│  • dashboard.php      - KPIs & Charts   │
│  • receiving.php      - Invoice List    │
│  • receiving_scan.php - Scanner UI      │
│  • inventory.php      - Stock View      │
│  • binning.php        - Location UI     │
│  • products.php       - Product Mgmt    │
│  • bins.php           - Location Mgmt   │
│  • releasing.php      - Future Module   │
└─────────────────────────────────────────┘
           │
           ▼
┌─────────────────────────────────────────┐
│       Assets (Static Resources)         │
├─────────────────────────────────────────┤
│  CSS:                                   │
│    • style.css - 7.4KB professional UI  │
│                                         │
│  JavaScript:                            │
│    • main.js - Barcode scanner          │
│    • Chart.js - CDN loaded              │
└─────────────────────────────────────────┘
```

### 2️⃣ Application Layer (Backend)
```
┌─────────────────────────────────────────┐
│        Configuration                     │
├─────────────────────────────────────────┤
│  • database.php - DB connection config  │
└─────────────────────────────────────────┘
           │
           ▼
┌─────────────────────────────────────────┐
│         Core Includes                    │
├─────────────────────────────────────────┤
│  • auth.php    - Auth & logging         │
│  • sidebar.php - Navigation component   │
└─────────────────────────────────────────┘
           │
           ▼
┌─────────────────────────────────────────┐
│      Business Logic (Modules)           │
├─────────────────────────────────────────┤
│  Receiving Module:                      │
│    • Create invoices                    │
│    • Scan barcodes                      │
│    • Track shipments                    │
│                                         │
│  Inventory Module:                      │
│    • View stock                         │
│    • Track binned items                 │
│    • Print labels                       │
│                                         │
│  Binning Module:                        │
│    • Scan locations                     │
│    • Assign pallets                     │
│    • Track capacity                     │
│                                         │
│  Product Module:                        │
│    • Add products                       │
│    • Manage barcodes                    │
│                                         │
│  Bin Module:                            │
│    • Create locations                   │
│    • Monitor utilization                │
└─────────────────────────────────────────┘
```

### 3️⃣ Data Layer (Database)
```
┌─────────────────────────────────────────┐
│         MySQL Database (wms_db)         │
├─────────────────────────────────────────┤
│                                         │
│  Core Tables:                           │
│  ┌──────────┐  ┌──────────┐           │
│  │  users   │  │ products │           │
│  └──────────┘  └──────────┘           │
│                                         │
│  Operational Tables:                    │
│  ┌──────────┐  ┌──────────┐           │
│  │ invoices │  │shipments │           │
│  └──────────┘  └──────────┘           │
│                                         │
│  Location Tables:                       │
│  ┌──────────┐  ┌──────────┐           │
│  │   bins   │  │inventory │           │
│  └──────────┘  └──────────┘           │
│                                         │
│  Future/Logging:                        │
│  ┌──────────┐  ┌──────────┐           │
│  │ releases │  │activity_ │           │
│  │          │  │   log    │           │
│  └──────────┘  └──────────┘           │
└─────────────────────────────────────────┘
```

---

## 🔄 Data Flow Diagrams

### Receiving Workflow
```
┌──────────┐      ┌──────────┐      ┌──────────┐
│  User    │      │  System  │      │ Database │
└────┬─────┘      └────┬─────┘      └────┬─────┘
     │                 │                 │
     │ 1. Create       │                 │
     │   Invoice       │                 │
     ├────────────────>│                 │
     │                 │ 2. Insert       │
     │                 │   Invoice       │
     │                 ├────────────────>│
     │                 │                 │
     │                 │ 3. Confirm      │
     │                 │<────────────────┤
     │                 │                 │
     │ 4. Navigate to  │                 │
     │   Scanner       │                 │
     ├────────────────>│                 │
     │                 │                 │
     │ 5. Scan         │                 │
     │   Barcode       │                 │
     ├────────────────>│                 │
     │                 │ 6. Lookup       │
     │                 │   Product       │
     │                 ├────────────────>│
     │                 │                 │
     │                 │ 7. Product Data │
     │                 │<────────────────┤
     │                 │                 │
     │                 │ 8. Update       │
     │                 │   Shipment      │
     │                 ├────────────────>│
     │                 │                 │
     │                 │ 9. Update       │
     │                 │   Inventory     │
     │                 ├────────────────>│
     │                 │                 │
     │ 10. Show        │                 │
     │    Success      │                 │
     │<────────────────┤                 │
     │                 │                 │
```

### Binning Workflow
```
┌──────────┐      ┌──────────┐      ┌──────────┐
│  User    │      │  System  │      │ Database │
└────┬─────┘      └────┬─────┘      └────┬─────┘
     │                 │                 │
     │ 1. Select       │                 │
     │   Product       │                 │
     ├────────────────>│                 │
     │                 │ 2. Get Product  │
     │                 │   & Bins        │
     │                 ├────────────────>│
     │                 │                 │
     │                 │ 3. Display Data │
     │                 │<────────────────┤
     │                 │                 │
     │ 4. Scan Bin     │                 │
     │   Barcode       │                 │
     ├────────────────>│                 │
     │                 │ 5. Validate Bin │
     │                 ├────────────────>│
     │                 │                 │
     │                 │ 6. Bin Details  │
     │                 │<────────────────┤
     │                 │                 │
     │ 7. Enter Qty    │                 │
     │   & Pallet      │                 │
     ├────────────────>│                 │
     │                 │ 8. Insert       │
     │                 │   Inventory     │
     │                 ├────────────────>│
     │                 │                 │
     │                 │ 9. Update Bin   │
     │                 │   Utilization   │
     │                 ├────────────────>│
     │                 │                 │
     │ 10. Confirm     │                 │
     │<────────────────┤                 │
     │                 │                 │
```

---

## 🎯 Component Interaction

### Authentication Flow
```
login.php
   │
   ├─→ Validate credentials
   │   ├─→ config/database.php
   │   └─→ Check users table
   │
   ├─→ Create session
   │   └─→ includes/auth.php
   │
   └─→ Redirect to dashboard
       └─→ dashboard.php
```

### Dashboard Flow
```
dashboard.php
   │
   ├─→ Check authentication
   │   └─→ includes/auth.php
   │
   ├─→ Load sidebar
   │   └─→ includes/sidebar.php
   │
   ├─→ Fetch KPIs
   │   ├─→ Total Products (SELECT COUNT)
   │   ├─→ Pending Shipments (SELECT COUNT)
   │   ├─→ Total Inventory (SELECT SUM)
   │   └─→ Bin Utilization (SELECT AVG)
   │
   ├─→ Get Recent Invoices
   │   └─→ SELECT with JOIN
   │
   ├─→ Get Inventory by Zone
   │   └─→ SELECT with GROUP BY
   │
   └─→ Render HTML + Chart.js
```

### Scanner Flow
```
receiving_scan.php
   │
   ├─→ Load barcode scanner JS
   │   └─→ assets/js/main.js
   │       └─→ BarcodeScanner class
   │
   ├─→ Listen for input
   │   ├─→ Keyboard input
   │   └─→ Scanner (keyboard emulation)
   │
   ├─→ On Enter key
   │   ├─→ Send AJAX request
   │   ├─→ POST barcode data
   │   └─→ receiving_scan.php (action=scan_barcode)
   │
   ├─→ Backend processing
   │   ├─→ Lookup product by barcode
   │   ├─→ Update/Create shipment
   │   ├─→ Update inventory quantity
   │   └─→ Return JSON response
   │
   └─→ Update UI
       ├─→ Show notification
       ├─→ Refresh table
       └─→ Clear input
```

---

## 🔐 Security Architecture

```
┌─────────────────────────────────────────┐
│         Security Layers                  │
├─────────────────────────────────────────┤
│                                         │
│  Layer 1: Authentication                │
│    • Password hashing (bcrypt)          │
│    • Session management                 │
│    • Auto-logout on inactivity          │
│                                         │
│  Layer 2: Authorization                 │
│    • Role-based access control          │
│    • checkAuth() on every page          │
│    • Permission validation              │
│                                         │
│  Layer 3: Input Validation              │
│    • Prepared statements (SQL)          │
│    • htmlspecialchars() (XSS)           │
│    • Type casting (PHP)                 │
│                                         │
│  Layer 4: Audit Trail                   │
│    • Activity logging                   │
│    • User action tracking               │
│    • Timestamp all operations           │
└─────────────────────────────────────────┘
```

---

## 📊 Database Relationships

```
         ┌──────────┐
         │  users   │
         └────┬─────┘
              │ created_by
              │
    ┌─────────┼─────────┐
    │         │         │
    ▼         ▼         ▼
┌─────────┐ ┌─────────┐ ┌─────────┐
│invoices │ │activity │ │inventory│
└────┬────┘ │   log   │ └────┬────┘
     │      └─────────┘      │
     │                       │
     │ invoice_id            │ product_id
     │                       │
     ▼                       ▼
┌──────────┐           ┌──────────┐
│shipments │           │ products │
└──────────┘           └──────────┘
                            │
                            │ product_id
                            │
                            ▼
                       ┌──────────┐
                       │inventory │
                       └────┬─────┘
                            │ bin_id
                            │
                            ▼
                       ┌──────────┐
                       │   bins   │
                       └──────────┘
```

---

## 🚀 Deployment Architecture

```
┌─────────────────────────────────────────┐
│       Production Environment             │
├─────────────────────────────────────────┤
│                                         │
│  Web Server: Apache (XAMPP)             │
│  ├─→ htdocs/Web-based-WMS/             │
│  ├─→ PHP 7.4+ enabled                  │
│  └─→ mod_rewrite (optional)            │
│                                         │
│  Database: MySQL                        │
│  ├─→ Port 3306                         │
│  ├─→ Database: wms_db                  │
│  └─→ User: root (change in prod)       │
│                                         │
│  Browser Access:                        │
│  └─→ http://localhost/Web-based-WMS/   │
│                                         │
└─────────────────────────────────────────┘
```

---

## 📈 Performance Considerations

### Optimization Strategies
```
1. Database Level:
   ✓ Indexes on frequently queried columns
   ✓ Efficient JOINs instead of multiple queries
   ✓ Connection pooling (mysqli)

2. Application Level:
   ✓ Prepared statements (query caching)
   ✓ Session management
   ✓ Minimal page reloads (AJAX)

3. Frontend Level:
   ✓ CSS minification (optional)
   ✓ CDN for libraries (Chart.js)
   ✓ Responsive images
```

---

## 🔄 Future Architecture (Scalability)

```
Current (Single Server):
┌──────────────┐
│   XAMPP      │
│  Apache +    │
│   MySQL      │
└──────────────┘

Future (Distributed):
┌──────────────┐
│ Load Balancer│
└──────┬───────┘
       │
   ┌───┴───┐
   │       │
   ▼       ▼
┌─────┐ ┌─────┐
│Web 1│ │Web 2│
└──┬──┘ └──┬──┘
   │       │
   └───┬───┘
       ▼
┌──────────────┐
│   Database   │
│   Cluster    │
└──────────────┘
```

---

## 📝 Technology Stack Details

```
Frontend:
├── HTML5 (semantic markup)
├── CSS3 (flexbox, grid)
├── JavaScript ES6+
└── Chart.js 3.9.1

Backend:
├── PHP 7.4+
│   ├── MySQLi extension
│   ├── Session handling
│   └── JSON encoding
└── Apache 2.4+

Database:
└── MySQL 5.7+
    ├── InnoDB engine
    ├── Foreign keys
    └── Indexes

Development:
└── XAMPP
    ├── Apache
    ├── MySQL
    ├── PHP
    └── phpMyAdmin
```

---

**Architecture Version**: 1.0.0  
**Last Updated**: 2026-02-03  
**Status**: Production Ready ✅
