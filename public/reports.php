<?php
require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn()) {
    redirect(BASE_URL . '/public/login.php');
}

$currentPage = 'reports';
$pageTitle = 'Reports & Analytics';

$pdo = getDBConnection();

// Get report data
$dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$dateTo = $_GET['date_to'] ?? date('Y-m-d');

// Receiving statistics
$stmt = $pdo->prepare("
    SELECT 
        DATE(received_date) as date,
        COUNT(*) as shipment_count,
        COUNT(DISTINCT received_by) as receivers,
        SUM((SELECT COUNT(*) FROM shipment_items WHERE shipment_id = shipments.id)) as total_items
    FROM shipments
    WHERE DATE(received_date) BETWEEN ? AND ?
    GROUP BY DATE(received_date)
    ORDER BY date DESC
");
$stmt->execute([$dateFrom, $dateTo]);
$receivingStats = $stmt->fetchAll();

// Binning statistics
$stmt = $pdo->prepare("
    SELECT 
        DATE(binned_date) as date,
        COUNT(*) as pallet_count,
        COUNT(DISTINCT binned_by) as binners,
        SUM((SELECT SUM(quantity) FROM pallet_items WHERE pallet_id = pallets.id)) as total_quantity
    FROM pallets
    WHERE status = 'binned' AND DATE(binned_date) BETWEEN ? AND ?
    GROUP BY DATE(binned_date)
    ORDER BY date DESC
");
$stmt->execute([$dateFrom, $dateTo]);
$binningStats = $stmt->fetchAll();

// Top parts by quantity
$stmt = $pdo->query("
    SELECT part_number, SUM(quantity) as total_quantity
    FROM pallet_items pi
    JOIN pallets p ON pi.pallet_id = p.id
    WHERE p.status = 'binned'
    GROUP BY part_number
    ORDER BY total_quantity DESC
    LIMIT 10
");
$topParts = $stmt->fetchAll();

// User activity summary
$stmt = $pdo->prepare("
    SELECT 
        u.full_name,
        COUNT(DISTINCT al.id) as activity_count,
        COUNT(DISTINCT CASE WHEN al.module = 'receiving' THEN al.id END) as receiving_count,
        COUNT(DISTINCT CASE WHEN al.module = 'binning' THEN al.id END) as binning_count
    FROM users u
    LEFT JOIN activity_logs al ON u.id = al.user_id AND DATE(al.created_at) BETWEEN ? AND ?
    WHERE u.is_active = 1
    GROUP BY u.id
    ORDER BY activity_count DESC
");
$stmt->execute([$dateFrom, $dateTo]);
$userActivity = $stmt->fetchAll();

ob_start();
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-filter"></i> Report Filters
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Date From</label>
                        <input type="date" class="form-control" name="date_from" value="<?= e($dateFrom) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date To</label>
                        <input type="date" class="form-control" name="date_to" value="<?= e($dateTo) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-scan w-100">
                            <i class="fas fa-search"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-truck-loading"></i> Receiving Statistics
            </div>
            <div class="card-body">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Date</th>
                                <th>Shipments</th>
                                <th>Receivers</th>
                                <th>Items</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($receivingStats)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No data for selected period</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($receivingStats as $stat): ?>
                                    <tr>
                                        <td><?= formatDate($stat['date'], 'M d, Y') ?></td>
                                        <td><span class="badge bg-primary"><?= $stat['shipment_count'] ?></span></td>
                                        <td><?= $stat['receivers'] ?></td>
                                        <td><?= $stat['total_items'] ?></td>
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
                <i class="fas fa-box"></i> Binning Statistics
            </div>
            <div class="card-body">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Date</th>
                                <th>Pallets</th>
                                <th>Binners</th>
                                <th>Total Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($binningStats)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No data for selected period</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($binningStats as $stat): ?>
                                    <tr>
                                        <td><?= formatDate($stat['date'], 'M d, Y') ?></td>
                                        <td><span class="badge bg-success"><?= $stat['pallet_count'] ?></span></td>
                                        <td><?= $stat['binners'] ?></td>
                                        <td><?= $stat['total_quantity'] ?></td>
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

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-bar"></i> Top 10 Parts by Quantity
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Part Number</th>
                                <th>Total Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($topParts)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No data available</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($topParts as $index => $part): ?>
                                    <tr>
                                        <td><strong>#<?= $index + 1 ?></strong></td>
                                        <td><?= e($part['part_number']) ?></td>
                                        <td><span class="badge bg-primary"><?= $part['total_quantity'] ?></span></td>
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
                <i class="fas fa-users"></i> User Activity Summary
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Total</th>
                                <th>Receiving</th>
                                <th>Binning</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($userActivity)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No activity data</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($userActivity as $user): ?>
                                    <tr>
                                        <td><strong><?= e($user['full_name']) ?></strong></td>
                                        <td><span class="badge bg-secondary"><?= $user['activity_count'] ?></span></td>
                                        <td><span class="badge bg-info"><?= $user['receiving_count'] ?></span></td>
                                        <td><span class="badge bg-success"><?= $user['binning_count'] ?></span></td>
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
