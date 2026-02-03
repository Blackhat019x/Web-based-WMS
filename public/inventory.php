<?php
require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn()) {
    redirect(BASE_URL . '/public/login.php');
}

$currentPage = 'inventory';
$pageTitle = 'Inventory Overview';

$pdo = getDBConnection();

// Get inventory summary by location
$stmt = $pdo->query("
    SELECT l.location_code, l.location_name, l.location_type, l.capacity, l.current_quantity,
           COUNT(DISTINCT p.id) as pallet_count
    FROM locations l
    LEFT JOIN pallets p ON l.id = p.location_id AND p.status = 'binned'
    WHERE l.is_active = 1
    GROUP BY l.id
    ORDER BY l.location_type, l.location_code
");
$locations = $stmt->fetchAll();

// Get inventory by part number
$stmt = $pdo->query("
    SELECT pi.part_number, 
           SUM(pi.quantity) as main_quantity,
           l.location_code
    FROM pallet_items pi
    JOIN pallets p ON pi.pallet_id = p.id
    LEFT JOIN locations l ON p.location_id = l.id
    WHERE p.status = 'binned'
    GROUP BY pi.part_number, l.location_code
    ORDER BY pi.part_number
");
$partInventory = $stmt->fetchAll();

// Get secondary locations inventory
$stmt = $pdo->query("
    SELECT part_number, SUM(quantity) as quantity, secondary_location_code
    FROM secondary_locations
    GROUP BY part_number, secondary_location_code
    ORDER BY part_number
");
$secondaryInventory = $stmt->fetchAll();

// Get total inventory stats
$stmt = $pdo->query("
    SELECT 
        COUNT(DISTINCT pi.part_number) as unique_parts,
        SUM(pi.quantity) as total_main_quantity
    FROM pallet_items pi
    JOIN pallets p ON pi.pallet_id = p.id
    WHERE p.status = 'binned'
");
$mainStats = $stmt->fetch();

$stmt = $pdo->query("
    SELECT 
        COUNT(DISTINCT part_number) as unique_parts,
        SUM(quantity) as total_quantity
    FROM secondary_locations
");
$secondaryStats = $stmt->fetch();

ob_start();
?>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon text-primary">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="kpi-value"><?= $mainStats['unique_parts'] ?? 0 ?></div>
            <div class="kpi-label">Unique Parts (Main)</div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="kpi-card" style="border-left-color: #28a745;">
            <div class="kpi-icon text-success">
                <i class="fas fa-cubes"></i>
            </div>
            <div class="kpi-value"><?= $mainStats['total_main_quantity'] ?? 0 ?></div>
            <div class="kpi-label">Total Qty (Main)</div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="kpi-card" style="border-left-color: #ffc107;">
            <div class="kpi-icon text-warning">
                <i class="fas fa-box-open"></i>
            </div>
            <div class="kpi-value"><?= $secondaryStats['unique_parts'] ?? 0 ?></div>
            <div class="kpi-label">Unique Parts (Secondary)</div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="kpi-card" style="border-left-color: #17a2b8;">
            <div class="kpi-icon text-info">
                <i class="fas fa-warehouse"></i>
            </div>
            <div class="kpi-value"><?= $secondaryStats['total_quantity'] ?? 0 ?></div>
            <div class="kpi-label">Total Qty (Secondary)</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-map-marker-alt"></i> Locations Status
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Location Code</th>
                                <th>Location Name</th>
                                <th>Type</th>
                                <th>Capacity</th>
                                <th>Current Qty</th>
                                <th>Utilization</th>
                                <th>Pallets</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($locations as $location): ?>
                                <?php 
                                $utilization = $location['capacity'] > 0 
                                    ? ($location['current_quantity'] / $location['capacity']) * 100 
                                    : 0;
                                $utilizationClass = $utilization >= 90 ? 'danger' : ($utilization >= 70 ? 'warning' : 'success');
                                ?>
                                <tr>
                                    <td><strong><?= e($location['location_code']) ?></strong></td>
                                    <td><?= e($location['location_name']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $location['location_type'] === 'main' ? 'primary' : 'secondary' ?>">
                                            <?= e(ucfirst($location['location_type'])) ?>
                                        </span>
                                    </td>
                                    <td><?= $location['capacity'] ?></td>
                                    <td><?= $location['current_quantity'] ?></td>
                                    <td>
                                        <div class="progress" style="height: 25px;">
                                            <div class="progress-bar bg-<?= $utilizationClass ?>" 
                                                 role="progressbar" 
                                                 style="width: <?= min($utilization, 100) ?>%"
                                                 aria-valuenow="<?= $utilization ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                <?= number_format($utilization, 1) ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= $location['pallet_count'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-warehouse"></i> Main Location Inventory
            </div>
            <div class="card-body">
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-sm table-hover">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Part Number</th>
                                <th>Location</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($partInventory)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No inventory in main locations</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($partInventory as $item): ?>
                                    <tr>
                                        <td><strong><?= e($item['part_number']) ?></strong></td>
                                        <td><?= e($item['location_code'] ?? 'Not assigned') ?></td>
                                        <td><span class="badge bg-primary"><?= $item['main_quantity'] ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-box-open"></i> Secondary Location Inventory (Overflow)
            </div>
            <div class="card-body">
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-sm table-hover">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Part Number</th>
                                <th>Location Code</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($secondaryInventory)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No inventory in secondary locations</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($secondaryInventory as $item): ?>
                                    <tr>
                                        <td><strong><?= e($item['part_number']) ?></strong></td>
                                        <td><?= e($item['secondary_location_code']) ?></td>
                                        <td><span class="badge bg-warning"><?= $item['quantity'] ?></span></td>
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

<?php
$content = ob_get_clean();
include __DIR__ . '/../app/views/layouts/main.php';
?>
