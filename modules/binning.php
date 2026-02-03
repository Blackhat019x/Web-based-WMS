<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

checkAuth();

$product_id = $_GET['product_id'] ?? 0;

$conn = getDBConnection();

// Get product details
$product = null;
if ($product_id) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Get binned quantity
        $binned_result = $conn->query("SELECT COALESCE(SUM(quantity), 0) as binned FROM inventory WHERE product_id = $product_id");
        $product['binned_quantity'] = $binned_result->fetch_assoc()['binned'];
        $product['unbinned'] = $product['quantity'] - $product['binned_quantity'];
    }
    $stmt->close();
}

// Get available bins
$bins = [];
$result = $conn->query("SELECT * FROM bins WHERE is_active = 1 ORDER BY location_type, bin_code");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $bins[] = $row;
    }
}

// Handle binning action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'bin_items') {
        $product_id = $_POST['product_id'];
        $bin_id = $_POST['bin_id'];
        $quantity = intval($_POST['quantity']);
        $pallet_number = $_POST['pallet_number'] ?? null;
        $location_type = $_POST['location_type'] ?? 'primary';
        
        // Check if enough unbinned quantity
        $product_result = $conn->query("SELECT p.quantity, COALESCE(SUM(inv.quantity), 0) as binned 
                                        FROM products p 
                                        LEFT JOIN inventory inv ON p.product_id = inv.product_id 
                                        WHERE p.product_id = $product_id 
                                        GROUP BY p.product_id");
        $product_data = $product_result->fetch_assoc();
        $unbinned = $product_data['quantity'] - $product_data['binned'];
        
        if ($quantity > $unbinned) {
            echo json_encode(['success' => false, 'message' => 'Not enough unbinned quantity']);
            exit();
        }
        
        // Insert into inventory
        $stmt = $conn->prepare("INSERT INTO inventory (product_id, bin_id, pallet_number, quantity, location_type, binned_by) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisisi", $product_id, $bin_id, $pallet_number, $quantity, $location_type, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            // Update bin utilization
            $conn->query("UPDATE bins SET current_utilization = current_utilization + $quantity WHERE bin_id = $bin_id");
            
            logActivity($conn, $_SESSION['user_id'], 'BIN', 'inventory', $stmt->insert_id, "Binned $quantity items");
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
        $stmt->close();
    } elseif ($_POST['action'] === 'scan_bin') {
        $bin_code = $_POST['bin_code'];
        
        $stmt = $conn->prepare("SELECT * FROM bins WHERE bin_code = ? AND is_active = 1");
        $stmt->bind_param("s", $bin_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $bin = $result->fetch_assoc();
            echo json_encode(['success' => true, 'bin' => $bin]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Bin not found']);
        }
        $stmt->close();
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
    <title>Binning - WMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1>🏢 Binning</h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <a href="../logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
            
            <?php if ($product): ?>
                <div class="content-card">
                    <h2>Product Details</h2>
                    <div class="grid-3">
                        <div>
                            <strong>Part Number:</strong> <?php echo htmlspecialchars($product['part_number']); ?>
                        </div>
                        <div>
                            <strong>Description:</strong> <?php echo htmlspecialchars($product['description']); ?>
                        </div>
                        <div>
                            <strong>Barcode:</strong> <?php echo htmlspecialchars($product['barcode']); ?>
                        </div>
                    </div>
                    <div class="grid-3" style="margin-top: 15px;">
                        <div>
                            <strong>Total Quantity:</strong> <?php echo number_format($product['quantity']); ?>
                        </div>
                        <div>
                            <strong>Binned:</strong> <?php echo number_format($product['binned_quantity']); ?>
                        </div>
                        <div>
                            <strong>Unbinned:</strong> 
                            <span class="badge badge-warning">
                                <?php echo number_format($product['unbinned']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="content-card">
                    <h2>Scan Bin Location</h2>
                    <div class="scanner-box">
                        <p style="margin-bottom: 20px; font-size: 16px; color: #6b7280;">
                            Scan bin barcode or enter bin code
                        </p>
                        <input type="text" 
                               id="bin-scan-input" 
                               class="scanner-input" 
                               placeholder="Scan bin location..." 
                               autofocus>
                        <p style="margin-top: 20px; font-size: 14px; color: #9ca3af;">
                            Selected Bin: <span id="selected-bin">None</span>
                        </p>
                    </div>
                </div>
                
                <div class="content-card">
                    <h2>Bin Items</h2>
                    <form id="binning-form">
                        <input type="hidden" name="action" value="bin_items">
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        <input type="hidden" id="bin_id" name="bin_id">
                        
                        <div class="grid-3">
                            <div class="form-group">
                                <label for="bin_select">Select Bin (or scan above)</label>
                                <select id="bin_select" name="bin_id_select" class="form-control">
                                    <option value="">-- Select Bin --</option>
                                    <?php foreach ($bins as $bin): ?>
                                        <option value="<?php echo $bin['bin_id']; ?>" 
                                                data-code="<?php echo htmlspecialchars($bin['bin_code']); ?>"
                                                data-type="<?php echo $bin['location_type']; ?>">
                                            <?php echo htmlspecialchars($bin['bin_code']); ?> 
                                            (<?php echo ucfirst($bin['location_type']); ?> - Zone <?php echo $bin['zone']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="pallet_number">Pallet Number</label>
                                <input type="text" id="pallet_number" name="pallet_number" class="form-control" placeholder="Optional">
                            </div>
                            
                            <div class="form-group">
                                <label for="quantity">Quantity to Bin</label>
                                <input type="number" id="quantity" name="quantity" class="form-control" min="1" max="<?php echo $product['unbinned']; ?>" value="1" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="location_type">Location Type</label>
                            <select id="location_type" name="location_type" class="form-control">
                                <option value="primary">Primary</option>
                                <option value="secondary">Secondary (Overflow)</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Bin Items</button>
                        <a href="inventory.php" class="btn btn-warning">Back to Inventory</a>
                    </form>
                </div>
                
                <div class="content-card">
                    <h2>Available Bins</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Bin Code</th>
                                <th>Type</th>
                                <th>Zone</th>
                                <th>Aisle-Rack-Level</th>
                                <th>Capacity</th>
                                <th>Utilization</th>
                                <th>Available</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bins as $bin): ?>
                                <?php $available = $bin['capacity'] - $bin['current_utilization']; ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($bin['bin_code']); ?></strong></td>
                                    <td>
                                        <span class="badge badge-<?php echo $bin['location_type'] === 'primary' ? 'completed' : 'warning'; ?>">
                                            <?php echo ucfirst($bin['location_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($bin['zone']); ?></td>
                                    <td><?php echo $bin['aisle'] . '-' . $bin['rack'] . '-' . $bin['level']; ?></td>
                                    <td><?php echo $bin['capacity']; ?></td>
                                    <td><?php echo $bin['current_utilization']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $available > 0 ? 'badge-completed' : 'badge-danger'; ?>">
                                            <?php echo $available; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="content-card">
                    <h2>Select Product to Bin</h2>
                    <p>Please select a product from the <a href="inventory.php">Inventory page</a> to start binning.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        let selectedBinId = null;
        
        // Barcode scanner for bins
        const binScanner = new BarcodeScanner('bin-scan-input', function(binCode) {
            ajax('binning.php', 'POST', {
                action: 'scan_bin',
                bin_code: binCode
            }, function(error, response) {
                if (error || !response.success) {
                    showNotification(response?.message || 'Bin not found', 'error');
                } else {
                    const bin = response.bin;
                    selectedBinId = bin.bin_id;
                    document.getElementById('selected-bin').textContent = bin.bin_code + ' (' + bin.location_type + ')';
                    document.getElementById('bin_id').value = bin.bin_id;
                    document.getElementById('bin_select').value = bin.bin_id;
                    document.getElementById('location_type').value = bin.location_type;
                    showNotification('Bin scanned: ' + bin.bin_code, 'success');
                }
            });
        });
        
        // Manual bin selection
        document.getElementById('bin_select').addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (option.value) {
                selectedBinId = option.value;
                document.getElementById('bin_id').value = option.value;
                document.getElementById('selected-bin').textContent = option.dataset.code + ' (' + option.dataset.type + ')';
                document.getElementById('location_type').value = option.dataset.type;
            }
        });
        
        // Form submission
        document.getElementById('binning-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!document.getElementById('bin_id').value) {
                showNotification('Please select or scan a bin location', 'error');
                return;
            }
            
            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => data[key] = value);
            
            // Use the scanned/selected bin_id
            data.bin_id = document.getElementById('bin_id').value;
            
            ajax('binning.php', 'POST', data, function(error, response) {
                if (error || !response.success) {
                    showNotification(response?.message || 'Error binning items', 'error');
                } else {
                    showNotification('Items binned successfully', 'success');
                    setTimeout(() => location.reload(), 1500);
                }
            });
        });
    </script>
</body>
</html>
