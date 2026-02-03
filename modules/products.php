<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

checkAuth();

$conn = getDBConnection();

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_product') {
        $part_number = $_POST['part_number'];
        $description = $_POST['description'];
        $barcode = $_POST['barcode'];
        $unit_of_measure = $_POST['unit_of_measure'];
        
        $stmt = $conn->prepare("INSERT INTO products (part_number, description, barcode, unit_of_measure) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $part_number, $description, $barcode, $unit_of_measure);
        
        if ($stmt->execute()) {
            logActivity($conn, $_SESSION['user_id'], 'CREATE', 'product', $stmt->insert_id, "Added product: $part_number");
            header('Location: products.php?success=1');
        } else {
            header('Location: products.php?error=1');
        }
        $stmt->close();
        exit();
    }
}

// Get all products
$products = [];
$result = $conn->query("SELECT p.*, COALESCE(SUM(inv.quantity), 0) as binned_quantity
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
    <title>Products - WMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1>🔧 Products</h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <a href="../logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Product added successfully!</div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">Error adding product. Please try again.</div>
            <?php endif; ?>
            
            <div class="content-card">
                <h2>Add New Product</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_product">
                    <div class="grid-3">
                        <div class="form-group">
                            <label for="part_number">Part Number *</label>
                            <input type="text" id="part_number" name="part_number" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="barcode">Barcode *</label>
                            <input type="text" id="barcode" name="barcode" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="unit_of_measure">Unit of Measure</label>
                            <select id="unit_of_measure" name="unit_of_measure" class="form-control">
                                <option value="EA">Each (EA)</option>
                                <option value="BOX">Box</option>
                                <option value="CASE">Case</option>
                                <option value="PALLET">Pallet</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <input type="text" id="description" name="description" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </form>
            </div>
            
            <div class="content-card">
                <h2>All Products</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Part Number</th>
                            <th>Description</th>
                            <th>Barcode</th>
                            <th>UOM</th>
                            <th>Total Qty</th>
                            <th>Binned Qty</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($product['part_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($product['description']); ?></td>
                                <td><?php echo htmlspecialchars($product['barcode']); ?></td>
                                <td><?php echo htmlspecialchars($product['unit_of_measure']); ?></td>
                                <td><?php echo number_format($product['quantity']); ?></td>
                                <td><?php echo number_format($product['binned_quantity']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($product['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
