<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php'; // Ensures the user is logged in

// --- Placeholder Data ---
// The dashboard will now use data from your existing tables.

$total_products = 0;
$total_categories = 0;
$total_admins = 0;
$total_orders = 0;
$recent_products = [];
$visitors_count = 2834; // This is a static placeholder for now.

try {
    // Fetch total products count
    $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

    // Fetch total categories count
    $total_categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

    // Fetch total admins count
    $total_admins = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();

    // Fetch total orders count
    $total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

    // Fetch recently added products
    $recent_products_stmt = $pdo->query("SELECT product_id, name, created_at, price FROM products ORDER BY created_at DESC LIMIT 5");
    $recent_products = $recent_products_stmt ? $recent_products_stmt->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (PDOException $e) {
    // If tables don't exist, we'll just show 0s and an empty list.
    // This prevents the page from crashing.
}

// To-Do List (static example)
$todos = [
    ['task' => 'Check Inventory', 'completed' => true],
    ['task' => 'Manage Delivery Team', 'completed' => true],
    ['task' => 'Contact Selma: Confirm Delivery', 'completed' => false],
    ['task' => 'Update Shop Catalogue', 'completed' => true],
    ['task' => 'Count Profit Analytics', 'completed' => false],
];

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "dashboard";
$page_title = "Dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="assets/css/admin-style.css">
	<title>Admin Panel - Liyas Mineral Water</title>
	<style>
		/* Make stat boxes clickable with hover effect */
		.box-info li a {
			transition: all 0.3s ease;
		}
		.box-info li a:hover {
			transform: translateY(-5px);
			box-shadow: 0 5px 15px rgba(0,0,0,0.1);
		}
		
		/* Quick links hover effect */
		.todo-list li a {
			transition: all 0.2s ease;
		}
		.todo-list li a:hover {
			background-color: #f5f5f5;
			padding-left: 5px;
		}
		
		/* Table row hover */
		.table-data table tbody tr {
			transition: background-color 0.2s ease;
		}
		.table-data table tbody tr:hover {
			background-color: #f9f9f9;
		}
	</style>
</head>
<body>
	<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

	<!-- CONTENT -->
	<section id="content">
		<!-- NAVBAR -->
		<nav>
			<i class='bx bx-menu bx-sm' ></i>
			<a href="#" class="nav-link"><?= $page_title ?></a>
			<form action="#">
				<div class="form-input">
					<input type="search" placeholder="Search...">
					<button type="submit" class="search-btn"><i class='bx bx-search' ></i></button>
				</div>
			</form>
			<input type="checkbox" id="switch-mode" hidden>
			<label for="switch-mode" class="switch-mode"></label>
			<a href="#" class="notification">
				<i class='bx bxs-bell bx-tada-hover' ></i>
				<span class="num">8</span>
			</a>
			<a href="#" class="profile">
				<img src="https://i.pravatar.cc/36?u=<?= urlencode($admin_name) ?>" alt="Profile">
			</a>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>Dashboard</h1>
					<ul class="breadcrumb">
						<li>
							<a href="#">Dashboard</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="#">Home</a>
						</li>
					</ul>
				</div>
				<a href="#" class="btn-download">
					<i class='bx bxs-cloud-download'></i>
					<span class="text">Download PDF</span>
				</a>
			</div>

			<ul class="box-info">
				<li>
					<a href="products/index.php" style="text-decoration: none; color: inherit; display: flex; align-items: center; width: 100%;">
						<i class='bx bxs-shopping-bag-alt' ></i>
						<span class="text">
							<h3><?= $total_products ?></h3>
							<p>Total Products</p>
						</span>
					</a>
				</li>
				<li>
					<a href="categories/index.php" style="text-decoration: none; color: inherit; display: flex; align-items: center; width: 100%;">
						<i class='bx bxs-category' ></i>
						<span class="text">
							<h3><?= $total_categories ?></h3>
							<p>Categories</p>
						</span>
					</a>
				</li>
				<li>
					<a href="users/index.php" style="text-decoration: none; color: inherit; display: flex; align-items: center; width: 100%;">
						<i class='bx bxs-group' ></i>
						<span class="text">
							<h3><?= $total_admins ?></h3>
							<p>Admin Users</p>
						</span>
					</a>
				</li>
				<li>
					<a href="orders/index.php" style="text-decoration: none; color: inherit; display: flex; align-items: center; width: 100%;">
						<i class='bx bxs-cart-alt' ></i>
						<span class="text">
							<h3><?= $total_orders ?></h3>
							<p>Total Orders</p>
						</span>
					</a>
				</li>
			</ul>


			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Recent Products</h3>
						<a href="products/index.php" style="text-decoration: none; color: inherit;">
							<i class='bx bx-search' title="View All Products"></i>
						</a>
						<a href="products/add.php" style="text-decoration: none; color: inherit;">
							<i class='bx bx-plus' title="Add New Product"></i>
						</a>
					</div>
					<table>
						<thead>
							<tr>
								<th>Product Name</th>
								<th>Price</th>
								<th>Date Added</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
                            <?php if (empty($recent_products)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 20px;">
                                        No recent products found. 
                                        <a href="products/add.php" style="color: #4CAF50; text-decoration: none; margin-left: 10px;">Add Product</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_products as $product): ?>
                                    <tr>
                                        <td>
                                            <img src="https://i.pravatar.cc/36?u=<?= urlencode($product['name']) ?>">
                                            <p><?= htmlspecialchars($product['name']) ?></p>
                                        </td>
                                        <td>$<?= number_format($product['price'], 2) ?></td>
                                        <td><?= date('d M, Y', strtotime($product['created_at'])) ?></td>
                                        <td>
                                            <a href="products/edit.php?id=<?= $product['product_id'] ?>" style="color: #3b82f6; text-decoration: none;">
                                                <i class='bx bx-edit' title="Edit"></i> Edit
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
						</tbody>
					</table>
					<div style="padding: 15px; text-align: center; border-top: 1px solid #eee;">
						<a href="products/index.php" style="color: #4CAF50; text-decoration: none; font-weight: 600;">
							View All Products <i class='bx bx-right-arrow-alt'></i>
						</a>
					</div>
				</div>
				<div class="todo">
					<div class="head">
						<h3>Quick Links</h3>
						<i class='bx bx-link-external' ></i>
					</div>
					<ul class="todo-list" style="list-style: none; padding: 0;">
						<li style="padding: 15px; border-bottom: 1px solid #eee;">
							<a href="products/index.php" style="text-decoration: none; color: inherit; display: flex; align-items: center; justify-content: space-between;">
								<span style="display: flex; align-items: center; gap: 10px;">
									<i class='bx bxs-shopping-bag-alt' style="color: #4CAF50;"></i>
									<p style="margin: 0;">Manage Products</p>
								</span>
								<i class='bx bx-right-arrow-alt'></i>
							</a>
						</li>
						<li style="padding: 15px; border-bottom: 1px solid #eee;">
							<a href="categories/index.php" style="text-decoration: none; color: inherit; display: flex; align-items: center; justify-content: space-between;">
								<span style="display: flex; align-items: center; gap: 10px;">
									<i class='bx bxs-category' style="color: #2196F3;"></i>
									<p style="margin: 0;">Manage Categories</p>
								</span>
								<i class='bx bx-right-arrow-alt'></i>
							</a>
						</li>
						<li style="padding: 15px; border-bottom: 1px solid #eee;">
							<a href="users/index.php" style="text-decoration: none; color: inherit; display: flex; align-items: center; justify-content: space-between;">
								<span style="display: flex; align-items: center; gap: 10px;">
									<i class='bx bxs-group' style="color: #FF9800;"></i>
									<p style="margin: 0;">Manage Users</p>
								</span>
								<i class='bx bx-right-arrow-alt'></i>
							</a>
						</li>
						<li style="padding: 15px;">
							<a href="orders/index.php" style="text-decoration: none; color: inherit; display: flex; align-items: center; justify-content: space-between;">
								<span style="display: flex; align-items: center; gap: 10px;">
									<i class='bx bxs-cart-alt' style="color: #9C27B0;"></i>
									<p style="margin: 0;">View Orders</p>
								</span>
								<i class='bx bx-right-arrow-alt'></i>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</main>
		<!-- MAIN -->
	</section>
	<!-- CONTENT -->
	
	<script src="assets/js/admin-script.js"></script>
</body>
</html>
