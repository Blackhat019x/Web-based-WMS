<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            color: white;
            overflow-y: auto;
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h4 {
            margin: 0;
            font-size: 1.2rem;
        }
        
        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }
        
        .nav-menu li a {
            display: block;
            padding: 15px 25px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .nav-menu li a:hover,
        .nav-menu li a.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
            border-left: 4px solid white;
            padding-left: 21px;
        }
        
        .nav-menu li a i {
            width: 25px;
            margin-right: 10px;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 0;
        }
        
        .top-navbar {
            background-color: white;
            padding: 15px 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .content-area {
            padding: 30px;
        }
        
        .kpi-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: transform 0.3s;
            border-left: 4px solid var(--primary-color);
        }
        
        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .kpi-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .kpi-value {
            font-size: 2rem;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .kpi-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .btn-scan {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
        }
        
        .btn-scan:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .scan-input {
            font-size: 1.5rem;
            padding: 15px;
            border: 2px solid #667eea;
            border-radius: 8px;
        }
        
        .scan-input:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 2px solid #f0f0f0;
            font-weight: 600;
            padding: 20px;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: #ffc107;
            color: #000;
        }
        
        .status-ongoing {
            background-color: #17a2b8;
            color: white;
        }
        
        .status-finished {
            background-color: #28a745;
            color: white;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-warehouse"></i> WMS</h4>
            <small>Warehouse Management</small>
        </div>
        
        <ul class="nav-menu">
            <li>
                <a href="<?= BASE_URL ?>/index.php" class="<?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/public/receiving.php" class="<?= ($currentPage ?? '') === 'receiving' ? 'active' : '' ?>">
                    <i class="fas fa-truck-loading"></i> Receiving
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/public/binning.php" class="<?= ($currentPage ?? '') === 'binning' ? 'active' : '' ?>">
                    <i class="fas fa-box"></i> Binning
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/public/inventory.php" class="<?= ($currentPage ?? '') === 'inventory' ? 'active' : '' ?>">
                    <i class="fas fa-warehouse"></i> Inventory
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/public/releasing.php" class="<?= ($currentPage ?? '') === 'releasing' ? 'active' : '' ?>">
                    <i class="fas fa-shipping-fast"></i> Releasing
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/public/reports.php" class="<?= ($currentPage ?? '') === 'reports' ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
            </li>
            <?php if (hasRole('admin')): ?>
            <li>
                <a href="<?= BASE_URL ?>/public/settings.php" class="<?= ($currentPage ?? '') === 'settings' ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div>
                <h5 class="mb-0"><?= $pageTitle ?? 'Dashboard' ?></h5>
            </div>
            <div class="user-info">
                <div>
                    <div><strong><?= e($_SESSION['full_name'] ?? 'User') ?></strong></div>
                    <small class="text-muted"><?= e(ucfirst($_SESSION['role'] ?? 'user')) ?></small>
                </div>
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)) ?>
                </div>
                <a href="<?= BASE_URL ?>/index.php?logout=1" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
            <?php if (isset($contentFile) && file_exists($contentFile)): ?>
                <?php include $contentFile; ?>
            <?php else: ?>
                <!-- Page content will be inserted here -->
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="<?= BASE_URL ?>/public/js/main.js"></script>
</body>
</html>
