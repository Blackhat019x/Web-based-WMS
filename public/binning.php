<?php
require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn()) {
    redirect(BASE_URL . '/public/login.php');
}

$currentPage = 'binning';
$pageTitle = 'Binning';

$pdo = getDBConnection();
$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_pallet') {
        $palletNumber = trim($_POST['pallet_number'] ?? '');
        $shipmentId = intval($_POST['shipment_id'] ?? 0);
        
        if (empty($palletNumber)) {
            $message = 'Pallet number is required';
            $messageType = 'danger';
        } elseif (!$shipmentId) {
            $message = 'Please select a shipment';
            $messageType = 'danger';
        } else {
            // Check if pallet already exists
            $stmt = $pdo->prepare("SELECT id FROM pallets WHERE pallet_number = ?");
            $stmt->execute([$palletNumber]);
            
            if ($stmt->fetch()) {
                $message = 'Pallet number already exists';
                $messageType = 'danger';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO pallets (pallet_number, shipment_id, status, assigned_to) 
                    VALUES (?, ?, 'pending', ?)
                ");
                
                if ($stmt->execute([$palletNumber, $shipmentId, getCurrentUserId()])) {
                    $palletId = $pdo->lastInsertId();
                    
                    // Log binning activity
                    $logStmt = $pdo->prepare("
                        INSERT INTO binning_logs (pallet_id, action_type, performed_by) 
                        VALUES (?, 'created', ?)
                    ");
                    $logStmt->execute([$palletId, getCurrentUserId()]);
                    
                    logActivity('create', 'binning', "Created pallet: $palletNumber");
                    
                    redirect(BASE_URL . '/public/binning.php?view=' . $palletId);
                } else {
                    $message = 'Failed to create pallet';
                    $messageType = 'danger';
                }
            }
        }
    }
    
    elseif ($_POST['action'] === 'add_to_pallet') {
        $palletId = intval($_POST['pallet_id'] ?? 0);
        $partNumber = trim($_POST['part_number'] ?? '');
        $quantity = intval($_POST['quantity'] ?? 0);
        
        if ($palletId && $partNumber && $quantity > 0) {
            // Check if part already exists on pallet
            $stmt = $pdo->prepare("
                SELECT * FROM pallet_items 
                WHERE pallet_id = ? AND part_number = ?
            ");
            $stmt->execute([$palletId, $partNumber]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update quantity
                $updateStmt = $pdo->prepare("
                    UPDATE pallet_items 
                    SET quantity = quantity + ? 
                    WHERE id = ?
                ");
                $updateStmt->execute([$quantity, $existing['id']]);
            } else {
                // Insert new item
                $insertStmt = $pdo->prepare("
                    INSERT INTO pallet_items (pallet_id, part_number, quantity, added_by) 
                    VALUES (?, ?, ?, ?)
                ");
                $insertStmt->execute([$palletId, $partNumber, $quantity, getCurrentUserId()]);
            }
            
            logActivity('add_item', 'binning', "Added $quantity x $partNumber to pallet");
            
            echo json_encode(['success' => true, 'message' => 'Item added to pallet']);
            exit;
        }
        
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }
    
    elseif ($_POST['action'] === 'assign_location') {
        $palletId = intval($_POST['pallet_id'] ?? 0);
        $locationId = intval($_POST['location_id'] ?? 0);
        
        if ($palletId && $locationId) {
            $stmt = $pdo->prepare("
                UPDATE pallets 
                SET location_id = ?, status = 'binned', binned_by = ?, binned_date = NOW() 
                WHERE id = ?
            ");
            
            if ($stmt->execute([$locationId, getCurrentUserId(), $palletId])) {
                // Update location current quantity
                $itemsStmt = $pdo->prepare("SELECT SUM(quantity) as total FROM pallet_items WHERE pallet_id = ?");
                $itemsStmt->execute([$palletId]);
                $total = $itemsStmt->fetch()['total'] ?? 0;
                
                $updateLocStmt = $pdo->prepare("
                    UPDATE locations 
                    SET current_quantity = current_quantity + ? 
                    WHERE id = ?
                ");
                $updateLocStmt->execute([$total, $locationId]);
                
                // Log binning activity
                $logStmt = $pdo->prepare("
                    INSERT INTO binning_logs (pallet_id, action_type, location_id, performed_by) 
                    VALUES (?, 'binned', ?, ?)
                ");
                $logStmt->execute([$palletId, $locationId, getCurrentUserId()]);
                
                // Log inventory transaction
                $transStmt = $pdo->prepare("
                    INSERT INTO inventory_transactions (transaction_type, part_number, quantity, to_location, reference_type, reference_id, performed_by) 
                    SELECT 'binning', part_number, quantity, 
                           (SELECT location_code FROM locations WHERE id = ?),
                           'pallet', ?, ?
                    FROM pallet_items WHERE pallet_id = ?
                ");
                $transStmt->execute([$locationId, $palletId, getCurrentUserId(), $palletId]);
                
                logActivity('bin', 'binning', "Binned pallet to location");
                
                $message = 'Pallet successfully binned to location';
                $messageType = 'success';
            }
        }
    }
    
    elseif ($_POST['action'] === 'assign_overflow') {
        $palletId = intval($_POST['pallet_id'] ?? 0);
        $crateNumber = trim($_POST['crate_number'] ?? '');
        $partNumber = trim($_POST['part_number'] ?? '');
        $quantity = intval($_POST['quantity'] ?? 0);
        $secondaryLocationCode = trim($_POST['secondary_location_code'] ?? '');
        
        if ($palletId && $crateNumber && $partNumber && $quantity > 0) {
            $stmt = $pdo->prepare("
                INSERT INTO secondary_locations (crate_number, pallet_id, part_number, quantity, secondary_location_code, assigned_by) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$crateNumber, $palletId, $partNumber, $quantity, $secondaryLocationCode, getCurrentUserId()])) {
                logActivity('overflow', 'binning', "Assigned overflow: $quantity x $partNumber to crate $crateNumber");
                
                echo json_encode(['success' => true, 'message' => 'Overflow assigned successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to assign overflow']);
            }
            exit;
        }
        
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }
}

// Get finished shipments for pallet creation
$finishedShipments = $pdo->query("
    SELECT s.*, COUNT(si.id) as item_count, SUM(si.quantity) as total_quantity
    FROM shipments s
    LEFT JOIN shipment_items si ON s.id = si.shipment_id
    WHERE s.status = 'finished_checking'
    GROUP BY s.id
    ORDER BY s.finished_date DESC
")->fetchAll();

// Get all pallets
$pallets = $pdo->query("
    SELECT p.*, s.invoice_number, l.location_code, u.full_name as assigned_to_name,
           COUNT(pi.id) as item_count, SUM(pi.quantity) as total_quantity
    FROM pallets p
    LEFT JOIN shipments s ON p.shipment_id = s.id
    LEFT JOIN locations l ON p.location_id = l.id
    LEFT JOIN users u ON p.assigned_to = u.id
    LEFT JOIN pallet_items pi ON p.id = pi.pallet_id
    GROUP BY p.id
    ORDER BY p.created_at DESC
")->fetchAll();

// Get all locations
$locations = $pdo->query("
    SELECT * FROM locations 
    WHERE is_active = 1 
    ORDER BY location_type, location_code
")->fetchAll();

// Get viewing pallet if requested
$viewingPallet = null;
$palletItems = [];
$secondaryLocations = [];
if (isset($_GET['view'])) {
    $palletId = intval($_GET['view']);
    $stmt = $pdo->prepare("
        SELECT p.*, s.invoice_number, l.location_code, l.location_name
        FROM pallets p
        LEFT JOIN shipments s ON p.shipment_id = s.id
        LEFT JOIN locations l ON p.location_id = l.id
        WHERE p.id = ?
    ");
    $stmt->execute([$palletId]);
    $viewingPallet = $stmt->fetch();
    
    if ($viewingPallet) {
        $stmt = $pdo->prepare("SELECT * FROM pallet_items WHERE pallet_id = ?");
        $stmt->execute([$palletId]);
        $palletItems = $stmt->fetchAll();
        
        $stmt = $pdo->prepare("SELECT * FROM secondary_locations WHERE pallet_id = ?");
        $stmt->execute([$palletId]);
        $secondaryLocations = $stmt->fetchAll();
    }
}

ob_start();
?>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
        <?= e($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($viewingPallet): ?>
    <!-- Pallet Management Interface -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-pallet"></i> Pallet: <?= e($viewingPallet['pallet_number']) ?>
                    </h5>
                    <div>
                        <span class="badge bg-light text-dark me-2">
                            Invoice: <?= e($viewingPallet['invoice_number'] ?? 'N/A') ?>
                        </span>
                        <span class="badge bg-<?= $viewingPallet['status'] === 'binned' ? 'success' : 'warning' ?>">
                            <?= e(ucfirst($viewingPallet['status'])) ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($viewingPallet['status'] !== 'binned'): ?>
                        <!-- Add Items to Pallet -->
                        <div class="mb-4">
                            <h6>Add Items to Pallet</h6>
                            <form id="addItemForm" class="row g-3">
                                <input type="hidden" name="pallet_id" value="<?= $viewingPallet['id'] ?>">
                                <div class="col-md-6">
                                    <label class="form-label">Part Number</label>
                                    <input type="text" class="form-control" name="part_number" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Quantity</label>
                                    <input type="number" class="form-control" name="quantity" min="1" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-scan w-100">
                                        <i class="fas fa-plus"></i> Add Item
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <div id="actionMessage" class="alert" style="display: none;"></div>
                        
                        <!-- Assign Main Location -->
                        <div class="mb-4">
                            <h6>Assign Main Location</h6>
                            <form method="POST" class="row g-3">
                                <input type="hidden" name="action" value="assign_location">
                                <input type="hidden" name="pallet_id" value="<?= $viewingPallet['id'] ?>">
                                <div class="col-md-9">
                                    <select class="form-select" name="location_id" required>
                                        <option value="">Select Location</option>
                                        <?php foreach ($locations as $location): ?>
                                            <?php if ($location['location_type'] === 'main'): ?>
                                                <option value="<?= $location['id'] ?>">
                                                    <?= e($location['location_code']) ?> - <?= e($location['location_name']) ?>
                                                    (Available: <?= $location['capacity'] - $location['current_quantity'] ?>)
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-map-marker-alt"></i> Assign & Bin
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Assign Overflow -->
                        <div class="mb-4">
                            <h6>Assign Overflow to Secondary Location</h6>
                            <form id="overflowForm" class="row g-3">
                                <input type="hidden" name="pallet_id" value="<?= $viewingPallet['id'] ?>">
                                <div class="col-md-3">
                                    <label class="form-label">Crate Number</label>
                                    <input type="text" class="form-control" name="crate_number" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Part Number</label>
                                    <input type="text" class="form-control" name="part_number" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Quantity</label>
                                    <input type="number" class="form-control" name="quantity" min="1" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Location Code</label>
                                    <select class="form-select" name="secondary_location_code" required>
                                        <option value="">Select</option>
                                        <?php foreach ($locations as $location): ?>
                                            <?php if ($location['location_type'] === 'secondary'): ?>
                                                <option value="<?= e($location['location_code']) ?>">
                                                    <?= e($location['location_code']) ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-warning w-100">
                                        <i class="fas fa-exclamation-triangle"></i> Assign Overflow
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> This pallet has been binned to location: 
                            <strong><?= e($viewingPallet['location_code']) ?> - <?= e($viewingPallet['location_name']) ?></strong>
                        </div>
                    <?php endif; ?>
                    
                    <h6 class="mt-4 mb-3">Pallet Items (<?= count($palletItems) ?> items)</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Part Number</th>
                                    <th>Quantity</th>
                                    <th>Added At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($palletItems)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No items on pallet yet</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($palletItems as $item): ?>
                                        <tr>
                                            <td><strong><?= e($item['part_number']) ?></strong></td>
                                            <td><span class="badge bg-primary"><?= $item['quantity'] ?></span></td>
                                            <td><?= formatDate($item['added_at'], 'M d, Y H:i') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (!empty($secondaryLocations)): ?>
                        <h6 class="mt-4 mb-3">Secondary Locations / Overflow (<?= count($secondaryLocations) ?> items)</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Crate Number</th>
                                        <th>Part Number</th>
                                        <th>Quantity</th>
                                        <th>Location Code</th>
                                        <th>Assigned At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($secondaryLocations as $item): ?>
                                        <tr>
                                            <td><strong><?= e($item['crate_number']) ?></strong></td>
                                            <td><?= e($item['part_number']) ?></td>
                                            <td><span class="badge bg-warning"><?= $item['quantity'] ?></span></td>
                                            <td><?= e($item['secondary_location_code']) ?></td>
                                            <td><?= formatDate($item['assigned_date'], 'M d, Y H:i') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                    <a href="<?= BASE_URL ?>/public/binning.php" class="btn btn-secondary mt-3">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Create New Pallet -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-plus-circle"></i> Create New Pallet
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="create_pallet">
                        <div class="mb-3">
                            <label class="form-label">Pallet Number</label>
                            <input type="text" class="form-control scan-input" name="pallet_number" 
                                   placeholder="Scan or enter pallet number" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select Finished Shipment</label>
                            <select class="form-select" name="shipment_id" required>
                                <option value="">Choose shipment...</option>
                                <?php foreach ($finishedShipments as $shipment): ?>
                                    <option value="<?= $shipment['id'] ?>">
                                        <?= e($shipment['invoice_number']) ?> 
                                        (<?= $shipment['item_count'] ?> items, <?= $shipment['total_quantity'] ?> total qty)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-scan">
                            <i class="fas fa-plus"></i> Create Pallet
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i> Binning Instructions
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li>Create a pallet for a finished shipment</li>
                        <li>Add parts and quantities to the pallet</li>
                        <li>Assign pallet to a main location and bin</li>
                        <li>If main location exceeds capacity, assign overflow to secondary locations</li>
                        <li>Track all inventory by pallet, crate, and location</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pallets List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list"></i> All Pallets
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Pallet Number</th>
                                    <th>Invoice</th>
                                    <th>Status</th>
                                    <th>Items</th>
                                    <th>Total Qty</th>
                                    <th>Location</th>
                                    <th>Assigned To</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pallets)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No pallets found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($pallets as $pallet): ?>
                                        <tr>
                                            <td><strong><?= e($pallet['pallet_number']) ?></strong></td>
                                            <td><?= e($pallet['invoice_number'] ?? 'N/A') ?></td>
                                            <td>
                                                <span class="badge bg-<?= $pallet['status'] === 'binned' ? 'success' : 'warning' ?>">
                                                    <?= e(ucfirst($pallet['status'])) ?>
                                                </span>
                                            </td>
                                            <td><?= $pallet['item_count'] ?? 0 ?></td>
                                            <td><?= $pallet['total_quantity'] ?? 0 ?></td>
                                            <td><?= e($pallet['location_code'] ?? 'Not assigned') ?></td>
                                            <td><?= e($pallet['assigned_to_name'] ?? 'N/A') ?></td>
                                            <td><?= formatDate($pallet['created_at'], 'M d, Y') ?></td>
                                            <td>
                                                <a href="?view=<?= $pallet['id'] ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
$(document).ready(function() {
    // Handle add item to pallet
    $('#addItemForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '',
            method: 'POST',
            data: $(this).serializeArray().concat([{name: 'action', value: 'add_to_pallet'}]),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showMessage(response.message, 'success');
                    $('#addItemForm')[0].reset();
                    setTimeout(function() { location.reload(); }, 1000);
                } else {
                    showMessage(response.message, 'danger');
                }
            }
        });
    });
    
    // Handle overflow assignment
    $('#overflowForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '',
            method: 'POST',
            data: $(this).serializeArray().concat([{name: 'action', value: 'assign_overflow'}]),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showMessage(response.message, 'success');
                    $('#overflowForm')[0].reset();
                    setTimeout(function() { location.reload(); }, 1000);
                } else {
                    showMessage(response.message, 'danger');
                }
            }
        });
    });
    
    function showMessage(message, type) {
        const $msg = $('#actionMessage');
        $msg.removeClass('alert-success alert-danger alert-warning')
            .addClass('alert-' + type)
            .html('<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i> ' + message)
            .show();
        
        setTimeout(function() {
            $msg.fadeOut();
        }, 3000);
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../app/views/layouts/main.php';
?>
