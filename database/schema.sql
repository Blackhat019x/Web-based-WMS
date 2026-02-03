-- Warehouse Management System Database Schema
-- Version: 1.0
-- Compatible with MySQL 5.7+

CREATE DATABASE IF NOT EXISTS warehouse_wms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE warehouse_wms;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'receiver', 'binner', 'inventory_controller') DEFAULT 'receiver',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shipments table
CREATE TABLE IF NOT EXISTS shipments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(100) UNIQUE NOT NULL,
    status ENUM('pending', 'ongoing_checking', 'finished_checking') DEFAULT 'pending',
    received_by INT,
    received_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    finished_date TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_invoice (invoice_number),
    INDEX idx_status (status),
    INDEX idx_received_date (received_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shipment items table
CREATE TABLE IF NOT EXISTS shipment_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shipment_id INT NOT NULL,
    part_number VARCHAR(100) NOT NULL,
    barcode VARCHAR(255) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    scanned_by INT,
    notes TEXT,
    FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE,
    FOREIGN KEY (scanned_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_shipment (shipment_id),
    INDEX idx_part_number (part_number),
    INDEX idx_barcode (barcode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Locations table (main warehouse locations)
CREATE TABLE IF NOT EXISTS locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location_code VARCHAR(50) UNIQUE NOT NULL,
    location_name VARCHAR(100) NOT NULL,
    location_type ENUM('main', 'secondary', 'overflow') DEFAULT 'main',
    capacity INT DEFAULT 0,
    current_quantity INT DEFAULT 0,
    zone VARCHAR(50),
    aisle VARCHAR(10),
    rack VARCHAR(10),
    level VARCHAR(10),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_location_code (location_code),
    INDEX idx_location_type (location_type),
    INDEX idx_zone (zone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pallets table
CREATE TABLE IF NOT EXISTS pallets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pallet_number VARCHAR(50) UNIQUE NOT NULL,
    shipment_id INT,
    location_id INT,
    status ENUM('pending', 'binned', 'released') DEFAULT 'pending',
    assigned_to INT,
    binned_by INT,
    binned_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE SET NULL,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (binned_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_pallet_number (pallet_number),
    INDEX idx_status (status),
    INDEX idx_location (location_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pallet items table
CREATE TABLE IF NOT EXISTS pallet_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pallet_id INT NOT NULL,
    shipment_item_id INT,
    part_number VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    added_by INT,
    FOREIGN KEY (pallet_id) REFERENCES pallets(id) ON DELETE CASCADE,
    FOREIGN KEY (shipment_item_id) REFERENCES shipment_items(id) ON DELETE SET NULL,
    FOREIGN KEY (added_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_pallet (pallet_id),
    INDEX idx_part_number (part_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Secondary locations (overflow/crates)
CREATE TABLE IF NOT EXISTS secondary_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    crate_number VARCHAR(50) UNIQUE NOT NULL,
    pallet_id INT,
    part_number VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    main_location_id INT,
    secondary_location_code VARCHAR(50),
    assigned_by INT,
    assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (pallet_id) REFERENCES pallets(id) ON DELETE SET NULL,
    FOREIGN KEY (main_location_id) REFERENCES locations(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_crate_number (crate_number),
    INDEX idx_pallet (pallet_id),
    INDEX idx_part_number (part_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inventory transactions table
CREATE TABLE IF NOT EXISTS inventory_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_type ENUM('receiving', 'binning', 'relocation', 'release', 'adjustment') NOT NULL,
    part_number VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    from_location VARCHAR(50),
    to_location VARCHAR(50),
    reference_type VARCHAR(50),
    reference_id INT,
    performed_by INT,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_transaction_type (transaction_type),
    INDEX idx_part_number (part_number),
    INDEX idx_transaction_date (transaction_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Binning logs table
CREATE TABLE IF NOT EXISTS binning_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pallet_id INT NOT NULL,
    action_type ENUM('created', 'assigned', 'binned', 'moved', 'overflow_assigned') NOT NULL,
    location_id INT,
    performed_by INT,
    action_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (pallet_id) REFERENCES pallets(id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_pallet (pallet_id),
    INDEX idx_action_type (action_type),
    INDEX idx_action_date (action_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    activity_type VARCHAR(50) NOT NULL,
    module VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    metadata JSON,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_activity_type (activity_type),
    INDEX idx_module (module),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, full_name, email, role, is_active) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@warehouse.local', 'admin', 1);

-- Insert sample locations
INSERT INTO locations (location_code, location_name, location_type, capacity, zone, aisle, rack, level) VALUES
('A01-R01-L01', 'Zone A - Aisle 01 - Rack 01 - Level 01', 'main', 100, 'A', '01', '01', '01'),
('A01-R01-L02', 'Zone A - Aisle 01 - Rack 01 - Level 02', 'main', 100, 'A', '01', '01', '02'),
('A01-R02-L01', 'Zone A - Aisle 01 - Rack 02 - Level 01', 'main', 100, 'A', '01', '02', '01'),
('B01-R01-L01', 'Zone B - Aisle 01 - Rack 01 - Level 01', 'main', 150, 'B', '01', '01', '01'),
('B01-R02-L01', 'Zone B - Aisle 01 - Rack 02 - Level 01', 'main', 150, 'B', '01', '02', '01'),
('OVERFLOW-01', 'Overflow Area 01', 'secondary', 500, 'OVERFLOW', 'OF', '01', '01'),
('OVERFLOW-02', 'Overflow Area 02', 'secondary', 500, 'OVERFLOW', 'OF', '02', '01');
