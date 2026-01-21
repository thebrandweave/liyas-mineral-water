<?php

session_start();
require_once 'config/db.php';
require_once 'config/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json'); // Ensure JSON response header

    $product_id = (int)$_POST['product_id']; // Cast to integer
    $quantity = (int)$_POST['quantity'];     // Cast to integer
    $user_id = (int)$_SESSION['user_id'];   // Cast to integer

    try {
        if ($quantity > 0) {
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$quantity, $user_id, $product_id]);
            $affected_rows = $stmt->rowCount();
            echo json_encode(['success' => true, 'action' => 'updated', 'affected_rows' => $affected_rows, 'product_id' => $product_id, 'quantity' => $quantity]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
            $affected_rows = $stmt->rowCount();
            echo json_encode(['success' => true, 'action' => 'deleted', 'affected_rows' => $affected_rows, 'product_id' => $product_id]);
        }
    } catch (PDOException $e) {
        error_log("Cart update/delete error: " . $e->getMessage() . " for user_id: " . $user_id . ", product_id: " . $product_id);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
    exit; // Ensure script stops after JSON output
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid request method']);
}
