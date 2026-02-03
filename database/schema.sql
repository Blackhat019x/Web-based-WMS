-- Warehouse Management System Database Schema
-- For use with MySQL/XAMPP

CREATE DATABASE IF NOT EXISTS wms_db;
USE wms_db;

-- Users table for authentication
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'manager', 'operator') DEFAULT 'operator',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Products/Parts table
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    part_number VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    barcode VARCHAR(100) UNIQUE,
    quantity INT DEFAULT 0,
    unit_of_measure VARCHAR(20) DEFAULT 'EA',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_barcode (barcode),
    INDEX idx_part_number (part_number)
);

-- Invoices table
CREATE TABLE IF NOT EXISTS invoices (
    invoice_id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(100) UNIQUE NOT NULL,
    supplier_name VARCHAR(200),
    invoice_date DATE,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    total_items INT DEFAULT 0,
    received_items INT DEFAULT 0,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    INDEX idx_invoice_number (invoice_number),
    INDEX idx_status (status)
);

-- Shipments/Receiving table
CREATE TABLE IF NOT EXISTS shipments (
    shipment_id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    product_id INT NOT NULL,
    expected_quantity INT NOT NULL,
    received_quantity INT DEFAULT 0,
    status ENUM('pending', 'partial', 'completed') DEFAULT 'pending',
    scanned_at TIMESTAMP NULL,
    received_by INT,
    notes TEXT,
    FOREIGN KEY (invoice_id) REFERENCES invoices(invoice_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (received_by) REFERENCES users(user_id),
    INDEX idx_invoice (invoice_id),
    INDEX idx_status (status)
);

-- Bins/Locations table
CREATE TABLE IF NOT EXISTS bins (
    bin_id INT AUTO_INCREMENT PRIMARY KEY,
    bin_code VARCHAR(50) UNIQUE NOT NULL,
    location_type ENUM('primary', 'secondary', 'overflow') DEFAULT 'primary',
    zone VARCHAR(50),
    aisle VARCHAR(20),
    rack VARCHAR(20),
    level VARCHAR(20),
    capacity INT DEFAULT 100,
    current_utilization INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_bin_code (bin_code),
    INDEX idx_location_type (location_type)
);

-- Inventory/Binning records
CREATE TABLE IF NOT EXISTS inventory (
    inventory_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    bin_id INT NOT NULL,
    pallet_number VARCHAR(50),
    quantity INT NOT NULL,
    location_type ENUM('primary', 'secondary') DEFAULT 'primary',
    binned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    binned_by INT,
    barcode_printed BOOLEAN DEFAULT FALSE,
    notes TEXT,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (bin_id) REFERENCES bins(bin_id),
    FOREIGN KEY (binned_by) REFERENCES users(user_id),
    INDEX idx_product (product_id),
    INDEX idx_bin (bin_id),
    INDEX idx_pallet (pallet_number)
);

-- Releasing/Picking table (for future implementation)
CREATE TABLE IF NOT EXISTS releases (
    release_id INT AUTO_INCREMENT PRIMARY KEY,
    release_number VARCHAR(100) UNIQUE NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    requested_by INT,
    picked_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (requested_by) REFERENCES users(user_id),
    FOREIGN KEY (picked_by) REFERENCES users(user_id),
    INDEX idx_status (status)
);

-- Activity log for tracking
CREATE TABLE IF NOT EXISTS activity_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action_type VARCHAR(50),
    entity_type VARCHAR(50),
    entity_id INT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    INDEX idx_user (user_id),
    INDEX idx_action (action_type)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, full_name, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin')
ON DUPLICATE KEY UPDATE username=username;

-- Insert sample bins
INSERT INTO bins (bin_code, location_type, zone, aisle, rack, level, capacity) VALUES
('A-01-01-01', 'primary', 'A', '01', '01', '01', 100),
('A-01-01-02', 'primary', 'A', '01', '01', '02', 100),
('A-01-02-01', 'primary', 'A', '01', '02', '01', 100),
('B-01-01-01', 'secondary', 'B', '01', '01', '01', 150),
('B-01-01-02', 'secondary', 'B', '01', '01', '02', 150),
('OF-01', 'overflow', 'OF', '01', '01', '01', 200)
ON DUPLICATE KEY UPDATE bin_code=bin_code;

-- Insert sample products
INSERT INTO products (part_number, description, barcode, quantity) VALUES
('PART-001', 'Widget Type A', '1234567890', 0),
('PART-002', 'Widget Type B', '1234567891', 0),
('PART-003', 'Gadget Assembly', '1234567892', 0)
ON DUPLICATE KEY UPDATE part_number=part_number;
