<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "orders";
$page_title = "Manage Orders";

// Fetch all orders
$orders = [];
try {
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <title><?= $page_title ?> - Admin Panel</title>
    <style>
        /* --- BASE BUTTON STYLE --- */
        .button {
            width: 50px; height: 50px; border-radius: 50%; background-color: rgb(20, 20, 20); border: none; font-weight: 600; display: flex; align-items: center; justify-content: center; box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.164); cursor: pointer; transition-duration: .3s; overflow: hidden; position: relative; text-decoration: none !important;
        }
        .button .svgIcon, .button::before { pointer-events: none; }
        .svgIcon { width: 17px; transition-duration: .3s; }
        .svgIcon path { fill: white; }
        .button:hover { width: 120px; border-radius: 50px; transition-duration: .3s; background-color: rgb(255, 69, 69); align-items: center; }
        .button:hover .svgIcon { width: 20px; transition-duration: .3s; transform: translateY(60%); }
        .button::before { position: absolute; top: -20px; content: "Delete"; color: white; transition-duration: .3s; font-size: 2px; }
        .button:hover::before { font-size: 13px; opacity: 1; transform: translateY(30px); transition-duration: .3s; }

        /* View Button (Blue) */
        .view-btn:hover { background-color: #3b82f6; }
        .view-btn::before { content: "View Details"; }
        
        .action-column { min-width: 150px; width: 150px; }
        
        /* Status Badges */
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; color: white; text-transform: uppercase; }
        .status-pending { background-color: #f59e0b; }
        .status-processing { background-color: #3b82f6; }
        .status-shipped { background-color: #8b5cf6; }
        .status-delivered { background-color: #10b981; }
        .status-cancelled { background-color: #ef4444; }
        
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <section id="content">
        <nav>
            <i class='bx bx-menu bx-sm'></i>
            <a href="#" class="nav-link"><?= $page_title ?></a>
            <a href="#" class="profile" style="margin-left: auto;">
                <img src="https://i.pravatar.cc/36?u=<?= urlencode($admin_name) ?>" alt="Profile">
            </a>
        </nav>
        <main>
            <div class="head-title">
                <div class="left">
                    <h1><?= $page_title ?></h1>
                    <ul class="breadcrumb">
                        <li><a href="../index.php">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Orders</a></li>
                    </ul>
                </div>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>

            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3>Order History</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="action-column">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr><td colspan="6" style="text-align: center; padding: 20px;">No orders found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?= $order['order_id'] ?></td>
                                        <td>
                                            <p><?= htmlspecialchars($order['customer_name']) ?></p>
                                            <small style="color: #888;"><?= htmlspecialchars($order['customer_phone']) ?></small>
                                        </td>
                                        <td>$<?= number_format($order['total_amount'], 2) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= $order['status'] ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                                        <td class="table-actions action-column">
                                            <a href="view.php?id=<?= $order['order_id'] ?>" class="button view-btn" title="View Order">
                                                <svg class="svgIcon" viewBox="0 0 576 512"><path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"/></svg>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </section>
    <script src="../assets/js/admin-script.js"></script>
</body>
</html>