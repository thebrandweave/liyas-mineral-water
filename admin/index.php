<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php'; // Ensures the user is logged in

// --- Placeholder Data ---
// The dashboard will now use data from your existing tables.

$total_products = 0;
$total_admins = 0;
$recent_products = [];
$visitors_count = 2834; // This is a static placeholder for now.

try {
    // Fetch total products count
    $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

    // Fetch total admins count
    $total_admins = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();

    // Fetch recently added products
    $recent_products_stmt = $pdo->query("SELECT name, created_at, stock FROM products ORDER BY created_at DESC LIMIT 5");
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
$current_page = basename($_SERVER['PHP_SELF']);
$page_title = "Dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Boxicons -->
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

	<!-- My CSS -->
	<link rel="stylesheet" href="assets/css/admin-style.css">

	<title>Admin Panel - Liyas Mineral Water</title>
</head>
<body>
	<!-- SIDEBAR -->
	<section id="sidebar">
		<a href="index.php" class="brand">
			<i class='bx bxs-smile bx-lg'></i>
			<span class="text">Admin Panel</span>
		</a>
		<ul class="side-menu top">
			<li class="<?= ($current_page === 'index.php') ? 'active' : '' ?>">
				<a href="index.php">
					<i class='bx bxs-dashboard bx-sm' ></i>
					<span class="text">Dashboard</span>
				</a>
			</li>
			<li class="<?= ($current_page === 'products.php') ? 'active' : '' ?>">
				<a href="products.php">
					<i class='bx bxs-shopping-bag-alt bx-sm' ></i>
					<span class="text">Products</span>
				</a>
			</li>
			<li class="<?= ($current_page === 'categories.php') ? 'active' : '' ?>"><a href="categories.php"><i class='bx bxs-category bx-sm'></i><span class="text">Categories</span></a></li>
			<li><a href="#"><i class='bx bxs-doughnut-chart bx-sm'></i><span class="text">Orders</span></a></li>
			<li class="<?= ($current_page === 'users.php') ? 'active' : '' ?>">
				<a href="users.php">
					<i class='bx bxs-group bx-sm' ></i>
					<span class="text">Users</span>
				</a>
			</li>
		</ul>
		<ul class="side-menu">
			<li>
				<a href="#">
					<i class='bx bxs-cog bx-sm'></i>
					<span class="text">Settings</span>
				</a>
			</li>
			<li>
				<a href="logout.php" class="logout">
					<i class='bx bxs-log-out-circle bx-sm'></i>
					<span class="text">Logout</span>
				</a>
			</li>
		</ul>
	</section>
	<!-- SIDEBAR -->

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
					<i class='bx bxs-calendar-check' ></i>
					<span class="text">
						<h3><?= $total_products ?></h3>
						<p>Total Products</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-group' ></i>
					<span class="text">
						<h3><?= $visitors_count ?></h3>
						<p>Visitors</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-dollar-circle' ></i>
					<span class="text">
						<h3><?= $total_admins ?></h3>
						<p>Admin Users</p>
					</span>
				</li>
			</ul>


			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Recent Products</h3>
						<i class='bx bx-search' ></i>
						<i class='bx bx-filter' ></i>
					</div>
					<table>
						<thead>
							<tr>
								<th>User</th>
								<th>Date Added</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
                            <?php if (empty($recent_products)): ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; padding: 20px;">No recent products found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_products as $product): ?>
                                    <tr>
                                        <td>
                                            <img src="https://i.pravatar.cc/36?u=<?= urlencode($product['name']) ?>">
                                            <p><?= htmlspecialchars($product['name']) ?></p>
                                        </td>
                                        <td><?= date('d-m-Y', strtotime($product['created_at'])) ?></td>
                                        <td><span class="status <?= ($product['stock'] > 0) ? 'completed' : 'pending' ?>"><?= ($product['stock'] > 0) ? 'In Stock' : 'Out of Stock' ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
						</tbody>
					</table>
				</div>
				<div class="todo">
					<div class="head">
						<h3>Todos</h3>
						<i class='bx bx-plus' ></i>
						<i class='bx bx-filter' ></i>
					</div>
					<ul class="todo-list">
                        <?php foreach ($todos as $todo): ?>
                            <li class="<?= $todo['completed'] ? 'completed' : 'not-completed' ?>">
                                <p><?= htmlspecialchars($todo['task']) ?></p>
                                <i class='bx bx-dots-vertical-rounded' ></i>
                            </li>
                        <?php endforeach; ?>
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



