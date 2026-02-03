<?php
require_once __DIR__ . '/../../../config/config.php';

if (!isLoggedIn()) {
    redirect(BASE_URL . '/public/login.php');
}

$currentPage = 'dashboard';
$pageTitle = 'Dashboard';

// Get dashboard statistics
$pdo = getDBConnection();

// Total shipments today
$stmt = $pdo->query("
    SELECT COUNT(*) as count 
    FROM shipments 
    WHERE DATE(received_date) = CURDATE()
");
$shipmentsToday = $stmt->fetch()['count'];

// Pending receivings
$stmt = $pdo->query("
    SELECT COUNT(*) as count 
    FROM shipments 
    WHERE status = 'pending'
");
$pendingReceivings = $stmt->fetch()['count'];

// Ongoing checking
$stmt = $pdo->query("
    SELECT COUNT(*) as count 
    FROM shipments 
    WHERE status = 'ongoing_checking'
");
$ongoingChecking = $stmt->fetch()['count'];

// Finished receivings
$stmt = $pdo->query("
    SELECT COUNT(*) as count 
    FROM shipments 
    WHERE status = 'finished_checking'
");
$finishedReceivings = $stmt->fetch()['count'];

// Pallets pending binning
$stmt = $pdo->query("
    SELECT COUNT(*) as count 
    FROM pallets 
    WHERE status = 'pending'
");
$palletsPending = $stmt->fetch()['count'];

// Inventory in secondary locations
$stmt = $pdo->query("
    SELECT SUM(quantity) as count 
    FROM secondary_locations
");
$secondaryInventory = $stmt->fetch()['count'] ?? 0;

// Recent activity logs
$stmt = $pdo->query("
    SELECT al.*, u.full_name 
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT 10
");
$recentActivities = $stmt->fetchAll();

// Daily receiving volume (last 7 days)
$stmt = $pdo->query("
    SELECT DATE(received_date) as date, COUNT(*) as count
    FROM shipments
    WHERE received_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(received_date)
    ORDER BY date ASC
");
$dailyReceiving = $stmt->fetchAll();

// Inventory status breakdown
$stmt = $pdo->query("
    SELECT status, COUNT(*) as count
    FROM pallets
    GROUP BY status
");
$inventoryStatus = $stmt->fetchAll();

ob_start();
?>

<div class="row g-4 mb-4">
    <!-- KPI Cards -->
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon text-primary">
                <i class="fas fa-truck-loading"></i>
            </div>
            <div class="kpi-value"><?= $shipmentsToday ?></div>
            <div class="kpi-label">Total Shipments Today</div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="kpi-card" style="border-left-color: #ffc107;">
            <div class="kpi-icon text-warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="kpi-value"><?= $pendingReceivings ?></div>
            <div class="kpi-label">Pending Receivings</div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="kpi-card" style="border-left-color: #17a2b8;">
            <div class="kpi-icon text-info">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="kpi-value"><?= $ongoingChecking ?></div>
            <div class="kpi-label">Ongoing Checking</div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="kpi-card" style="border-left-color: #28a745;">
            <div class="kpi-icon text-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="kpi-value"><?= $finishedReceivings ?></div>
            <div class="kpi-label">Finished Receivings</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="kpi-card" style="border-left-color: #dc3545;">
            <div class="kpi-icon text-danger">
                <i class="fas fa-pallet"></i>
            </div>
            <div class="kpi-value"><?= $palletsPending ?></div>
            <div class="kpi-label">Pallets Pending Binning</div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="kpi-card" style="border-left-color: #6f42c1;">
            <div class="kpi-icon" style="color: #6f42c1;">
                <i class="fas fa-warehouse"></i>
            </div>
            <div class="kpi-value"><?= $secondaryInventory ?></div>
            <div class="kpi-label">Items in Secondary Locations</div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center justify-content-center">
                <div class="text-center">
                    <h5 class="mb-3">Quick Actions</h5>
                    <div class="d-flex gap-2 flex-wrap justify-content-center">
                        <a href="<?= BASE_URL ?>/public/receiving.php" class="btn btn-scan">
                            <i class="fas fa-truck-loading"></i> New Receiving
                        </a>
                        <a href="<?= BASE_URL ?>/public/binning.php" class="btn btn-scan">
                            <i class="fas fa-box"></i> Start Binning
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Daily Receiving Volume Chart -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-line"></i> Daily Receiving Volume (Last 7 Days)
            </div>
            <div class="card-body">
                <canvas id="dailyReceivingChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Inventory Status Breakdown Chart -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-pie"></i> Inventory Status Breakdown
            </div>
            <div class="card-body">
                <canvas id="inventoryStatusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Log -->
<div class="row g-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history"></i> Recent Activity
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>User</th>
                                <th>Module</th>
                                <th>Activity</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentActivities)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No recent activity</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentActivities as $activity): ?>
                                    <tr>
                                        <td><?= formatDate($activity['created_at'], 'M d, H:i') ?></td>
                                        <td><?= e($activity['full_name'] ?? 'System') ?></td>
                                        <td>
                                            <span class="badge bg-secondary"><?= e(ucfirst($activity['module'])) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?= e($activity['activity_type']) ?></span>
                                        </td>
                                        <td><?= e($activity['description']) ?></td>
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

<script>
// Daily Receiving Volume Chart
const dailyReceivingCtx = document.getElementById('dailyReceivingChart').getContext('2d');
new Chart(dailyReceivingCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($dailyReceiving, 'date')) ?>,
        datasets: [{
            label: 'Shipments Received',
            data: <?= json_encode(array_column($dailyReceiving, 'count')) ?>,
            borderColor: 'rgb(102, 126, 234)',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});

// Inventory Status Chart
const inventoryStatusCtx = document.getElementById('inventoryStatusChart').getContext('2d');
new Chart(inventoryStatusCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($inventoryStatus, 'status')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($inventoryStatus, 'count')) ?>,
            backgroundColor: [
                'rgba(255, 193, 7, 0.8)',
                'rgba(40, 167, 69, 0.8)',
                'rgba(102, 126, 234, 0.8)'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
