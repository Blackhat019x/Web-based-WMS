<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

checkAuth();

$conn = getDBConnection();

// Get all releases
$releases = [];
$result = $conn->query("SELECT r.*, p.part_number, p.description, 
                        u1.full_name as requested_by_name, u2.full_name as picked_by_name
                        FROM releases r
                        JOIN products p ON r.product_id = p.product_id
                        LEFT JOIN users u1 ON r.requested_by = u1.user_id
                        LEFT JOIN users u2 ON r.picked_by = u2.user_id
                        ORDER BY r.created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $releases[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Releasing - WMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1>📤 Releasing</h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <a href="../logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
            
            <div class="content-card">
                <div style="text-align: center; padding: 40px;">
                    <h2 style="color: #6b7280; margin-bottom: 20px;">🚧 Releasing Module</h2>
                    <p style="font-size: 16px; color: #9ca3af;">
                        This module is currently under development. It will support:
                    </p>
                    <ul style="list-style: none; padding: 20px 0; font-size: 14px; color: #6b7280;">
                        <li>✓ Pick list generation with barcode scanning</li>
                        <li>✓ Wave picking optimization</li>
                        <li>✓ Order fulfillment tracking</li>
                        <li>✓ Shipping label printing</li>
                        <li>✓ Inventory deduction automation</li>
                    </ul>
                    <p style="font-size: 14px; color: #9ca3af; margin-top: 20px;">
                        Check back soon for updates!
                    </p>
                </div>
            </div>
            
            <div class="content-card">
                <h2>Release History</h2>
                <?php if (count($releases) > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Release #</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Status</th>
                                <th>Requested By</th>
                                <th>Picked By</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($releases as $release): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($release['release_number']); ?></td>
                                    <td><?php echo htmlspecialchars($release['part_number']); ?></td>
                                    <td><?php echo number_format($release['quantity']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $release['status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $release['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($release['requested_by_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($release['picked_by_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($release['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No release records found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
