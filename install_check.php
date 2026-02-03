<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WMS Installation Check</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .check-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .check-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .check-item:last-child {
            border-bottom: none;
        }
        .check-ok {
            color: #28a745;
        }
        .check-error {
            color: #dc3545;
        }
        .check-warning {
            color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="check-container">
        <div class="card">
            <div class="card-header bg-primary text-white text-center py-4">
                <h3><i class="fas fa-warehouse"></i> WMS Installation Checker</h3>
                <p class="mb-0">Verify your system meets all requirements</p>
            </div>
            <div class="card-body">
                <?php
                $checks = [];
                
                // Check PHP version
                $phpVersion = phpversion();
                $phpOk = version_compare($phpVersion, '7.4.0', '>=');
                $checks[] = [
                    'name' => 'PHP Version',
                    'status' => $phpOk,
                    'message' => "Current: $phpVersion " . ($phpOk ? '(OK)' : '(Minimum 7.4.0 required)'),
                    'icon' => 'fa-php'
                ];
                
                // Check PDO MySQL
                $pdoOk = extension_loaded('pdo_mysql');
                $checks[] = [
                    'name' => 'PDO MySQL Extension',
                    'status' => $pdoOk,
                    'message' => $pdoOk ? 'Installed' : 'Not installed (Required)',
                    'icon' => 'fa-database'
                ];
                
                // Check database connection
                $dbOk = false;
                $dbMessage = '';
                if (file_exists(__DIR__ . '/config/database.php')) {
                    require_once __DIR__ . '/config/database.php';
                    try {
                        $pdo = getDBConnection();
                        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                        $userCount = $stmt->fetchColumn();
                        $dbOk = true;
                        $dbMessage = "Connected successfully ($userCount users found)";
                    } catch (Exception $e) {
                        $dbMessage = "Connection failed: " . $e->getMessage();
                    }
                } else {
                    $dbMessage = 'Configuration file not found';
                }
                $checks[] = [
                    'name' => 'Database Connection',
                    'status' => $dbOk,
                    'message' => $dbMessage,
                    'icon' => 'fa-server'
                ];
                
                // Check file permissions
                $writable = is_writable(__DIR__);
                $checks[] = [
                    'name' => 'Directory Permissions',
                    'status' => $writable,
                    'message' => $writable ? 'Directory is writable' : 'Directory is not writable',
                    'icon' => 'fa-folder-open'
                ];
                
                // Check Apache mod_rewrite
                $modRewriteOk = function_exists('apache_get_modules') ? in_array('mod_rewrite', apache_get_modules()) : null;
                $modRewriteMessage = $modRewriteOk === true ? 'Enabled' : ($modRewriteOk === false ? 'Not enabled' : 'Cannot detect (may be enabled)');
                $checks[] = [
                    'name' => 'Apache mod_rewrite',
                    'status' => $modRewriteOk !== false,
                    'message' => $modRewriteMessage,
                    'icon' => 'fa-cogs',
                    'warning' => $modRewriteOk === null
                ];
                
                // Check required files
                $requiredFiles = [
                    'config/config.php',
                    'config/database.php',
                    'index.php',
                    'public/login.php',
                    'database/schema.sql'
                ];
                $allFilesExist = true;
                $missingFiles = [];
                foreach ($requiredFiles as $file) {
                    if (!file_exists(__DIR__ . '/' . $file)) {
                        $allFilesExist = false;
                        $missingFiles[] = $file;
                    }
                }
                $checks[] = [
                    'name' => 'Required Files',
                    'status' => $allFilesExist,
                    'message' => $allFilesExist ? 'All files present' : 'Missing: ' . implode(', ', $missingFiles),
                    'icon' => 'fa-file-code'
                ];
                
                // Overall status
                $allOk = true;
                foreach ($checks as $check) {
                    if (!$check['status'] && empty($check['warning'])) {
                        $allOk = false;
                        break;
                    }
                }
                ?>
                
                <?php foreach ($checks as $check): ?>
                    <div class="check-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas <?= $check['icon'] ?> me-2"></i>
                            <strong><?= $check['name'] ?>:</strong>
                            <span class="ms-2"><?= $check['message'] ?></span>
                        </div>
                        <div>
                            <?php if (!empty($check['warning'])): ?>
                                <i class="fas fa-exclamation-triangle check-warning fa-2x"></i>
                            <?php elseif ($check['status']): ?>
                                <i class="fas fa-check-circle check-ok fa-2x"></i>
                            <?php else: ?>
                                <i class="fas fa-times-circle check-error fa-2x"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="mt-4 p-3 text-center <?= $allOk ? 'bg-success' : 'bg-warning' ?> text-white rounded">
                    <?php if ($allOk): ?>
                        <h5><i class="fas fa-check-circle"></i> System Ready!</h5>
                        <p class="mb-0">Your WMS installation is ready to use.</p>
                        <a href="index.php" class="btn btn-light mt-3">
                            <i class="fas fa-arrow-right"></i> Go to Application
                        </a>
                    <?php else: ?>
                        <h5><i class="fas fa-exclamation-triangle"></i> Action Required</h5>
                        <p class="mb-0">Please fix the issues above before using the system.</p>
                    <?php endif; ?>
                </div>
                
                <div class="mt-4">
                    <h6>Quick Setup Guide:</h6>
                    <ol>
                        <li>Install XAMPP and start Apache & MySQL</li>
                        <li>Import <code>database/schema.sql</code> in phpMyAdmin</li>
                        <li>Configure <code>config/database.php</code> if needed</li>
                        <li>Access the application at <code>http://localhost/warehouse-wms</code></li>
                        <li>Login with: <strong>admin</strong> / <strong>admin123</strong></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
