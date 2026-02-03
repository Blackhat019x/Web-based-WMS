<?php
require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn()) {
    redirect(BASE_URL . '/public/login.php');
}

$currentPage = 'receiving';
$pageTitle = 'Receiving';

$pdo = getDBConnection();
$message = '';
$messageType = '';

// Handle creating new shipment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_shipment') {
        $invoiceNumber = trim($_POST['invoice_number'] ?? '');
        
        if (empty($invoiceNumber)) {
            $message = 'Invoice number is required';
            $messageType = 'danger';
        } else {
            // Check if invoice already exists
            $stmt = $pdo->prepare("SELECT id FROM shipments WHERE invoice_number = ?");
            $stmt->execute([$invoiceNumber]);
            
            if ($stmt->fetch()) {
                $message = 'Invoice number already exists';
                $messageType = 'danger';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO shipments (invoice_number, status, received_by) 
                    VALUES (?, 'pending', ?)
                ");
                
                if ($stmt->execute([$invoiceNumber, getCurrentUserId()])) {
                    logActivity('create', 'receiving', "Created new shipment: $invoiceNumber");
                    $message = 'Shipment created successfully';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to create shipment';
                    $messageType = 'danger';
                }
            }
        }
    }
    
    // Handle scanning part
    elseif ($_POST['action'] === 'scan_part') {
        $shipmentId = intval($_POST['shipment_id'] ?? 0);
        $barcode = trim($_POST['barcode'] ?? '');
        $partNumber = trim($_POST['part_number'] ?? '');
        
        if ($shipmentId && $barcode) {
            // Check if shipment exists and is not finished
            $stmt = $pdo->prepare("SELECT * FROM shipments WHERE id = ?");
            $stmt->execute([$shipmentId]);
            $shipment = $stmt->fetch();
            
            if (!$shipment) {
                echo json_encode(['success' => false, 'message' => 'Shipment not found']);
                exit;
            }
            
            if ($shipment['status'] === 'finished_checking') {
                echo json_encode(['success' => false, 'message' => 'Shipment already finished']);
                exit;
            }
            
            // Update shipment status to ongoing if it's pending
            if ($shipment['status'] === 'pending') {
                $updateStmt = $pdo->prepare("UPDATE shipments SET status = 'ongoing_checking' WHERE id = ?");
                $updateStmt->execute([$shipmentId]);
            }
            
            // Check if part already exists in this shipment
            $stmt = $pdo->prepare("
                SELECT * FROM shipment_items 
                WHERE shipment_id = ? AND barcode = ?
            ");
            $stmt->execute([$shipmentId, $barcode]);
            $existingItem = $stmt->fetch();
            
            if ($existingItem) {
                // Increment quantity
                $updateStmt = $pdo->prepare("
                    UPDATE shipment_items 
                    SET quantity = quantity + 1 
                    WHERE id = ?
                ");
                $updateStmt->execute([$existingItem['id']]);
                
                logActivity('scan', 'receiving', "Scanned part: $partNumber (qty incremented)");
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Part scanned - Quantity incremented',
                    'quantity' => $existingItem['quantity'] + 1
                ]);
            } else {
                // Insert new item
                $insertStmt = $pdo->prepare("
                    INSERT INTO shipment_items (shipment_id, part_number, barcode, quantity, scanned_by) 
                    VALUES (?, ?, ?, 1, ?)
                ");
                
                if ($insertStmt->execute([$shipmentId, $partNumber, $barcode, getCurrentUserId()])) {
                    logActivity('scan', 'receiving', "Scanned new part: $partNumber");
                    
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Part scanned successfully',
                        'quantity' => 1
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to scan part']);
                }
            }
            exit;
        }
    }
    
    // Handle finish checking
    elseif ($_POST['action'] === 'finish_checking') {
        $shipmentId = intval($_POST['shipment_id'] ?? 0);
        
        if ($shipmentId) {
            $stmt = $pdo->prepare("
                UPDATE shipments 
                SET status = 'finished_checking', finished_date = NOW() 
                WHERE id = ?
            ");
            
            if ($stmt->execute([$shipmentId])) {
                logActivity('finish', 'receiving', "Finished checking shipment ID: $shipmentId");
                $message = 'Shipment checking completed';
                $messageType = 'success';
            } else {
                $message = 'Failed to finish checking';
                $messageType = 'danger';
            }
        }
    }
}

// Get all shipments
$stmt = $pdo->query("
    SELECT s.*, u.full_name as received_by_name,
           COUNT(si.id) as item_count,
           SUM(si.quantity) as total_quantity
    FROM shipments s
    LEFT JOIN users u ON s.received_by = u.id
    LEFT JOIN shipment_items si ON s.id = si.shipment_id
    GROUP BY s.id
    ORDER BY s.received_date DESC
");
$shipments = $stmt->fetchAll();

// Get shipment details if viewing
$viewingShipment = null;
$shipmentItems = [];
if (isset($_GET['view'])) {
    $shipmentId = intval($_GET['view']);
    $stmt = $pdo->prepare("SELECT * FROM shipments WHERE id = ?");
    $stmt->execute([$shipmentId]);
    $viewingShipment = $stmt->fetch();
    
    if ($viewingShipment) {
        $stmt = $pdo->prepare("
            SELECT * FROM shipment_items 
            WHERE shipment_id = ? 
            ORDER BY scanned_at DESC
        ");
        $stmt->execute([$shipmentId]);
        $shipmentItems = $stmt->fetchAll();
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

<?php if ($viewingShipment): ?>
    <!-- Scanning Interface -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-barcode"></i> Scanning: <?= e($viewingShipment['invoice_number']) ?>
                    </h5>
                    <span class="badge bg-light text-dark">
                        Status: <?= e(ucwords(str_replace('_', ' ', $viewingShipment['status']))) ?>
                    </span>
                </div>
                <div class="card-body">
                    <?php if ($viewingShipment['status'] !== 'finished_checking'): ?>
                        <form id="scanForm" class="mb-4">
                            <input type="hidden" name="shipment_id" value="<?= $viewingShipment['id'] ?>">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">Scan Barcode</label>
                                    <input type="text" class="form-control scan-input" name="barcode" 
                                           id="barcodeInput" placeholder="Scan or enter barcode" autofocus>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Part Number</label>
                                    <input type="text" class="form-control scan-input" name="part_number" 
                                           id="partNumberInput" placeholder="Enter part number">
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-scan btn-lg">
                                    <i class="fas fa-barcode"></i> Scan Part
                                </button>
                                <button type="button" class="btn btn-success btn-lg" id="finishBtn">
                                    <i class="fas fa-check"></i> Finish Checking
                                </button>
                            </div>
                        </form>
                        
                        <div id="scanMessage" class="alert" style="display: none;"></div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> This shipment has been finished and is ready for binning.
                        </div>
                    <?php endif; ?>
                    
                    <h6 class="mt-4 mb-3">Scanned Items (<?= count($shipmentItems) ?> items, <?= array_sum(array_column($shipmentItems, 'quantity')) ?> total qty)</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Part Number</th>
                                    <th>Barcode</th>
                                    <th>Quantity</th>
                                    <th>Scanned At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($shipmentItems)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No items scanned yet</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($shipmentItems as $item): ?>
                                        <tr>
                                            <td><strong><?= e($item['part_number']) ?></strong></td>
                                            <td><?= e($item['barcode']) ?></td>
                                            <td><span class="badge bg-primary"><?= $item['quantity'] ?></span></td>
                                            <td><?= formatDate($item['scanned_at'], 'M d, Y H:i:s') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <a href="<?= BASE_URL ?>/public/receiving.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Create New Shipment -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-plus-circle"></i> Create New Shipment
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="create_shipment">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Invoice Number</label>
                            <input type="text" class="form-control scan-input" name="invoice_number" 
                                   placeholder="Scan or enter invoice number" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-scan">
                            <i class="fas fa-plus"></i> Create Shipment
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i> Instructions
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li>Create a new shipment by scanning or entering the invoice number</li>
                        <li>Click on the shipment to start scanning parts</li>
                        <li>Scan each part's barcode (auto-increments quantity for duplicates)</li>
                        <li>Click "Finish Checking" when done</li>
                        <li>Finished shipments can proceed to Binning</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Shipments List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list"></i> All Shipments
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Invoice Number</th>
                                    <th>Status</th>
                                    <th>Items</th>
                                    <th>Total Qty</th>
                                    <th>Received By</th>
                                    <th>Received Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($shipments)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No shipments found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($shipments as $shipment): ?>
                                        <tr>
                                            <td><strong><?= e($shipment['invoice_number']) ?></strong></td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'pending' => 'status-pending',
                                                    'ongoing_checking' => 'status-ongoing',
                                                    'finished_checking' => 'status-finished'
                                                ];
                                                $statusLabel = ucwords(str_replace('_', ' ', $shipment['status']));
                                                ?>
                                                <span class="badge <?= $statusClass[$shipment['status']] ?>">
                                                    <?= $statusLabel ?>
                                                </span>
                                            </td>
                                            <td><?= $shipment['item_count'] ?? 0 ?></td>
                                            <td><?= $shipment['total_quantity'] ?? 0 ?></td>
                                            <td><?= e($shipment['received_by_name'] ?? 'N/A') ?></td>
                                            <td><?= formatDate($shipment['received_date'], 'M d, Y H:i') ?></td>
                                            <td>
                                                <a href="?view=<?= $shipment['id'] ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> View/Scan
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
    // Handle scan form submission
    $('#scanForm').on('submit', function(e) {
        e.preventDefault();
        
        const barcode = $('#barcodeInput').val().trim();
        const partNumber = $('#partNumberInput').val().trim();
        
        if (!barcode) {
            showMessage('Please scan or enter a barcode', 'danger');
            return;
        }
        
        if (!partNumber) {
            showMessage('Please enter a part number', 'danger');
            return;
        }
        
        $.ajax({
            url: '',
            method: 'POST',
            data: {
                action: 'scan_part',
                shipment_id: $('input[name="shipment_id"]').val(),
                barcode: barcode,
                part_number: partNumber
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showMessage(response.message, 'success');
                    $('#barcodeInput').val('');
                    $('#partNumberInput').val('');
                    $('#barcodeInput').focus();
                    
                    // Reload page after 1 second to show updated items
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showMessage(response.message, 'danger');
                }
            },
            error: function() {
                showMessage('Failed to process scan', 'danger');
            }
        });
    });
    
    // Handle finish checking
    $('#finishBtn').on('click', function() {
        if (confirm('Are you sure you want to finish checking this shipment?')) {
            const form = $('<form method="POST">' +
                '<input type="hidden" name="action" value="finish_checking">' +
                '<input type="hidden" name="shipment_id" value="' + $('input[name="shipment_id"]').val() + '">' +
                '</form>');
            $('body').append(form);
            form.submit();
        }
    });
    
    function showMessage(message, type) {
        const $msg = $('#scanMessage');
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
