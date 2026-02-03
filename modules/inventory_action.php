<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

checkAuth();

header('Content-Type: application/json');

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'mark_printed') {
        $inventory_id = intval($_POST['inventory_id']);
        
        $stmt = $conn->prepare("UPDATE inventory SET barcode_printed = 1 WHERE inventory_id = ?");
        $stmt->bind_param("i", $inventory_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
        $stmt->close();
    }
}

$conn->close();
?>
