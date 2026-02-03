# Visual Guide - Warehouse Management System

## UI Screenshots Guide

This document describes the visual appearance of each module in the WMS.

## 1. Login Screen
![Login Screen](docs/login-preview.png)

**Features:**
- Modern gradient background (purple to blue)
- Clean white card with rounded corners
- Large input fields with icons
- Professional branding
- Default credentials shown

**Colors:**
- Primary Gradient: #667eea → #764ba2
- Card: White with shadow
- Icons: Font Awesome

## 2. Dashboard
![Dashboard](docs/dashboard-preview.png)

**Layout:**
- Sidebar navigation (left, purple gradient)
- Top navbar with user info
- 6 KPI cards in 2 rows
- 2 interactive charts (line and doughnut)
- Recent activity table

**KPI Cards:**
1. Total Shipments Today (blue)
2. Pending Receivings (yellow)
3. Ongoing Checking (cyan)
4. Finished Receivings (green)
5. Pallets Pending Binning (red)
6. Items in Secondary Locations (purple)

**Charts:**
- Daily Receiving Volume (7-day line chart)
- Inventory Status Breakdown (doughnut chart)

## 3. Receiving Module
![Receiving](docs/receiving-preview.png)

**Create Shipment View:**
- Large input field for invoice number
- Barcode scanner ready
- Instructions card
- Shipments list table

**Scanning View:**
- Blue header with shipment info
- Extra-large barcode input field
- Part number field
- Scanned items table
- Finish Checking button

**Features:**
- Real-time scan feedback
- Quantity auto-increment
- Duplicate prevention
- Status badges (pending, ongoing, finished)

## 4. Binning Module
![Binning](docs/binning-preview.png)

**Create Pallet View:**
- Pallet number input
- Shipment selector dropdown
- Instructions
- Pallets list

**Pallet Management View:**
- Add items form
- Assign main location dropdown
- Overflow assignment form
- Pallet items table
- Secondary locations table

**Color Coding:**
- Pending: Yellow badge
- Binned: Green badge
- Overflow: Orange badge

## 5. Inventory Overview
![Inventory](docs/inventory-preview.png)

**Displays:**
- 4 KPI cards (parts and quantities)
- Locations status table with progress bars
- Main location inventory table
- Secondary location inventory table

**Features:**
- Capacity utilization (color-coded)
- Scrollable tables
- Real-time quantities
- Location hierarchy

## 6. Releasing Module (Scaffold)
![Releasing](docs/releasing-preview.png)

**Shows:**
- "Future Module" information banner
- Planned features list
- Workflow preview
- Database structure confirmation

**Indicates:**
- Module structure ready
- Implementation planned
- Database support complete

## 7. Reports & Analytics
![Reports](docs/reports-preview.png)

**Components:**
- Date range filter
- Receiving statistics table
- Binning statistics table
- Top 10 parts chart
- User activity summary

**Data Visualization:**
- Scrollable data tables
- Badge indicators
- Date-based filtering
- Summary metrics

## 8. Settings (Admin Only)
![Settings](docs/settings-preview.png)

**Sections:**
- Create new user form
- Create new location form
- User management table
- Location management table

**Features:**
- Role selector
- Status indicators
- Last login tracking
- Location capacity management

## UI Components Library

### Buttons
```
Primary (Gradient): Purple to blue, white text
Success: Green
Warning: Yellow
Danger: Red
Secondary: Gray
```

### Input Fields
```
Regular: White background, gray border
Scan Input: Extra large (1.5rem), blue border, focused state
Focus State: Blue glow shadow
```

### Badges
```
Status Pending: Yellow with black text
Status Ongoing: Cyan with white text
Status Finished: Green with white text
Role Badges: Secondary gray
```

### Cards
```
White background
Rounded corners (10px)
Shadow: 0 2px 10px rgba(0,0,0,0.08)
Hover: Lift effect (-5px translateY)
```

### Sidebar
```
Fixed left position
Gradient background (vertical)
White text
Active state: White background overlay
Icon spacing: 25px width
```

### Tables
```
Hover effect: Light blue background
Striped: Optional
Responsive: Horizontal scroll on mobile
Sticky headers: In scrollable containers
```

## Color Palette

### Primary Colors
- Primary: #667eea (Purple-blue)
- Secondary: #764ba2 (Purple)
- Success: #28a745 (Green)
- Warning: #ffc107 (Yellow)
- Danger: #dc3545 (Red)
- Info: #17a2b8 (Cyan)

### Status Colors
- Pending: #ffc107 (Yellow)
- Ongoing: #17a2b8 (Cyan)
- Finished: #28a745 (Green)
- Binned: #28a745 (Green)
- Not Binned: #ffc107 (Yellow)

### Background Colors
- Page Background: #f4f6f9 (Light gray)
- Card Background: #ffffff (White)
- Sidebar: Gradient #667eea → #764ba2

## Typography

### Fonts
- Primary: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif
- Monospace (codes): 'Courier New', monospace

### Sizes
- H1: 2.5rem
- H2: 2rem
- H3: 1.75rem
- H4: 1.5rem
- H5: 1.25rem
- Body: 1rem
- Small: 0.875rem
- Scan Input: 1.5rem

### Weights
- Bold: 700
- Semibold: 600
- Regular: 400
- Light: 300

## Responsive Breakpoints

```
Desktop:  > 1200px (Full sidebar, multi-column)
Laptop:   992px - 1199px (Full sidebar, responsive grid)
Tablet:   768px - 991px (Collapsible sidebar)
Mobile:   < 768px (Hidden sidebar, mobile menu)
```

## Icons (Font Awesome)

### Navigation
- Dashboard: fa-tachometer-alt
- Receiving: fa-truck-loading
- Binning: fa-box
- Inventory: fa-warehouse
- Releasing: fa-shipping-fast
- Reports: fa-chart-bar
- Settings: fa-cog

### Actions
- Login: fa-sign-in-alt
- Logout: fa-sign-out-alt
- Scan: fa-barcode
- Add: fa-plus
- Edit: fa-edit
- Delete: fa-trash
- View: fa-eye
- Print: fa-print

### Status
- Success: fa-check-circle
- Error: fa-times-circle
- Warning: fa-exclamation-triangle
- Info: fa-info-circle

## Animation Effects

### Hover Effects
```
Buttons: translateY(-2px) + shadow
Cards: translateY(-5px) + shadow
Links: color change + left border
```

### Transitions
```
All: 0.3s ease
Color: 0.3s
Transform: 0.3s
Shadow: 0.3s
```

### Loading States
```
Spinner: Rotating circle
Overlay: Semi-transparent black
Fade In: 0.3s opacity
```

### Success/Error Feedback
```
Success: Green pulse animation
Error: Red shake animation
Toast: Slide in from top-right
```

## Accessibility

### Features
- High contrast text
- Large clickable areas
- Keyboard navigation
- Focus indicators
- Screen reader friendly
- ARIA labels (future)

### Contrast Ratios
- Text on white: > 4.5:1
- Text on colored bg: > 4.5:1
- Icons: > 3:1

## Print Styles

When printing:
- Hide sidebar
- Hide buttons
- Hide navigation
- Show content only
- Black and white friendly
- Page breaks optimized

---

**Note**: Actual screenshots can be generated after deployment to XAMPP. This guide describes the visual design and layout of all screens.

To generate screenshots:
1. Deploy to XAMPP
2. Login to the system
3. Navigate to each module
4. Take screenshots
5. Save to `docs/` folder
