-- Sample Data for Warehouse Management System
-- This file contains sample data for testing purposes

USE warehouse_wms;

-- Insert additional sample users (password for all: password123)
INSERT INTO users (username, password, full_name, email, role, is_active) VALUES
('receiver1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Receiver', 'receiver@warehouse.local', 'receiver', 1),
('binner1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Binner', 'binner@warehouse.local', 'binner', 1),
('controller1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bob Controller', 'controller@warehouse.local', 'inventory_controller', 1);

-- Insert sample shipments
INSERT INTO shipments (invoice_number, status, received_by, received_date, finished_date) VALUES
('INV-2026-001', 'finished_checking', 1, NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY),
('INV-2026-002', 'finished_checking', 2, NOW() - INTERVAL 1 DAY, NOW() - INTERVAL 1 DAY),
('INV-2026-003', 'ongoing_checking', 2, NOW(), NULL),
('INV-2026-004', 'pending', 1, NOW(), NULL);

-- Insert sample shipment items
INSERT INTO shipment_items (shipment_id, part_number, barcode, quantity, scanned_by) VALUES
-- Shipment 1
(1, 'PART-001', 'BC-PART-001', 10, 1),
(1, 'PART-002', 'BC-PART-002', 20, 1),
(1, 'PART-003', 'BC-PART-003', 15, 1),
-- Shipment 2
(2, 'PART-004', 'BC-PART-004', 25, 2),
(2, 'PART-005', 'BC-PART-005', 30, 2),
(2, 'PART-001', 'BC-PART-001', 5, 2),
-- Shipment 3
(3, 'PART-006', 'BC-PART-006', 12, 2);

-- Insert sample pallets
INSERT INTO pallets (pallet_number, shipment_id, location_id, status, assigned_to, binned_by, binned_date) VALUES
('PLT-001', 1, 1, 'binned', 3, 3, NOW() - INTERVAL 2 DAY),
('PLT-002', 2, 2, 'binned', 3, 3, NOW() - INTERVAL 1 DAY);

-- Insert sample pallet items
INSERT INTO pallet_items (pallet_id, part_number, quantity, added_by) VALUES
-- Pallet 1
(1, 'PART-001', 10, 3),
(1, 'PART-002', 20, 3),
(1, 'PART-003', 15, 3),
-- Pallet 2
(2, 'PART-004', 25, 3),
(2, 'PART-005', 30, 3),
(2, 'PART-001', 5, 3);

-- Update location quantities
UPDATE locations SET current_quantity = 45 WHERE id = 1;
UPDATE locations SET current_quantity = 60 WHERE id = 2;

-- Insert sample secondary locations (overflow)
INSERT INTO secondary_locations (crate_number, pallet_id, part_number, quantity, main_location_id, secondary_location_code, assigned_by) VALUES
('CRATE-001', 1, 'PART-002', 10, 1, 'OVERFLOW-01', 3),
('CRATE-002', 2, 'PART-004', 15, 2, 'OVERFLOW-01', 3);

-- Insert sample inventory transactions
INSERT INTO inventory_transactions (transaction_type, part_number, quantity, from_location, to_location, reference_type, reference_id, performed_by) VALUES
('receiving', 'PART-001', 10, NULL, 'RECEIVING', 'shipment', 1, 1),
('receiving', 'PART-002', 20, NULL, 'RECEIVING', 'shipment', 1, 1),
('receiving', 'PART-003', 15, NULL, 'RECEIVING', 'shipment', 1, 1),
('binning', 'PART-001', 10, 'RECEIVING', 'A01-R01-L01', 'pallet', 1, 3),
('binning', 'PART-002', 20, 'RECEIVING', 'A01-R01-L01', 'pallet', 1, 3),
('binning', 'PART-003', 15, 'RECEIVING', 'A01-R01-L01', 'pallet', 1, 3);

-- Insert sample binning logs
INSERT INTO binning_logs (pallet_id, action_type, location_id, performed_by) VALUES
(1, 'created', NULL, 3),
(1, 'binned', 1, 3),
(2, 'created', NULL, 3),
(2, 'binned', 2, 3);

-- Insert sample activity logs
INSERT INTO activity_logs (user_id, activity_type, module, description, ip_address) VALUES
(1, 'login', 'auth', 'User logged in', '127.0.0.1'),
(1, 'create', 'receiving', 'Created new shipment: INV-2026-001', '127.0.0.1'),
(1, 'scan', 'receiving', 'Scanned part: PART-001', '127.0.0.1'),
(1, 'finish', 'receiving', 'Finished checking shipment ID: 1', '127.0.0.1'),
(3, 'create', 'binning', 'Created pallet: PLT-001', '127.0.0.1'),
(3, 'bin', 'binning', 'Binned pallet to location', '127.0.0.1'),
(2, 'login', 'auth', 'User logged in', '127.0.0.1'),
(2, 'create', 'receiving', 'Created new shipment: INV-2026-002', '127.0.0.1'),
(2, 'scan', 'receiving', 'Scanned part: PART-004', '127.0.0.1');
