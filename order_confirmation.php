<?php
session_start();
require_once __DIR__ . '/config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/login/index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = filter_var($_GET['order_id'] ?? '', FILTER_VALIDATE_INT);
$order_details = null;
$order_items = [];
$shipping_address = null;

if (!$order_id) {
    // If no order_id is provided, redirect to products or order history
    header("Location: " . BASE_URL . "/products.php"); 
    exit;
}

try {
    // Fetch order details
    $stmt = $pdo->prepare("SELECT o.*, sa.full_name as sa_full_name, sa.address_line_1, sa.address_line_2, sa.city, sa.state, sa.zip_code, sa.country, sa.phone_number FROM orders o JOIN shipping_addresses sa ON o.shipping_address_id = sa.address_id WHERE o.order_id = ? AND o.user_id = ?");
    $stmt->execute([$order_id, $user_id]);
    $order_details = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order_details) {
        // Order not found or doesn't belong to the user
        header("Location: " . BASE_URL . "/products.php"); // Or user's order history page
        exit;
    }

    // Fetch order items
    $stmt_items = $pdo->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
    $stmt_items->execute([$order_id]);
    $order_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Order confirmation page error: " . $e->getMessage());
    // Display a generic error message to the user
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Order Confirmation | Liyas Mineral Water</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="assets/images/logo/logo-bg.jpg">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/checkout.css">
</head>
<body>
    <div class="order-confirmation-container">
        <?php if ($order_details): ?>
            <i class="fas fa-check-circle success-icon"></i>
            <h1>Order Placed Successfully!</h1>
            <p>Thank you for your purchase. Your order #<strong><?= htmlspecialchars($order_details['order_id']) ?></strong> has been placed.</p>
            <p>You will receive an email confirmation shortly.</p>

            <div class="order-details-section">
                <h2>Order Details</h2>
                <div class="detail-row"><span>Order ID:</span><span><?= htmlspecialchars($order_details['order_id']) ?></span></div>
                <div class="detail-row"><span>Order Date:</span><span><?= date('M d, Y H:i A', strtotime($order_details['order_date'])) ?></span></div>
                <div class="detail-row"><span>Total Amount:</span><span>₹<?= number_format($order_details['total_amount'], 2) ?></span></div>
                <div class="detail-row"><span>Payment Method:</span><span><?= htmlspecialchars(ucwords(str_replace('_', ' ', $order_details['payment_method']))) ?></span></div>
                <div class="detail-row"><span>Order Status:</span><span><?= htmlspecialchars(ucwords($order_details['status'])) ?></span></div>
            </div>

            <div class="order-details-section">
                <h2>Shipping Address</h2>
                <div class="shipping-details">
                    <p><strong><?= htmlspecialchars($order_details['sa_full_name']) ?></strong></p>
                    <p><?= htmlspecialchars($order_details['address_line_1']) ?><?php echo !empty($order_details['address_line_2']) ? ', ' . htmlspecialchars($order_details['address_line_2']) : ''; ?></p>
                    <p><?= htmlspecialchars($order_details['city']) ?>, <?= htmlspecialchars($order_details['state']) ?> - <?= htmlspecialchars($order_details['zip_code']) ?></p>
                    <p><?= htmlspecialchars($order_details['country']) ?></p>
                    <p>Phone: <?= htmlspecialchars($order_details['phone_number']) ?></p>
                </div>
            </div>

            <div class="order-details-section">
                <h2>Items Ordered</h2>
                <div class="item-list">
                    <?php foreach ($order_items as $item): ?>
                        <div class="item">
                            <img src="admin/uploads/products/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                            <div class="item-info">
                                <h4><?= htmlspecialchars($item['name']) ?></h4>
                                <p>Quantity: <?= htmlspecialchars($item['quantity']) ?></p>
                                <p>Price: ₹<?= number_format($item['price_at_purchase'], 2) ?></p>
                                <p>Subtotal: ₹<?= number_format($item['price_at_purchase'] * $item['quantity'], 2) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="total-summary">Total: ₹<?= number_format($order_details['total_amount'], 2) ?></div>
            </div>

        <?php else: ?>
            <i class="fas fa-exclamation-circle success-icon" style="color: #dc3545;"></i>
            <h1>Order Not Found!</h1>
            <p>We could not find details for the requested order.</p>
            <p>Please check your order history or contact support if you believe this is an error.</p>
        <?php endif; ?>

        <a href="<?php echo BASE_URL; ?>/products/index.php" class="back-home">Continue Shopping</a>
    </div>
</body>
</html>