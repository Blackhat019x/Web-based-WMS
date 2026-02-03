<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

checkAuth();

$conn = getDBConnection();

// Fetch KPIs
$total_products = 0;
$result = $conn->query("SELECT COUNT(*) as count FROM products");
if ($result) {
    $total_products = $result->fetch_assoc()['count'];
}

$pending_shipments = 0;
$result = $conn->query("SELECT COUNT(*) as count FROM invoices WHERE status IN ('pending', 'in_progress')");
if ($result) {
    $pending_shipments = $result->fetch_assoc()['count'];
}

$total_inventory = 0;
$result = $conn->query("SELECT SUM(quantity) as total FROM products");
if ($result) {
    $row = $result->fetch_assoc();
    $total_inventory = $row['total'] ?? 0;
}

$bin_utilization = 0;
$result = $conn->query("SELECT AVG(current_utilization) as avg_util FROM bins WHERE is_active = 1");
if ($result) {
    $row = $result->fetch_assoc();
    $bin_utilization = round($row['avg_util'] ?? 0, 1);
}

// Recent activity
$recent_invoices = [];
$result = $conn->query("SELECT i.*, u.full_name as created_by_name 
                        FROM invoices i 
                        LEFT JOIN users u ON i.created_by = u.user_id 
                        ORDER BY i.created_at DESC LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recent_invoices[] = $row;
    }
}

// Chart data for inventory by location
$inventory_by_zone = [];
$result = $conn->query("SELECT b.zone, SUM(inv.quantity) as total 
                        FROM inventory inv 
                        JOIN bins b ON inv.bin_id = b.bin_id 
                        GROUP BY b.zone");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $inventory_by_zone[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - WMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1>📊 Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
            
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-icon blue">
                        📦
                    </div>
                    <div class="kpi-info">
                        <h3>Total Products</h3>
                        <p><?php echo number_format($total_products); ?></p>
                    </div>
                </div>
                
                <div class="kpi-card">
                    <div class="kpi-icon orange">
                        🚚
                    </div>
                    <div class="kpi-info">
                        <h3>Pending Shipments</h3>
                        <p><?php echo number_format($pending_shipments); ?></p>
                    </div>
                </div>
                
                <div class="kpi-card">
                    <div class="kpi-icon green">
                        📊
                    </div>
                    <div class="kpi-info">
                        <h3>Total Inventory</h3>
                        <p><?php echo number_format($total_inventory); ?></p>
                    </div>
                </div>
                
                <div class="kpi-card">
                    <div class="kpi-icon red">
                        🏢
                    </div>
                    <div class="kpi-info">
                        <h3>Bin Utilization</h3>
                        <p><?php echo $bin_utilization; ?>%</p>
                    </div>
                </div>
            </div>
            
            <div class="grid-2">
                <div class="content-card">
                    <h2>Recent Invoices</h2>
                    <?php if (count($recent_invoices) > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Supplier</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_invoices as $invoice): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                                        <td><?php echo htmlspecialchars($invoice['supplier_name']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $invoice['status']; ?>">
                                                <?php echo ucfirst($invoice['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($invoice['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No recent invoices found.</p>
                    <?php endif; ?>
                </div>
                
                <div class="content-card">
                    <h2>Inventory by Zone</h2>
                    <canvas id="inventoryChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Chart for inventory by zone
        <?php if (count($inventory_by_zone) > 0): ?>
        const ctx = document.getElementById('inventoryChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($inventory_by_zone, 'zone')); ?>,
                datasets: [{
                    label: 'Inventory Quantity',
                    data: <?php echo json_encode(array_column($inventory_by_zone, 'total')); ?>,
                    backgroundColor: [
                        'rgba(37, 99, 235, 0.7)',
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(245, 158, 11, 0.7)',
                        'rgba(239, 68, 68, 0.7)'
                    ],
                    borderColor: [
                        'rgba(37, 99, 235, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(239, 68, 68, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
    
    <script src="assets/js/main.js"></script>
</body>
</html>
