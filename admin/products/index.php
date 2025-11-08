<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

// Fetch products with categories
$stmt = $pdo->query("
  SELECT p.*, c.name AS category_name
  FROM products p
  LEFT JOIN categories c ON p.category_id = c.category_id
  ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<div class="page-header">
  <h1 class="page-title">Products</h1>
  <p class="page-subtitle">Manage your product inventory</p>
</div>

<div class="products-header">
  <h2 class="products-title">All Products</h2>
  <a href="add.php" class="btn btn-primary btn-icon">
    <i class="fas fa-plus"></i>
    <span>Add Product</span>
  </a>
</div>

<?php if (empty($products)): ?>
  <div class="empty-state">
    <div class="empty-state-icon">
      <i class="fas fa-box-open"></i>
    </div>
    <div class="empty-state-text">No products found. Add your first product to get started!</div>
    <a href="add.php" class="btn btn-primary btn-icon">
      <i class="fas fa-plus"></i>
      <span>Add Product</span>
    </a>
  </div>
<?php else: ?>
  <div class="card">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Category</th>
          <th>Price</th>
          <th>Stock</th>
          <th>Status</th>
          <th>Featured</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): ?>
        <tr>
          <td><?= $p['product_id'] ?></td>
          <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
          <td><?= htmlspecialchars($p['category_name'] ?? '—') ?></td>
          <td><strong>₹<?= number_format($p['price'], 2) ?></strong></td>
          <td><?= $p['stock'] ?></td>
          <td>
            <span class="status-badge <?= $p['is_active'] ? 'active' : 'inactive' ?>">
              <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
            </span>
          </td>
          <td>
            <?php if ($p['featured']): ?>
              <span class="status-badge featured">⭐ Featured</span>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
          <td>
            <div class="table-actions">
              <a href="edit.php?id=<?= $p['product_id'] ?>" class="action-link edit">
                <i class="fas fa-edit"></i>
                <span>Edit</span>
              </a>
              <a href="delete.php?id=<?= $p['product_id'] ?>" class="action-link delete" onclick="return confirm('Are you sure you want to delete this product?')">
                <i class="fas fa-trash"></i>
                <span>Delete</span>
              </a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
