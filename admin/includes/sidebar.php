<?php
// Determine base path: Check if we are in a subfolder
$current_dir = basename(dirname($_SERVER['SCRIPT_FILENAME']));
// Handle all admin subdirectories: users, orders, products, categories, qr-rewards
$base_path = (in_array($current_dir, ['users', 'orders', 'products', 'categories', 'qr-rewards'])) ? '../' : './';

// Define the current page, defaulting to an empty string if not set
$current_page = $current_page ?? '';

// Calculate counts for badges
$products_count = 0;
$categories_count = 0;
$orders_count = 0;
$users_count = 0;

try {
    if (isset($pdo)) {
        $products_count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $categories_count = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        $orders_count = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
        $users_count = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    }
} catch (PDOException $e) {
    // Handle error silently
}

// Determine current page for active state
$current_file = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>

<button id="sidebarToggle" class="sidebar-toggle" aria-label="Toggle sidebar">
    <i class='bx bx-menu'></i>
</button>
<div class="sidebar">
    <div class="logo" style="justify-content: center;">
        <span class="logo-text">Liyas</span>
    </div>
    
    <div class="search-box">
        <div class="search-wrapper">
            <i class='bx bx-search search-icon'></i>
            <input type="text" class="search-input" placeholder="Search...">
        </div>
    </div>
    
    <nav class="nav-menu">
        <a href="<?= $base_path ?>index.php" class="nav-item <?= ($current_file == 'index.php' && $current_dir == 'admin') ? 'active' : '' ?>">
            <i class='bx bx-home'></i>
            <span>Dashboard</span>
        </a>
        
        <a href="<?= $base_path ?>products/index.php" class="nav-item <?= ($current_dir == 'products' || $current_page === 'products') ? 'active' : '' ?>">
            <i class='bx bx-shopping-bag'></i>
            <span>Products</span>
            <?php if($products_count > 0): ?>
                <span class="nav-badge"><?= $products_count ?></span>
            <?php endif; ?>
        </a>
        
        <a href="<?= $base_path ?>categories/index.php" class="nav-item <?= ($current_dir == 'categories' || $current_page === 'categories') ? 'active' : '' ?>">
            <i class='bx bx-category'></i>
            <span>Categories</span>
            <?php if($categories_count > 0): ?>
                <span class="nav-badge"><?= $categories_count ?></span>
            <?php endif; ?>
        </a>
        
        <a href="<?= $base_path ?>orders/index.php" class="nav-item <?= ($current_dir == 'orders' || $current_page === 'orders') ? 'active' : '' ?>">
            <i class='bx bx-cart'></i>
            <span>Orders</span>
            <?php if($orders_count > 0): ?>
                <span class="badge-count"><?= $orders_count ?></span>
            <?php endif; ?>
        </a>
        
        <a href="<?= $base_path ?>users/index.php" class="nav-item <?= ($current_dir == 'users' || $current_page === 'users') ? 'active' : '' ?>">
            <i class='bx bx-group'></i>
            <span>Users</span>
            <?php if($users_count > 0): ?>
                <span class="nav-badge"><?= $users_count ?></span>
            <?php endif; ?>
        </a>
        
        <a href="<?= $base_path ?>qr-rewards/index.php" class="nav-item <?= ($current_dir == 'qr-rewards' || $current_page === 'qr-rewards') ? 'active' : '' ?>">
            <i class='bx bx-qr'></i>
            <span>QR Rewards</span>
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
    var body = document.body;

    // Collapse sidebar by default on small screens
    if (window.matchMedia && window.matchMedia('(max-width: 768px)').matches) {
        body.classList.add('sidebar-collapsed');
    }

    var toggle = document.getElementById('sidebarToggle');
    if (!toggle) return;

    toggle.addEventListener('click', function () {
        body.classList.toggle('sidebar-collapsed');
    });
})();
</script>