<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

checkAuth();

$conn = getDBConnection();

// Get all invoices
$invoices = [];
$result = $conn->query("SELECT i.*, u.full_name as created_by_name 
                        FROM invoices i 
                        LEFT JOIN users u ON i.created_by = u.user_id 
                        ORDER BY i.created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $invoices[] = $row;
    }
}

// Handle new invoice creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_invoice') {
    $invoice_number = $_POST['invoice_number'];
    $supplier_name = $_POST['supplier_name'];
    $invoice_date = $_POST['invoice_date'];
    
    $stmt = $conn->prepare("INSERT INTO invoices (invoice_number, supplier_name, invoice_date, created_by) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $invoice_number, $supplier_name, $invoice_date, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $invoice_id = $stmt->insert_id;
        logActivity($conn, $_SESSION['user_id'], 'CREATE', 'invoice', $invoice_id, "Created invoice: $invoice_number");
        header("Location: receiving_scan.php?invoice_id=$invoice_id");
        exit();
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receiving - WMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1>📥 Receiving</h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <a href="../logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
            
            <div class="content-card">
                <h2>Create New Receiving Invoice</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="create_invoice">
                    <div class="grid-3">
                        <div class="form-group">
                            <label for="invoice_number">Invoice Number</label>
                            <input type="text" id="invoice_number" name="invoice_number" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="supplier_name">Supplier Name</label>
                            <input type="text" id="supplier_name" name="supplier_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="invoice_date">Invoice Date</label>
                            <input type="date" id="invoice_date" name="invoice_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Create & Start Receiving</button>
                </form>
            </div>
            
            <div class="content-card">
                <h2>All Invoices</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Supplier</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                                <td><?php echo htmlspecialchars($invoice['supplier_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($invoice['invoice_date'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $invoice['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $invoice['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo $invoice['received_items']; ?> / <?php echo $invoice['total_items']; ?></td>
                                <td><?php echo htmlspecialchars($invoice['created_by_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if ($invoice['status'] !== 'completed'): ?>
                                        <a href="receiving_scan.php?invoice_id=<?php echo $invoice['invoice_id']; ?>" class="btn btn-primary" style="padding: 6px 12px;">Scan Items</a>
                                    <?php endif; ?>
                                </td>
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
