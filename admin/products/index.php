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
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Products</title>
<link rel="stylesheet" href="../../assets/css/admin.css">
<style>
table { width:100%; border-collapse:collapse; margin-top:20px; }
th, td { padding:10px; border:1px solid #ccc; text-align:left; }
a.btn { padding:8px 12px; background:#007bff; color:#fff; border-radius:4px; text-decoration:none; }
a.btn:hover { background:#0056b3; }
</style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<h2>Products</h2>
<a href="add.php" class="btn">+ Add Product</a>
<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Category</th>
      <th>Price</th>
      <th>Stock</th>
      <th>Active</th>
      <th>Featured</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($products as $p): ?>
    <tr>
      <td><?= $p['product_id'] ?></td>
      <td><?= htmlspecialchars($p['name']) ?></td>
      <td><?= htmlspecialchars($p['category_name'] ?? '—') ?></td>
      <td>₹<?= number_format($p['price'], 2) ?></td>
      <td><?= $p['stock'] ?></td>
      <td><?= $p['is_active'] ? '✅' : '❌' ?></td>
      <td><?= $p['featured'] ? '⭐' : '—' ?></td>
      <td>
        <a href="edit.php?id=<?= $p['product_id'] ?>">Edit</a> |
        <a href="delete.php?id=<?= $p['product_id'] ?>" onclick="return confirm('Delete this product?')">Delete</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php include '../includes/footer.php'; ?>
</body>
</html>
