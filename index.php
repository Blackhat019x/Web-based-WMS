<?php
require_once __DIR__ . '/config/config.php';

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    redirect(BASE_URL . '/public/login.php');
}

// Check if user is logged in
if (!isLoggedIn()) {
    redirect(BASE_URL . '/public/login.php');
}

// Include dashboard
require_once __DIR__ . '/app/views/dashboard/index.php';
