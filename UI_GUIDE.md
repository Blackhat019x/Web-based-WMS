# WMS User Interface Guide

## 🎨 System Screenshots & Features

### 1. Login Page
**URL:** `/login.php`
- Clean, professional login interface
- Centered login box with gradient background
- Default credentials displayed for easy access
- Secure password hashing
- Session-based authentication

**Features:**
- Username and password validation
- Error messages for invalid credentials
- Automatic redirect if already logged in
- Professional blue gradient background

---

### 2. Dashboard (Main)
**URL:** `/dashboard.php`
- Professional admin dashboard with KPIs
- Real-time metrics display
- Interactive charts using Chart.js
- Quick navigation sidebar

**KPI Cards:**
1. **Total Products** (Blue) - Count of all products in system
2. **Pending Shipments** (Orange) - Invoices awaiting completion
3. **Total Inventory** (Green) - Sum of all product quantities
4. **Bin Utilization** (Red) - Average bin capacity usage

**Dashboard Sections:**
- **Recent Invoices Table**: Shows last 5 invoices with status badges
- **Inventory by Zone Chart**: Bar chart showing inventory distribution

---

### 3. Receiving Module
**URL:** `/modules/receiving.php`

**Features:**
- Create new receiving invoice form
- Invoice list with real-time status
- Progress tracking (received/total items)
- One-click access to scanning interface

**Form Fields:**
- Invoice Number
- Supplier Name
- Invoice Date

**Table Columns:**
- Invoice #, Supplier, Date, Status, Progress, Created By, Actions

---

### 4. Receiving Scanner
**URL:** `/modules/receiving_scan.php?invoice_id=X`

**Barcode Scanning Interface:**
- Large, prominent scanner input box
- Auto-focus maintained on input field
- Real-time scan feedback
- Live table updates

**Features:**
- Scan parts one-by-one with barcode scanner
- Manual barcode entry support
- Status tracking for each item
- Complete invoice button
- Back navigation

**Scanner Box:**
- Dashed border for visual focus
- Clear instructions
- Last scan display
- Keyboard and scanner compatible

---

### 5. Inventory Module
**URL:** `/modules/inventory.php`

**Two Main Sections:**

**A) Products Summary Table**
- Part Number, Description, Barcode
- Total Quantity vs. Binned Quantity
- Unbinned count highlighted
- "Bin Items" button for unbinned products

**B) Binned Inventory Table**
- Complete inventory records
- Bin locations with codes
- Pallet numbers
- Location type badges (Primary/Secondary)
- Print label buttons

**Print Functionality:**
- Click print button for any inventory item
- Generates formatted label with:
  - Part Number
  - Bin Location
  - Pallet Number
  - Quantity
  - Date/Time

---

### 6. Binning Module
**URL:** `/modules/binning.php?product_id=X`

**Product Context Display:**
- Part Number, Description, Barcode
- Total/Binned/Unbinned quantities

**Bin Scanner:**
- Large scanner input for bin locations
- Selected bin display
- Real-time validation

**Binning Form:**
- Bin selection (scan or dropdown)
- Pallet number input
- Quantity to bin (validated against unbinned)
- Location type selector (Primary/Secondary)

**Available Bins Table:**
- Complete bin list with details
- Zone, Aisle, Rack, Level structure
- Capacity and utilization metrics
- Available space calculations

---

### 7. Products Management
**URL:** `/modules/products.php`

**Add Product Form:**
- Part Number (required)
- Description (required)
- Barcode (required)
- Unit of Measure (dropdown: EA, BOX, CASE, PALLET)

**Products Table:**
- All products with full details
- Total and binned quantities
- Creation dates
- Unit of measure

---

### 8. Bin Management
**URL:** `/modules/bins.php`

**Add Bin Form:**
- Bin Code (e.g., A-01-01-01)
- Location Type (Primary/Secondary/Overflow)
- Zone, Aisle, Rack, Level
- Capacity

**Bins Table:**
- Complete bin listing
- Type badges with colors
- Utilization metrics and percentages
- Available capacity
- Item count per bin
- Active/Inactive status

---

### 9. Releasing Module (Future)
**URL:** `/modules/releasing.php`

**Current Status:**
- Placeholder for future development
- Coming soon message
- Feature list preview
- Release history table (when data exists)

**Planned Features:**
- Pick list generation
- Wave picking
- Order fulfillment
- Shipping labels
- Inventory deduction

---

## 🎨 Design System

### Color Palette
- **Primary Blue:** #2563eb (buttons, accents)
- **Secondary Blue:** #1e40af (hover states)
- **Success Green:** #10b981 (completed status)
- **Warning Orange:** #f59e0b (pending status)
- **Danger Red:** #ef4444 (cancelled, critical)
- **Dark Gray:** #1f2937 (sidebar, text)
- **Light Gray:** #f3f4f6 (backgrounds)

### Status Badges
- **Pending:** Yellow/Amber background
- **In Progress:** Blue background
- **Completed:** Green background
- **Cancelled:** Red background
- **Partial:** Blue background

### Typography
- **Font:** System fonts (Segoe UI, Roboto, Helvetica)
- **Headings:** Bold, dark gray
- **Body:** Regular, medium gray
- **Labels:** Medium weight

### Layout
- **Sidebar:** Fixed 250px width, dark background
- **Main Content:** Fluid width with 20px padding
- **Cards:** White background, rounded corners, shadow
- **Tables:** Striped rows on hover, bordered

### Responsive Design
- **Desktop:** Full sidebar + content
- **Tablet:** Narrow sidebar (200px)
- **Mobile:** Stacked layout, collapsible sidebar

---

## 🎯 User Experience Features

### Navigation
- Persistent sidebar on all pages
- Active page highlighted
- Icon + text labels
- User info in top bar
- Quick logout access

### Feedback
- Success notifications (green)
- Error notifications (red)
- Auto-dismiss after 3 seconds
- Positioned top-right

### Forms
- Clear labels
- Required field indicators
- Validation messages
- Submit button styling
- Grid layouts for organization

### Tables
- Alternating row colors on hover
- Action buttons in last column
- Status badges for quick reference
- Sortable headers (future)
- Pagination (future)

---

## 📱 Mobile Optimization

### Responsive Features
- Touch-friendly buttons (min 44px)
- Readable text (14px minimum)
- Collapsible navigation
- Stacked form fields
- Horizontal scrolling tables

### Barcode Scanning
- Works with mobile camera scanners
- Keyboard input fallback
- Large touch targets

---

## 🖨️ Printing

### Print Styles
- Hide sidebar and navigation
- Hide buttons and controls
- Optimize for paper output
- Clean, minimal formatting

### Label Printing
- Monospace font for readability
- Border for cutting guide
- Essential information only
- Auto-print dialog

---

## 🔐 Security Visual Indicators

### Login Security
- Password field masked
- No credential storage
- Session timeout (implied)
- Secure logout

### Role Indicators
- User role displayed in sidebar
- Admin badge/color (future)
- Permission-based UI (future)

---

**Note:** This is a visual guide. For technical documentation, see README.md
**Version:** 1.0.0
