<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');

// Determine action and get data for forms
$action = $_GET['action'] ?? 'view';
$product_id = $_GET['id'] ?? null;

$product = null;
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($action === 'edit' && $product_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        header("Location: products.php");
        exit;
    }
}

// Fetch all products for the main table
$products = [];
try {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $page_error = "Error fetching products: " . $e->getMessage();
}

$page_title = "Products";
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="assets/css/admin-style.css">
	<title>Manage Products - Admin Panel</title>
</head>
<body>
	<!-- SIDEBAR -->
	<section id="sidebar">
		<a href="index.php" class="brand"><i class='bx bxs-smile bx-lg'></i><span class="text">Admin Panel</span></a>
		<ul class="side-menu top">
			<li class="<?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>"><a href="index.php"><i class='bx bxs-dashboard bx-sm'></i><span class="text">Dashboard</span></a></li>
			<li class="<?= (basename($_SERVER['PHP_SELF']) == 'products.php') ? 'active' : '' ?>"><a href="products.php"><i class='bx bxs-shopping-bag-alt bx-sm'></i><span class="text">Products</span></a></li>
			<li class="<?= (basename($_SERVER['PHP_SELF']) == 'categories.php') ? 'active' : '' ?>"><a href="categories.php"><i class='bx bxs-category bx-sm'></i><span class="text">Categories</span></a></li>
			<li class="<?= (basename($_SERVER['PHP_SELF']) == 'users.php') ? 'active' : '' ?>"><a href="users/index.php"><i class='bx bxs-group bx-sm'></i><span class="text">Users</span></a></li>
		</ul>
		<ul class="side-menu">
			<li><a href="#"><i class='bx bxs-cog bx-sm'></i><span class="text">Settings</span></a></li>
			<li><a href="logout.php" class="logout"><i class='bx bxs-log-out-circle bx-sm'></i><span class="text">Logout</span></a></li>
		</ul>
	</section>
	<!-- SIDEBAR -->

	<!-- CONTENT -->
	<section id="content">
		<!-- NAVBAR -->
		<nav>
			<i class='bx bx-menu bx-sm'></i>
			<a href="#" class="nav-link"><?= $page_title ?></a>
			<form action="#"><div class="form-input"><input type="search" placeholder="Search..."><button type="submit" class="search-btn"><i class='bx bx-search'></i></button></div></form>
			<input type="checkbox" id="switch-mode" hidden>
			<label for="switch-mode" class="switch-mode"></label>
			<a href="#" class="notification"><i class='bx bxs-bell bx-tada-hover'></i><span class="num">8</span></a>
			<a href="#" class="profile"><img src="https://i.pravatar.cc/36?u=<?= urlencode($admin_name) ?>"></a>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>Manage Products</h1>
					<ul class="breadcrumb">
						<li><a href="index.php">Dashboard</a></li>
						<li><i class='bx bx-chevron-right'></i></li>
						<li><a class="active" href="products.php">Products</a></li>
					</ul>
				</div>
			</div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success" style="margin-bottom: 1rem;"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger" style="margin-bottom: 1rem;"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>

            <!-- Form for Adding/Editing Product -->
            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3><?= ($action === 'edit') ? 'Edit Product' : 'Add New Product' ?></h3>
                        <?php if ($action === 'edit'): ?>
                            <a href="products.php" class="btn-secondary" style="margin-left: auto;">Cancel Edit</a>
                        <?php endif; ?>
                    </div>
                    <form action="product_handler.php" method="POST" class="form-modern">
                        <input type="hidden" name="action" value="<?= ($action === 'edit') ? 'update' : 'create' ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['product_id']) ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="name">Product Name</label>
                            <input type="text" name="name" id="name" value="<?= htmlspecialchars($product['name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" rows="4"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="price">Price</label>
                                <input type="number" name="price" id="price" step="0.01" value="<?= htmlspecialchars($product['price'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="stock">Stock</label>
                                <input type="number" name="stock" id="stock" value="<?= htmlspecialchars($product['stock'] ?? '0') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="category_id">Category</label>
                                <select name="category_id" id="category_id">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['category_id'] ?>" <?= (($product['category_id'] ?? '') == $cat['category_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                         <div class="form-group-inline">
                            <label for="is_active">
                                <input type="checkbox" name="is_active" id="is_active" value="1" <?= ($product['is_active'] ?? true) ? 'checked' : '' ?>>
                                Active
                            </label>
                            <label for="featured">
                                <input type="checkbox" name="featured" id="featured" value="1" <?= ($product['featured'] ?? false) ? 'checked' : '' ?>>
                                Featured
                            </label>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn-primary"><?= ($action === 'edit') ? 'Update Product' : 'Add Product' ?></button>
                        </div>
                    </form>
                </div>
            </div>

			<!-- Products List -->
			<div class="table-data">
				<div class="order">
					<div class="head"><h3>All Products</h3></div>
					<table>
						<thead>
							<tr>
								<th>Product</th>
								<th>Category</th>
								<th>Price</th>
								<th>Stock</th>
								<th>Status</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
                            <?php if (empty($products)): ?>
                                <tr><td colspan="6" style="text-align: center; padding: 20px;">No products found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($products as $prod): ?>
                                    <tr>
                                        <td>
                                            <img src="https://i.pravatar.cc/36?u=<?= urlencode($prod['name']) ?>">
                                            <p><?= htmlspecialchars($prod['name']) ?></p>
                                        </td>
                                        <td><?= htmlspecialchars($prod['category_name'] ?? 'N/A') ?></td>
                                        <td>$<?= number_format($prod['price'], 2) ?></td>
                                        <td><?= $prod['stock'] ?></td>
                                        <td>
                                            <span class="status <?= $prod['is_active'] ? 'completed' : 'pending' ?>">
                                                <?= $prod['is_active'] ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </td>
                                        <td class="actions">
                                            <a href="products.php?action=edit&id=<?= $prod['product_id'] ?>" class="btn-action btn-edit"><i class='bx bxs-edit'></i></a>
                                            <form action="product_handler.php" method="POST" onsubmit="return confirm('Are you sure?');" style="display: inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="product_id" value="<?= $prod['product_id'] ?>">
                                                <button type="submit" class="btn-action btn-delete"><i class='bx bxs-trash'></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</main>
		<!-- MAIN -->
	</section>
	<!-- CONTENT -->

	<script src="assets/js/admin-script.js"></script>
</body>
</html>
<style>
    /* Modern Form Styles */
    .form-modern { display: flex; flex-direction: column; gap: 1.5rem; }
    .form-modern .form-group { display: flex; flex-direction: column; }
    .form-modern label { margin-bottom: 0.5rem; font-weight: 600; font-size: 14px; color: var(--dark-grey); }
    .form-modern input, .form-modern textarea, .form-modern select {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        border: 1px solid var(--grey);
        background-color: var(--grey);
        font-size: 1rem;
        color: var(--dark);
        transition: all 0.3s ease;
        width: 100%;
    }
    .form-modern input:focus, .form-modern textarea:focus, .form-modern select:focus {
        outline: none;
        border-color: var(--blue);
        background-color: var(--light);
        box-shadow: 0 0 0 3px var(--light-blue);
    }
    .form-modern .btn-primary {
        align-self: flex-start;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-size: 1rem;
        border: none;
        cursor: pointer;
        background-color: var(--blue);
        color: var(--light);
    }
    .btn-secondary {
        background-color: var(--dark-grey);
        color: var(--light);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
    }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }
    .form-group-inline { display: flex; gap: 2rem; align-items: center; }
    .form-group-inline label { display: flex; align-items: center; gap: 0.5rem; font-weight: normal; }
    .form-group-inline input[type="checkbox"] { width: auto; }

    /* Actions column styles */
    .actions { display: flex; gap: 0.5rem; align-items: center; }
    .btn-action {
        padding: 0.5rem;
        border-radius: 50%;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        text-decoration: none;
    }
    .btn-edit { background-color: var(--blue); }
    .btn-delete { background-color: var(--red); }

    /* Alert Styles */
    .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
    .alert-success { background-color: var(--light-green); color: var(--green); border: 1px solid var(--green); }
    .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
</style>
```

### 3. New Handler for Product Operations

This backend script will handle creating, updating, and deleting products securely.

**New File: `c:\xampp\htdocs\liyas-mineral-water\admin\product_handler.php`**

```diff