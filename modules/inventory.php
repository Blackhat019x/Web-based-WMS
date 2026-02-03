<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

checkAuth();

$conn = getDBConnection();

// Get inventory with product and bin details
$inventory = [];
$result = $conn->query("SELECT inv.*, p.part_number, p.description, p.barcode, 
                        b.bin_code, b.location_type, b.zone, u.full_name as binned_by_name
                        FROM inventory inv
                        JOIN products p ON inv.product_id = p.product_id
                        JOIN bins b ON inv.bin_id = b.bin_id
                        LEFT JOIN users u ON inv.binned_by = u.user_id
                        ORDER BY inv.binned_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $inventory[] = $row;
    }
}

// Get products summary
$products = [];
$result = $conn->query("SELECT p.*, 
                        COALESCE(SUM(inv.quantity), 0) as binned_quantity
                        FROM products p
                        LEFT JOIN inventory inv ON p.product_id = inv.product_id
                        GROUP BY p.product_id
                        ORDER BY p.part_number");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - WMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1>📦 Inventory</h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <a href="../logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
            
            <div class="content-card">
                <h2>Products Summary</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Part Number</th>
                            <th>Description</th>
                            <th>Barcode</th>
                            <th>Total Quantity</th>
                            <th>Binned Quantity</th>
                            <th>Unbinned</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <?php $unbinned = $product['quantity'] - $product['binned_quantity']; ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['part_number']); ?></td>
                                <td><?php echo htmlspecialchars($product['description']); ?></td>
                                <td><?php echo htmlspecialchars($product['barcode']); ?></td>
                                <td><?php echo number_format($product['quantity']); ?></td>
                                <td><?php echo number_format($product['binned_quantity']); ?></td>
                                <td>
                                    <span class="badge <?php echo $unbinned > 0 ? 'badge-warning' : 'badge-completed'; ?>">
                                        <?php echo number_format($unbinned); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($unbinned > 0): ?>
                                        <a href="binning.php?product_id=<?php echo $product['product_id']; ?>" 
                                           class="btn btn-primary" style="padding: 6px 12px;">
                                            Bin Items
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="content-card">
                <h2>Binned Inventory</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Part Number</th>
                            <th>Description</th>
                            <th>Bin Code</th>
                            <th>Pallet #</th>
                            <th>Quantity</th>
                            <th>Location Type</th>
                            <th>Zone</th>
                            <th>Binned By</th>
                            <th>Binned At</th>
                            <th>Label</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventory as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['part_number']); ?></td>
                                <td><?php echo htmlspecialchars($item['description']); ?></td>
                                <td><strong><?php echo htmlspecialchars($item['bin_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($item['pallet_number'] ?? 'N/A'); ?></td>
                                <td><?php echo number_format($item['quantity']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $item['location_type'] === 'primary' ? 'completed' : 'warning'; ?>">
                                        <?php echo ucfirst($item['location_type']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($item['zone']); ?></td>
                                <td><?php echo htmlspecialchars($item['binned_by_name'] ?? 'N/A'); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($item['binned_at'])); ?></td>
                                <td>
                                    <button onclick="printLabel(<?php echo $item['inventory_id']; ?>, '<?php echo htmlspecialchars($item['part_number']); ?>', '<?php echo htmlspecialchars($item['bin_code']); ?>', '<?php echo htmlspecialchars($item['pallet_number'] ?? ''); ?>', <?php echo $item['quantity']; ?>)" 
                                            class="btn btn-success" style="padding: 6px 12px;">
                                        Print
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        function printLabel(inventoryId, partNumber, binCode, palletNumber, quantity) {
            const labelContent = `
                <h2 style="text-align: center; margin-bottom: 20px;">INVENTORY LABEL</h2>
                <div style="font-size: 14px; line-height: 2;">
                    <strong>Part Number:</strong> ${partNumber}<br>
                    <strong>Bin Location:</strong> ${binCode}<br>
                    <strong>Pallet #:</strong> ${palletNumber || 'N/A'}<br>
                    <strong>Quantity:</strong> ${quantity}<br>
                    <strong>Date:</strong> ${new Date().toLocaleString()}<br>
                </div>
            `;
            printLabel(labelContent);
            
            // Mark as printed
            ajax('../modules/inventory_action.php', 'POST', {
                action: 'mark_printed',
                inventory_id: inventoryId
            }, function(error, response) {
                if (!error && response.success) {
                    showNotification('Label marked as printed', 'success');
                }
            });
        }
    </script>
</body>
</html>
