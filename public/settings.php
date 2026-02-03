<?php
require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect(BASE_URL . '/index.php');
}

$currentPage = 'settings';
$pageTitle = 'Settings';

$pdo = getDBConnection();
$message = '';
$messageType = '';

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_user') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'receiver';
        
        if (empty($username) || empty($password) || empty($fullName) || empty($email)) {
            $message = 'All fields are required';
            $messageType = 'danger';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                $message = 'Username or email already exists';
                $messageType = 'danger';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, password, full_name, email, role) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                if ($stmt->execute([$username, $hashedPassword, $fullName, $email, $role])) {
                    $message = 'User created successfully';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to create user';
                    $messageType = 'danger';
                }
            }
        }
    }
    
    elseif ($_POST['action'] === 'create_location') {
        $locationCode = trim($_POST['location_code'] ?? '');
        $locationName = trim($_POST['location_name'] ?? '');
        $locationType = $_POST['location_type'] ?? 'main';
        $capacity = intval($_POST['capacity'] ?? 0);
        $zone = trim($_POST['zone'] ?? '');
        $aisle = trim($_POST['aisle'] ?? '');
        $rack = trim($_POST['rack'] ?? '');
        $level = trim($_POST['level'] ?? '');
        
        if (empty($locationCode) || empty($locationName)) {
            $message = 'Location code and name are required';
            $messageType = 'danger';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM locations WHERE location_code = ?");
            $stmt->execute([$locationCode]);
            
            if ($stmt->fetch()) {
                $message = 'Location code already exists';
                $messageType = 'danger';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO locations (location_code, location_name, location_type, capacity, zone, aisle, rack, level) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                if ($stmt->execute([$locationCode, $locationName, $locationType, $capacity, $zone, $aisle, $rack, $level])) {
                    $message = 'Location created successfully';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to create location';
                    $messageType = 'danger';
                }
            }
        }
    }
}

// Get all users
$users = $pdo->query("
    SELECT id, username, full_name, email, role, is_active, created_at, last_login
    FROM users
    ORDER BY created_at DESC
")->fetchAll();

// Get all locations
$locations = $pdo->query("
    SELECT * FROM locations 
    ORDER BY location_type, location_code
")->fetchAll();

ob_start();
?>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
        <?= e($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-plus"></i> Create New User
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="create_user">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" required>
                            <option value="receiver">Receiver</option>
                            <option value="binner">Binner</option>
                            <option value="inventory_controller">Inventory Controller</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-scan">
                        <i class="fas fa-plus"></i> Create User
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-map-marker-alt"></i> Create New Location
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="create_location">
                    <div class="mb-3">
                        <label class="form-label">Location Code</label>
                        <input type="text" class="form-control" name="location_code" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location Name</label>
                        <input type="text" class="form-control" name="location_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-select" name="location_type" required>
                            <option value="main">Main</option>
                            <option value="secondary">Secondary</option>
                            <option value="overflow">Overflow</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Capacity</label>
                        <input type="number" class="form-control" name="capacity" min="0" value="100" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Zone</label>
                            <input type="text" class="form-control" name="zone">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Aisle</label>
                            <input type="text" class="form-control" name="aisle">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Rack</label>
                            <input type="text" class="form-control" name="rack">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Level</label>
                            <input type="text" class="form-control" name="level">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-scan">
                        <i class="fas fa-plus"></i> Create Location
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-users"></i> User Management
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Last Login</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><strong><?= e($user['username']) ?></strong></td>
                                    <td><?= e($user['full_name']) ?></td>
                                    <td><?= e($user['email']) ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= e(ucfirst(str_replace('_', ' ', $user['role']))) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $user['is_active'] ? 'success' : 'danger' ?>">
                                            <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td><?= formatDate($user['created_at'], 'M d, Y') ?></td>
                                    <td><?= formatDate($user['last_login'], 'M d, Y H:i') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-map-marked-alt"></i> Location Management
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Location Code</th>
                                <th>Location Name</th>
                                <th>Type</th>
                                <th>Zone</th>
                                <th>Aisle-Rack-Level</th>
                                <th>Capacity</th>
                                <th>Current</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($locations as $location): ?>
                                <tr>
                                    <td><strong><?= e($location['location_code']) ?></strong></td>
                                    <td><?= e($location['location_name']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $location['location_type'] === 'main' ? 'primary' : 'secondary' ?>">
                                            <?= e(ucfirst($location['location_type'])) ?>
                                        </span>
                                    </td>
                                    <td><?= e($location['zone']) ?></td>
                                    <td><?= e($location['aisle']) ?>-<?= e($location['rack']) ?>-<?= e($location['level']) ?></td>
                                    <td><?= $location['capacity'] ?></td>
                                    <td><?= $location['current_quantity'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $location['is_active'] ? 'success' : 'danger' ?>">
                                            <?= $location['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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
