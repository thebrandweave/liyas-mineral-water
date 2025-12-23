<?php
// Detect base path (admin subfolders)
$current_dir_name = basename(dirname($_SERVER['SCRIPT_FILENAME']));

$base_path = (in_array($current_dir_name, [
    'users',
    'orders',
    'products',
    'categories',
    'qr-rewards',
    'activity-logs',
    'notifications',
    'subscriptions',
    'advertisements',
    'reviews',
    'social-links'
])) ? '../' : './';

// Current page (passed from each page)
$current_page = $current_page ?? '';

// Badge counts
$products_count = 0;
$categories_count = 0;
$orders_count = 0;
$users_count = 0;
$notifications_count = 0;
$subs_count = 0;
$ads_count = 0;
$reviews_pending_count = 0;
$social_count = 0;

try {
    if (isset($pdo)) {
        $products_count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $categories_count = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        $orders_count = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
        $users_count = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();

        // Newsletter subscribers
        $subs_count = $pdo->query("
            SELECT COUNT(*) FROM newsletter_subscriptions WHERE status='subscribed'
        ")->fetchColumn();

        // Advertisements
        $ads_count = $pdo->query("SELECT COUNT(*) FROM advertisements")->fetchColumn();

        // Pending reviews
        $reviews_pending_count = $pdo->query("
            SELECT COUNT(*) FROM reviews WHERE status='pending'
        ")->fetchColumn();

        // Social links
        $social_count = $pdo->query("
            SELECT COUNT(*) FROM social_links
        ")->fetchColumn();

        // Unread admin notifications
        $stmtNotif = $pdo->prepare("
            SELECT COUNT(*)
            FROM notifications
            WHERE recipient_type='admin'
              AND is_read=0
              AND admin_id = ?
        ");
        $stmtNotif->execute([$_SESSION['admin_id'] ?? 0]);
        $notifications_count = $stmtNotif->fetchColumn();
    }
} catch (PDOException $e) {
    // silent fail
}

// Current file/dir
$current_file = basename($_SERVER['PHP_SELF']);
$current_dir  = basename(dirname($_SERVER['PHP_SELF']));
?>

<button id="sidebarToggle" class="sidebar-toggle" aria-label="Toggle sidebar">
    <i class='bx bx-menu'></i>
</button>

<div class="sidebar">
    <div class="logo" style="justify-content:center;">
        <span class="logo-text">Liyas</span>
    </div>

    <div class="search-box">
        <div class="search-wrapper">
            <i class='bx bx-search search-icon'></i>
            <input type="text" class="search-input" placeholder="Search...">
        </div>
    </div>

    <nav class="nav-menu">

        <!-- DASHBOARD -->
        <a href="<?= $base_path ?>index.php"
           class="nav-item <?= ($current_file=='index.php' && $current_dir=='admin')?'active':'' ?>">
            <i class='bx bx-home'></i>
            <span>Dashboard</span>
        </a>

        <!-- PRODUCTS -->
        <a href="<?= $base_path ?>products/index.php"
           class="nav-item <?= ($current_dir=='products'||$current_page==='products')?'active':'' ?>">
            <i class='bx bx-shopping-bag'></i>
            <span>Products</span>
            <?php if ($products_count>0): ?>
                <span class="nav-badge"><?= $products_count ?></span>
            <?php endif; ?>
        </a>

        <!-- CATEGORIES -->
        <a href="<?= $base_path ?>categories/index.php"
           class="nav-item <?= ($current_dir=='categories'||$current_page==='categories')?'active':'' ?>">
            <i class='bx bx-category'></i>
            <span>Categories</span>
            <?php if ($categories_count>0): ?>
                <span class="nav-badge"><?= $categories_count ?></span>
            <?php endif; ?>
        </a>

        <!-- ORDERS -->
        <a href="<?= $base_path ?>orders/index.php"
           class="nav-item <?= ($current_dir=='orders'||$current_page==='orders')?'active':'' ?>">
            <i class='bx bx-cart'></i>
            <span>Orders</span>
            <?php if ($orders_count>0): ?>
                <span class="badge-count"><?= $orders_count ?></span>
            <?php endif; ?>
        </a>

        <!-- ADVERTISEMENTS -->
        <a href="<?= $base_path ?>advertisements/index.php"
           class="nav-item <?= ($current_dir=='advertisements'||$current_page==='advertisements')?'active':'' ?>">
            <i class='bx bx-image'></i>
            <span>Advertisements</span>
            <?php if ($ads_count>0): ?>
                <span class="nav-badge"><?= $ads_count ?></span>
            <?php endif; ?>
        </a>

        <!-- REVIEWS -->
        <a href="<?= $base_path ?>reviews/index.php"
           class="nav-item <?= ($current_dir=='reviews'||$current_page==='reviews')?'active':'' ?>">
            <i class='bx bx-star'></i>
            <span>Reviews</span>
            <?php if ($reviews_pending_count>0): ?>
                <span class="badge-count"><?= $reviews_pending_count ?></span>
            <?php endif; ?>
        </a>

        <!-- SOCIAL MEDIA -->
        <a href="<?= $base_path ?>social-links/index.php"
           class="nav-item <?= ($current_dir=='social-links'||$current_page==='social-links')?'active':'' ?>">
            <i class='bx bx-share-alt'></i>
            <span>Social Media</span>
            <?php if ($social_count>0): ?>
                <span class="nav-badge"><?= $social_count ?></span>
            <?php endif; ?>
        </a>

        <!-- NEWSLETTER -->
        <a href="<?= $base_path ?>subscriptions/index.php"
           class="nav-item <?= ($current_dir=='subscriptions'||$current_page==='subscriptions')?'active':'' ?>">
            <i class='bx bx-envelope'></i>
            <span>Newsletter</span>
            <?php if ($subs_count>0): ?>
                <span class="nav-badge"><?= $subs_count ?></span>
            <?php endif; ?>
        </a>

        <!-- NOTIFICATIONS -->
        <a href="<?= $base_path ?>notifications/index.php"
           class="nav-item <?= ($current_dir=='notifications'||$current_page==='notifications')?'active':'' ?>">
            <i class='bx bx-bell'></i>
            <span>Notifications</span>
            <?php if ($notifications_count>0): ?>
                <span class="badge-count"><?= $notifications_count ?></span>
            <?php endif; ?>
        </a>

        <!-- USERS -->
        <a href="<?= $base_path ?>users/index.php"
           class="nav-item <?= ($current_dir=='users'||$current_page==='users')?'active':'' ?>">
            <i class='bx bx-group'></i>
            <span>Users</span>
            <?php if ($users_count>0): ?>
                <span class="nav-badge"><?= $users_count ?></span>
            <?php endif; ?>
        </a>

        <!-- QR REWARDS -->
        <a href="<?= $base_path ?>qr-rewards/index.php"
           class="nav-item <?= ($current_dir=='qr-rewards'||$current_page==='qr-rewards')?'active':'' ?>">
            <i class='bx bx-qr'></i>
            <span>QR Rewards</span>
        </a>

        <!-- ACTIVITY LOGS -->
        <a href="<?= $base_path ?>activity-logs/index.php"
           class="nav-item <?= ($current_dir=='activity-logs'||$current_page==='activity-logs')?'active':'' ?>">
            <i class='bx bx-history'></i>
            <span>Activity Logs</span>
        </a>

    </nav>

    <div class="sidebar-footer">
        <a href="<?= $base_path ?>logout.php" class="nav-item">
            <i class='bx bx-log-out'></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<script>
(function () {
    const toggle = document.getElementById('sidebarToggle');
    if (!toggle) return;
    toggle.addEventListener('click', function () {
        document.body.classList.toggle('sidebar-open');
    });
})();
</script>
