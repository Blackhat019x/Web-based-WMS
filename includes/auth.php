<?php
session_start();

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit();
    }
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function logActivity($conn, $user_id, $action_type, $entity_type, $entity_id, $description) {
    $stmt = $conn->prepare("INSERT INTO activity_log (user_id, action_type, entity_type, entity_id, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issis", $user_id, $action_type, $entity_type, $entity_id, $description);
    $stmt->execute();
    $stmt->close();
}
?>
