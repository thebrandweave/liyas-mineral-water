<?php
// Determine the base path for links depending on the directory level
$base_path = (basename(dirname($_SERVER['SCRIPT_FILENAME'])) === 'users') ? '../' : './';

// Define the current page, defaulting to an empty string if not set
$current_page = $current_page ?? '';
?>
<!-- SIDEBAR -->
<section id="sidebar">
    <a href="<?= $base_path ?>index.php" class="brand">
        <i class='bx bxs-smile bx-lg'></i>
        <span class="text">Admin Panel</span>
    </a>
    <ul class="side-menu top">
        <li class="<?= ($current_page === 'dashboard') ? 'active' : '' ?>">
            <a href="<?= $base_path ?>index.php">
                <i class='bx bxs-dashboard bx-sm'></i>
                <span class="text">Dashboard</span>
            </a>
        </li>
        <li class="<?= ($current_page === 'products') ? 'active' : '' ?>">
            <a href="<?= $base_path ?>products.php">
                <i class='bx bxs-shopping-bag-alt bx-sm'></i>
                <span class="text">Products</span>
            </a>
        </li>
        <li class="<?= ($current_page === 'categories') ? 'active' : '' ?>"><a href="<?= $base_path ?>categories.php"><i class='bx bxs-category bx-sm'></i><span class="text">Categories</span></a></li>
        <li class="<?= ($current_page === 'users') ? 'active' : '' ?>">
            <a href="<?= $base_path ?>users/index.php">
                <i class='bx bxs-group bx-sm'></i>
                <span class="text">Users</span>
            </a>
        </li>
    </ul>
    <ul class="side-menu">
        <li><a href="#"><i class='bx bxs-cog bx-sm'></i><span class="text">Settings</span></a></li>
        <li><a href="<?= $base_path ?>logout.php" class="logout"><i class='bx bxs-log-out-circle bx-sm'></i><span class="text">Logout</span></a></li>
    </ul>
</section>
<!-- SIDEBAR -->