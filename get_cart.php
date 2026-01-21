<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/config/config.php';

if (!isset($_SESSION['user_id'])) {
    // Return a clear error message in JSON format
    header('Content-Type: application/json');
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// For debugging: let's see if we get the user_id
// echo json_encode(['debug_user_id' => $user_id]);
// exit;

$stmt = $pdo->prepare("
    SELECT p.product_id, p.name, p.price, p.image, ci.quantity
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.product_id
    WHERE ci.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepend the full path to the image filenames
foreach ($cart_items as &$item) {
    $item['image'] = BASE_URL . '/admin/uploads/products/' . $item['image'];
}

header('Content-Type: application/json');
echo json_encode($cart_items);
