<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

checkAuth();

$invoice_id = $_GET['invoice_id'] ?? 0;

$conn = getDBConnection();

// Get invoice details
$invoice = null;
$stmt = $conn->prepare("SELECT * FROM invoices WHERE invoice_id = ?");
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $invoice = $result->fetch_assoc();
}
$stmt->close();

if (!$invoice) {
    header('Location: receiving.php');
    exit();
}

// Get shipments for this invoice
$shipments = [];
$stmt = $conn->prepare("SELECT s.*, p.part_number, p.description, p.barcode 
                        FROM shipments s 
                        JOIN products p ON s.product_id = p.product_id 
                        WHERE s.invoice_id = ? 
                        ORDER BY s.shipment_id DESC");
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $shipments[] = $row;
}
$stmt->close();

// Handle barcode scan (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'scan_barcode') {
        $barcode = $_POST['barcode'];
        
        // Find product by barcode
        $stmt = $conn->prepare("SELECT * FROM products WHERE barcode = ?");
        $stmt->bind_param("s", $barcode);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            
            // Check if shipment exists for this product and invoice
            $check_stmt = $conn->prepare("SELECT * FROM shipments WHERE invoice_id = ? AND product_id = ?");
            $check_stmt->bind_param("ii", $invoice_id, $product['product_id']);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                // Update existing shipment
                $shipment = $check_result->fetch_assoc();
                $new_received = $shipment['received_quantity'] + 1;
                $new_status = $new_received >= $shipment['expected_quantity'] ? 'completed' : 'partial';
                
                $update_stmt = $conn->prepare("UPDATE shipments SET received_quantity = ?, status = ?, scanned_at = NOW(), received_by = ? WHERE shipment_id = ?");
                $update_stmt->bind_param("isii", $new_received, $new_status, $_SESSION['user_id'], $shipment['shipment_id']);
                $update_stmt->execute();
                $update_stmt->close();
            } else {
                // Create new shipment record
                $insert_stmt = $conn->prepare("INSERT INTO shipments (invoice_id, product_id, expected_quantity, received_quantity, status, scanned_at, received_by) VALUES (?, ?, 1, 1, 'completed', NOW(), ?)");
                $insert_stmt->bind_param("iii", $invoice_id, $product['product_id'], $_SESSION['user_id']);
                $insert_stmt->execute();
                $insert_stmt->close();
                
                // Update total items
                $conn->query("UPDATE invoices SET total_items = total_items + 1 WHERE invoice_id = $invoice_id");
            }
            
            // Update product quantity
            $conn->query("UPDATE products SET quantity = quantity + 1 WHERE product_id = " . $product['product_id']);
            
            // Update invoice received count
            $conn->query("UPDATE invoices SET received_items = (SELECT SUM(received_quantity) FROM shipments WHERE invoice_id = $invoice_id) WHERE invoice_id = $invoice_id");
            
            // Update invoice status
            $conn->query("UPDATE invoices SET status = 'in_progress' WHERE invoice_id = $invoice_id AND status = 'pending'");
            
            logActivity($conn, $_SESSION['user_id'], 'SCAN', 'product', $product['product_id'], "Scanned product: " . $product['part_number']);
            
            echo json_encode(['success' => true, 'product' => $product]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
        }
        $stmt->close();
    } elseif ($_POST['action'] === 'complete_invoice') {
        $update_stmt = $conn->prepare("UPDATE invoices SET status = 'completed', completed_at = NOW() WHERE invoice_id = ?");
        $update_stmt->bind_param("i", $invoice_id);
        $update_stmt->execute();
        $update_stmt->close();
        
        logActivity($conn, $_SESSION['user_id'], 'COMPLETE', 'invoice', $invoice_id, "Completed invoice: " . $invoice['invoice_number']);
        
        echo json_encode(['success' => true]);
    }
    
    $conn->close();
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Items - WMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1>📥 Scanning: <?php echo htmlspecialchars($invoice['invoice_number']); ?></h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <a href="../logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
            
            <div class="content-card">
                <h2>Invoice Details</h2>
                <div class="grid-3">
                    <div>
                        <strong>Supplier:</strong> <?php echo htmlspecialchars($invoice['supplier_name']); ?>
                    </div>
                    <div>
                        <strong>Status:</strong> 
                        <span class="badge badge-<?php echo $invoice['status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $invoice['status'])); ?>
                        </span>
                    </div>
                    <div>
                        <strong>Progress:</strong> <?php echo $invoice['received_items']; ?> / <?php echo $invoice['total_items']; ?> items
                    </div>
                </div>
            </div>
            
            <div class="content-card">
                <h2>Scan Parts</h2>
                <div class="scanner-box">
                    <p style="margin-bottom: 20px; font-size: 16px; color: #6b7280;">
                        Scan barcode or enter part number
                    </p>
                    <input type="text" 
                           id="barcode-input" 
                           class="scanner-input" 
                           placeholder="Scan barcode here..." 
                           autofocus>
                    <p style="margin-top: 20px; font-size: 14px; color: #9ca3af;">
                        Last scan: <span id="last-scan">None</span>
                    </p>
                </div>
                
                <div style="margin-top: 20px; text-align: center;">
                    <button onclick="completeInvoice()" class="btn btn-success">Complete Invoice</button>
                    <a href="receiving.php" class="btn btn-warning">Back to Receiving</a>
                </div>
            </div>
            
            <div class="content-card">
                <h2>Scanned Items</h2>
                <table class="data-table" id="shipments-table">
                    <thead>
                        <tr>
                            <th>Part Number</th>
                            <th>Description</th>
                            <th>Barcode</th>
                            <th>Received</th>
                            <th>Status</th>
                            <th>Scanned At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shipments as $shipment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($shipment['part_number']); ?></td>
                                <td><?php echo htmlspecialchars($shipment['description']); ?></td>
                                <td><?php echo htmlspecialchars($shipment['barcode']); ?></td>
                                <td><?php echo $shipment['received_quantity']; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $shipment['status']; ?>">
                                        <?php echo ucfirst($shipment['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $shipment['scanned_at'] ? date('M d, H:i', strtotime($shipment['scanned_at'])) : 'N/A'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        const scanner = new BarcodeScanner('barcode-input', function(barcode) {
            document.getElementById('last-scan').textContent = barcode;
            
            ajax('receiving_scan.php?invoice_id=<?php echo $invoice_id; ?>', 'POST', {
                action: 'scan_barcode',
                barcode: barcode
            }, function(error, response) {
                if (error) {
                    showNotification('Error scanning barcode', 'error');
                } else if (response.success) {
                    showNotification('Product scanned: ' + response.product.part_number, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(response.message || 'Product not found', 'error');
                }
            });
        });
        
        function completeInvoice() {
            if (confirm('Are you sure you want to complete this invoice?')) {
                ajax('receiving_scan.php?invoice_id=<?php echo $invoice_id; ?>', 'POST', {
                    action: 'complete_invoice'
                }, function(error, response) {
                    if (error) {
                        showNotification('Error completing invoice', 'error');
                    } else if (response.success) {
                        showNotification('Invoice completed successfully', 'success');
                        setTimeout(() => window.location.href = 'receiving.php', 1500);
                    }
                });
            }
        }
    </script>
</body>
</html>
