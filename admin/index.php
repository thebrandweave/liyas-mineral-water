<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php include 'includes/header.php'; ?>
<h2>Dashboard</h2>
<p>Welcome, <?= htmlspecialchars($_SESSION['admin_name']); ?> 👋</p>
<ul>
  <li><a href="products/index.php">Manage Products</a></li>
  <li><a href="categories/index.php">Manage Categories</a></li>
  <li><a href="users/index.php">Manage Users</a></li>
</ul>
<?php include 'includes/footer.php'; ?>

</body>
</html>