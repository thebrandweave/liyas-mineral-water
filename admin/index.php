<?php include 'includes/header.php'; ?>

<div class="page-header">
  <h1 class="page-title">Welcome back, <?= htmlspecialchars($_SESSION['admin_name']); ?>!</h1>
  <p class="page-subtitle">Track your activity and manage your content here</p>
</div>

<div class="dashboard-grid">
  <div class="dashboard-card">
    <div class="card-icon primary">
      <i class="fas fa-box"></i>
    </div>
    <div class="card-value">Products</div>
    <div class="card-label">Manage your products</div>
    <a href="products/index.php" class="btn btn-primary btn-sm btn-icon">
      <i class="fas fa-arrow-right"></i>
      <span>View Products</span>
    </a>
  </div>

  <div class="dashboard-card">
    <div class="card-icon success">
      <i class="fas fa-folder"></i>
    </div>
    <div class="card-value">Categories</div>
    <div class="card-label">Organize categories</div>
    <a href="#" class="btn btn-success btn-sm btn-icon">
      <i class="fas fa-arrow-right"></i>
      <span>View Categories</span>
    </a>
  </div>

  <div class="dashboard-card">
    <div class="card-icon warning">
      <i class="fas fa-users"></i>
    </div>
    <div class="card-value">Users</div>
    <div class="card-label">Manage users</div>
    <a href="#" class="btn btn-secondary btn-sm btn-icon">
      <i class="fas fa-arrow-right"></i>
      <span>View Users</span>
    </a>
  </div>

  <div class="dashboard-card">
    <div class="card-icon danger">
      <i class="fas fa-shopping-cart"></i>
    </div>
    <div class="card-value">Orders</div>
    <div class="card-label">View all orders</div>
    <a href="#" class="btn btn-secondary btn-sm btn-icon">
      <i class="fas fa-arrow-right"></i>
      <span>View Orders</span>
    </a>
  </div>
</div>

<?php include 'includes/footer.php'; ?>