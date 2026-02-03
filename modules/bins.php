<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

checkAuth();

$conn = getDBConnection();

// Handle bin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_bin') {
        $bin_code = $_POST['bin_code'];
        $location_type = $_POST['location_type'];
        $zone = $_POST['zone'];
        $aisle = $_POST['aisle'];
        $rack = $_POST['rack'];
        $level = $_POST['level'];
        $capacity = intval($_POST['capacity']);
        
        $stmt = $conn->prepare("INSERT INTO bins (bin_code, location_type, zone, aisle, rack, level, capacity) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $bin_code, $location_type, $zone, $aisle, $rack, $level, $capacity);
        
        if ($stmt->execute()) {
            logActivity($conn, $_SESSION['user_id'], 'CREATE', 'bin', $stmt->insert_id, "Added bin: $bin_code");
            header('Location: bins.php?success=1');
        } else {
            header('Location: bins.php?error=1');
        }
        $stmt->close();
        exit();
    }
}

// Get all bins
$bins = [];
$result = $conn->query("SELECT b.*, COUNT(inv.inventory_id) as item_count
                        FROM bins b
                        LEFT JOIN inventory inv ON b.bin_id = inv.bin_id
                        GROUP BY b.bin_id
                        ORDER BY b.location_type, b.bin_code");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $bins[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bins - WMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1>📍 Bin Management</h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <a href="../logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Bin added successfully!</div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">Error adding bin. Please try again.</div>
            <?php endif; ?>
            
            <div class="content-card">
                <h2>Add New Bin</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_bin">
                    <div class="grid-3">
                        <div class="form-group">
                            <label for="bin_code">Bin Code *</label>
                            <input type="text" id="bin_code" name="bin_code" class="form-control" placeholder="e.g., A-01-01-01" required>
                        </div>
                        <div class="form-group">
                            <label for="location_type">Location Type *</label>
                            <select id="location_type" name="location_type" class="form-control" required>
                                <option value="primary">Primary</option>
                                <option value="secondary">Secondary</option>
                                <option value="overflow">Overflow</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="zone">Zone *</label>
                            <input type="text" id="zone" name="zone" class="form-control" placeholder="e.g., A" required>
                        </div>
                    </div>
                    <div class="grid-3">
                        <div class="form-group">
                            <label for="aisle">Aisle *</label>
                            <input type="text" id="aisle" name="aisle" class="form-control" placeholder="e.g., 01" required>
                        </div>
                        <div class="form-group">
                            <label for="rack">Rack *</label>
                            <input type="text" id="rack" name="rack" class="form-control" placeholder="e.g., 01" required>
                        </div>
                        <div class="form-group">
                            <label for="level">Level *</label>
                            <input type="text" id="level" name="level" class="form-control" placeholder="e.g., 01" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="capacity">Capacity *</label>
                        <input type="number" id="capacity" name="capacity" class="form-control" min="1" value="100" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Bin</button>
                </form>
            </div>
            
            <div class="content-card">
                <h2>All Bins</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Bin Code</th>
                            <th>Type</th>
                            <th>Zone</th>
                            <th>Aisle</th>
                            <th>Rack</th>
                            <th>Level</th>
                            <th>Capacity</th>
                            <th>Utilization</th>
                            <th>Available</th>
                            <th>Items</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bins as $bin): ?>
                            <?php 
                                $available = $bin['capacity'] - $bin['current_utilization'];
                                $utilization_pct = $bin['capacity'] > 0 ? round(($bin['current_utilization'] / $bin['capacity']) * 100) : 0;
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($bin['bin_code']); ?></strong></td>
                                <td>
                                    <span class="badge badge-<?php echo $bin['location_type'] === 'primary' ? 'completed' : 'warning'; ?>">
                                        <?php echo ucfirst($bin['location_type']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($bin['zone']); ?></td>
                                <td><?php echo htmlspecialchars($bin['aisle']); ?></td>
                                <td><?php echo htmlspecialchars($bin['rack']); ?></td>
                                <td><?php echo htmlspecialchars($bin['level']); ?></td>
                                <td><?php echo number_format($bin['capacity']); ?></td>
                                <td><?php echo number_format($bin['current_utilization']); ?> (<?php echo $utilization_pct; ?>%)</td>
                                <td>
                                    <span class="badge <?php echo $available > 0 ? 'badge-completed' : 'badge-danger'; ?>">
                                        <?php echo number_format($available); ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($bin['item_count']); ?></td>
                                <td>
                                    <span class="badge <?php echo $bin['is_active'] ? 'badge-completed' : 'badge-cancelled'; ?>">
                                        <?php echo $bin['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
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
