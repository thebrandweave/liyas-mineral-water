<?php include 'auth_check.php'; 
// Calculate base path relative to admin directory
// Check if we're in a subdirectory (like products/)
$script_path = dirname($_SERVER['SCRIPT_NAME']);
$is_subdir = (strpos($script_path, '/products') !== false || strpos($script_path, '/categories') !== false || strpos($script_path, '/users') !== false || strpos($script_path, '/qr-rewards') !== false);
$base_path = $is_subdir ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel - Liyas Mineral Water</title>
<link rel="stylesheet" href="<?= $base_path ?>assets/css/style.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/admin.css">
<?php if (strpos($_SERVER['PHP_SELF'], 'products') !== false): ?>
<link rel="stylesheet" href="<?= $base_path ?>assets/css/products.css">
<?php endif; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
<div class="admin-wrapper">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-logo">
        <span><i class="fas fa-tint"></i></span>
        <span>Liyas Admin</span>
      </div>
    </div>
    
    <nav class="sidebar-nav">
      <ul class="nav-menu">
        <li class="nav-item">
          <a href="<?= $base_path ?>index.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'products') === false ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span class="nav-label">Dashboard</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= $base_path ?>products/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'products') !== false ? 'active' : '' ?>">
            <i class="fas fa-box"></i>
            <span class="nav-label">Products</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= $base_path ?>qr-rewards/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'qr-rewards') !== false ? 'active' : '' ?>">
            <i class="fas fa-qrcode"></i>
            <span class="nav-label">QR Rewards</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-folder"></i>
            <span class="nav-label">Categories</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-users"></i>
            <span class="nav-label">Users</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-shopping-cart"></i>
            <span class="nav-label">Orders</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-cog"></i>
            <span class="nav-label">Settings</span>
          </a>
        </li>
      </ul>
    </nav>
  </aside>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Top Header -->
    <header class="top-header">
      <div class="header-left">
        <button class="mobile-toggle" onclick="toggleSidebar()">
          <i class="fas fa-bars"></i>
        </button>
        <h1 class="header-title">Admin Panel</h1>
      </div>
      <div class="header-right">
        <div class="user-profile">
          <div class="user-avatar">
            <?= strtoupper(substr($_SESSION['admin_name'], 0, 1)) ?>
          </div>
          <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($_SESSION['admin_name']) ?></div>
            <div class="user-role"><?= htmlspecialchars($_SESSION['admin_role'] ?? 'Admin') ?></div>
          </div>
          <div style="margin-left: 10px;">
            <a href="<?= $base_path ?>logout.php" style="color: var(--danger-color); font-size: 12px;">Logout</a>
          </div>
        </div>
      </div>
    </header>

    <!-- Content Area -->
    <div class="content-area">
