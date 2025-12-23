<?php
require_once '../../config/config.php';
session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

if (!$admin_id) {
    die("Error: No admin session found. Please log in first.");
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO notifications (recipient_type, admin_id, title, message, type, is_read) 
        VALUES ('admin', ?, 'System Test', 'This is a test notification generated at ' + NOW(), 'system', 0)
    ");
    $stmt->execute([$admin_id]);
    
    echo "Success! A test notification has been created for Admin ID: " . $admin_id;
    echo "<br><a href='index.php'>Go back to Notifications</a>";
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
?>