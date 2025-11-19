
<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');

// Determine current action (view, add, edit)
$action = $_GET['action'] ?? 'view';
$category_id = $_GET['id'] ?? null;

$category = null;
if ($action === 'edit' && $category_id) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$category) {
        // Category not found, redirect to view
        header("Location: categories.php");
        exit;
    }
}

// Fetch all categories for the view table
$categories = [];
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error if the table doesn't exist, though it should based on schema
    $page_error = "Error fetching categories: " . $e->getMessage();
}

$page_title = "Categories";
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="assets/css/admin-style.css">
	<title>Manage Categories - Admin Panel</title>
</head>
<body>
	<!-- SIDEBAR -->
	<section id="sidebar">
		<a href="index.php" class="brand">
			<i class='bx bxs-smile bx-lg'></i>
			<span class="text">Admin Panel</span>
		</a>
		<ul class="side-menu top">
			<li class="<?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>"><a href="index.php"><i class='bx bxs-dashboard bx-sm'></i><span class="text">Dashboard</span></a></li>
			<li class="<?= (basename($_SERVER['PHP_SELF']) == 'products.php') ? 'active' : '' ?>"><a href="products.php"><i class='bx bxs-shopping-bag-alt bx-sm'></i><span class="text">Products</span></a></li>
			<li class="<?= (basename($_SERVER['PHP_SELF']) == 'categories.php') ? 'active' : '' ?>"><a href="categories.php"><i class='bx bxs-category bx-sm'></i><span class="text">Categories</span></a></li>
			<li class="<?= (basename($_SERVER['PHP_SELF']) == 'users.php') ? 'active' : '' ?>"><a href="users.php"><i class='bx bxs-group bx-sm'></i><span class="text">Users</span></a></li>
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
			<form action="#">
				<div class="form-input">
					<input type="search" placeholder="Search...">
					<button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
				</div>
			</form>
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
					<h1>Manage Categories</h1>
					<ul class="breadcrumb">
						<li><a href="index.php">Dashboard</a></li>
						<li><i class='bx bx-chevron-right'></i></li>
						<li><a class="active" href="categories.php">Categories</a></li>
					</ul>
				</div>
			</div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success" style="margin-bottom: 1rem;"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger" style="margin-bottom: 1rem;"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>

            <!-- Form for Adding/Editing -->
            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3><?= ($action === 'edit') ? 'Edit Category' : 'Add New Category' ?></h3>
                        <?php if ($action === 'edit'): ?>
                            <a href="categories.php" class="btn-secondary" style="margin-left: auto;">Cancel Edit</a>
                        <?php endif; ?>
                    </div>
                    <form action="category_handler.php" method="POST" class="form-modern">
                        <input type="hidden" name="action" value="<?= ($action === 'edit') ? 'update' : 'create' ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="category_id" value="<?= htmlspecialchars($category['category_id']) ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="name">Category Name</label>
                            <input type="text" name="name" id="name" value="<?= htmlspecialchars($category['name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" rows="3"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn-primary"><?= ($action === 'edit') ? 'Update Category' : 'Add Category' ?></button>
                        </div>
                    </form>
                </div>
            </div>

			<!-- Category List -->
			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>All Categories</h3>
						<i class='bx bx-search'></i>
						<i class='bx bx-filter'></i>
					</div>
					<table>
						<thead>
							<tr>
								<th>Name</th>
								<th>Description</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
                            <?php if (empty($categories)): ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; padding: 20px;">No categories found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($categories as $cat): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($cat['name']) ?></td>
                                        <td><?= htmlspecialchars(substr($cat['description'], 0, 50)) . (strlen($cat['description']) > 50 ? '...' : '') ?></td>
                                        <td class="actions">
                                            <a href="categories.php?action=edit&id=<?= $cat['category_id'] ?>" class="btn-action btn-edit"><i class='bx bxs-edit'></i> Edit</a>
                                            <form action="category_handler.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?');" style="display: inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
                                                <button type="submit" class="btn-action btn-delete"><i class='bx bxs-trash'></i> Delete</button>
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
    .form-modern { display: flex; flex-direction: column; gap: 1rem; }
    .form-modern .form-group { display: flex; flex-direction: column; }
    .form-modern label { margin-bottom: 0.5rem; font-weight: 600; font-size: 14px; color: var(--dark-grey); }
    .form-modern input, .form-modern textarea {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        border: 1px solid var(--grey);
        background-color: var(--grey);
        font-size: 1rem;
        color: var(--dark);
        transition: all 0.3s ease;
    }
    .form-modern input:focus, .form-modern textarea:focus {
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
    }
    
    .btn-secondary {
        background-color: var(--dark-grey);
        color: var(--light);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
    }

    /* Actions column styles */
    .actions { display: flex; gap: 0.5rem; align-items: center; }
    .btn-action {
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        text-decoration: none;
    }
    .btn-edit { background-color: var(--blue); }
    .btn-delete { background-color: var(--red); }
</style>
