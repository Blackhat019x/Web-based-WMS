<?php
/**
 * Application Configuration
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Application settings
define('APP_NAME', 'Warehouse Management System');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/warehouse-wms');

// Path settings
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('CONFIG_PATH', ROOT_PATH . '/config');

// Timezone
date_default_timezone_set('UTC');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once CONFIG_PATH . '/database.php';

/**
 * Autoloader for classes
 */
spl_autoload_register(function ($class) {
    $paths = [
        APP_PATH . '/controllers/' . $class . '.php',
        APP_PATH . '/models/' . $class . '.php',
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id, username, full_name, email, role FROM users WHERE id = ?");
    $stmt->execute([getCurrentUserId()]);
    return $stmt->fetch();
}

/**
 * Check if user has required role
 */
function hasRole($role) {
    $user = getCurrentUser();
    if (!$user) {
        return false;
    }
    
    // Admin has access to everything
    if ($user['role'] === 'admin') {
        return true;
    }
    
    return $user['role'] === $role;
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Log activity
 */
function logActivity($activityType, $module, $description, $metadata = null) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, activity_type, module, description, metadata, ip_address) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            getCurrentUserId(),
            $activityType,
            $module,
            $description,
            $metadata ? json_encode($metadata) : null,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

/**
 * Sanitize output
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format date
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    if (empty($date)) {
        return '-';
    }
    return date($format, strtotime($date));
}
