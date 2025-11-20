
<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$order_id = $_GET['id'] ?? null;
$page_title = "Order Details";
$current_page = "orders";

if (!$order_id) {
    header("Location: index.php");
    exit;
}

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $new_status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->execute([$new_status, $order_id]);
    $_SESSION['success_message'] = "Order status updated to " . ucfirst($new_status);
    header("Location: view.php?id=" . $order_id);
    exit;
}

// Fetch Order Info
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order not found");
}

// Fetch Order Items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name, p.product_id 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.product_id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <title><?= $page_title ?> - Admin Panel</title>
    <style>
        /* Layout for Details */
        .details-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-top: 20px;
        }
        
        @media(max-width: 900px) { .details-grid { grid-template-columns: 1fr; } }

        .card {
            background: var(--light);
            padding: 24px;
            border-radius: 20px;
            margin-bottom: 24px;
        }

        .card h3 { margin-bottom: 1rem; font-size: 1.2rem; color: var(--dark); }

        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .info-label { font-weight: 600; color: #888; }
        .info-value { font-weight: 500; color: var(--dark); text-align: right; }

        .status-select {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            width: 100%;
            margin-bottom: 10px;
        }

        .btn-update {
            background-color: #3C91E6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-weight: 600;
        }

        /* Table Styles */
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th { text-align: left; padding: 10px; border-bottom: 2px solid #eee; color: #555; }
        .items-table td { padding: 15px 10px; border-bottom: 1px solid #eee; }
        
        .total-row td { font-weight: 700; font-size: 1.1rem; border-top: 2px solid #eee; }
        
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-success { background-color: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <section id="content">
        <nav>
            <i class='bx bx-menu bx-sm'></i>
            <a href="#" class="nav-link"><?= $page_title ?></a>
        </nav>
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Order #<?= $order['order_id'] ?></h1>
                    <ul class="breadcrumb">
                        <li><a href="../index.php">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a href="index.php">Orders</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Details</a></li>
                    </ul>
                </div>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>

            <div class="details-grid">
                <div class="left-col">
                    <div class="card">
                        <h3>Order Items</h3>
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['product_name'] ?? 'Unknown Product') ?></td>
                                        <td>$<?= number_format($item['price_at_purchase'], 2) ?></td>
                                        <td>x<?= $item['quantity'] ?></td>
                                        <td>$<?= number_format($item['price_at_purchase'] * $item['quantity'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="total-row">
                                    <td colspan="3" style="text-align: right;">Grand Total:</td>
                                    <td>$<?= number_format($order['total_amount'], 2) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="right-col">
                    <div class="card">
                        <h3>Update Status</h3>
                        <form method="POST">
                            <select name="status" class="status-select">
                                <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                            <button type="submit" class="btn-update">Update Status</button>
                        </form>
                    </div>

                    <div class="card">
                        <h3>Customer Details</h3>
                        <div class="info-row">
                            <span class="info-label">Name:</span>
                            <span class="info-value"><?= htmlspecialchars($order['customer_name']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?= htmlspecialchars($order['customer_email']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Phone:</span>
                            <span class="info-value"><?= htmlspecialchars($order['customer_phone']) ?></span>
                        </div>
                        <div class="info-row" style="border:none; flex-direction:column; gap:5px;">
                            <span class="info-label">Address:</span>
                            <span class="info-value" style="text-align:left; line-height:1.4;"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </section>
    <script src="../assets/js/admin-script.js"></script>
</body>
</html>